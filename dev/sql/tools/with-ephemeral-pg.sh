#!/usr/bin/env bash
# Run a command against a throwaway PostgreSQL cluster (socket only, no TCP).
#
#   dev/sql/tools/with-ephemeral-pg.sh <command> [args...]
#
# The cluster is created with initdb in a temporary directory, started with
# listen_addresses='' (Unix socket only), exported to the command through
# PGHOST/PGPORT/PGUSER, and stopped and deleted on exit. It never touches an
# existing server. It also establishes the disposable identity that every
# database tool verifies before connecting (tools/disposable-guard.sh, #26):
# a random token in AGOH_DISPOSABLE_TOKEN, in the marker file
# <socket dir>/agoh-disposable.id and in the server setting
# agoh.disposable_token. PG_BINDIR selects the server binaries (default: the newest
# /usr/lib/postgresql/<major>/bin, else the directory of pg_ctl on PATH).
set -euo pipefail

if [ -z "${PG_BINDIR:-}" ]; then
  PG_BINDIR=$(ls -d /usr/lib/postgresql/*/bin 2>/dev/null | sort -V | tail -1 || true)
  [ -n "$PG_BINDIR" ] || PG_BINDIR=$(dirname "$(command -v pg_ctl)")
fi
tmp_root=${TMPDIR:-/tmp}
work=$(mktemp -d "$tmp_root/agoh-pg.XXXXXX")   # short path: socket paths are limited to ~100 bytes
work=$(cd "$work" && pwd -P)
token=$(od -An -N32 -tx1 /dev/urandom | tr -d ' \n')
port=${PG_EPHEMERAL_PORT:-55439}

cleanup() {
  "$PG_BINDIR/pg_ctl" -D "$work/data" -m immediate stop >/dev/null 2>&1 || true
  rm -rf "$work"
}
trap cleanup EXIT

"$PG_BINDIR/initdb" -D "$work/data" -U postgres --auth=trust -E UTF8 --locale=C.UTF-8 >"$work/initdb.log" 2>&1 \
  || { cat "$work/initdb.log" >&2; exit 1; }
(umask 077 && printf '%s' "$token" > "$work/agoh-disposable.id")
"$PG_BINDIR/pg_ctl" -D "$work/data" -l "$work/server.log" -w \
  -o "-k $work -p $port -c listen_addresses= -c fsync=off -c agoh.disposable_token=$token" start >/dev/null \
  || { cat "$work/server.log" >&2; exit 1; }

export PGHOST=$work PGPORT=$port PGUSER=postgres PGDATABASE=postgres
export AGOH_DISPOSABLE_TOKEN=$token AGOH_DISPOSABLE_DATA=$work/data
unset PGPASSWORD PGSERVICE PGSERVICEFILE PGHOSTADDR
"$@"
