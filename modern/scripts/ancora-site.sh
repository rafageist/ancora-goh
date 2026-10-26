#!/usr/bin/env bash
# Operator tool for the Áncora sites (issue #72): the control site
# (sites/default, served at /) and one Drupal site per organization
# (sites/org-<slug>, served at /o/<slug>/), each with its own PostgreSQL
# database, owner role and runtime role.
#
#   ancora-site.sh control-install --admin-mail <mail>
#   ancora-site.sh provision <slug> --name <site name> --admin-mail <mail>
#   ancora-site.sh admin-link <slug|control>
#   ancora-site.sh suspend <slug>
#   ancora-site.sh resume <slug>
#   ancora-site.sh deprovision <slug> --backup-dir <dir> --confirm <slug>
#   ancora-site.sh update-all [--backup-dir <dir>]
#   ancora-site.sh list
#   ancora-site.sh work <slug> [worker options, e.g. --once]
#
# Every command is idempotent: a failed run is repaired by running the same
# command again (see docs/modernization/drupal-multisite.md, "Failure
# recovery"). Only one command runs at a time (a lock in ANCORA_SITES_DIR).
#
# Environment (the operator's, never the web server's):
#   PGHOST, PGPORT, PGUSER, ...   administrative PostgreSQL connection
#                                 (superuser, or CREATEDB + CREATEROLE and
#                                 membership of the site roles)
#   ANCORA_SITES_DIR              per-site settings and secrets, outside the
#                                 web root, e.g. /etc/ancora/sites
#   ANCORA_SITES_DATA_DIR         per-site private and temporary files, e.g.
#                                 /var/lib/ancora/sites
#   ANCORA_PUBLIC_URL             public base URL, e.g. https://timetables.example
#   ANCORA_DB_HOST, ANCORA_DB_PORT  database server as seen by Drupal
#   ANCORA_ENGINE_ADMIN_INSTALL   1 (default): engine databases are installed
#                                 with engine-db.sh --admin-install into the
#                                 server the PG* variables name (the
#                                 operator's choice); 0: only into a verified
#                                 disposable cluster
#   ANCORA_WEB_GROUP              optional: group of the PHP-FPM pool; readable
#                                 secrets get this group, writable data
#                                 directories too
#   ANCORA_DB_NAME, ANCORA_DB_USER, ANCORA_DB_OWNER_USER,
#   ANCORA_DB_PASSWORD_FILE, ANCORA_DB_OWNER_PASSWORD_FILE,
#   ANCORA_HASH_SALT_FILE         the control site (control-install, update-all)
#   ANCORA_PROVISION_TEST_FAIL_AFTER  test hook: stop provisioning after the
#                                 named step (the verification uses it to prove
#                                 recovery). Never set it in production.
set -euo pipefail

project=$(cd "$(dirname "$0")/.." && pwd -P)
web=$project/web
drush_bin=$project/vendor/bin/drush
tasks=$project/scripts/lib/site-tasks.php
engine_db=$project/scripts/engine-db.sh

die() { echo "ancora-site: $*" >&2; exit 1; }
say() { echo "ancora-site: $*"; }
need_env() { local v; for v in "$@"; do [ -n "${!v:-}" ] || die "$v is required"; done; }

need_env ANCORA_SITES_DIR ANCORA_SITES_DATA_DIR ANCORA_PUBLIC_URL
sites_dir=$ANCORA_SITES_DIR
data_dir=$ANCORA_SITES_DATA_DIR
public_url=${ANCORA_PUBLIC_URL%/}
web_group=${ANCORA_WEB_GROUP:-}
mkdir -p "$sites_dir" "$data_dir"
chmod 0751 "$sites_dir"

valid_slug() { [[ $1 =~ ^[a-z][a-z0-9-]{1,30}$ ]]; }
db_id() { echo "tt_${1//-/_}"; }   # slugs have no "_", so this is injective

audit() { # action slug result
  printf '%s\t%s\t%s\t%s\t%s\n' "$(date -u +%Y-%m-%dT%H:%M:%SZ)" "${SUDO_USER:-${USER:-?}}" "$1" "$2" "$3" >>"$sites_dir/audit.log"
}

