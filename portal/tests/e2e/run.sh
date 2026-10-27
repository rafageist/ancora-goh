#!/usr/bin/env bash
# HTTP end-to-end test of the R0 access-request portal.
#
#   dev/sql/tools/with-ephemeral-pg.sh portal/tests/e2e/run.sh
#
# Refuses to run outside a verified disposable PostgreSQL cluster. Creates the
# control-plane database with the owner/runtime role split of production, starts
# the portal with PHP's built-in server on 127.0.0.1 only, and drives it with
# curl like a browser (cookies, form tokens), including hostile input. Email goes
# to a private outbox directory. Synthetic data only. Exit 0 only if every check
# passes.
set -uo pipefail

repo=$(cd "$(dirname "$0")/../../.." && pwd)
portal=$repo/portal
. "$repo/dev/sql/tools/disposable-guard.sh"
agoh_require_disposable || exit 3
PHP=${PHP:-php}

work=$(mktemp -d "${TMPDIR:-/tmp}/portal-e2e.XXXXXX")
pid=""
cleanup() { [ -n "$pid" ] && kill "$pid" 2>/dev/null && wait "$pid" 2>/dev/null; rm -rf "$work"; }
trap cleanup EXIT
pass=0; fail=0
ok()  { echo "PASS  $1"; pass=$((pass + 1)); }
bad() { echo "FAIL  $1"; fail=$((fail + 1)); }
check() { local d=$1; shift; if "$@" >/dev/null 2>&1; then ok "$d"; else bad "$d"; fi; }

# ------------------------------------------------------------ database: owner/runtime split
db=timetables_control
createdb "$db"
psql -X -q -v ON_ERROR_STOP=1 -d "$db" -c "CREATE ROLE tt_control_app LOGIN" >/dev/null
psql -X -q -v ON_ERROR_STOP=1 -d "$db" -f "$portal/sql/schema.sql" >/dev/null || { echo "FAIL  schema"; exit 1; }
psql -X -q -v ON_ERROR_STOP=1 -v app_role=tt_control_app -d "$db" -f "$portal/sql/grants.sql" >/dev/null || { echo "FAIL  grants"; exit 1; }
sql() { psql -X -At -d "$db" -c "$1"; }

# ------------------------------------------------------------ portal on 127.0.0.1
port=$("$PHP" -r '$s=stream_socket_server("tcp://127.0.0.1:0"); echo explode(":", stream_socket_get_name($s,false))[1];')
head -c 48 /dev/urandom | base64 > "$work/secret"; chmod 600 "$work/secret"
export PORTAL_BASE_URL="http://127.0.0.1:$port" PORTAL_SECRET_FILE="$work/secret" PORTAL_COOKIE_SECURE=0 \
  PORTAL_FORM_MIN_SECONDS=1 PORTAL_MAIL_DRIVER=outbox PORTAL_OUTBOX_DIR="$work/outbox" \
  PORTAL_DB_DSN="pgsql:host=$PGHOST;port=$PGPORT;dbname=$db" PORTAL_DB_USER=tt_control_app \
  PORTAL_CONTROLLER_NAME="Example Controller" PORTAL_CONTACT_EMAIL="privacy@example.org"
