#!/usr/bin/env bash
# Multisite verification (issue #72): the control site and two organization
# sites behind the real production stack, Caddy and PHP-FPM, on 127.0.0.1,
# against a disposable PostgreSQL cluster.
#
#   dev/sql/tools/with-ephemeral-pg.sh modern/scripts/multisite-verify.sh [--skip-composer]
#
# Environment: CADDY (default caddy), PHP_FPM (default php-fpm8.4, then
# php-fpm8.5, then php-fpm).
#
# What it does:
# 1. Provisions the control site and the organizations alpha and beta with
#    scripts/ancora-site.sh, exactly as an operator would.
# 2. Runs PHP-FPM with deploy/php-fpm/ancora.conf and Caddy with
#    deploy/caddy/ancora.caddy (only users, sockets, logs and paths are
#    replaced), loopback only, with Caddy's admin API off and its data and
#    configuration directories inside the temporary directory.
# 3. Checks routing, site and database selection, unknown sites, traversal
#    and Host header tricks, sessions and cookies, administrator access,
#    private and public files, page and render caches, database roles,
#    idempotent and failed provisioning, suspension, retirement and the
#    update loop. A PHP built-in server with a careless router
#    (scripts/lib/naive-router.php) proves the Drupal-side guards without
#    Caddy's filtering.
#
# Every check prints PASS or FAIL; the summary has the counts. Exit status 0
# only if every check passes. Everything is deleted on exit: the processes,
# the site directories under web/, the temporary directory (the cluster is
# deleted by with-ephemeral-pg.sh).
set -euo pipefail

skip_composer=0
for arg in "$@"; do
  case "$arg" in
    --skip-composer) skip_composer=1 ;;
    *) echo "unknown option: $arg" >&2; exit 2 ;;
  esac
done

project=$(cd "$(dirname "$0")/.." && pwd -P)
repo=$(dirname "$project")
web=$project/web
# shellcheck source=../../dev/sql/tools/disposable-guard.sh
. "$repo/dev/sql/tools/disposable-guard.sh"
agoh_require_disposable || exit 3

caddy_bin=${CADDY:-caddy}
fpm_bin=${PHP_FPM:-}
if [ -z "$fpm_bin" ]; then
  for c in php-fpm8.4 php-fpm8.5 php-fpm; do
    if command -v "$c" >/dev/null; then fpm_bin=$c; break; fi
  done
fi
command -v "$caddy_bin" >/dev/null || { echo "caddy not found" >&2; exit 2; }
[ -n "$fpm_bin" ] && command -v "$fpm_bin" >/dev/null || { echo "php-fpm not found" >&2; exit 2; }

# The verification creates and deletes organization sites in the web root; it
# refuses to run where some already exist.
if [ -e "$web/o" ] || compgen -G "$web/sites/org-*" >/dev/null; then
  echo "refusing: web/o or web/sites/org-* already exist in $web" >&2
  exit 3
fi

started=$SECONDS
step() { printf '\n==> [%3ss] %s\n' "$((SECONDS - started))" "$*"; }

work=$(mktemp -d "${TMPDIR:-/tmp}/ancora-ms.XXXXXX")
work=$(cd "$work" && pwd -P)
pids=()
cleanup() {
  local pid
  for pid in "${pids[@]}"; do kill "$pid" 2>/dev/null || true; done
  for pid in "${pids[@]}"; do wait "$pid" 2>/dev/null || true; done
  rm -rf "$web/o"
  local d
  for d in "$web"/sites/org-*; do
    [ -e "$d" ] || continue
    chmod -R u+w "$d"
    rm -rf "$d"
  done
  chmod u+w "$web/sites/default" "$web/sites/default/settings.php" 2>/dev/null || true
  if [ "${ANCORA_VERIFY_KEEP:-0}" = 1 ]; then echo "kept $work"; else rm -rf "$work"; fi
}
trap cleanup EXIT

pass=0 fail=0
check() { # description, test command...
  local what=$1; shift
  if "$@"; then pass=$((pass + 1)); echo "PASS $what"; else fail=$((fail + 1)); echo "FAIL $what"; fi
}
section() { printf '\n--- %s\n' "$*"; }

free_port() { php -r '$s = stream_socket_server("tcp://127.0.0.1:0"); echo explode(":", stream_socket_get_name($s, FALSE))[1];'; }
port=$(free_port)
naive_port=$(free_port)
base="http://127.0.0.1:$port"

