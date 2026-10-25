#!/usr/bin/env bash
# Tests for the disposable-cluster guard (issue #26).
#
#   dev/sql/tools/with-ephemeral-pg.sh dev/sql/tools/test-guard.sh
#
# Part 1 (no server involved): every database tool is run with an unsafe or
# forged environment. psql, createdb and dropdb are replaced on PATH by stubs
# that only record that they were called. Each tool must exit 3 and the stubs
# must never run, so nothing connected anywhere.
# Part 2 (inside the disposable cluster started by the launcher): forged
# identities that pass part of the checks are refused, and the list of
# databases is unchanged afterwards; the genuine identity is accepted; the
# --admin-install opt-in needs its confirmation.
set -uo pipefail
sql=$(cd "$(dirname "$0")/.." && pwd)
work=$(mktemp -d "${TMPDIR:-/tmp}/agoh-guard.XXXXXX"); trap 'rm -rf "$work"' EXIT
fails=0
ok()   { echo "ok   $1"; }
bad()  { echo "FAIL $1"; fails=$((fails + 1)); }

[ -n "${AGOH_DISPOSABLE_TOKEN:-}" ] || { echo "run under dev/sql/tools/with-ephemeral-pg.sh" >&2; exit 2; }
real_env=(PGHOST="$PGHOST" PGPORT="$PGPORT" PGUSER="$PGUSER" AGOH_DISPOSABLE_TOKEN="$AGOH_DISPOSABLE_TOKEN" AGOH_DISPOSABLE_DATA="$AGOH_DISPOSABLE_DATA")

# ---------------------------------------------------------------- part 1
mkdir -p "$work/bin" "$work/fake"
for t in psql createdb dropdb pg_dump; do
  printf '#!/bin/sh\necho "%s $*" >> "%s/calls"\nexit 0\n' "$t" "$work" > "$work/bin/$t"
  chmod +x "$work/bin/$t"
done
token=$(od -An -N32 -tx1 /dev/urandom | tr -d ' \n')
printf '%s' "$token" > "$work/fake/agoh-disposable.id"   # forged marker, but no server socket

tools=(
  "install.sh agoh_guard_probe"
  "install.sh --with-data agoh_guard_probe"
  "tools/check-sources.sh"
  "tools/compare-catalog.sh agoh_a agoh_b"
  "tools/smoke-check.sh agoh_guard_probe"
  "tools/test-installer.sh"
  "tests/run.sh"
)
envs=(
  "no PGHOST (system default server)|"
  "PGHOST=localhost|PGHOST=localhost PGPORT=5432"
  "TCP address|PGHOSTADDR=127.0.0.1 PGHOST=$PGHOST PGPORT=$PGPORT"
  "service file|PGSERVICE=agoh PGHOST=$PGHOST PGPORT=$PGPORT"
  "system socket directory|PGHOST=/var/run/postgresql PGPORT=5432"
  "relative PGHOST|PGHOST=tmp PGPORT=5432"
  "forged marker without a server|PGHOST=$work/fake PGPORT=5432 AGOH_DISPOSABLE_TOKEN=$token AGOH_DISPOSABLE_DATA=$work/fake"
  "real socket, token missing|PGHOST=$PGHOST PGPORT=$PGPORT AGOH_DISPOSABLE_DATA=$AGOH_DISPOSABLE_DATA"
)
for e in "${envs[@]}"; do
  label=${e%%|*}; vars=${e#*|}; before_fails=$fails
  for t in "${tools[@]}"; do
    rm -f "$work/calls"
    read -r -a cmd <<<"$t"
    # shellcheck disable=SC2086
    env -i PATH="$work/bin:$PATH" HOME="$HOME" TMPDIR="${TMPDIR:-/tmp}" PHP="${PHP:-php}" $vars \
      bash "$sql/${cmd[0]}" "${cmd[@]:1}" >"$work/out" 2>&1 < /dev/null
    rc=$?
    cat "$work/out" >> "$work/all-out"
    if [ $rc = 3 ] && [ ! -e "$work/calls" ] && grep -q '^\[REFUSED\]' "$work/out"; then :
    else bad "$label: ${cmd[0]} rc=$rc calls=$( [ -e "$work/calls" ] && wc -l < "$work/calls" || echo 0)"; fi
  done
  [ $fails = "$before_fails" ] && ok "$label: all ${#tools[@]} tool invocations refused before any client program ran"
done
if grep -qiE "$AGOH_DISPOSABLE_TOKEN|$token" "$work/all-out"; then bad "a refusal message printed a token"; else ok "refusal messages print no token"; fi

# ---------------------------------------------------------------- part 2
dbs() { psql -X -At -d postgres -c "SELECT string_agg(datname, ',' ORDER BY datname) FROM pg_database"; }
before=$(dbs)
marker="$PGHOST/agoh-disposable.id"
cp "$marker" "$work/marker.bak"

live() { # label, expected rc, expected refusal text (or ""), env assignments...
  local label=$1 want=$2 msg=$3; shift 3
  env "${real_env[@]}" "$@" bash "$sql/install.sh" agoh_guard_live >"$work/out" 2>&1 < /dev/null
  local rc=$?
  if [ $rc = "$want" ] && { [ -z "$msg" ] || grep -qF "$msg" "$work/out"; }; then ok "$label"
  else bad "$label (rc=$rc, want $want): $(head -1 "$work/out")"; fi
  psql -X -q -d postgres -c "DROP DATABASE IF EXISTS agoh_guard_live" >/dev/null 2>&1
}

live "wrong token in the environment is refused" 3 "marker does not match" AGOH_DISPOSABLE_TOKEN="$token"
live "data directory of another cluster is refused" 3 "no running server" AGOH_DISPOSABLE_DATA="$work/fake"
printf '%s' "$token" > "$marker"
live "forged marker + token is refused by the server identity check" 3 "server does not report" AGOH_DISPOSABLE_TOKEN="$token"
cp "$work/marker.bak" "$marker"
[ "$(dbs)" = "$before" ] && ok "refused runs left the database list unchanged" || bad "database list changed"

live "the genuine disposable identity is accepted" 0 ""
env "${real_env[@]}" bash "$sql/install.sh" --admin-install agoh_guard_live >"$work/out" 2>&1 </dev/null
[ $? = 3 ] && ok "--admin-install without confirmation is refused" || bad "--admin-install without confirmation accepted"
env "${real_env[@]}" AGOH_ADMIN_INSTALL_CONFIRM=other bash "$sql/install.sh" --admin-install agoh_guard_live >"$work/out" 2>&1 </dev/null
[ $? = 3 ] && ok "--admin-install with a confirmation for another database is refused" || bad "--admin-install wrong confirmation accepted"
env "${real_env[@]}" AGOH_ADMIN_INSTALL_CONFIRM=agoh_guard_live bash "$sql/install.sh" --admin-install agoh_guard_live >"$work/out" 2>&1 </dev/null
[ $? = 0 ] && ok "--admin-install with its confirmation installs (here: into the disposable cluster)" || bad "--admin-install with confirmation failed: $(tail -1 "$work/out")"
psql -X -q -d postgres -c "DROP DATABASE IF EXISTS agoh_guard_live" >/dev/null 2>&1
[ "$(dbs)" = "$before" ] && ok "database list restored" || bad "database list not restored"

echo "# guard tests: $fails failure(s)"
[ $fails = 0 ]