lock() {
  exec 9>"$sites_dir/.lock"
  flock -n 9 || die "another ancora-site command is running"
}

fail_point() {
  if [ "${ANCORA_PROVISION_TEST_FAIL_AFTER:-}" = "$1" ]; then
    die "test hook: stopping after step '$1'"
  fi
}

# quiet <command...>: run it; show its output only when it fails.
quiet() {
  local out rc=0
  out=$("$@" 2>&1) || rc=$?
  if [ "$rc" -ne 0 ]; then printf '%s\n' "$out" >&2; fi
  return "$rc"
}

random_hex() { od -An -N"$1" -tx1 /dev/urandom | tr -d ' \n'; }

# secret <file> <mode> <bytes>: create a random secret once; keep it on re-runs.
secret() {
  if [ ! -s "$1" ]; then
    (umask 077 && random_hex "$3" >"$1.tmp" && mv "$1.tmp" "$1")
  fi
  chmod "$2" "$1"
  if [ -n "$web_group" ] && [ "$2" = 0640 ]; then chgrp "$web_group" "$1"; fi
}

# State of a site: key=value lines in $sites_dir/<slug>/provision.state.
state_get() {
  local f="$sites_dir/$1/provision.state"
  if [ -f "$f" ]; then sed -n "s/^$2=//p" "$f" | tail -1; fi
}
state_set() {
  local f="$sites_dir/$1/provision.state"
  { grep -v "^$2=" "$f" 2>/dev/null || true; echo "$2=$3"; } >"$f.tmp"
  mv "$f.tmp" "$f"
}

# site.ini, the site's settings outside the web root (read by the web server).
ini_get() {
  local f="$sites_dir/$1/site.ini"
  if [ -f "$f" ]; then sed -n "s/^$2 = \"\(.*\)\"$/\1/p" "$f" | tail -1; fi
}
write_ini() { # slug status name
  local slug=$1 dir="$sites_dir/$1" id
  id=$(db_id "$slug")
  {
    echo "; Áncora organization site $slug. Written by ancora-site.sh; do not edit."
    printf '%s = "%s"\n' \
      SLUG "$slug" STATUS "$2" SITE_NAME "$3" \
      DB_NAME "$id" DB_USER "${id}_app" DB_PASSWORD_FILE "$dir/db-password" \
      DB_OWNER_USER "${id}_owner" DB_OWNER_PASSWORD_FILE "$dir/owner-password" \
      HASH_SALT_FILE "$dir/hash-salt" \
      ENGINE_DB_NAME "${id}_engine" ENGINE_DB_USER "${id}_engine_app" ENGINE_DB_PASSWORD_FILE "$dir/engine-db-password" \
      PRIVATE_PATH "$data_dir/$slug/private" TMP_PATH "$data_dir/$slug/tmp"
  } >"$dir/site.ini.tmp"
  chmod 0640 "$dir/site.ini.tmp"
  if [ -n "$web_group" ]; then chgrp "$web_group" "$dir/site.ini.tmp"; fi
  mv "$dir/site.ini.tmp" "$dir/site.ini"
}

# Drush against one site. The URI goes through Drupal's own site discovery,
# and ANCORA_EXPECT_SITE_PATH makes the settings refuse any other site.
site_drush() { # slug|control drush-args...
  local site=$1; shift
  if [ "$site" = control ]; then
    ANCORA_EXPECT_SITE_PATH=sites/default "$drush_bin" --root="$web" --uri="$public_url" --yes "$@"
  else
    ANCORA_EXPECT_SITE_PATH="sites/org-$site" "$drush_bin" --root="$web" --uri="$public_url/o/$site" --yes "$@"
  fi
}
owner_drush() { ANCORA_DB_AS_OWNER=1 site_drush "$@"; }
task() { local site=$1; shift; site_drush "$site" php:script "$tasks" -- "$@"; }
owner_task() { local site=$1; shift; owner_drush "$site" php:script "$tasks" -- "$@"; }

psql_admin() { psql -X -q -v ON_ERROR_STOP=1 "$@"; }