if [ "${PORTAL_E2E_SERVER:-builtin}" = native ]; then
  # Rehearsal of the production setup: the real Caddy site block and PHP-FPM pool
  # from deploy/native, with only paths, the socket and the address replaced.
  fpm=${PHP_FPM:-php-fpm8.4}
  pool=$work/pool.conf
  sed -e '/^user = \|^group = \|^listen\.owner\|^listen\.group/d' \
      -e "s#^listen = .*#listen = $work/fpm.sock#" \
      -e "s#/opt/divengine/timetables/portal#$portal#g; s#/etc/divengine/timetables#$work#g" \
      -e "s#^php_admin_value\[error_log\] = .*#php_admin_value[error_log] = $work/php.log#" \
      -e '/^env\[/d' "$portal/deploy/native/php-fpm-timetables.conf" > "$pool"
  sed -i "s#^php_admin_value\[open_basedir\] = .*#&:$work:$PGHOST#" "$pool"
  for v in PORTAL_BASE_URL PORTAL_SECRET_FILE PORTAL_COOKIE_SECURE PORTAL_FORM_MIN_SECONDS PORTAL_MAIL_DRIVER \
           PORTAL_OUTBOX_DIR PORTAL_DB_DSN PORTAL_DB_USER PORTAL_CONTROLLER_NAME PORTAL_CONTACT_EMAIL; do
    printf 'env[%s] = "%s"\n' "$v" "${!v}" >> "$pool"
  done
  printf '[global]\npid = %s/fpm.pid\nerror_log = %s/fpm.log\ndaemonize = no\n' "$work" "$work" > "$work/fpm.conf"
  cat "$pool" >> "$work/fpm.conf"
  "$fpm" -F -y "$work/fpm.conf" >"$work/fpm.out" 2>&1 &
  fpm_pid=$!
  { printf '{\n\tadmin off\n\tauto_https off\n\tpersist_config off\n}\n(access_log) {\n\tlog {\n\t\toutput file %s/access.log\n\t}\n}\n' "$work"
    sed -e "s#^timetables.divengine.com {#http://127.0.0.1:$port {\n\tbind 127.0.0.1#" \
        -e "s#/opt/divengine/timetables/portal#$portal#g; s#unix//run/php/php8.5-fpm-timetables.sock#unix/$work/fpm.sock#" \
        "$portal/deploy/native/timetables.caddy"; } > "$work/Caddyfile"
  XDG_DATA_HOME="$work/caddy-data" XDG_CONFIG_HOME="$work/caddy-config" caddy run --config "$work/Caddyfile" --adapter caddyfile >"$work/server.log" 2>&1 &
  pid=$!
  trap 'kill $fpm_pid 2>/dev/null; wait $fpm_pid 2>/dev/null; cleanup' EXIT
else
  (cd "$portal/public" && exec "$PHP" -d display_errors=0 -d log_errors=1 -d error_log="$work/php.log" \
    -S "127.0.0.1:$port" index.php) >"$work/server.log" 2>&1 &
  pid=$!
