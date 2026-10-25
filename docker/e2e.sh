#!/usr/bin/env bash
# End-to-end scheduling run against the Docker Compose deployment (#32).
#
#   docker/e2e.sh                     disposable stack (default; used by CI)
#   docker/e2e.sh --deployed          the running "ancora-goh" deployment
#   docker/e2e.sh --verify-persisted  re-check a timetable made by --deployed
#
# Disposable (default): builds the images and starts a separate Compose
# project "ancora-goh-e2e-<random>" with its own volume, network and
# throwaway secrets, on a free 127.0.0.1 port. It then
#   - initializes the database from the SQL sources twice (the second run
#     must change nothing), sets the administrator password with
#     bin/setpassword.php and runs the HTTP flows of dev/e2e/lib.sh
#     (login, denial, data entry, generation, readback, logout);
#   - checks that only 127.0.0.1 is published, the database has no published
#     port and an internal network, and the images hold no secret, no dev/
#     tree and no debug script;
#   - stops and starts the stack ("down", then "up") and checks that the
#     timetable, the teacher entered over HTTP and the password survived.
# On exit it removes that project only, including its volume and images
# (E2E_KEEP=1 keeps it for inspection). Nothing else is touched.
#
# --deployed: the same HTTP flows against the running deployment, which must
# be initialized and must not hold the synthetic data yet. The administrator
# password is read from the file in ANCORA_E2E_ADMIN_PASSWORD_FILE (it is not
# changed). The synthetic organization and its timetable stay as demo data;
# the temporary user without roles and the tst helper schema are removed.
#
# --verify-persisted: after a restart, logs in over HTTP and checks that the
# timetable made by --deployed is still there, unchanged.
#
# Exit status 0 only if every check passes.
set -uo pipefail

repo=$(cd "$(dirname "$0")/.." && pwd)
mode=disposable
case "${1:-}" in
  "") ;;
  --deployed) mode=deployed ;;
  --verify-persisted) mode=verify ;;
  *) sed -n '2,33p' "$0" | sed 's/^# \{0,1\}//'; exit 2 ;;
esac

STEP_TIMEOUT=${E2E_STEP_TIMEOUT:-30}
GEN_TIMEOUT=${E2E_GEN_TIMEOUT:-180}
dbname=${ANCORA_DB_NAME:-ancoragoh}
dbuser=${ANCORA_DB_USER:-ancoragoh}
work=$(mktemp -d "${TMPDIR:-/tmp}/agoh-docker-e2e.XXXXXX")
project=""

. "$repo/dev/e2e/lib.sh"

dc() { docker compose -f "$repo/compose.yaml" -p "$project" "$@"; }
sql() { dc exec -T db psql -X -At -v ON_ERROR_STOP=1 -U "$dbuser" -d "$dbname" -c "$1"; }
sql_file() { dc exec -T db psql -X -q -v ON_ERROR_STOP=1 -U "$dbuser" -d "$dbname" -f - < "$1" >/dev/null; }
app_cli() { dc exec -T -w /var/www/ancora/bin web php "$@"; }
since() { date -u +%Y-%m-%dT%H:%M:%SZ; }
collect_logs() { # since
  dc logs --no-log-prefix --no-color --since "$1" web > "$work/php.log" 2>&1
  dc logs --no-log-prefix --no-color --since "$1" db > "$work/pg.log" 2>&1
}
free_port() {
  local p
  for p in $(seq 18087 18187); do
    (exec 3<>"/dev/tcp/127.0.0.1/$p") 2>/dev/null || { echo "$p"; return 0; }
  done
  return 1
}

cleanup() {
  if [ "$mode" = disposable ] && [[ $project =~ ^ancora-goh-e2e-[0-9a-f]{8}$ ]]; then
    if [ "${E2E_KEEP:-0}" = 1 ]; then
      echo "# kept: docker compose -p $project ... (secrets in $work/secrets)"; return
    fi
    dc --profile setup down -v --rmi local --remove-orphans >/dev/null 2>&1
  fi
  rm -rf "$work"
}
trap cleanup EXIT

exposure_checks() {
  local web_id db_id hostips dbports internal listen
  web_id=$(dc ps -q web); db_id=$(dc ps -q db)
  hostips=$(docker inspect -f '{{range $p, $b := .NetworkSettings.Ports}}{{range $b}}{{.HostIp}}:{{.HostPort}} {{end}}{{end}}' "$web_id")
  check "web publishes only 127.0.0.1:$port ($hostips)" test "$hostips" = "127.0.0.1:$port "
  dbports=$(docker inspect -f '{{range $p, $b := .NetworkSettings.Ports}}{{range $b}}{{.HostIp}}:{{.HostPort}} {{end}}{{end}}' "$db_id")
  check "database publishes no host port" test -z "$dbports"
  internal=$(docker network inspect -f '{{.Internal}}' "${project}_backend")
  check "database network is internal (no route out)" test "$internal" = true
  listen=$(ss -Hltn "sport = :$port" 2>/dev/null | awk '{print $4}' | sort -u | tr '\n' ' ')
  check "host listens on port $port at 127.0.0.1 only ($listen)" test "$listen" = "127.0.0.1:$port "
}

