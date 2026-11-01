#!/usr/bin/env bash
# Differential parity harness (issue #70): one synthetic scenario entered
# through the legacy application and through Drupal, then compared.
#
#   dev/sql/tools/with-ephemeral-pg.sh modern/scripts/parity-diff.sh [--skip-composer] [--workspace] [scenario...]
#
# A scenario is a JSON file of modern/tests/parity/scenarios/ (a path or its
# base name, without .json). Without arguments every scenario runs.
# Requires modern/vendor (composer install), jq and a PHP CLI with curl and
# pgsql. Documented in docs/modernization/legacy-parity-harness.md.
#
# For each scenario, against a disposable PostgreSQL cluster only:
# 1. Legacy: a fresh legacy database (dev/sql/install.sh --with-data), a copy
#    of the git-tracked application served by the PHP built-in server on
#    127.0.0.1 (as dev/e2e/run.sh does), the scenario's users created with
#    bin/setpassword.php and bin/adduser.php, and every record entered over
#    HTTP with the real handlers (scripts/lib/parity/legacy-driver.php).
#    Each run calls generar() through control/custom/generar.php.
# 2. Drupal: a fresh site (installed once from config/sync, copied per
#    scenario) and a fresh engine database (scripts/engine-db.sh); the
#    records entered through the Entity API as their users
#    (scripts/lib/parity/drupal-driver.php); each run queued as a timetable
#    request and run by the engine worker, which syncs the site into the
#    engine database (EngineSync, LegacyRows) and calls generar() with the
#    same options.
# 3. Both databases normalized (scripts/lib/parity/normalize.php: generated
#    ids replaced by scenario keys) and compared (scripts/lib/parity/compare.php):
#    PASS, KNOWN (a registered deviation, modern/tests/parity/known-deviations.json)
#    or FAIL per table, per other table and per run, with a diff on mismatch.
#    PHP warnings and SQL errors of both sides are reported.
#
# 4. With --workspace (issue #93), additionally: the unchanged legacy engine
#    is installed INTO the site database (dev/workspace/install.sh)
#    and every run is executed again in a job-scoped workspace schema there,
#    from one REPEATABLE READ snapshot of the Drupal entities, reduced to the
#    #92 closure (scripts/lib/parity/workspace-driver.php). Its placements,
#    inserted rows and DML counters are compared with the full engine
#    database and with the legacy application's database, both measured by
#    a rolled-back rerun (workspace-oracle.php, workspace-compare.php).
#    Then every request runs again through the REAL worker in workspace mode
#    (#95): its placements are published as Drupal placement entities, read
#    back and compared placement by placement with the legacy oracle
#    (workspace-roundtrip.php). The comparisons above are unchanged and run
#    first.
#
# Exit status 0 only if no scenario has a FAIL.
set -euo pipefail

skip_composer=0
workspace=0
scenarios=()
for arg in "$@"; do
  case "$arg" in
    --skip-composer) skip_composer=1 ;;
    --workspace) workspace=1 ;;
    -*) echo "unknown option: $arg" >&2; exit 2 ;;
    *) scenarios+=("$arg") ;;
  esac
done

project=$(cd "$(dirname "$0")/.." && pwd -P)
repo=$(dirname "$project")
# shellcheck source=../../dev/sql/tools/disposable-guard.sh
. "$repo/dev/sql/tools/disposable-guard.sh"
agoh_require_disposable || exit 3
command -v jq >/dev/null || { echo "jq is required" >&2; exit 2; }
php -r 'exit(function_exists("curl_init") && function_exists("pg_connect") ? 0 : 1);' \
  || { echo "the PHP CLI needs the curl and pgsql extensions" >&2; exit 2; }
