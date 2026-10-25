# Fail-closed check that the libpq target is a disposable cluster (issue #26).
# Source it, then call agoh_require_disposable before any connection:
#
#   . "$(dirname "$0")/disposable-guard.sh"   # path relative to the caller
#   agoh_require_disposable || exit 3
#
# A disposable cluster is one started by dev/sql/tools/with-ephemeral-pg.sh in
# this process tree. The launcher creates a random token and records it in
# three places it alone controls: the environment (AGOH_DISPOSABLE_TOKEN), a
# marker file next to the server socket, and a custom server setting given at
# server start (agoh.disposable_token). The checks are:
#
#   before connecting (no network or socket traffic at all)
#     - no PGHOSTADDR / PGSERVICE / PGSERVICEFILE (they bypass PGHOST)
#     - PGHOST is one absolute socket directory, not a host name, not TCP and
#       not a system default socket directory
#     - the socket file .s.PGSQL.<PGPORT> exists there and belongs to us
#     - the marker file exists, is a regular file we own, and holds the token
#     - the data directory belongs to us, and its postmaster.pid names a live
#       process of ours serving exactly this socket directory and port, with
#       no TCP listener
#   after connecting (read-only, to the maintenance database "postgres")
#     - the server reports the same token, data directory and an empty
#       listen_addresses
#
# Any failure prints one line naming the failed check (never a token, a
# password or a path outside the check) and returns non-zero. There is no
# environment variable that skips the check. A deliberate installation into a
# non-disposable server is only possible through install.sh --admin-install
# (see docs/modernization/sql-test-safety.md).

agoh_guard_fail() {
  echo "[REFUSED] not a verified disposable PostgreSQL cluster: $1" >&2
  echo "          run the command under dev/sql/tools/with-ephemeral-pg.sh" >&2
  return 3
}