# Operator environment and the control site's settings.
mkdir -p "$work/etc/control" "$work/var/control/private" "$work/var/control/tmp" "$work/backup" "$work/log"
export ANCORA_SITES_DIR=$work/etc/sites ANCORA_SITES_DATA_DIR=$work/var/sites ANCORA_PUBLIC_URL=$base
export ANCORA_ENVIRONMENT=production ANCORA_TRUSTED_HOSTS=127.0.0.1,localhost
export ANCORA_DB_HOST=$PGHOST ANCORA_DB_PORT=$PGPORT
export ANCORA_DB_NAME=tt_control ANCORA_DB_USER=tt_control_app ANCORA_DB_OWNER_USER=tt_control_owner
export ANCORA_DB_PASSWORD_FILE=$work/etc/control/db-password
export ANCORA_DB_OWNER_PASSWORD_FILE=$work/etc/control/owner-password
export ANCORA_HASH_SALT_FILE=$work/etc/control/hash-salt
export ANCORA_PRIVATE_PATH=$work/var/control/private ANCORA_TMP_PATH=$work/var/control/tmp
site=$project/scripts/ancora-site.sh
drush=("$project/vendor/bin/drush" --root="$web" --yes)

cd "$project"
if [ "$skip_composer" -eq 0 ]; then
  step "composer install"
  composer install --no-interaction --no-progress
fi

# Every connection is logged: the checks read which role used which database.
psql -X -q -d postgres -c "ALTER SYSTEM SET log_connections = on" -c "SELECT pg_reload_conf()" >/dev/null
pg_log=$(dirname "$AGOH_DISPOSABLE_DATA")/server.log

step "Provisioning: control site, alpha, beta ($(psql -X -At -d postgres -c 'SHOW server_version'))"
"$site" control-install --admin-mail operator@example.invalid
t0=$SECONDS
"$site" provision alpha --name "Alpha School" --admin-mail admin@alpha.example.invalid
echo "provisioning alpha took $((SECONDS - t0)) s"
"$site" provision beta --name "Beta Institute" --admin-mail admin@beta.example.invalid
"$site" list

# Markers: one academic term, one public and one private file per site.
mark() { # site uri marker
  ANCORA_EXPECT_SITE_PATH=$1 "${drush[@]}" --uri="$2" php:eval "
    \Drupal::entityTypeManager()->getStorage('ancora_academic_term')->create(['label' => '$3 term', 'code' => '$3-MARK', 'starts_on' => '2026-09-01', 'ends_on' => '2027-01-29'])->save();
    file_put_contents('public://verify-$3.txt', '$3-PUBLIC');
    file_put_contents('private://verify-private.txt', '$3-PRIVATE');
    print \Drupal::service('file_system')->realpath('private://verify-private.txt') . PHP_EOL;"
}
mark sites/default "$base" CONTROL
mark sites/org-alpha "$base/o/alpha" ALPHA
mark sites/org-beta "$base/o/beta" BETA

step "PHP-FPM ($fpm_bin) and Caddy ($("$caddy_bin" version | cut -d' ' -f1)) on 127.0.0.1:$port"
{
  printf '[global]\npid = %s/fpm.pid\nerror_log = %s/log/fpm.log\ndaemonize = no\n' "$work" "$work"
  sed -e '/^user = \|^group = \|^listen\.owner\|^listen\.group/d' \
      -e "s#^listen = .*#listen = $work/fpm.sock#" \
      -e "s#^php_admin_value\[error_log\] = .*#php_admin_value[error_log] = $work/log/php.log#" \
      -e "s#/srv/ancora/modern#$project#g; s#/etc/ancora#$work/etc#g; s#/var/lib/ancora#$work/var#g" \
      -e '/^env\[/d' "$project/deploy/php-fpm/ancora.conf"
  for v in ANCORA_ENVIRONMENT ANCORA_DB_HOST ANCORA_DB_PORT ANCORA_TRUSTED_HOSTS ANCORA_SITES_DIR \
           ANCORA_DB_NAME ANCORA_DB_USER ANCORA_DB_PASSWORD_FILE ANCORA_HASH_SALT_FILE \
           ANCORA_PRIVATE_PATH ANCORA_TMP_PATH; do
    printf 'env[%s] = "%s"\n' "$v" "${!v}"
  done
} >"$work/fpm.conf"
"$fpm_bin" -F -y "$work/fpm.conf" >"$work/log/fpm.out" 2>&1 &
pids+=($!)
{
  printf '{\n\tadmin off\n\tauto_https off\n\tpersist_config off\n\tdefault_bind 127.0.0.1\n}\n'
  cat "$project/deploy/caddy/ancora.caddy"
} >"$work/Caddyfile"
# The test site address accepts any Host header, so the Drupal-side checks
# see the tricks; a production address names the host.
ANCORA_CADDY_SITE="http://:$port" ANCORA_WEB_ROOT="$web" ANCORA_FPM="unix/$work/fpm.sock" \
XDG_DATA_HOME="$work/caddy-data" XDG_CONFIG_HOME="$work/caddy-config" \
  "$caddy_bin" run --config "$work/Caddyfile" --adapter caddyfile >"$work/log/caddy.log" 2>&1 &
