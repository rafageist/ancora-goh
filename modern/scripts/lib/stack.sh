# Shared helpers of the verifications that run the real stack (Caddy and
# PHP-FPM on 127.0.0.1): scripts/multisite-verify.sh (#72) and
# scripts/r1-journey.sh (R1). Sourced, not executed.
#
# The caller sets: project, web, work (a temporary directory), fpm_bin,
# caddy_bin, port, base (http://127.0.0.1:<port>), and an array pids (the
# processes to stop on exit). The PHP-FPM pool gets the ANCORA_* variables
# listed in stack_pool_env from the caller's environment.
# shellcheck shell=bash

pass=0 fail=0
check() { # description, test command...
  local what=$1; shift
  if "$@"; then pass=$((pass + 1)); echo "PASS $what"; else fail=$((fail + 1)); echo "FAIL $what"; fi
}
section() { printf '\n--- %s\n' "$*"; }

free_port() { php -r '$s = stream_socket_server("tcp://127.0.0.1:0"); echo explode(":", stream_socket_get_name($s, FALSE))[1];'; }

stack_pool_env=(ANCORA_ENVIRONMENT ANCORA_DB_HOST ANCORA_DB_PORT ANCORA_TRUSTED_HOSTS ANCORA_SITES_DIR
  ANCORA_DB_NAME ANCORA_DB_USER ANCORA_DB_PASSWORD_FILE ANCORA_HASH_SALT_FILE
  ANCORA_PRIVATE_PATH ANCORA_TMP_PATH)

# stack_start: PHP-FPM with deploy/php-fpm/ancora.conf and Caddy with
# deploy/caddy/ancora.caddy. Only the users, socket, log and paths are
# replaced; Caddy runs with its admin API off and its data inside $work.
stack_start() {
  mkdir -p "$work/log"
  local v
  {
    printf '[global]\npid = %s/fpm.pid\nerror_log = %s/log/fpm.log\ndaemonize = no\n' "$work" "$work"
    sed -e '/^user = \|^group = \|^listen\.owner\|^listen\.group/d' \
        -e "s#^listen = .*#listen = $work/fpm.sock#" \
        -e "s#^php_admin_value\[error_log\] = .*#php_admin_value[error_log] = $work/log/php.log#" \
        -e "s#/srv/ancora/modern#$project#g; s#/etc/ancora#$work/etc#g; s#/var/lib/ancora#$work/var#g" \
        -e '/^env\[/d' "$project/deploy/php-fpm/ancora.conf"
    for v in "${stack_pool_env[@]}"; do
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
}

# stack_wait <url>...: until every URL answers 2xx (20 s), or exit 1.
stack_wait() {
  local url ok
  for _ in $(seq 1 100); do
    ok=1
    for url in "$@"; do curl -sf -o /dev/null "$url" || { ok=0; break; }; done
    if [ "$ok" = 1 ]; then return 0; fi
    sleep 0.2
  done
  local f
  for f in "$work"/log/*; do echo "--- $f"; tail -20 "$f"; done
  echo "the servers did not start" >&2
  exit 1
}

# HTTP helpers: body, headers and status of the last request in $work/r.*.
req() { # jar (or -) path [curl args...]
  local jar=$1 path=$2; shift 2
  local cookies=()
  mkdir -p "$work/jar"
  [ "$jar" = - ] || cookies=(-b "$work/jar/$jar" -c "$work/jar/$jar")
  curl -s --path-as-is -o "$work/r.body" -D "$work/r.headers" -w '%{http_code}' "${cookies[@]}" "$@" "$base$path" >"$work/r.status" || echo 000 >"$work/r.status"
}
status_is() { [ "$(cat "$work/r.status")" = "$1" ]; }
status_in() { local s; s=$(cat "$work/r.status"); [[ " $* " == *" $s "* ]]; }
body_has() { grep -q -- "$1" "$work/r.body"; }
body_lacks() { ! grep -q -- "$1" "$work/r.body"; }
header_has() { grep -qi -- "$1" "$work/r.headers"; }
title_has() { grep -o '<title>[^<]*</title>' "$work/r.body" | grep -q -- "$1"; }
sql() { psql -X -At -d "$1" -c "$2"; }
# form_value <name>: the value of a hidden input of the last response.
form_value() { grep -o "name=\"$1\" value=\"[^\"]*\"" "$work/r.body" | head -1 | sed 's/.*value="\([^"]*\)"/\1/'; }
