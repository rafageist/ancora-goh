#!/usr/bin/env bash
# Install the Áncora Drupal site from scratch into a disposable PostgreSQL
# cluster and test it (issue #69).
#
#   dev/sql/tools/with-ephemeral-pg.sh modern/scripts/install-disposable.sh [options]
#
# Options:
#   --bootstrap       Build the configuration from the minimal profile and
#                     export it to config/sync (maintainers, after a change
#                     that adds configuration). Default: install from the
#                     committed config/sync (drush site:install --existing-config).
#   --skip-composer   Do not run composer install (vendor/ is already current).
#   --skip-tests      Install only: no phpcs, PHPUnit or HTTP smoke test.
#   --keep-server     After the tests, keep the 127.0.0.1 server running until
#                     Ctrl-C (manual inspection; the admin password file path
#                     is printed, never the password).
#
# Steps: refuse anything but a verified disposable cluster; create the
# database role, the site and test databases and the pg_trgm extension;
# write random secrets (database password, hash salt, admin password) to
# 0600 files in a temporary directory; composer install; site install;
# prove the configuration is deterministic (the export equals config/sync);
# phpcs; PHPUnit Kernel and Functional tests; HTTP smoke test on 127.0.0.1.
# Everything temporary is deleted on exit, and the cluster is deleted by
# with-ephemeral-pg.sh.
set -euo pipefail

bootstrap=0 skip_composer=0 skip_tests=0 keep_server=0
for arg in "$@"; do
  case "$arg" in
    --bootstrap) bootstrap=1 ;;
    --skip-composer) skip_composer=1 ;;
    --skip-tests) skip_tests=1 ;;
    --keep-server) keep_server=1 ;;
    *) echo "unknown option: $arg" >&2; exit 2 ;;
  esac
done

project=$(cd "$(dirname "$0")/.." && pwd -P)
repo=$(dirname "$project")
# shellcheck source=../../dev/sql/tools/disposable-guard.sh
. "$repo/dev/sql/tools/disposable-guard.sh"
agoh_require_disposable || exit 3

started=$SECONDS
step() { printf '\n==> [%3ss] %s\n' "$((SECONDS - started))" "$*"; }

work=$(mktemp -d "${TMPDIR:-/tmp}/ancora-site.XXXXXX")
server_pid=""
cleanup() {
  if [ -n "$server_pid" ]; then kill "$server_pid" 2>/dev/null || true; wait "$server_pid" 2>/dev/null || true; fi
  rm -rf "$work"
  # Drupal hardens sites/default to read-only when it installs (it should on a
  # server); give the developer's working tree its write bit back.
  chmod u+w "$project/web/sites/default" "$project/web/sites/default/settings.php" 2>/dev/null || true
}
trap cleanup EXIT
mkdir -p "$work/secrets" "$work/private" "$work/tmp" "$work/export"
chmod 700 "$work/secrets"

random_hex() { od -An -N"$1" -tx1 /dev/urandom | tr -d ' \n'; }
(umask 077
  random_hex 24 >"$work/secrets/db-password"
  random_hex 48 >"$work/secrets/hash-salt"
  random_hex 16 >"$work/secrets/admin-password")