pids+=($!)
# The careless router on a second port (PHP built-in server, same settings).
(cd "$web" && exec env -i PATH="$PATH" ANCORA_ENVIRONMENT=production ANCORA_DB_HOST="$PGHOST" ANCORA_DB_PORT="$PGPORT" \
  ANCORA_TRUSTED_HOSTS=127.0.0.1,localhost ANCORA_SITES_DIR="$ANCORA_SITES_DIR" ANCORA_DB_NAME=tt_control \
  ANCORA_DB_USER=tt_control_app ANCORA_DB_PASSWORD_FILE="$ANCORA_DB_PASSWORD_FILE" ANCORA_HASH_SALT_FILE="$ANCORA_HASH_SALT_FILE" \
  ANCORA_PRIVATE_PATH="$ANCORA_PRIVATE_PATH" ANCORA_TMP_PATH="$ANCORA_TMP_PATH" \
  php -S "127.0.0.1:$naive_port" ../scripts/lib/naive-router.php) >"$work/log/naive.log" 2>&1 &
pids+=($!)
up=0
for _ in $(seq 1 100); do
  # A PHP page through Caddy (PHP-FPM ready) and through the careless router.
  if curl -sf -o /dev/null "$base/user/login" && curl -sf -o /dev/null "http://127.0.0.1:$naive_port/user/login"; then up=1; break; fi
  sleep 0.2