# db_ensure <db> <owner role> <runtime role> <owner password file> <runtime password file>
# Roles and database, idempotently. Nobody but the two roles may connect.
db_ensure() {
  psql_admin -d postgres -v db="$1" -v owner="$2" -v app="$3" \
    -v owner_pw="$(cat "$4")" -v app_pw="$(cat "$5")" <<'SQL'
SELECT format('CREATE ROLE %I', :'owner') WHERE NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = :'owner') \gexec
SELECT format('CREATE ROLE %I', :'app') WHERE NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = :'app') \gexec
-- A non-superuser operator (CREATEDB + CREATEROLE) may not even restate the
-- SUPERUSER, REPLICATION or BYPASSRLS attributes (PostgreSQL 16+). New roles
-- never have them; the check below fails the run if a site role ever does.
ALTER ROLE :"owner" LOGIN NOCREATEDB NOCREATEROLE PASSWORD :'owner_pw';
ALTER ROLE :"app" LOGIN NOCREATEDB NOCREATEROLE PASSWORD :'app_pw';
SELECT set_config('ancora.site_roles', :'owner' || ',' || :'app', false);
DO $$
BEGIN
  IF EXISTS (SELECT 1 FROM pg_roles
             WHERE rolname = ANY (string_to_array(current_setting('ancora.site_roles'), ','))
               AND (rolsuper OR rolreplication OR rolbypassrls OR rolcreatedb OR rolcreaterole)) THEN
    RAISE EXCEPTION 'a site role has SUPERUSER, REPLICATION, BYPASSRLS, CREATEDB or CREATEROLE';
  END IF;
END $$;
SELECT format('CREATE DATABASE %I OWNER %I ENCODING %L TEMPLATE template0', :'db', :'owner', 'UTF8')
  WHERE NOT EXISTS (SELECT 1 FROM pg_database WHERE datname = :'db') \gexec
REVOKE ALL ON DATABASE :"db" FROM PUBLIC;
GRANT CONNECT, TEMPORARY ON DATABASE :"db" TO :"app";
\c :"db"
SET client_min_messages = warning;
CREATE EXTENSION IF NOT EXISTS pg_trgm;
REVOKE ALL ON SCHEMA public FROM PUBLIC;
GRANT USAGE ON SCHEMA public TO :"app";
ALTER DEFAULT PRIVILEGES FOR ROLE :"owner" IN SCHEMA public
  GRANT SELECT, INSERT, UPDATE, DELETE, TRUNCATE ON TABLES TO :"app";
ALTER DEFAULT PRIVILEGES FOR ROLE :"owner" IN SCHEMA public
  GRANT USAGE, SELECT, UPDATE ON SEQUENCES TO :"app";
SQL
}

# db_grant_runtime <db> <owner role> <runtime role>: DML on what exists now.
db_grant_runtime() {
  psql_admin -d "$1" -v owner="$2" -v app="$3" <<'SQL'
GRANT SELECT, INSERT, UPDATE, DELETE, TRUNCATE ON ALL TABLES IN SCHEMA public TO :"app";
GRANT USAGE, SELECT, UPDATE ON ALL SEQUENCES IN SCHEMA public TO :"app";
REVOKE CREATE ON SCHEMA public FROM :"app";
SQL
}

# engine_ensure <slug>: the site's scheduling engine database tt_<slug>_engine
# and its runtime role tt_<slug>_engine_app (scripts/engine-db.sh, #71),
# idempotently: an installed engine database is kept; the role's password and
# the grants are applied again. The legacy schema is installed without the
# legacy seed: the sync from Drupal creates the owner of each plan's rows.
engine_ensure() {
  local slug=$1 id mode=()
  id=$(db_id "$slug")
  if [ "${ANCORA_ENGINE_ADMIN_INSTALL:-1}" = 1 ]; then mode=(--admin-install); fi
  quiet env AGOH_ADMIN_INSTALL_CONFIRM="${id}_engine" "$engine_db" create "${mode[@]}" --if-missing \
    --password-file "$sites_dir/$slug/engine-db-password" "${id}_engine" "${id}_engine_app" \
    || die "$slug: the engine database could not be provisioned"
}