step "PostgreSQL: role, databases and pg_trgm ($(psql -X -At -d postgres -c 'SHOW server_version'))"
psql -X -q -v ON_ERROR_STOP=1 -d postgres <<SQL
\set db_password \`cat "$work/secrets/db-password"\`
CREATE ROLE ancora LOGIN PASSWORD :'db_password';
CREATE DATABASE ancora OWNER ancora ENCODING 'UTF8' TEMPLATE template0;
CREATE DATABASE ancora_test OWNER ancora ENCODING 'UTF8' TEMPLATE template0;
\c ancora
CREATE EXTENSION IF NOT EXISTS pg_trgm;
\c ancora_test
CREATE EXTENSION IF NOT EXISTS pg_trgm;
SQL

# The site's configuration, exactly as a deployment provides it.
export ANCORA_ENVIRONMENT=production
export ANCORA_DB_HOST="$PGHOST" ANCORA_DB_PORT="$PGPORT" ANCORA_DB_NAME=ancora ANCORA_DB_USER=ancora
export ANCORA_DB_PASSWORD_FILE="$work/secrets/db-password"
export ANCORA_HASH_SALT_FILE="$work/secrets/hash-salt"
export ANCORA_TRUSTED_HOSTS=127.0.0.1,localhost
export ANCORA_PRIVATE_PATH="$work/private" ANCORA_TMP_PATH="$work/tmp"
export ANCORA_ADMIN_PASSWORD_FILE="$work/secrets/admin-password"

cd "$project"
drush=(vendor/bin/drush --yes)

if [ "$skip_composer" -eq 0 ]; then
  step "composer install"
  composer install --no-interaction --no-progress
fi

# Drush prints a generated password; give it a throwaway one instead and set
# the real one from its file afterwards, so no log ever shows a usable secret.
throwaway=$(random_hex 16)
common=(--site-name=Áncora --site-mail=noreply@example.invalid
  --account-name=admin --account-mail=admin@example.invalid --account-pass="$throwaway")
if [ "$bootstrap" -eq 1 ] || [ ! -f config/sync/core.extension.yml ]; then
  step "drush site:install minimal (bootstrap: configuration is rebuilt and exported)"
  "${drush[@]}" site:install minimal "${common[@]}" install_configure_form.date_default_timezone=UTC
  "${drush[@]}" pm:install ancora
  "${drush[@]}" theme:install ancora_theme claro
  "${drush[@]}" config:set system.theme default ancora_theme
  "${drush[@]}" config:set system.theme admin claro
  "${drush[@]}" config:set node.settings use_admin_theme true
  # Security update e-mails are an operator setting of each deployment.
  "${drush[@]}" php:eval '\Drupal::configFactory()->getEditable("update.settings")->set("notification.emails", [])->save();'
  # Stark only served the installer; its blocks were copied to ancora_theme.
  "${drush[@]}" theme:uninstall stark
  find config/sync -maxdepth 1 -name '*.yml' -delete
  "${drush[@]}" config:export
else
  step "drush site:install --existing-config (from config/sync)"
  "${drush[@]}" site:install --existing-config "${common[@]}"
fi
"${drush[@]}" php:eval '$account = user_load_by_name("admin"); $account->setPassword(trim(file_get_contents(getenv("ANCORA_ADMIN_PASSWORD_FILE")))); $account->save();'

step "Configuration is deterministic: a fresh export equals config/sync"
"${drush[@]}" config:export --destination="$work/export" >/dev/null 2>&1
diff -r -x .htaccess config/sync "$work/export"
echo "config/sync matches the installed site ($(find config/sync -name '*.yml' | wc -l) files)"
"${drush[@]}" status --fields=drupal-version,php-version,db-driver,db-status,bootstrap,theme,admin-theme

if [ "$skip_tests" -eq 1 ]; then
  step "done (tests skipped) in $((SECONDS - started)) s"
  exit 0
fi

step "phpcs (Drupal, DrupalPractice) on custom code"
vendor/bin/phpcs

step "HTTP server on 127.0.0.1"
port=$(php -r '$s = stream_socket_server("tcp://127.0.0.1:0"); echo explode(":", stream_socket_get_name($s, FALSE))[1];')
# The working directory must be the web root: Drupal resolves template paths
# relative to it.
(cd web && PHP_CLI_SERVER_WORKERS=4 exec php -S "127.0.0.1:$port" .ht.router.php) >"$work/server.log" 2>&1 &
server_pid=$!
for _ in $(seq 1 50); do
  curl -s -o /dev/null "http://127.0.0.1:$port/robots.txt" && break
  sleep 0.2
done
base_url="http://127.0.0.1:$port"

step "PHPUnit Kernel and Functional tests (database ancora_test)"
mkdir -p web/sites/simpletest/browser_output
SIMPLETEST_DB="pgsql://ancora@localhost:$PGPORT/ancora_test" \
ANCORA_TEST_DB_SOCKET_DIR="$PGHOST" \
SIMPLETEST_BASE_URL="$base_url" \
BROWSERTEST_OUTPUT_DIRECTORY="$project/web/sites/simpletest/browser_output" \
  vendor/bin/phpunit -c phpunit.xml.dist --testsuite kernel,functional

step "HTTP smoke test against the installed site"
"$project/scripts/http-smoke.sh" "$base_url" "$ANCORA_ADMIN_PASSWORD_FILE"

step "all checks passed in $((SECONDS - started)) s"
if [ "$keep_server" -eq 1 ]; then
  echo "Site: $base_url  user: admin  password file: $ANCORA_ADMIN_PASSWORD_FILE  (Ctrl-C to stop)"
  wait "$server_pid"
fi
