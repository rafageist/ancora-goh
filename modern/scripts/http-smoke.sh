#!/usr/bin/env bash
# HTTP smoke test of an installed Áncora site (issue #69).
#
#   modern/scripts/http-smoke.sh <base URL> <admin password file>
#
# Only loopback URLs are accepted. The password is sent from its file
# (curl --data-urlencode name@file) and never appears in arguments or output.
# Checks: front page, security headers, the status route denied to anonymous
# and allowed to the administrator (rendered with the theme component), the
# administrator login, adding an academic term through its form, the term in
# the administrative list and in the Views listing, and anonymous denial of
# both lists. Exit status 0 only if every check passes.
set -euo pipefail

base=${1:?base URL}
password_file=${2:?admin password file}
case "$base" in
  http://127.0.0.1:*|http://localhost:*) ;;
  *) echo "refusing a non-loopback base URL" >&2; exit 2 ;;
esac
[ -r "$password_file" ] || { echo "password file not readable" >&2; exit 2; }

work=$(mktemp -d "${TMPDIR:-/tmp}/ancora-smoke.XXXXXX")
trap 'rm -rf "$work"' EXIT
jar="$work/cookies"
pass=0 fail=0

check() { # $1 = description, $2... = test command
  local what=$1; shift
  if "$@"; then pass=$((pass + 1)); echo "ok   $what"; else fail=$((fail + 1)); echo "FAIL $what"; fi
}
# fetch <path> [curl args...]: body to $work/body, headers to $work/headers, status to $work/status
fetch() {
  local path=$1; shift
  curl -s -o "$work/body" -D "$work/headers" -w '%{http_code}' -b "$jar" -c "$jar" "$@" "$base$path" >"$work/status"
}
status_is() { [ "$(cat "$work/status")" = "$1" ]; }
body_has() { grep -q -- "$1" "$work/body"; }
header_has() { grep -qi -- "$1" "$work/headers"; }
form_value() { # $1 = input name: value of that hidden input in $work/body
  grep -o "name=\"$1\" value=\"[^\"]*\"" "$work/body" | head -1 | sed 's/.*value="\([^"]*\)"/\1/'
}

anonymous() { rm -f "$jar"; }

anonymous
fetch /
check "front page answers 200" status_is 200
check "X-Content-Type-Options: nosniff" header_has '^x-content-type-options: nosniff'
check "X-Frame-Options: SAMEORIGIN" header_has '^x-frame-options: SAMEORIGIN'
check "front page uses the Áncora theme" body_has 'class="site-header"'
check "no PHP error text on the front page" bash -c "! grep -qiE 'fatal error|stack trace|warning:' '$work/body'"

fetch /ancora/status
check "status route denied to anonymous (403)" status_is 403
fetch /admin/content/ancora-term
check "term administration denied to anonymous (403)" status_is 403
fetch /ancora/terms
check "term listing denied to anonymous (403)" status_is 403

fetch /user/login
build_id=$(form_value form_build_id)
fetch /user/login --data-urlencode name=admin --data-urlencode pass=wrong-password \
  --data-urlencode form_id=user_login_form --data-urlencode "form_build_id=$build_id" --data-urlencode "op=Log in"
check "a wrong password is refused" body_has 'Unrecognized username or password'
fetch /user/login
build_id=$(form_value form_build_id)
fetch /user/login --data-urlencode name=admin --data-urlencode "pass@$password_file" \
  --data-urlencode form_id=user_login_form --data-urlencode "form_build_id=$build_id" --data-urlencode "op=Log in"
check "administrator login redirects (303)" status_is 303

fetch /ancora/status
check "status route allowed to the administrator (200)" status_is 200
check "status page rendered by the status-card component" body_has 'class="status-card"'
check "status page reports PostgreSQL" body_has 'pgsql'

code="SMOKE-$(od -An -N3 -tx1 /dev/urandom | tr -d ' \n')"
fetch /admin/content/ancora-term/add
check "term add form answers 200" status_is 200
build_id=$(form_value form_build_id)
token=$(form_value form_token)
fetch /admin/content/ancora-term/add \
  --data-urlencode "label[0][value]=Smoke term" --data-urlencode "code[0][value]=$code" \
  --data-urlencode "starts_on[0][value][date]=2026-09-01" --data-urlencode "ends_on[0][value][date]=2027-01-29" \
  --data-urlencode form_id=ancora_academic_term_add_form --data-urlencode "form_build_id=$build_id" \
  --data-urlencode "form_token=$token" --data-urlencode op=Save
check "term saved (303 to the list)" status_is 303
fetch /admin/content/ancora-term
check "term in the administrative list" body_has "$code"
fetch /ancora/terms
check "term in the Views listing" body_has "$code"

echo "http-smoke: $pass passed, $fail failed"
[ "$fail" -eq 0 ]