db_exists() { # db
  [ "$(psql -X -At -d postgres -v db="$1" <<<"SELECT 1 FROM pg_database WHERE datname = :'db'")" = 1 ]
}

db_installed() { # db: Drupal tables present?
  [ "$(psql -X -At -d "$1" -c "SELECT to_regclass('public.users_field_data') IS NOT NULL" 2>/dev/null)" = t ]
}

# install_site <site> <admin mail> <site name>: drush site:install from config/sync.
install_site() {
  local site=$1 throwaway subdir=default
  [ "$site" = control ] || subdir="org-$site"
  # Drush never sees a usable password: the account is blocked and its
  # password removed right after.
  throwaway=$(random_hex 24)
  owner_drush "$site" site:install --existing-config --sites-subdir="$subdir" \
    --site-name="$3" --site-mail=noreply@example.invalid \
    --account-name=admin --account-mail="$2" --account-pass="$throwaway" >/dev/null
}

cmd_control_install() {
  local mail=""
  while [ $# -gt 0 ]; do
    case "$1" in --admin-mail) mail=$2; shift 2 ;; *) die "unknown option $1" ;; esac
  done
  [ -n "$mail" ] || die "--admin-mail is required"
  need_env ANCORA_DB_NAME ANCORA_DB_USER ANCORA_DB_OWNER_USER ANCORA_DB_PASSWORD_FILE ANCORA_DB_OWNER_PASSWORD_FILE ANCORA_HASH_SALT_FILE
  lock
  secret "$ANCORA_DB_PASSWORD_FILE" 0640 24
  secret "$ANCORA_DB_OWNER_PASSWORD_FILE" 0600 24
  secret "$ANCORA_HASH_SALT_FILE" 0640 48
  db_ensure "$ANCORA_DB_NAME" "$ANCORA_DB_OWNER_USER" "$ANCORA_DB_USER" "$ANCORA_DB_OWNER_PASSWORD_FILE" "$ANCORA_DB_PASSWORD_FILE"
  if db_installed "$ANCORA_DB_NAME"; then
    say "control: already installed"
  else
    say "control: installing from config/sync"
    install_site control "$mail" "Áncora"
    owner_task control admin-blocked >/dev/null
  fi
  owner_task control runtime-tables
  db_grant_runtime "$ANCORA_DB_NAME" "$ANCORA_DB_OWNER_USER" "$ANCORA_DB_USER"
  quiet site_drush control cache:rebuild
  audit control-install control ok
  say "control: ready"
}

