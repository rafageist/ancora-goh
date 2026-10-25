#!/usr/bin/env bash
# One-off: create timetables_control with the owner/runtime role split
# (docs/saas/adr-001-tenant-isolation.md). Safe to repeat.
set -euo pipefail
umask 077
printf 'db:5432:*:postgres:%s\n' "$(cat /run/secrets/pg_superuser)" > /tmp/pgpass
app_pw=$(cat /run/secrets/portal_db_password)
[[ "$app_pw" =~ ^[A-Za-z0-9]{24,}$ ]] || { echo "setup: portal_db_password must be 24+ letters/digits" >&2; exit 64; }
psql -X -v ON_ERROR_STOP=1 -q -d postgres -v pw="$app_pw" <<'SQL'
SELECT 'CREATE ROLE tt_control_owner NOLOGIN' WHERE NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'tt_control_owner')\gexec
SELECT 'CREATE ROLE tt_control_app LOGIN' WHERE NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'tt_control_app')\gexec
ALTER ROLE tt_control_app NOSUPERUSER NOCREATEDB NOCREATEROLE NOREPLICATION PASSWORD :'pw';
SELECT 'CREATE DATABASE timetables_control OWNER tt_control_owner' WHERE NOT EXISTS (SELECT 1 FROM pg_database WHERE datname = 'timetables_control')\gexec
REVOKE ALL ON DATABASE timetables_control FROM PUBLIC;
GRANT CONNECT ON DATABASE timetables_control TO tt_control_app;
SQL
if [ "$(psql -X -At -d timetables_control -c "SELECT to_regclass('portal.schema_version') IS NOT NULL")" = t ]; then
  echo "setup: schema already present; nothing changed"; exit 0
fi
psql -X -v ON_ERROR_STOP=1 -q -d timetables_control <<'SQL'
SET ROLE tt_control_owner;
\i /sql/schema.sql
\set app_role tt_control_app
\i /sql/grants.sql
SQL
echo "setup: timetables_control ready"
