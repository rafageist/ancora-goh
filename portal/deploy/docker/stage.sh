#!/usr/bin/env bash
# Private staging of the R0 portal with Docker Compose (loopback only).
#
#   portal/deploy/docker/stage.sh init-secrets   random secrets in deploy/docker/secrets (git-ignored)
#   portal/deploy/docker/stage.sh up             build, start db + web, create the database
#   portal/deploy/docker/stage.sh verify         HTTP journey against the staged stack
#   portal/deploy/docker/stage.sh operator ARGS  run bin/portal in the web container
#   portal/deploy/docker/stage.sh backup|restore-test
#   portal/deploy/docker/stage.sh down           stop (keeps data)   | reset: delete staging data
set -euo pipefail

here=$(cd "$(dirname "$0")" && pwd)
dc() { docker compose -f "$here/compose.yaml" "$@"; }
port=${PORTAL_HTTP_PORT:-8088}
base="http://127.0.0.1:$port"

case "${1:-help}" in
  init-secrets)
    mkdir -p "$here/secrets"; chmod 700 "$here/secrets"
    for f in pg_superuser portal_db_password portal_hmac; do
      [ -e "$here/secrets/$f" ] && { echo "kept     $f"; continue; }
      od -An -N32 -tx1 /dev/urandom | tr -d ' \n' > "$here/secrets/$f"; chmod 644 "$here/secrets/$f"
      echo "created  $f"
    done ;;
  up)
    dc up -d --wait db
    dc run --rm setup
    dc up -d --build --wait web
    echo "staging portal: $base/ (loopback only; email goes to the outbox volume)" ;;
  operator) shift; dc exec -T web php /srv/portal/bin/portal "$@" ;;
  verify)
    pass=0; fail=0; jar=$(mktemp); body=$(mktemp)
    trap 'rm -f "$jar" "$body"' EXIT
    t() { if eval "$2" >/dev/null 2>&1; then echo "PASS  $1"; pass=$((pass+1)); else echo "FAIL  $1"; fail=$((fail+1)); fi; }
    t "home page 200" "[ \$(curl -s -o '$body' -w '%{http_code}' $base/) = 200 ]"
    t "CSP header" "curl -sI $base/ | grep -qi \"content-security-policy: default-src 'none'\""
    t "no server version banner" "! curl -sI $base/ | grep -qiE '^server: .*[0-9]|x-powered-by'"
    for p in /index.php/../../vendor/autoload.php /vendor/autoload.php /src/Config.php /sql/schema.sql /bin/portal /templates/layout.php /login; do
      t "not reachable: $p" "[ \$(curl -s -o /dev/null -w '%{http_code}' $base$p) = 404 ]"
    done
    t "health" "[ \"\$(curl -s $base/healthz)\" = ok ]"
    curl -s -c "$jar" -b "$jar" -o "$body" "$base/request"
    tok=$(grep -o 'name="form_token" value="[^"]*"' "$body" | sed 's/.*value="//; s/"$//'); sleep 3.2
    email="stage-$(date +%s)@example.org"
    code=$(curl -s -c "$jar" -b "$jar" -o "$body" -w '%{http_code}' -X POST "$base/request" \
      --data-urlencode "form_token=$tok" --data-urlencode "full_name=Staging Tester" --data-urlencode "email=$email" \
      --data-urlencode "organization=Staging School" --data-urlencode organization_type=institution --data-urlencode country=ES \
      --data-urlencode role=coordinator --data-urlencode "intended_use=Staging verification of the request journey." --data-urlencode privacy=accepted)
    t "request accepted" "[ $code = 200 ]"
    link=$(dc exec -T web sh -c 'cat /outbox/*.eml' | grep -o "$base/verify?token=[A-Za-z0-9_-]*" | tail -1)
    t "verification email in the outbox" "[ -n '$link' ]"
    curl -s -c "$jar" -b "$jar" -o "$body" "$link"
    ft=$(grep -o 'name="form_token" value="[^"]*"' "$body" | sed 's/.*value="//; s/"$//')
    code=$(curl -s -c "$jar" -b "$jar" -o "$body" -w '%{http_code}' -X POST "$base/verify" --data-urlencode "form_token=$ft" --data-urlencode "verification=${link##*token=}")
    t "email verified" "[ $code = 200 ]"
    id=$(dc exec -T web php /srv/portal/bin/portal list --status=pending_review | grep -o '^[0-9a-f-]\{36\}' | tail -1)
    t "request waits for review" "[ -n '$id' ]"
    t "operator approval (CLI)" "dc exec -T web php /srv/portal/bin/portal approve $id --operator=staging --reason='staging journey'"
    t "decision audited" "dc exec -T web php /srv/portal/bin/portal show $id | grep -q 'operator  staging'"
    echo "# staging verify: $pass passed, $fail failed"; [ "$fail" = 0 ] ;;
  backup)
    mkdir -p "$here/backups"; chmod 700 "$here/backups"
    f="$here/backups/timetables_control-$(date -u +%Y%m%dT%H%M%SZ).dump"
    (umask 077; dc exec -T db pg_dump -U postgres -Fc -d timetables_control > "$f"); echo "$f" ;;
  restore-test)
    # restore the latest backup into a scratch database and compare row counts
    f=$(ls -1 "$here"/backups/*.dump | tail -1)
    dc exec -T db dropdb -U postgres --if-exists restore_check
    dc exec -T db createdb -U postgres restore_check
    dc exec -T db pg_restore -U postgres --no-owner --exit-on-error -d restore_check < "$f"
    q="SELECT (SELECT count(*) FROM portal.access_request) || '/' || (SELECT count(*) FROM portal.request_event)"
    a=$(dc exec -T db psql -X -At -U postgres -d timetables_control -c "$q"); b=$(dc exec -T db psql -X -At -U postgres -d restore_check -c "$q")
    dc exec -T db dropdb -U postgres restore_check
    [ "$a" = "$b" ] && echo "restore-test: OK ($f, requests/events $b)" || { echo "restore-test: MISMATCH $a vs $b"; exit 1; } ;;
  down) dc down ;;
  reset) dc --profile setup down -v ;;
  *) sed -n '2,10p' "$0" ;;
esac