cmd_provision() {
  local slug=${1:-} name="" mail=""
  shift || true
  while [ $# -gt 0 ]; do
    case "$1" in
      --name) name=$2; shift 2 ;;
      --admin-mail) mail=$2; shift 2 ;;
      *) die "unknown option $1" ;;
    esac
  done
  valid_slug "$slug" || die "invalid slug '$slug' (expected ^[a-z][a-z0-9-]{1,30}\$)"
  [ -n "$name" ] && [ -n "$mail" ] || die "--name and --admin-mail are required"
  [[ $name != *'"'* && $name != *$'\n'* ]] || die "the site name cannot contain quotes or newlines"
  lock
  local dir="$sites_dir/$slug" id status
  id=$(db_id "$slug")
  audit provision "$slug" started

  # 1. Site record and secrets (kept on re-runs).
  mkdir -p "$dir"
  chmod 0750 "$dir"
  if [ -n "$web_group" ]; then chgrp "$web_group" "$dir"; fi
  [ -f "$dir/provision.state" ] || state_set "$slug" CREATED "$(date -u +%Y-%m-%dT%H:%M:%SZ)"
  secret "$dir/db-password" 0640 24
  secret "$dir/owner-password" 0600 24
  secret "$dir/hash-salt" 0640 48
  secret "$dir/engine-db-password" 0640 24
  status=$(ini_get "$slug" STATUS)
  case "$status" in
    ""|provisioning) status=provisioning ;;
    active|suspended) ;;
    *) die "$slug has status '$status'" ;;
  esac
  write_ini "$slug" "$status" "$name"
  fail_point record

  # 2. Database and roles.
  db_ensure "$id" "${id}_owner" "${id}_app" "$dir/owner-password" "$dir/db-password"
  # The site's engine database, with its own runtime role (#71). The site
  # reads its connection settings from site.ini (ENGINE_DB_*), never from job
  # or request data.
  engine_ensure "$slug"
  fail_point database

  # 3. Files: private and temporary directories outside the web root, the
  # site directory with its settings.php stub (no secret in it).
  mkdir -p "$data_dir/$slug/private" "$data_dir/$slug/tmp"
  chmod 0770 "$data_dir/$slug" "$data_dir/$slug/private" "$data_dir/$slug/tmp"
  if [ -n "$web_group" ]; then chgrp "$web_group" "$data_dir/$slug" "$data_dir/$slug/private" "$data_dir/$slug/tmp"; fi
  local site_dir="$web/sites/org-$slug"
  mkdir -p "$site_dir"
  chmod u+w "$site_dir" "$site_dir/settings.php" 2>/dev/null || true
  install -m 0644 "$project/settings/org-site.settings.php" "$site_dir/settings.php"
  fail_point files

  # 4. Drupal install from config/sync. A run that stopped during or after
  # the install but before INSTALLED was recorded installs again: drush
  # site:install empties this site's own database first.
  if [ "$(state_get "$slug" INSTALLED)" = 1 ] && db_installed "$id"; then
    say "$slug: already installed"
  else
    say "$slug: installing from config/sync into $id"
    install_site "$slug" "$mail" "$name"
    state_set "$slug" INSTALLED 1
  fi
  fail_point install

  # 5. Tables Drupal would create lazily, then runtime privileges.
  owner_task "$slug" runtime-tables
  db_grant_runtime "$id" "${id}_owner" "${id}_app"
  fail_point grants

  # 6. First administrator: blocked, no password, until admin-link.
  if [ "$(state_get "$slug" ADMIN)" != created ]; then
    owner_task "$slug" admin-blocked >/dev/null
    state_set "$slug" ADMIN created
  fi
  fail_point admin

  # 7. The runtime role can run the site (cache rebuild as the web server's
  # role), then the site is published: web/o/<slug> and STATUS=active.
  quiet site_drush "$slug" cache:rebuild || die "$slug: the runtime role cannot rebuild the caches"
  mkdir -p "$web/o"
  ln -sfn .. "$web/o/$slug"
  if [ "$status" = provisioning ]; then
    write_ini "$slug" active "$name"
    status=active
  fi
  state_set "$slug" PROVISIONED "$(date -u +%Y-%m-%dT%H:%M:%SZ)"
  audit provision "$slug" "ok:$status"
  say "$slug: $status at $public_url/o/$slug/ (database $id)"
  say "$slug: the first administrator is blocked; run 'ancora-site.sh admin-link $slug' after the organization's administrator is verified"
}

cmd_admin_link() {
  local site=${1:-}
  [ "$site" = control ] || valid_slug "$site" || die "invalid site '$site'"
  if [ "$site" != control ]; then
    [ "$(ini_get "$site" STATUS)" = active ] || die "$site is not active"
  fi
  lock
  # Unblock uid 1 (it has no password) and print a single-use login link,
  # valid for 24 hours. The link is the only way in; it is shown once, to the
  # operator, who sends it to the verified administrator.
  site_drush "$site" user:unblock --uid=1 >/dev/null 2>&1
  site_drush "$site" user:login --uid=1 --no-browser 2>/dev/null
  audit admin-link "$site" issued
}

cmd_suspend() {
  local slug=${1:-}
  valid_slug "$slug" && [ -f "$sites_dir/$slug/site.ini" ] || die "unknown site '$slug'"
  lock
  local name
  name=$(ini_get "$slug" SITE_NAME)
  # Web requests get 503 from the settings file at once, before Drupal
  # bootstraps; then maintenance mode and no live session, so a resume does
  # not bring old sessions back.
  write_ini "$slug" suspended "$name"
  task "$slug" maintenance-on >/dev/null
  task "$slug" end-sessions
  audit suspend "$slug" ok
  say "$slug: suspended"
}