fi
for _ in $(seq 100); do [ "$(curl -s -o /dev/null -w "%{http_code}" "http://127.0.0.1:$port/")" = 200 ] && break; sleep 0.1; done
base="http://127.0.0.1:$port"
listen=$(ss -Hltn "sport = :$port" | awk '{print $4}' | sort -u | tr '\n' ' ')
check "the portal listens on 127.0.0.1 only ($listen)" test "$listen" = "127.0.0.1:$port "

jar=$work/jar
get()  { curl -s -c "$jar" -b "$jar" -D "$work/h" -o "$work/b" -w '%{http_code}' "$base$1"; }
post() { local path=$1; shift; curl -s -c "$jar" -b "$jar" -D "$work/h" -o "$work/b" -w '%{http_code}' -X POST "$@" "$base$path"; }
token() { grep -o 'name="form_token" value="[^"]*"' "$work/b" | head -1 | sed 's/.*value="//; s/"$//'; }
has()   { grep -qF -- "$1" "$work/${2:-b}"; }
form() { # extra curl args for a valid request
  printf '%s\n' --data-urlencode "form_token=$1" --data-urlencode "full_name=${2:-Ada Lovelace}" \
    --data-urlencode "email=${3:-ada@example.org}" --data-urlencode "organization=Analytical School" \
    --data-urlencode "organization_type=institution" --data-urlencode "country=GB" --data-urlencode "role=coordinator" \
    --data-urlencode "intended_use=Weekly timetables for thirty groups and forty teachers." --data-urlencode "privacy=accepted"
}
fresh_form() { get /request >/dev/null; sleep 1.1; token; }

# ------------------------------------------------------------ pages and headers
check "GET / is 200" test "$(get /)" = 200
for h in "Content-Security-Policy: default-src 'none'" "X-Content-Type-Options: nosniff" "X-Frame-Options: DENY" "Referrer-Policy: no-referrer"; do
  check "header: $h" has "$h" h
done
check "no X-Powered-By header" bash -c "! grep -qi '^X-Powered-By' '$work/h'"
check "privacy notice published with controller and contact" bash -c "[ \"\$(curl -s -o '$work/p' -w '%{http_code}' '$base/privacy')\" = 200 ] && grep -q 'privacy@example.org' '$work/p' && grep -q 'Example Controller' '$work/p'"
check "terms published" test "$(get /terms)" = 200
check "health endpoint" test "$(get /healthz)" = 200
for p in /login /index.php /control/Application.php /model/data/PartModel.php /www/index.php /bin/portal \
         /vendor/autoload.php /sql/schema.sql /composer.json /.env /admin /api/requests "/?execute"; do
  code=$(get "$p")
  if [ "$p" = "/?execute" ]; then check "legacy query string on / is just the home page" bash -c "[ $code = 200 ] && ! grep -q execute '$work/b'"
  else check "no legacy, admin or source path: $p -> 404" test "$code" = 404; fi
done
check "wrong method is 405" test "$(post / --data x=1)" = 405

# ------------------------------------------------------------ CSRF, timing, origin, honeypot
check "GET /request sets the CSRF cookie" bash -c "[ \"\$(curl -s -c '$work/jar2' -o '$work/b2' -w '%{http_code}' '$base/request')\" = 200 ] && grep -q portal_csrf '$work/jar2'"
before=$(sql "SELECT count(*) FROM portal.access_request")
mapfile -t args < <(form "")
check "POST without a form token is refused (400)" test "$(post /request "${args[@]}")" = 400
get /request >/dev/null; t=$(token)
mapfile -t args < <(form "$t")
check "a form sent instantly is refused (timing)" bash -c "[ \"\$(curl -s -c '$jar' -b '$jar' -o /dev/null -w '%{http_code}' -X POST $(printf "%q " "${args[@]}") '$base/request')\" = 400 ]"
t=$(fresh_form); mapfile -t args < <(form "$t")
check "a cross-origin POST is refused (403)" test "$(post /request -H 'Origin: https://evil.example' "${args[@]}")" = 403
check "a token without its cookie is refused" bash -c "[ \"\$(curl -s -o /dev/null -w '%{http_code}' -X POST $(printf "%q " "${args[@]}") '$base/request')\" = 400 ]"
t=$(fresh_form); mapfile -t args < <(form "$t" "Bot" "bot@example.org")
code=$(post /request "${args[@]}" --data-urlencode "website_url=http://spam.example")
check "honeypot: generic answer, nothing stored" bash -c "[ $code = 200 ] && [ \"\$(psql -X -At -d $db -c 'SELECT count(*) FROM portal.access_request')\" = $before ]"

# ------------------------------------------------------------ hostile input is escaped and bound
t=$(fresh_form); mapfile -t args < <(form "$t" "<script>alert(1)</script>" "x@example.org")
code=$(post /request "${args[@]}" --data-urlencode "intended_use=short")
check "invalid form is redisplayed (400)" test "$code" = 400
check "XSS payload is escaped on redisplay" bash -c "grep -qF '&lt;script&gt;alert(1)&lt;/script&gt;' '$work/b' && ! grep -qF '<script>alert(1)' '$work/b'"
check "no request stored for an invalid form" test "$(sql "SELECT count(*) FROM portal.access_request")" = "$before"

# ------------------------------------------------------------ submission, enumeration, verification
t=$(fresh_form); mapfile -t args < <(form "$t" "O'Brien\"); DROP TABLE portal.access_request; --" "ada@example.org")
code=$(post /request "${args[@]}"); cp "$work/b" "$work/first"
check "valid request accepted (200)" test "$code" = 200
check "SQL payload stored literally, table intact" test "$(sql "SELECT full_name FROM portal.access_request")" = "O'Brien\"); DROP TABLE portal.access_request; --"
check "status is email_verification_pending" test "$(sql "SELECT status FROM portal.access_request")" = email_verification_pending
mails=$(ls "$work/outbox" | wc -l)
check "one verification email in the outbox" test "$mails" = 1
link=$(grep -ho "$base/verify?token=[A-Za-z0-9_-]*" "$work"/outbox/*.eml | head -1)
check "the email holds a verification link" test -n "$link"
t=$(fresh_form); mapfile -t args < <(form "$t" "Someone Else" "ADA@example.org")
post /request "${args[@]}" >/dev/null; cp "$work/b" "$work/dup"
t=$(fresh_form); mapfile -t args < <(form "$t" "New Person" "new@example.org")
post /request "${args[@]}" >/dev/null; cp "$work/b" "$work/new"
check "enumeration: same answer for a known address" cmp -s "$work/first" "$work/dup"
check "enumeration: same answer for a new address" cmp -s "$work/first" "$work/new"
check "no second email for the known address" test "$(grep -l 'ADA@example.org\|To: ada@example.org' "$work"/outbox/*.eml | wc -l)" = 1
vtoken=${link##*token=}
code=$(get "/verify?token=$vtoken")
check "GET on the link only shows a confirm button (no state change)" bash -c "[ $code = 200 ] && [ \"\$(psql -X -At -d $db -c \"SELECT status FROM portal.access_request WHERE email_normalized='ada@example.org'\")\" = email_verification_pending ]"
ft=$(token)
code=$(post /verify --data-urlencode "form_token=$ft" --data-urlencode "verification=$vtoken")
check "confirming moves the request to pending_review (not approved)" bash -c "[ $code = 200 ] && [ \"\$(psql -X -At -d $db -c \"SELECT status FROM portal.access_request WHERE email_normalized='ada@example.org'\")\" = pending_review ]"
check "the confirmation page says no account was created" has "No account has been created"
check "a used link is refused" test "$(post /verify --data-urlencode "form_token=$ft" --data-urlencode "verification=$vtoken")" = 400
check "a forged token is refused" test "$(post /verify --data-urlencode "form_token=$ft" --data-urlencode "verification=' OR '1'='1")" = 400
check "the token is not stored in clear" bash -c "! psql -X -At -d $db -c 'SELECT * FROM portal.verification_token' | grep -q '$vtoken'"

# ------------------------------------------------------------ operator decision (CLI)
id=$(sql "SELECT id FROM portal.access_request WHERE email_normalized='ada@example.org'")
check "approval without an operator name is refused" bash -c "! '$PHP' '$portal/bin/portal' approve $id --reason=ok"
check "operator approves through the CLI" "$PHP" "$portal/bin/portal" approve "$id" --operator=e2e-maint --reason="synthetic test approval"
check "status approved, decision audited with operator and reason" test "$(sql "SELECT to_status || ' ' || actor || ' ' || reason FROM portal.request_event WHERE request_id='$id' ORDER BY id DESC LIMIT 1" | sed 's#/[^ ]*##')" = "approved e2e-maint synthetic test approval"
check "decision email sent" bash -c "grep -l 'approved' '$work'/outbox/*.eml | grep -q ."
check "approval created no account and no login exists" bash -c "[ -z \"\$(psql -X -At -d $db -c \"SELECT to_regclass('portal.account')\")\" ] && [ \"\$(curl -s -o /dev/null -w '%{http_code}' '$base/login')\" = 404 ]"

# ------------------------------------------------------------ rate limit per IP
codes=""
for _ in $(seq 12); do
  t=$(fresh_form); mapfile -t args < <(form "$t" "Burst" "burst$RANDOM@example.org")
  codes="$codes $(post /request "${args[@]}")"
done
check "per-IP rate limit answers 429 ($codes)" bash -c "echo '$codes' | grep -q 429"

# ------------------------------------------------------------ logs
check "the portal wrote structured log records (checks below are not vacuous)" bash -c "cat '$work/server.log' '$work/php.log' 2>/dev/null | grep -q '\"msg\":\"verification email sent\"'"
check "no PHP warning or error in the server log" bash -c "! grep -qiE 'warning|fatal|error' '$work/php.log' 2>/dev/null"
check "logs hold no full email address or token" bash -c "! grep -qE 'ada@example.org|$vtoken' '$work/server.log' '$work/php.log' 2>/dev/null"

if [ "$fail" != 0 ]; then
  for f in fpm.out fpm.log php.log server.log; do
    [ -s "$work/$f" ] && { echo "--- $f (last lines)"; tail -n 8 "$work/$f" | sed 's/^/    /'; }
  done
fi
echo "# portal e2e: $pass passed, $fail failed"
[ "$fail" = 0 ]