agoh_require_disposable() {
  local uid sockdir port sock marker data pidfile pid line_data line_port line_sock line_listen
  uid=$(id -u)

  [ -z "${PGHOSTADDR:-}" ] || { agoh_guard_fail "PGHOSTADDR is set"; return 3; }
  [ -z "${PGSERVICE:-}" ] || { agoh_guard_fail "PGSERVICE is set"; return 3; }
  [ -z "${PGSERVICEFILE:-}" ] || { agoh_guard_fail "PGSERVICEFILE is set"; return 3; }

  [ -n "${PGHOST:-}" ] || { agoh_guard_fail "PGHOST is not set (libpq would use the system default server)"; return 3; }
  case "$PGHOST" in
    /*) ;;
    *) agoh_guard_fail "PGHOST is not an absolute socket directory (host names and TCP are refused)"; return 3 ;;
  esac
  case "$PGHOST" in
    *,*) agoh_guard_fail "PGHOST lists several hosts"; return 3 ;;
  esac
  [ -d "$PGHOST" ] && [ ! -L "$PGHOST" ] || { agoh_guard_fail "PGHOST is not a directory"; return 3; }
  sockdir=$(cd "$PGHOST" && pwd -P)
  case "$sockdir" in
    /var/run/postgresql|/run/postgresql|/tmp|/private/tmp|/var/pgsql_socket)
      agoh_guard_fail "PGHOST is a system default socket directory"; return 3 ;;
  esac

  port=${PGPORT:-5432}
  case "$port" in
    ''|*[!0-9]*) agoh_guard_fail "PGPORT is not a number"; return 3 ;;
  esac
  sock="$sockdir/.s.PGSQL.$port"
  [ -S "$sock" ] || { agoh_guard_fail "no server socket in PGHOST for PGPORT"; return 3; }
  [ "$(stat -c %u "$sock")" = "$uid" ] || { agoh_guard_fail "the server socket belongs to another user"; return 3; }

  [ -n "${AGOH_DISPOSABLE_TOKEN:-}" ] || { agoh_guard_fail "AGOH_DISPOSABLE_TOKEN is not set"; return 3; }
  [[ $AGOH_DISPOSABLE_TOKEN =~ ^[0-9a-f]{64}$ ]] || { agoh_guard_fail "AGOH_DISPOSABLE_TOKEN is malformed"; return 3; }
  marker="$sockdir/agoh-disposable.id"
  [ -f "$marker" ] && [ ! -L "$marker" ] || { agoh_guard_fail "no disposable marker next to the socket"; return 3; }
  [ "$(stat -c %u "$marker")" = "$uid" ] || { agoh_guard_fail "the disposable marker belongs to another user"; return 3; }
  [ "$(cat "$marker")" = "$AGOH_DISPOSABLE_TOKEN" ] || { agoh_guard_fail "the disposable marker does not match AGOH_DISPOSABLE_TOKEN"; return 3; }

  data=${AGOH_DISPOSABLE_DATA:-}
  [ -n "$data" ] && [ -d "$data" ] && [ ! -L "$data" ] || { agoh_guard_fail "AGOH_DISPOSABLE_DATA is not a data directory"; return 3; }
  data=$(cd "$data" && pwd -P)
  [ "$(stat -c %u "$data")" = "$uid" ] || { agoh_guard_fail "the data directory belongs to another user"; return 3; }
  pidfile="$data/postmaster.pid"
  [ -f "$pidfile" ] || { agoh_guard_fail "the data directory has no running server"; return 3; }
  # postmaster.pid lines: 1 pid, 2 data directory, 4 port, 5 socket directory, 6 listen address
  pid=$(sed -n 1p "$pidfile"); line_data=$(sed -n 2p "$pidfile"); line_port=$(sed -n 4p "$pidfile")
  line_sock=$(sed -n 5p "$pidfile"); line_listen=$(sed -n 6p "$pidfile")
  [[ $pid =~ ^[0-9]+$ ]] && kill -0 "$pid" 2>/dev/null || { agoh_guard_fail "the server process is not running or not ours"; return 3; }
  [ "$(stat -c %u "/proc/$pid" 2>/dev/null || ps -o uid= -p "$pid" | tr -d ' ')" = "$uid" ] \
    || { agoh_guard_fail "the server process belongs to another user"; return 3; }
  line_data=$(cd "$line_data" 2>/dev/null && pwd -P) || line_data=""
  line_sock=$(cd "$line_sock" 2>/dev/null && pwd -P) || line_sock=""
  [ "$line_data" = "$data" ] || { agoh_guard_fail "postmaster.pid names another data directory"; return 3; }
  [ "$line_port" = "$port" ] || { agoh_guard_fail "the server listens on another port"; return 3; }
  [ "$line_sock" = "$sockdir" ] || { agoh_guard_fail "the server uses another socket directory"; return 3; }
  [ -z "$line_listen" ] || { agoh_guard_fail "the server has a TCP listener"; return 3; }

  # Read-only confirmation from the server itself.
  local got srv_token srv_data srv_listen
  got=$(PGOPTIONS= psql -X -At -F '|' -d postgres -c \
        "SELECT current_setting('agoh.disposable_token', true), current_setting('data_directory'), current_setting('listen_addresses')" 2>/dev/null) \
    || { agoh_guard_fail "cannot query the server identity"; return 3; }
  IFS='|' read -r srv_token srv_data srv_listen <<<"$got"
  srv_data=$(cd "$srv_data" 2>/dev/null && pwd -P) || srv_data=""
  [ "$srv_token" = "$AGOH_DISPOSABLE_TOKEN" ] && [ "$srv_data" = "$data" ] && [ -z "$srv_listen" ] \
    || { agoh_guard_fail "the server does not report this disposable identity"; return 3; }
  return 0
}

# install.sh --admin-install: an explicit, separately confirmed opt-in for a
# server the operator chose deliberately. Never used by tests or CI.
agoh_require_admin_confirmation() { # $1 = database name
  [ "${AGOH_ADMIN_INSTALL_CONFIRM:-}" = "$1" ] || {
    echo "[REFUSED] --admin-install requires AGOH_ADMIN_INSTALL_CONFIRM=<the database name>" >&2
    return 3
  }
  [ -n "${PGHOST:-}" ] || [ -n "${PGHOSTADDR:-}" ] || [ -n "${PGSERVICE:-}" ] || {
    echo "[REFUSED] --admin-install requires an explicit target (PGHOST, PGHOSTADDR or PGSERVICE)" >&2
    return 3
  }
  echo "[ADMIN] installing into a non-disposable server chosen by the operator" >&2
}