cmd_resume() {
  local slug=${1:-}
  valid_slug "$slug" && [ -f "$sites_dir/$slug/site.ini" ] || die "unknown site '$slug'"
  [ "$(ini_get "$slug" STATUS)" = suspended ] || die "$slug is not suspended"
  lock
  task "$slug" maintenance-off >/dev/null
  write_ini "$slug" active "$(ini_get "$slug" SITE_NAME)"
  audit resume "$slug" ok
  say "$slug: active"
}

cmd_deprovision() {
  local slug=${1:-} backup="" confirm=""
  shift || true
  while [ $# -gt 0 ]; do
    case "$1" in
      --backup-dir) backup=$2; shift 2 ;;
      --confirm) confirm=$2; shift 2 ;;
      *) die "unknown option $1" ;;
    esac
  done
  valid_slug "$slug" || die "invalid slug '$slug'"
  [ "$confirm" = "$slug" ] || die "repeat the slug with --confirm to delete the site"
  [ -n "$backup" ] || die "--backup-dir is required"
  local status id
  status=$(ini_get "$slug" STATUS)
  [ "$status" != active ] || die "$slug is active: suspend it first"
  lock
  id=$(db_id "$slug")
  audit deprovision "$slug" started
  # 1. Unpublish, then a final backup of the database (when it exists).
  rm -f "$web/o/$slug"
  if [ -f "$sites_dir/$slug/site.ini" ]; then write_ini "$slug" retired "$(ini_get "$slug" SITE_NAME)"; fi
  if db_exists "${id}_engine"; then
    mkdir -p "$backup"
    local engine_dump
    engine_dump="$backup/${id}_engine-$(date -u +%Y%m%dT%H%M%SZ).dump"
    (umask 077 && pg_dump -Fc -d "${id}_engine" -f "$engine_dump")
    say "$slug: final backup $engine_dump"
    psql_admin -d postgres -v db="${id}_engine" <<'SQL'
SELECT pg_terminate_backend(pid) FROM pg_stat_activity WHERE datname = :'db' AND pid <> pg_backend_pid();
SQL
    psql_admin -d postgres -c "DROP DATABASE IF EXISTS \"${id}_engine\""
  fi
  if db_exists "$id"; then
    mkdir -p "$backup"
    local dump
    dump="$backup/$id-$(date -u +%Y%m%dT%H%M%SZ).dump"
    (umask 077 && pg_dump -Fc -d "$id" -f "$dump")
    say "$slug: final backup $dump"
    # 2. Database and roles.
    psql_admin -d postgres -v db="$id" <<'SQL'
SELECT pg_terminate_backend(pid) FROM pg_stat_activity WHERE datname = :'db' AND pid <> pg_backend_pid();
SQL
    psql_admin -d postgres -c "DROP DATABASE IF EXISTS \"$id\""
  fi
  psql_admin -d postgres -c "DROP ROLE IF EXISTS \"${id}_app\"" -c "DROP ROLE IF EXISTS \"${id}_owner\"" \
    -c "DROP ROLE IF EXISTS \"${id}_engine_app\""
  # 3. Files and the site record (the audit log keeps the history).
  if [ -d "$web/sites/org-$slug" ]; then chmod -R u+w "$web/sites/org-$slug"; rm -rf "$web/sites/org-$slug"; fi
  rm -rf "$data_dir/$slug" "$sites_dir/$slug"
  audit deprovision "$slug" ok
  say "$slug: deprovisioned"
}