lib=$project/scripts/lib/parity
scenario_dir=$project/tests/parity/scenarios
known=$project/tests/parity/known-deviations.json
if [ "${#scenarios[@]}" -eq 0 ]; then
  for f in "$scenario_dir"/*.json; do scenarios+=("$f"); done
fi

started=$SECONDS
step() { printf '\n==> [%3ss] %s\n' "$((SECONDS - started))" "$*"; }
work=$(mktemp -d "${TMPDIR:-/tmp}/ancora-parity.XXXXXX")
work=$(cd "$work" && pwd -P)
web_pid=""
cleanup() {
  if [ -n "$web_pid" ]; then kill "$web_pid" 2>/dev/null || true; wait "$web_pid" 2>/dev/null || true; fi
  chmod u+w "$project/web/sites/default" "$project/web/sites/default/settings.php" 2>/dev/null || true
  if [ "${ANCORA_PARITY_KEEP:-0}" = 1 ]; then echo "kept $work"; else rm -rf "$work"; fi
}
trap cleanup EXIT
mkdir -p "$work/secrets" "$work/private" "$work/tmp"
chmod 700 "$work/secrets"
random_hex() { od -An -N"$1" -tx1 /dev/urandom | tr -d ' \n'; }
(umask 077
  for f in db-password hash-salt admin-password engine-password; do random_hex 24 >"$work/secrets/$f"; done)
psql_q() { psql -X -q -v ON_ERROR_STOP=1 "$@"; }

cd "$project"
if [ "$skip_composer" -eq 0 ]; then
  step "composer install"
  composer install --no-interaction --no-progress
fi
[ -x "$project/vendor/bin/drush" ] || { echo "run composer install in modern/ first" >&2; exit 2; }

step "PostgreSQL $(psql -X -At -d postgres -c 'SHOW server_version'): legacy database template"
"$repo/dev/sql/install.sh" --with-data agoh_parity_template >"$work/install.log" 2>&1 \
  || { cat "$work/install.log"; exit 1; }

step "legacy application: temporary copy of the tracked files"
app=$work/app
mkdir -p "$app"
git -C "$repo" ls-files -z | (cd "$repo" && tar --null -T - -cf -) | tar -xf - -C "$app"

step "Drupal: site installed once from config/sync (copied per scenario)"
psql_q -d postgres <<SQL
\set db_password \`cat "$work/secrets/db-password"\`
CREATE ROLE ancora LOGIN PASSWORD :'db_password';
CREATE DATABASE ancora_parity_template OWNER ancora ENCODING 'UTF8' TEMPLATE template0;
\c ancora_parity_template
CREATE EXTENSION IF NOT EXISTS pg_trgm;
SQL
export ANCORA_ENVIRONMENT=production
export ANCORA_DB_HOST="$PGHOST" ANCORA_DB_PORT="$PGPORT" ANCORA_DB_USER=ancora
export ANCORA_DB_PASSWORD_FILE="$work/secrets/db-password" ANCORA_HASH_SALT_FILE="$work/secrets/hash-salt"
export ANCORA_TRUSTED_HOSTS=127.0.0.1,localhost
export ANCORA_PRIVATE_PATH="$work/private" ANCORA_TMP_PATH="$work/tmp"
export ANCORA_ENGINE_DB_HOST="$PGHOST" ANCORA_ENGINE_DB_PORT="$PGPORT"
export ANCORA_ENGINE_DB_PASSWORD_FILE="$work/secrets/engine-password"
drush() { vendor/bin/drush --yes "$@"; }
ANCORA_DB_NAME=ancora_parity_template drush site:install --existing-config --site-name=Áncora \
  --site-mail=noreply@example.invalid --account-name=admin --account-mail=admin@example.invalid \
  --account-pass="$(cat "$work/secrets/admin-password")" >"$work/site-install.log" 2>&1 \
  || { cat "$work/site-install.log"; exit 1; }

port=$(php -r '$s = stream_socket_server("tcp://127.0.0.1:0"); echo explode(":", stream_socket_get_name($s, FALSE))[1];')
base="http://127.0.0.1:$port/index.php"
pg_log="$PGHOST/server.log"
results=()
overall=0

# The legacy application on 127.0.0.1 (PHP built-in server), as dev/e2e/run.sh.
legacy_start() { # out dir
  (
    cd "$app/www" || exit 1
    PGOPTIONS="-c statement_timeout=600s" exec php -S "127.0.0.1:$port" -t . \
      -d display_errors=0 -d log_errors=1 -d error_log="$1/legacy-php.log" -d error_reporting=E_ALL \
      -d session.save_path="$1" -d max_execution_time=0
  ) >>"$1/legacy-web.log" 2>&1 &
  web_pid=$!
  for _ in $(seq 1 50); do (exec 3<>"/dev/tcp/127.0.0.1/$port") 2>/dev/null && break; sleep 0.1; done
}
legacy_stop() {
  kill "$web_pid" 2>/dev/null || true
  wait "$web_pid" 2>/dev/null || true
  web_pid=""
}

run_scenario() { # scenario file
  local file=$1 id n db edb out users nick pw key rc=0
  id=$(jq -r .id "$file")
  n=${#results[@]}
  db=agoh_parity_${n}_legacy
  edb=ancora_parity_${n}_engine
  out=$work/$id
  mkdir -p "$out"
  step "scenario $id: $(jq -r .title "$file")"

  # ---------------------------------------------------------------- legacy
  echo "--- legacy: database $db, application on 127.0.0.1:$port"
  psql_q -d postgres -c "CREATE DATABASE $db TEMPLATE agoh_parity_template"
  psql_q -d "$db" -f "$repo/dev/sql/tests/fixtures/helpers.sql" >/dev/null
  cat >"$app/bin/ancoragoh.conf" <<EOF
# generated by modern/scripts/parity-diff.sh for one scenario; trust authentication on a socket-only cluster
host = "$PGHOST"
port = $PGPORT
dbname = "$db"
user = "$PGUSER"
password = "unused-trust-auth"
EOF
  echo '{}' >"$out/credentials.json"
  for key in $(jq -r '.users[].key' "$file"); do
    pw=$(random_hex 12)
    if [ "$(jq -r --arg k "$key" '.users[] | select(.key == $k) | .legacy // ""' "$file")" = seeded-admin ]; then
      nick=admin
      printf '%s\n' "$pw" | (cd "$app/bin" && php setpassword.php -nick admin) >>"$out/legacy-cli.log" 2>&1
    else
      nick=$key
      printf '%s\n' "$pw" | (cd "$app/bin" && php adduser.php -nick "$key" -nombre "$(jq -r --arg k "$key" '.users[] | select(.key == $k) | .name' "$file")" -correo "$key@example.invalid") >>"$out/legacy-cli.log" 2>&1
      # bin/adduser.php assigns no role, and the generated role handler is
      # broken (see LegacyDriver::users()): the role row is inserted here.
      psql_q -d "$db" -c "INSERT INTO seguridad.rol_de_usuario (usuario, rol) SELECT id, 'superusuario' FROM seguridad.usuario WHERE nick = '$key'"
    fi
    jq --arg k "$key" --arg n "$nick" --arg p "$pw" '.[$k] = {nick: $n, password: $p}' "$out/credentials.json" >"$out/c.tmp" && mv "$out/c.tmp" "$out/credentials.json"
  done
  local php_log=$out/legacy-php.log pg_start
  : >"$php_log"
  pg_start=$(wc -l <"$pg_log")
  legacy_start "$out"
  php "$lib/legacy-driver.php" "$file" "$base" "$db" "$out/credentials.json" "$out" >"$out/legacy-driver.log" 2>&1 \
    || { echo "FAIL  legacy driver"; rc=1; }
  grep -v '^ok' "$out/legacy-driver.log" || true
  legacy_stop
  echo "      legacy requests: $(grep -c '^ok' "$out/legacy-driver.log") ok"
  local php_bad
  php_bad=$(grep -E 'PHP (Warning|Fatal error|Parse error)|TypeError|Uncaught' "$php_log" | sed -E 's/^\[[^]]*\] //' | sort | uniq -c | sort -rn || true)
  if [ -n "$php_bad" ]; then echo "      legacy PHP warnings and errors (reported, not compared):"; printf '%s\n' "$php_bad" | head -10 | sed 's/^/        /'; fi

  # ---------------------------------------------------------------- Drupal
  echo "--- Drupal: site database ancora_parity_${n}, engine database $edb"
  psql_q -d postgres -c "CREATE DATABASE ancora_parity_${n} TEMPLATE ancora_parity_template OWNER ancora"
  psql_q -d postgres -c "CREATE DATABASE $edb TEMPLATE agoh_parity_template"
  "$project/scripts/engine-db.sh" create --if-missing --password-file "$work/secrets/engine-password" "$edb" "${edb}_app" >"$out/engine-db.log" 2>&1 \
    || { cat "$out/engine-db.log"; return 1; }
  psql_q -d "$edb" -f "$repo/dev/sql/tests/fixtures/helpers.sql" >/dev/null
  export ANCORA_DB_NAME=ancora_parity_${n} ANCORA_ENGINE_DB_NAME=$edb ANCORA_ENGINE_DB_USER=${edb}_app
  drush php:script "$lib/drupal-driver.php" -- "$file" "$out" >"$out/drupal-driver.log" 2>&1 \
    || { echo "FAIL  Drupal driver"; rc=1; }
  grep -v '^ok' "$out/drupal-driver.log" || true
  echo "      Drupal steps: $(grep -c '^ok' "$out/drupal-driver.log") ok"
  local runs
  runs=$(jq '.runs | length' "$file")
  for _ in $(seq 1 "$runs"); do drush ancora-engine:work --once >>"$out/worker.log" 2>&1 || { echo "FAIL  worker"; rc=1; }; done
  local job
  for job in $(psql -X -At -d "ancora_parity_${n}" -c 'SELECT id FROM ancora_engine_job ORDER BY id'); do
    echo "      job $job: $(drush ancora-engine:job "$job" | jq -c '{state, error, sync: .result.input | {inserted, updated, deleted}}')"
  done
  # Drupal creates some tables on first use; those misses are not errors.
  tail -n +"$((pg_start + 1))" "$pg_log" | grep -E 'ERROR:|FATAL:' \
    | grep -vE 'relation "(public\.)?(cache_[a-z_]+|cachetags|semaphore|key_value_expire|flood|queue|batch|sessions|router)" does not exist' \
    | sed -E 's/^[0-9-]+ [0-9:.]+ [A-Z]+ \[[0-9]+\] //' | sort | uniq -c | head -10 | sed 's/^/      SQL /' || true

  # ---------------------------------------------------------------- compare
  php "$lib/normalize.php" "$file" "$db" "$out/legacy-users.json" >"$out/legacy.json"
  php "$lib/normalize.php" "$file" "$edb" "$out/drupal-users.json" >"$out/drupal.json"
  php "$lib/compare.php" "$file" "$out/legacy.json" "$out/drupal.json" "$known" || rc=1

  # ---------------------------------------------------------------- workspace (--workspace)
  if [ "$workspace" -eq 1 ]; then
    echo "--- workspace: the engine inside the site database ancora_parity_${n}, no engine database"
    local t_ws=$SECONDS
    psql_q -d postgres -c "CREATE ROLE ancora_parity_${n}_worker LOGIN" >/dev/null
    "$repo/dev/workspace/install.sh" --worker "ancora_parity_${n}_worker" "ancora_parity_${n}" >"$out/workspace-install.log" 2>&1 \
      || { cat "$out/workspace-install.log"; return 1; }
    for d in "$db" "$edb"; do
      PGOPTIONS="-c client_min_messages=warning" psql_q -d "$d" -f "$repo/dev/workspace/measure.sql" >/dev/null
    done
    drush php:script "$lib/workspace-driver.php" -- "$file" "$out" >"$out/workspace-driver.log" 2>&1 \
      || { echo "FAIL  workspace driver"; tail -20 "$out/workspace-driver.log"; return 1; }
    grep '^ok' "$out/workspace-driver.log" | sed 's/^/      /'
    php "$lib/workspace-oracle.php" "$file" "$db" "$out/legacy-users.json" legacy "$out/workspace.json" >"$out/legacy-oracle.json" || return 1
    php "$lib/workspace-oracle.php" "$file" "$edb" "$out/drupal-users.json" engine "$out/workspace.json" >"$out/engine-oracle.json" || return 1
    # The real worker in workspace mode, the output published as placement
    # entities and read back from Drupal (#95).
    ANCORA_ENGINE_MODE=workspace ANCORA_ENGINE_DB_NAME="ancora_parity_${n}" ANCORA_ENGINE_DB_USER="ancora_parity_${n}_worker" \
      drush php:script "$lib/workspace-roundtrip.php" -- "$file" "$out" >"$out/workspace-roundtrip.log" 2>&1 \
      || { echo "FAIL  workspace round trip"; tail -20 "$out/workspace-roundtrip.log"; return 1; }
    grep '^ok' "$out/workspace-roundtrip.log" | sed 's/^/      /'
    php "$lib/workspace-compare.php" "$file" "$out" || rc=1
    echo "METRIC two-database layout: engine database $edb $(psql -X -At -d postgres -c "SELECT pg_database_size('$edb')") bytes, its sync wrote $(jq '[.runs[] | .site_rows] | max' "$out/workspace.json") rows of the whole site per job; single database: workspace path $((SECONDS - t_ws)) s for $(jq '.runs | length' "$out/workspace.json") jobs"
  fi

  # ---------------------------------------------------------------- characterization (legacy only)
  if [ "$(jq '.characterize // [] | length' "$file")" -gt 0 ]; then
    echo "# legacy characterization (after the comparison: it changes data)"
    # A scenario may turn the legacy public viewer's anonymous switch on for
    # its characterization (#121); the disposable copy of the application
    # is switched back afterwards.
    local anon_switch
    anon_switch=$(jq -r '.legacy_switches.public_viewer_anonymous // false' "$file")
    [ "$anon_switch" = true ] && sed -i 's/^public_viewer_anonymous = false$/public_viewer_anonymous = true/' "$app/bin/security.conf"
    legacy_start "$out"
    php "$lib/legacy-driver.php" --characterize "$file" "$base" "$db" "$out/credentials.json" "$out" >"$out/legacy-characterize.log" 2>&1 \
      || { echo "FAIL  legacy characterization"; rc=1; }
    legacy_stop
    [ "$anon_switch" = true ] && sed -i 's/^public_viewer_anonymous = true$/public_viewer_anonymous = false/' "$app/bin/security.conf"
    grep -v '^ok' "$out/legacy-characterize.log" || true
  fi

  # ---------------------------------------------------------------- timetable explorer (#119)
  # The legacy explorer's grids (characterize steps "explorer_grid") against
  # the Drupal explorer, in the two-database mode and, with --workspace,
  # on the placement entities the round trip published.
  if [ "$(jq '[.characterize // [] | .[] | select(.kind == "explorer_grid" or .kind == "viewer_lists")] | length' "$file")" -gt 0 ]; then
    echo "# timetable explorer: the legacy explorer or public viewer against the Drupal explorer or viewer"
    local modes=(two_database)
    [ "$workspace" -eq 1 ] && modes+=(workspace)
    local mode
    for mode in "${modes[@]}"; do
      if [ "$mode" = workspace ]; then
        ANCORA_ENGINE_MODE=workspace ANCORA_ENGINE_DB_NAME="ancora_parity_${n}" ANCORA_ENGINE_DB_USER="ancora_parity_${n}_worker" \
          drush php:script "$lib/explorer-dump.php" -- "$file" "$out" "$mode" >"$out/explorer-$mode.log" 2>&1 \
          || { echo "FAIL  explorer dump ($mode)"; tail -5 "$out/explorer-$mode.log"; rc=1; }
      else
        drush php:script "$lib/explorer-dump.php" -- "$file" "$out" "$mode" >"$out/explorer-$mode.log" 2>&1 \
          || { echo "FAIL  explorer dump ($mode)"; tail -5 "$out/explorer-$mode.log"; rc=1; }
      fi
      php "$lib/explorer-compare.php" "$file" "$out" "$mode" || rc=1
    done
  fi
  return "$rc"
}

for s in "${scenarios[@]}"; do
  [ -f "$s" ] || s=$scenario_dir/$s.json
  [ -f "$s" ] || { echo "no such scenario: $s" >&2; exit 2; }
  if run_scenario "$s"; then results+=("PASS  $(jq -r .id "$s")"); else results+=("FAIL  $(jq -r .id "$s")"); overall=1; fi
done

step "parity: $(printf '%s\n' "${results[@]}" | grep -c '^PASS') of ${#results[@]} scenarios pass ($((SECONDS - started)) s)"
printf '%s\n' "${results[@]}"
exit "$overall"