image_checks() { # secret values that must not appear
  local img
  check "the web image holds no dev/, docs/, .git, historical dumps or debug scripts" \
    dc exec -T web sh -c 'cd /var/www/ancora && for p in dev docs .git dev/backup www/test.php www/test2.php www/test.html bin/test.php www/js/jquery/jquery-treeview/demo; do [ ! -e "$p" ] || exit 1; done; [ -L bin/ancoragoh.conf ]'
  check "the application tree is read-only for the web server user" \
    dc exec -T web sh -c '! touch /var/www/ancora/www/x 2>/dev/null && ! touch /var/www/ancora/bin/x 2>/dev/null'
  check "Apache and PHP run as www-data, not root" \
    test "$(dc exec -T web sh -c 'for p in /proc/[0-9]*; do [ "$(cat $p/comm 2>/dev/null)" = apache2 ] && stat -c %U $p; done; id -un' | sort -u | tr '\n' ' ')" = "www-data "
  for img in "${project}-web" "${project}-setup"; do
    if docker history --no-trunc --format '{{.CreatedBy}}' "$img" | grep -qF -e "$1" -e "$2"; then
      bad "image $img: a secret appears in the build history"
    elif docker run --rm --network none --entrypoint sh "$img" -c 'grep -rlsF -e "$1" -e "$2" / --exclude-dir=proc --exclude-dir=sys --exclude-dir=dev 2>/dev/null | head -1' _ "$1" "$2" | grep -q .; then
      bad "image $img: a secret appears in the image file system"
    else
      ok "image $img: no secret in its layers or history"
    fi
  done
  check "docker compose config shows no secret value" bash -c "! docker compose -f '$repo/compose.yaml' -p '$project' config 2>/dev/null | grep -qF -e '$1' -e '$2'"
}

persisted_checks() { # admin password
  local tb code
  tb=$(sql "SELECT id FROM profesor WHERE nombre = 'Teacher Beta'")
  check "Teacher Beta (entered over HTTP) is still stored" test -n "$tb"
  check "the timetable is unchanged (placement digest)" \
    test "$(printf '%s' "$(e2e_placements)" | md5sum | cut -d' ' -f1)" = "$(e2e_expected_digest)"
  check "metrics unchanged: 8 of 8 placed, no collision" \
    test "$(sql "SELECT tst.metrics('E', 'N', 'PER')" | sed 's/^METRIC //')" = "demands=8 placed=8 unplaced=0 collisions=0 overflow=0"
  rm -f "$work/persist.jar"
  code=$(login persist persist.html admin "$1")
  check "administrator still logs in with the same password (HTTP $code)" \
    bash -c "[ $code = 200 ] && ! grep -q 'access_denied' '$work/persist.html' && ! grep -q 'name=\"edtUser\"' '$work/persist.html'"
  e2e_readback persist "$tb"
  e2e_logout persist
}