done
if [ "$up" != 1 ]; then
  for f in "$work"/log/*; do echo "--- $f"; tail -20 "$f"; done
  echo "the servers did not start" >&2
  exit 1
fi
listen=$(ss -Hltn | awk '{print $4}' | grep -E ":($port|$naive_port)\$" | sort -u | tr '\n' ' ')

# HTTP helpers: body, headers and status of the last request in $work/r.*.
mkdir -p "$work/jar"
req() { # jar (or -) path [curl args...]
  local jar=$1 path=$2; shift 2
  local cookies=()
  [ "$jar" = - ] || cookies=(-b "$work/jar/$jar" -c "$work/jar/$jar")
  curl -s --path-as-is -o "$work/r.body" -D "$work/r.headers" -w '%{http_code}' "${cookies[@]}" "$@" "$base$path" >"$work/r.status" || echo 000 >"$work/r.status"
}
naive() { # path [curl args...]
  local path=$1; shift
  curl -s --path-as-is -o "$work/r.body" -D "$work/r.headers" -w '%{http_code}' "$@" "http://127.0.0.1:$naive_port$path" >"$work/r.status" || echo 000 >"$work/r.status"
}
status_is() { [ "$(cat "$work/r.status")" = "$1" ]; }
status_in() { local s; s=$(cat "$work/r.status"); [[ " $* " == *" $s "* ]]; }
body_has() { grep -q -- "$1" "$work/r.body"; }
body_lacks() { ! grep -q -- "$1" "$work/r.body"; }
header_has() { grep -qi -- "$1" "$work/r.headers"; }
title_has() { grep -o '<title>[^<]*</title>' "$work/r.body" | grep -q -- "$1"; }
not_drupal_page() { ! grep -q 'class="site-header"\|<title>' "$work/r.body"; }
sql() { psql -X -At -d "$1" -c "$2"; }
session_cookie() { # jar: "name=value" of the Drupal session cookie
  awk '$6 ~ /^S?SESS/ {print $6 "=" $7}' "$work/jar/$1" | tail -1
}
login_with_link() { # jar site: the operator's one-time link, used once
  local link
  link=$("$site" admin-link "$2" | tail -1)
  echo "$link" >"$work/link-$2"
  [[ $link == "$base/o/$2/user/reset/"* ]] || return 1
  req "$1" "${link#"$base"}"
  # Drupal logs the user in and redirects to the account form of this site.
  status_in 302 303 && grep -qi "^location: $base/o/$2/user/1/edit?pass-reset-token=" "$work/r.headers"
}
log_mark() { wc -l <"$pg_log"; }
log_since() { tail -n +"$(($1 + 1))" "$pg_log"; }

web_log_start=$(log_mark)

section "The stack"
expected_listen=$(printf '127.0.0.1:%s\n' "$port" "$naive_port" | sort -u | tr '\n' ' ')
check "Caddy and the careless router listen on 127.0.0.1 only ($listen)" test "$listen" = "$expected_listen"
check "Caddy's admin endpoint is off" grep -q 'admin endpoint disabled' "$work/log/caddy.log"
check "Caddy keeps its data inside the temporary directory" test -d "$work/caddy-data/caddy"
check "PHP-FPM runs the pool from deploy/php-fpm/ancora.conf" grep -q '^\[ancora\]' "$work/fpm.conf"

section "Routing and site selection"
req - /
check "/ answers 200 from the control site" status_is 200
check "/ has the control site's name" title_has 'Áncora'
check "/ is not an organization site" bash -c "! grep -q 'Alpha School\|Beta Institute' '$work/r.body'"
req - /o/alpha/
check "/o/alpha/ answers 200" status_is 200
check "/o/alpha/ is Alpha School" title_has 'Alpha School'
check "/o/alpha/ is not Beta Institute" body_lacks 'Beta Institute'
req - /o/beta/
check "/o/beta/ answers 200" status_is 200
check "/o/beta/ is Beta Institute" title_has 'Beta Institute'
check "/o/beta/ is not Alpha School" body_lacks 'Alpha School'
req - /o/alpha
check "/o/alpha redirects to /o/alpha/ (308)" bash -c "[ \"\$(cat '$work/r.status')\" = 308 ] && grep -qi '^location: /o/alpha/' '$work/r.headers'"
req - /o/alpha/user/login
check "/o/alpha/user/login is alpha's login form" bash -c "grep -q 'Log in | Alpha School' '$work/r.body' && grep -q 'action=\"/o/alpha/user/login' '$work/r.body'"
req - /o/alpha/core/misc/drupal.js
check "core assets are served under /o/alpha/ (200)" status_is 200

section "Unknown sites and path tricks never reach the control site or another site"
for p in /o/gamma/ /o/gamma/user/login /o/gamma /o/ /o /o/Alpha/ /o/a/ /o/alpha_x/ "/o/alpha/o/beta/" "/o/alpha/o/beta/user/login" \
         /o/alpha/index.php/../../beta/ /core/install.php /o/alpha/core/install.php /update.php /o/alpha/update.php \
         /o/alpha/sites/org-alpha/settings.php /sites/default/settings.php /o/alpha/sites/default/files/verify-CONTROL.txt \
         /sites/org-alpha/files/verify-ALPHA.txt /o/beta/sites/org-alpha/files/verify-ALPHA.txt /composer.json /o/alpha/web.config; do
  req - "$p"
  check "$p -> 404, no Drupal page" bash -c "[ \"\$(cat '$work/r.status')\" = 404 ] && ! grep -q 'class=\"site-header\"' '$work/r.body'"
done
for p in "/o/alpha/../beta/" "/o/alpha/%2e%2e/beta/" "/o/alpha/..%2fbeta/" "/o/alpha/%2E%2E%2Fbeta/" "/o/%61lpha/" "/o//alpha/" "/o/alpha/%2f..%2f..%2f" "/o/gamma/../alpha/"; do
  req - "$p"
  s=$(cat "$work/r.status")
  # Caddy normalizes a dot segment to the path it names: then this is simply
  # that site's own page. Anything else must be refused.
  if [ "$s" = 200 ]; then
    if title_has 'Alpha School'; then got=alpha; elif title_has 'Beta Institute'; then got=beta; else got=other; fi
    check "$p -> $s served by $got only (no mixed or control content)" bash -c "[ '$got' != other ] && ! (grep -q 'Alpha School' '$work/r.body' && grep -q 'Beta Institute' '$work/r.body')"
  else
    check "$p -> $s (refused)" status_in 400 404
  fi
done

section "Host header tricks"
for h in org-alpha org-beta default evil.example "127.0.0.1.o.alpha" "localhost.o.beta"; do
  req - / -H "Host: $h"
  check "Host: $h on / -> refused ($(cat "$work/r.status"))" bash -c "[[ \"\$(cat '$work/r.status')\" =~ ^4 ]] && ! grep -q 'Alpha School\|Beta Institute' '$work/r.body'"
  req - /o/beta/ -H "Host: $h"
  check "Host: $h on /o/beta/ -> refused ($(cat "$work/r.status"))" bash -c "[[ \"\$(cat '$work/r.status')\" =~ ^4 ]] && ! grep -q 'Alpha School' '$work/r.body'"
done
req - /o/alpha/ -H "X-Forwarded-Host: org-beta" -H "X-Original-URL: /o/beta/" -H "X-Rewrite-URL: /o/beta/"
check "forwarding headers do not change the site (still Alpha School)" title_has 'Alpha School'

section "Defense in depth: the Drupal guards with a careless router (no Caddy filtering)"
naive /o/gamma/
check "naive /o/gamma/ (falls back to sites/default) -> 404, not the control site" bash -c "[ \"\$(cat '$work/r.status')\" = 404 ] && ! grep -q '<title>' '$work/r.body'"
naive /o/gamma/user/login
check "naive /o/gamma/user/login -> 404" status_is 404
naive /o/alpha/
check "naive /o/alpha/ -> Alpha School (positive control)" title_has 'Alpha School'
naive / -H "Host: org-alpha"
check "naive Host: org-alpha on / (selects sites/org-alpha) -> refused ($(cat "$work/r.status"))" bash -c "[[ \"\$(cat '$work/r.status')\" =~ ^4 ]] && ! grep -q 'Alpha School' '$work/r.body'"
naive /o/beta/ -H "Host: org-alpha"
check "naive Host: org-alpha on /o/beta/ -> refused, never alpha ($(cat "$work/r.status"))" bash -c "[[ \"\$(cat '$work/r.status')\" =~ ^4 ]] && ! grep -q 'Alpha School' '$work/r.body'"

section "First administrators: blocked, no password, one-time login link"
for s in alpha beta; do
  check "$s: uid 1 is blocked with no password" test "$(sql "tt_$s" "SELECT status || ':' || (pass IS NULL) FROM users_field_data WHERE uid = 1")" = "0:true"
done
req alpha-guess /o/alpha/user/login
build_id=$(grep -o 'name="form_build_id" value="[^"]*"' "$work/r.body" | head -1 | sed 's/.*value="\([^"]*\)"/\1/')
req alpha-guess /o/alpha/user/login --data-urlencode name=admin --data-urlencode pass=admin \
  --data-urlencode form_id=user_login_form --data-urlencode "form_build_id=$build_id" --data-urlencode "op=Log in"
check "alpha: the login of the blocked administrator is refused" bash -c "grep -q 'has not been activated or is blocked\|Unrecognized username or password' '$work/r.body'"
req alpha-guess /o/alpha/admin/people
check "alpha: no session from the refused login (admin page 403)" status_is 403
check "alpha: one-time login link logs in" login_with_link alpha alpha
req alpha /o/alpha/admin/people
check "alpha: the administrator is logged in on alpha (admin page 200)" status_is 200
check "beta: one-time login link logs in" login_with_link beta beta
link=$(cat "$work/link-alpha")
req alpha-reuse "${link#"$base"}"
check "alpha: the one-time link cannot be used twice" bash -c "! grep -qi '^location: .*pass-reset-token' '$work/r.headers'"
req alpha-reuse /o/alpha/admin/people
check "alpha: no session from the reused link (admin page 403)" status_is 403

section "Sessions and cookies"
alpha_cookie=$(session_cookie alpha)
beta_cookie=$(session_cookie beta)
check "alpha and beta session cookies exist" test -n "$alpha_cookie" -a -n "$beta_cookie"
check "the session cookie names differ (${alpha_cookie%%=*} / ${beta_cookie%%=*})" test "${alpha_cookie%%=*}" != "${beta_cookie%%=*}"
check "alpha's cookie path is /o/alpha/" bash -c "awk '\$6 ~ /SESS/ {print \$3}' '$work/jar/alpha' | grep -qx /o/alpha/"
check "beta's cookie path is /o/beta/" bash -c "awk '\$6 ~ /SESS/ {print \$3}' '$work/jar/beta' | grep -qx /o/beta/"
req alpha /o/beta/admin/people
check "the alpha browser is anonymous on beta (cookie path; 403)" status_is 403
req - /o/beta/user/1 -H "Cookie: $alpha_cookie"
check "alpha's cookie replayed on beta -> anonymous" bash -c "[ \"\$(cat '$work/r.status')\" = 403 ] && ! grep -q 'user/logout' '$work/r.body'"
req - /o/beta/user/1 -H "Cookie: ${beta_cookie%%=*}=${alpha_cookie#*=}"
check "alpha's session id under beta's cookie name -> anonymous" bash -c "[ \"\$(cat '$work/r.status')\" = 403 ] && ! grep -q 'user/logout' '$work/r.body'"
req - /user/1 -H "Cookie: $alpha_cookie"
check "alpha's cookie on the control site -> anonymous" bash -c "[ \"\$(cat '$work/r.status')\" = 403 ] && ! grep -q 'user/logout' '$work/r.body'"
sid_hash() { php -r 'echo rtrim(strtr(base64_encode(hash("sha256", $argv[1], TRUE)), "+/", "-_"), "=");' "${1#*=}"; }
alpha_sid=$(sid_hash "$alpha_cookie")
check "alpha's session is stored only in tt_alpha" test "$(sql tt_alpha "SELECT count(*) FROM sessions WHERE sid = '$alpha_sid'")/$(sql tt_beta "SELECT count(*) FROM sessions WHERE sid = '$alpha_sid'")/$(sql tt_control "SELECT count(*) FROM sessions WHERE sid = '$alpha_sid'")" = "1/0/0"

section "Administrator access does not cross sites"
req alpha /o/alpha/admin/people
check "alpha admin: /o/alpha/admin/people 200" status_is 200
req - /o/beta/admin/people -H "Cookie: $alpha_cookie"
check "alpha admin's cookie: /o/beta/admin/people 403" status_is 403
req - /admin/people -H "Cookie: $alpha_cookie"
check "alpha admin's cookie: control /admin/people 403" status_is 403
req - /o/beta/admin/people -H "Cookie: ${beta_cookie%%=*}=${alpha_cookie#*=}"
check "alpha's session id as beta's cookie: /o/beta/admin/people 403" status_is 403

section "Private and public files"
alpha_private=$ANCORA_SITES_DATA_DIR/alpha/private
check "alpha's private file is in alpha's private directory, outside the web root" bash -c "grep -qx ALPHA-PRIVATE '$alpha_private/verify-private.txt' && [[ '$alpha_private' != '$web'* ]]"
check "beta's private directory has beta's file, not alpha's" grep -qx BETA-PRIVATE "$ANCORA_SITES_DATA_DIR/beta/private/verify-private.txt"
check "the private directories differ per site" test "$(grep -h '^PRIVATE_PATH' "$ANCORA_SITES_DIR"/alpha/site.ini)" != "$(grep -h '^PRIVATE_PATH' "$ANCORA_SITES_DIR"/beta/site.ini)"
for p in /o/beta/system/files/verify-private.txt /system/files/verify-private.txt "/o/beta/system/files?file=verify-private.txt" "/system/files?file=verify-private.txt"; do
  req - "$p" -H "Cookie: $alpha_cookie"
  check "$p with alpha's cookie: never alpha's private file ($(cat "$work/r.status"))" bash -c "! grep -q ALPHA-PRIVATE '$work/r.body' && [ \"\$(cat '$work/r.status')\" != 200 ]"
done
req beta /o/beta/system/files/verify-private.txt
check "beta admin on beta's private path: never alpha's file" body_lacks ALPHA-PRIVATE
req - /o/alpha/sites/org-alpha/files/verify-ALPHA.txt
check "alpha's public file at /o/alpha/sites/org-alpha/files/ (200)" bash -c "[ \"\$(cat '$work/r.status')\" = 200 ] && grep -qx ALPHA-PUBLIC '$work/r.body'"
req - /o/beta/sites/org-beta/files/verify-ALPHA.txt
check "alpha's public file is not in beta's public directory" status_is 404
check "public directories are per site (sites/org-alpha/files, sites/org-beta/files)" test -f "$web/sites/org-alpha/files/verify-ALPHA.txt" -a ! -e "$web/sites/org-beta/files/verify-ALPHA.txt"

section "Page, render and Views caches do not leak"
req alpha /o/alpha/ancora/terms
check "alpha admin sees ALPHA-MARK in the Views listing" body_has ALPHA-MARK
check "alpha's listing has no BETA or CONTROL marker" bash -c "! grep -q 'BETA-MARK\|CONTROL-MARK' '$work/r.body'"
req beta /o/beta/ancora/terms
check "beta admin sees BETA-MARK, not ALPHA-MARK" bash -c "grep -q BETA-MARK '$work/r.body' && ! grep -q ALPHA-MARK '$work/r.body'"
req - /o/beta/ancora/terms
check "anonymous on beta's listing: 403 without markers" bash -c "[ \"\$(cat '$work/r.status')\" = 403 ] && ! grep -q 'MARK' '$work/r.body'"
req - /o/alpha/ancora/terms
check "anonymous on alpha's listing after the admin render: 403 without markers" bash -c "[ \"\$(cat '$work/r.status')\" = 403 ] && ! grep -q 'MARK' '$work/r.body'"
req - /o/alpha/
req - /o/alpha/
check "anonymous alpha front page is a page cache HIT" header_has '^x-drupal-cache: HIT'
check "the cached alpha page is Alpha School" title_has 'Alpha School'
req - /o/beta/
check "beta's front page is still Beta Institute after alpha's cache HIT" title_has 'Beta Institute'
req - /
check "the control front page is still the control site" bash -c "grep -q '<title>[^<]*Áncora' '$work/r.body' && ! grep -q 'Alpha School' '$work/r.body'"

section "Which database and role served the web requests"
web_log=$(log_since "$web_log_start")
auth=$(printf '%s\n' "$web_log" | grep -o 'connection authorized: user=[^ ]* database=[^ ]*' | grep -v 'user=postgres ' || true)
check "web requests connected to tt_control, tt_alpha and tt_beta" bash -c "for d in tt_control tt_alpha tt_beta; do printf '%s\n' \"\$1\" | grep -q \"database=\$d\$\" || exit 1; done" _ "$auth"
check "every web connection used its own site's runtime role" bash -c "printf '%s\n' \"\$1\" | awk '{split(\$3,u,\"=\"); split(\$4,d,\"=\"); if (u[2] != d[2] \"_app\") bad=1} END {exit bad}'" _ "$auth"
check "no web connection used an owner role" bash -c "! printf '%s\n' \"\$1\" | grep -q '_owner'" _ "$auth"
check "no permission error in PostgreSQL during the web requests" bash -c "! printf '%s\n' \"\$1\" | grep -q 'permission denied'" _ "$web_log"
check "no PHP error logged by PHP-FPM" bash -c "! grep -qi 'fatal\|warning\|exception' '$work/log/php.log' 2>/dev/null"

section "Database roles"
connects() { psql -X -At -U "$1" -d "$2" -c 'SELECT 1' >/dev/null 2>&1; }
check "tt_alpha_app connects to tt_alpha (positive control)" connects tt_alpha_app tt_alpha
check "tt_alpha_app cannot connect to tt_beta" bash -c "! psql -X -At -U tt_alpha_app -d tt_beta -c 'SELECT 1' 2>&1 | grep -q '^1\$'"
check "tt_alpha_app cannot connect to tt_control" bash -c "! psql -X -U tt_alpha_app -d tt_control -c 'SELECT 1' >/dev/null 2>&1"
check "tt_alpha_owner cannot connect to tt_beta" bash -c "! psql -X -U tt_alpha_owner -d tt_beta -c 'SELECT 1' >/dev/null 2>&1"
check "tt_beta_app cannot connect to tt_alpha" bash -c "! psql -X -U tt_beta_app -d tt_alpha -c 'SELECT 1' >/dev/null 2>&1"
check "tt_control_app cannot connect to tt_alpha" bash -c "! psql -X -U tt_control_app -d tt_alpha -c 'SELECT 1' >/dev/null 2>&1"
check "the refusal is PostgreSQL's CONNECT privilege" bash -c "psql -X -U tt_alpha_app -d tt_beta -c 'SELECT 1' 2>&1 | grep -q 'permission denied for database'"
check "PUBLIC has no CONNECT on any site database" test "$(sql postgres "SELECT count(*) FROM pg_database WHERE datname LIKE 'tt\_%' AND has_database_privilege('public', datname, 'CONNECT')")" = 0
check "tt_alpha_app cannot create a table in tt_alpha" bash -c "! psql -X -U tt_alpha_app -d tt_alpha -c 'CREATE TABLE ancora_probe (id int)' >/dev/null 2>&1"
check "tt_alpha_app cannot drop a Drupal table" bash -c "! psql -X -U tt_alpha_app -d tt_alpha -c 'DROP TABLE users' >/dev/null 2>&1"
check "tt_alpha_app can read and write rows (DML)" bash -c "psql -X -U tt_alpha_app -d tt_alpha -v ON_ERROR_STOP=1 -c 'UPDATE key_value SET name = name WHERE false' >/dev/null 2>&1"
check "the site roles are not superusers and cannot create roles or databases" test "$(sql postgres "SELECT count(*) FROM pg_roles WHERE rolname LIKE 'tt\_%' AND (rolsuper OR rolcreaterole OR rolcreatedb)")" = 0
check "secrets and site files are mode 0640/0600" bash -c "[ \"\$(stat -c %a '$ANCORA_SITES_DIR/alpha/site.ini' '$ANCORA_SITES_DIR/alpha/db-password' '$ANCORA_SITES_DIR/alpha/hash-salt' '$ANCORA_SITES_DIR/alpha/owner-password' | tr '\n' ' ')\" = '640 640 640 600 ' ]"
check "no secret is in the web root" bash -c "! grep -rqsF \"\$(cat '$ANCORA_SITES_DIR/alpha/db-password')\" '$web/sites'"

section "Provisioning: idempotent re-run, failure and recovery, suspend, retire"
before=$(sql tt_alpha "SELECT count(*) FROM ancora_academic_term")
check "re-running provision alpha succeeds" bash -c "'$site' provision alpha --name 'Alpha School' --admin-mail admin@alpha.example.invalid >'$work/log/rerun.log' 2>&1"
check "the re-run did not reinstall (markers kept)" test "$(sql tt_alpha "SELECT count(*) FROM ancora_academic_term")" = "$before"
check "the re-run reported 'already installed'" grep -q 'already installed' "$work/log/rerun.log"
req alpha /o/alpha/admin/people
check "alpha's administrator session survives the re-run (200)" status_is 200
check "the re-run left the administrator active (not re-blocked)" test "$(sql tt_alpha 'SELECT status FROM users_field_data WHERE uid = 1')" = 1
check "an invalid slug is refused" bash -c "! '$site' provision Bad_Slug --name x --admin-mail x@example.invalid >/dev/null 2>&1"
check "a reserved-looking slug with a dot is refused" bash -c "! '$site' provision 'a.b' --name x --admin-mail x@example.invalid >/dev/null 2>&1"

check "provision gamma, failing after the install step (test hook), exits non-zero" bash -c "! ANCORA_PROVISION_TEST_FAIL_AFTER=install '$site' provision gamma --name 'Gamma College' --admin-mail admin@gamma.example.invalid >'$work/log/gamma-fail.log' 2>&1"
req - /o/gamma/
check "the half-provisioned gamma is not published (404)" status_is 404
check "gamma's status is still provisioning" grep -q '^STATUS = "provisioning"' "$ANCORA_SITES_DIR/gamma/site.ini"
check "re-running provision gamma recovers" bash -c "'$site' provision gamma --name 'Gamma College' --admin-mail admin@gamma.example.invalid >'$work/log/gamma.log' 2>&1"
req - /o/gamma/
check "gamma is published after the recovery (200, Gamma College)" bash -c "[ \"\$(cat '$work/r.status')\" = 200 ] && grep -q '<title>[^<]*Gamma College' '$work/r.body'"
check "provision delta, failing during the database step, exits non-zero" bash -c "! ANCORA_PROVISION_TEST_FAIL_AFTER=database '$site' provision delta --name 'Delta' --admin-mail admin@delta.example.invalid >/dev/null 2>&1"
check "an aborted site that never went live can be deprovisioned" bash -c "'$site' deprovision delta --backup-dir '$work/backup' --confirm delta >/dev/null 2>&1"
check "delta's database and roles are gone" test "$(sql postgres "SELECT count(*) FROM pg_database WHERE datname = 'tt_delta'")/$(sql postgres "SELECT count(*) FROM pg_roles WHERE rolname LIKE 'tt\_delta%'")" = "0/0"

check "suspend gamma" bash -c "'$site' suspend gamma >/dev/null 2>&1"
req - /o/gamma/
check "a suspended site answers 503" status_is 503
req - /o/gamma/user/login
check "a suspended site has no login form (503)" bash -c "[ \"\$(cat '$work/r.status')\" = 503 ] && ! grep -q 'user_login_form' '$work/r.body'"
check "an active site cannot be deprovisioned" bash -c "! '$site' deprovision alpha --backup-dir '$work/backup' --confirm alpha >/dev/null 2>&1"
check "resume gamma" bash -c "'$site' resume gamma >/dev/null 2>&1"
req - /o/gamma/
check "a resumed site answers 200" status_is 200
"$site" suspend gamma >/dev/null 2>&1
check "deprovision gamma (suspended) with a final backup" bash -c "'$site' deprovision gamma --backup-dir '$work/backup' --confirm gamma >/dev/null 2>&1"
check "gamma's final backup exists" bash -c "ls '$work/backup'/tt_gamma-*.dump >/dev/null 2>&1"
req - /o/gamma/
check "a retired site answers 404" status_is 404
check "gamma's database, roles and files are gone" bash -c "[ \"\$(psql -X -At -d postgres -c \"SELECT count(*) FROM pg_database WHERE datname = 'tt_gamma'\")\" = 0 ] && [ ! -e '$web/sites/org-gamma' ] && [ ! -e '$ANCORA_SITES_DATA_DIR/gamma' ] && [ ! -e '$ANCORA_SITES_DIR/gamma' ]"
check "the audit log records the operations" bash -c "grep -q 'provision	alpha	ok' '$ANCORA_SITES_DIR/audit.log' && grep -q 'deprovision	gamma	ok' '$ANCORA_SITES_DIR/audit.log'"

section "Update loop over all sites"
check "update-all succeeds on control, alpha and beta" bash -c "'$site' update-all --backup-dir '$work/backup' >'$work/log/update.log' 2>&1 && grep -q 'updated 3 sites: control alpha beta' '$work/log/update.log'"
check "update-all took a backup of every site first" bash -c "ls '$work/backup'/tt_control-*.dump '$work/backup'/tt_alpha-*.dump '$work/backup'/tt_beta-*.dump >/dev/null 2>&1"
req - /o/beta/
check "the sites serve after the update (not in maintenance mode)" bash -c "[ \"\$(cat '$work/r.status')\" = 200 ] && grep -q 'Beta Institute' '$work/r.body'"
# A real failure in the middle of the loop: alpha's owner role cannot log in.
psql -X -q -d postgres -c 'ALTER ROLE tt_alpha_owner NOLOGIN'
check "update-all stops at the first failing site" bash -c "! '$site' update-all >'$work/log/update-fail.log' 2>&1 && grep -q 'update FAILED on alpha' '$work/log/update-fail.log' && grep -q 'updated: control; not attempted: beta' '$work/log/update-fail.log'"
# (The front page is the login form, which Drupal keeps reachable in
# maintenance mode; the listing is not.)
req - /o/alpha/ancora/terms
check "the failed site stays in maintenance mode ($(cat "$work/r.status"))" status_is 503
req - /o/beta/
check "the site after it was not touched (200)" status_is 200
psql -X -q -d postgres -c 'ALTER ROLE tt_alpha_owner LOGIN'
check "update-all succeeds again after the cause is fixed" bash -c "'$site' update-all >'$work/log/update2.log' 2>&1"
req - /o/alpha/
check "alpha serves after the recovered update" title_has 'Alpha School'

step "multisite verification: $pass passed, $fail failed ($((SECONDS - started)) s)"
if [ "$fail" -ne 0 ]; then
  for f in "$work"/log/*; do echo "--- $f"; tail -20 "$f"; done
fi
[ "$fail" -eq 0 ]