org_sites() { # slugs with a site.ini, sorted
  local f
  for f in "$sites_dir"/*/site.ini; do
    [ -f "$f" ] || continue
    basename "$(dirname "$f")"
  done | sort
}

# work <slug> [options]: the engine worker of one site (drush
# ancora-engine:work) with the site's runtime role. It takes no lock: a worker
# loop runs for a long time, and operator commands must not wait for it.
cmd_work() {
  local slug=${1:-}
  shift || true
  valid_slug "$slug" && [ "$(ini_get "$slug" STATUS)" = active ] || die "$slug is not an active site"
  site_drush "$slug" ancora-engine:work "$@"
}

cmd_list() {
  local slug
  printf '%-32s %-13s %s\n' SITE STATUS NAME
  if [ -n "${ANCORA_DB_NAME:-}" ]; then printf '%-32s %-13s %s\n' control - "$ANCORA_DB_NAME"; fi
  for slug in $(org_sites); do
    printf '%-32s %-13s %s\n' "$slug" "$(ini_get "$slug" STATUS)" "$(ini_get "$slug" SITE_NAME)"
  done
}

# update_one <site> <db> <owner> <runtime> <backup dir>
update_one() {
  local site=$1 suspended=0
  if [ -n "$5" ]; then
    mkdir -p "$5"
    (umask 077 && pg_dump -Fc -d "$2" -f "$5/$2-$(date -u +%Y%m%dT%H%M%SZ).dump")
  fi
  [ "$site" = control ] || [ "$(ini_get "$site" STATUS)" != suspended ] || suspended=1
  task "$site" maintenance-on >/dev/null
  owner_drush "$site" updatedb
  owner_drush "$site" config:import
  owner_task "$site" runtime-tables >/dev/null
  db_grant_runtime "$2" "$3" "$4"
  quiet site_drush "$site" cache:rebuild
  # Configuration drift: the site must equal config/sync after the import.
  local drift
  drift=$(site_drush "$site" config:status --format=list 2>/dev/null || true)
  [ -z "$drift" ] || { echo "$drift" >&2; die "$site: configuration differs from config/sync after the import"; }
  [ "$suspended" = 1 ] || task "$site" maintenance-off >/dev/null
}

cmd_update_all() {
  local backup="" site id done_sites=() pending=()
  while [ $# -gt 0 ]; do
    case "$1" in --backup-dir) backup=$2; shift 2 ;; *) die "unknown option $1" ;; esac
  done
  lock
  local all=()
  [ -z "${ANCORA_DB_NAME:-}" ] || all+=(control)
  for site in $(org_sites); do
    [ -r "$sites_dir/$site/site.ini" ] || die "cannot read the site file of $site; nothing was updated"
    case "$(ini_get "$site" STATUS)" in
      active|suspended) all+=("$site") ;;
      provisioning|retired) say "skipping $site (status $(ini_get "$site" STATUS))" ;;
      *) die "$site has an unknown status; nothing was updated" ;;
    esac
  done
  pending=("${all[@]}")
  for site in "${all[@]}"; do
    pending=("${pending[@]:1}")
    say "update: $site"
    # The subshell must not run in a conditional context, or bash would
    # ignore "set -e" inside it and carry on after a failed step.
    local rc=0
    set +e
    if [ "$site" = control ]; then
      (set -e; update_one control "$ANCORA_DB_NAME" "$ANCORA_DB_OWNER_USER" "$ANCORA_DB_USER" "$backup")
    else
      id=$(db_id "$site")
      (set -e; update_one "$site" "$id" "${id}_owner" "${id}_app" "$backup")
    fi
    rc=$?
    set -e
    if [ "$rc" -ne 0 ]; then
      audit update-all "$site" failed
      echo "ancora-site: update FAILED on $site; it stays in maintenance mode." >&2
      echo "ancora-site: updated: ${done_sites[*]:-none}; not attempted: ${pending[*]:-none}" >&2
      exit 1
    fi
    done_sites+=("$site")
    audit update-all "$site" ok
  done
  say "updated ${#done_sites[@]} sites: ${done_sites[*]:-none}"
}

command=${1:-}
shift || true
case "$command" in
  control-install) cmd_control_install "$@" ;;
  provision) cmd_provision "$@" ;;
  admin-link) cmd_admin_link "$@" ;;
  suspend) cmd_suspend "$@" ;;
  resume) cmd_resume "$@" ;;
  deprovision) cmd_deprovision "$@" ;;
  update-all) cmd_update_all "$@" ;;
  list) cmd_list ;;
  work) cmd_work "$@" ;;
  *) sed -n '2,40p' "$0" | sed 's/^# \{0,1\}//'; exit 2 ;;
esac