# ------------------------------------------------------------------ disposable
if [ "$mode" = disposable ]; then
  project=ancora-goh-e2e-$(od -An -N4 -tx1 /dev/urandom | tr -d ' \n')
  port=$(free_port) || { echo "FAIL  no free loopback port"; exit 1; }
  mkdir -m 700 "$work/secrets"
  for f in postgres_password db_password; do
    od -An -N16 -tx1 /dev/urandom | tr -d ' \n' > "$work/secrets/$f"; chmod 644 "$work/secrets/$f"
  done
  export ANCORA_SECRETS_DIR=$work/secrets ANCORA_HTTP_PORT=$port
  echo "# compose project $project on 127.0.0.1:$port"
  check "docker compose config is valid" bash -c "docker compose -f '$repo/compose.yaml' -p '$project' --profile setup config -q"
  timeout 1200 docker compose -f "$repo/compose.yaml" -p "$project" --profile setup build > "$work/build.log" 2>&1 \
    || { tail -30 "$work/build.log"; echo "FAIL  image build"; exit 1; }
  ok "images build"
  t0=$(since)
  timeout 300 docker compose -f "$repo/compose.yaml" -p "$project" up -d --wait --wait-timeout 240 > "$work/up.log" 2>&1 \
    || { cat "$work/up.log"; dc logs > "$work/all.log" 2>&1; tail -40 "$work/all.log"; echo "FAIL  stack start"; exit 1; }
  ok "db and web start and become healthy"
  exposure_checks

  echo "# database: initialization from the SQL sources"
  timeout 300 docker compose -f "$repo/compose.yaml" -p "$project" run --rm setup > "$work/setup1.log" 2>&1
  check "first initialization installs the schema" grep -q "installed from the maintained SQL sources" "$work/setup1.log"
  [ $fail = 0 ] || { cat "$work/setup1.log"; exit 1; }
  rows_before=$(sql "SELECT count(*) FROM seguridad.usuario")
  timeout 300 docker compose -f "$repo/compose.yaml" -p "$project" run --rm setup > "$work/setup2.log" 2>&1
  check "second initialization changes nothing" bash -c "grep -q 'already initialized; nothing was changed' '$work/setup2.log' && [ \"\$(docker compose -f '$repo/compose.yaml' -p '$project' exec -T db psql -X -At -U '$dbuser' -d '$dbname' -c 'SELECT count(*) FROM seguridad.usuario')\" = '$rows_before' ]"
  check "the application role is not a superuser and cannot create databases" \
    test "$(sql "SELECT rolsuper OR rolcreatedb OR rolcreaterole FROM pg_roles WHERE rolname = current_user")" = f

  sql_file "$repo/dev/e2e/fixture.sql" || { echo "FAIL  fixture"; exit 1; }
  sql_file "$repo/dev/sql/tests/fixtures/helpers.sql" || { echo "FAIL  helpers"; exit 1; }
  base="http://127.0.0.1:$port/index.php"
  e2e_set_admin_password
  e2e_add_plain_user
  e2e_http_flows
  collect_logs "$t0"
  e2e_log_checks "$work/php.log" "$work/pg.log"
  image_checks "$(cat "$work/secrets/postgres_password")" "$(cat "$work/secrets/db_password")"

  echo "# persistence: down, then up"
  timeout 180 docker compose -f "$repo/compose.yaml" -p "$project" down > "$work/down.log" 2>&1
  check "down removes the containers and keeps the data volume" \
    bash -c "[ -z \"\$(docker compose -f '$repo/compose.yaml' -p '$project' ps -aq)\" ] && docker volume inspect '${project}_pgdata' >/dev/null"
  t1=$(since)
  timeout 300 docker compose -f "$repo/compose.yaml" -p "$project" up -d --wait --wait-timeout 240 > "$work/up2.log" 2>&1
  check "the stack starts again from the existing volume" test $? = 0
  persisted_checks "$admin_pw"
  timeout 300 docker compose -f "$repo/compose.yaml" -p "$project" run --rm setup > "$work/setup3.log" 2>&1
  check "initialization after a restart changes nothing" grep -q 'already initialized; nothing was changed' "$work/setup3.log"
  collect_logs "$t1"
  e2e_log_checks "$work/php.log" "$work/pg.log"

# ------------------------------------------------------------------ deployed
else
  project=$(docker compose -f "$repo/compose.yaml" config --format json 2>/dev/null | sed -n 's/^ *"name": *"\([^"]*\)".*/\1/p' | head -1)
  [ -n "$project" ] || { echo "cannot read the project name from compose.yaml" >&2; exit 2; }
  [ -n "$(dc ps -q --status running web)" ] && [ -n "$(dc ps -q --status running db)" ] \
    || { echo "the $project deployment is not running (docker/manage.sh up)" >&2; exit 2; }
  pwfile=${ANCORA_E2E_ADMIN_PASSWORD_FILE:-}
  [ -n "$pwfile" ] && [ -r "$pwfile" ] || { echo "set ANCORA_E2E_ADMIN_PASSWORD_FILE to a file holding the administrator password" >&2; exit 2; }
  admin_pw=$(head -1 "$pwfile")
  port=$(dc port web 8080 | sed 's/.*://')
  base="http://127.0.0.1:$port/index.php"
  echo "# compose project $project on 127.0.0.1:$port ($mode)"
  exposure_checks
  [ "$(sql "SELECT to_regclass('seguridad.usuario') IS NOT NULL")" = t ] || { echo "the database is not initialized (docker/manage.sh init-db)" >&2; exit 2; }
  have_fixture=$(sql "SELECT count(*) FROM periodo WHERE id = 'PER'")
  t0=$(since)
  if [ "$mode" = deployed ]; then
    [ "$have_fixture" = 0 ] || { echo "the synthetic data is already loaded; use --verify-persisted" >&2; exit 2; }
    sql_file "$repo/dev/e2e/fixture.sql" || { echo "FAIL  fixture"; exit 1; }
    sql_file "$repo/dev/sql/tests/fixtures/helpers.sql" || { echo "FAIL  helpers"; exit 1; }
    e2e_add_plain_user
    e2e_http_flows
    sql "DELETE FROM seguridad.usuario WHERE nick = 'e2e_plain'" >/dev/null
    check "the temporary user without roles is removed" test "$(sql "SELECT count(*) FROM seguridad.usuario WHERE nick = 'e2e_plain'")" = 0
  else
    [ "$have_fixture" = 1 ] || { echo "no synthetic data to verify; run --deployed first" >&2; exit 2; }
    sql_file "$repo/dev/sql/tests/fixtures/helpers.sql" || { echo "FAIL  helpers"; exit 1; }
    persisted_checks "$admin_pw"
  fi
  sql "DROP SCHEMA tst CASCADE" >/dev/null 2>&1
  collect_logs "$t0"
  e2e_log_checks "$work/php.log" "$work/pg.log"
fi

echo "# docker e2e ($mode): $pass passed, $fail failed"
[ "$fail" = 0 ]
