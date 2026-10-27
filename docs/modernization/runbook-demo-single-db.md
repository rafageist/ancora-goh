# Runbook: migrating the private `demo` site to the single-database layout (#96)

This runbook is for the maintainer, to run on the private host. **Nothing in it has been run against the host.** Every command was exercised in disposable clusters by `modern/scripts/tenant-verify.sh`, sections 6 to 9 ([tenant-migration.md](tenant-migration.md)).

What it does:

- `demo` moves from its engine database `tt_demo_engine` (two-database layout, #71) to the engine in schema `scheduler` of `tt_demo`, with job workspaces (#93, #95).
- The placements of `tt_demo_engine.actividad` become placement entities, with the same engine ids, order and job links.
- The jobs and every Drupal record are left as they are. `tt_demo_engine` is kept, unchanged, for the rollback; retiring it is a separate, later step.

Facts this runbook relies on:

- PostgreSQL 18.6, with 1 vCPU and about 1 GiB of RAM.
- The operator role `ancora_operator` is not a superuser (`LOGIN CREATEDB CREATEROLE`). It runs `ancora-site.sh`.
- The web server, PHP-FPM, drush and the worker run as the user `ancora`, with the site's runtime role.
- The control site is `tt_control`.

Expected duration for a small site: a few minutes, mostly the two `pg_dump` runs. The site is suspended (503) from step 3 to step 7.

The commands below use the operator's usual environment: the `PG*` variables of `ancora_operator`, plus `ANCORA_SITES_DIR`, `ANCORA_SITES_DATA_DIR`, `ANCORA_PUBLIC_URL`, `ANCORA_DB_HOST`, `ANCORA_DB_PORT` and `ANCORA_WEB_GROUP`, as for `provision` and `update-all`. `$SITES` stands for `ANCORA_SITES_DIR` (for example `/etc/ancora/sites`). `$BACKUP` stands for a directory with room for two dumps of `tt_demo` and `tt_demo_engine`, readable only by the operator. `site` stands for the full path of `modern/scripts/ancora-site.sh`.

## 0. Deploy the code

Deploy the commit that contains #96 parts 1 and 2 as usual, then run the update loop. It applies `ancora_scheduling_update_11002`: the placement moment becomes double precision. It also takes a backup of every site before updating.

```sh
site update-all --backup-dir "$BACKUP/pre-96-update"
```

## 1. Preflight (read-only)

```sh
psql -d postgres -Atc "SHOW server_version"                     # 16 or later (18.6)
psql -d postgres -Atc "SELECT rolsuper, rolcreatedb, rolcreaterole FROM pg_roles WHERE rolname = current_user"
                                                                # f|t|t
psql -d postgres -Atc "SELECT pg_has_role(current_user, 'tt_demo_owner', 'USAGE'), pg_has_role(current_user, 'tt_demo_owner', 'SET')"
                                                                # t|t (already needed by provision, #72)
site list                                                       # demo  active  two_database  ...
site migrate-single-db demo --check
```

`--check` changes nothing and prints a report:

- `rows`: the placements in `tt_demo_engine.actividad`;
- `jobs`: job id => how many of them are linked to it;
- `other_resources` must be 0;
- `leftover_cancel_requests`: rows of `generador.cancelar`. They are not carried over: in the workspace mode a request only ever reaches its own job (#95, D12);
- `problems` must be `[]`.

**Stop here if `problems` is not empty.** Report the output. Do not edit data to make it pass.

Then check the disk space:

```sh
psql -d postgres -Atc "SELECT pg_size_pretty(pg_database_size('tt_demo')), pg_size_pretty(pg_database_size('tt_demo_engine'))"
df -h "$BACKUP" /var/lib/postgresql
```

- `$BACKUP` needs room for both dumps.
- The engine adds about 10 MB to `tt_demo`. Each running job adds a workspace of about 1 MB, dropped when the job ends.

## 2. Stop demo's worker

Stop the unit or cron entry that runs `ancora-site.sh work demo …` as `ancora`. A running worker loop keeps the settings it read when it started, so it must not survive the switch.

```sh
pgrep -af 'ancora-engine:work' | grep -- '/o/demo'           # must print nothing
```

## 3. Suspend demo

```sh
site suspend demo
```

## 4. Migrate

```sh
site migrate-single-db demo --backup-dir "$BACKUP/demo-single-db"
```

It refuses an active site and a queued or running job. In order, it:

1. dumps `tt_demo` and `tt_demo_engine` (`pg_dump -Fc`) and copies `site.ini` into the backup directory;
2. takes checksums of the Drupal records (`$SITES/demo/migration/before.json`);
3. installs the engine into `tt_demo` as `tt_demo_engine_owner`, in one transaction;
4. imports the placements through the Entity API, in one transaction;
5. takes the checksums again and compares them with step 2;
6. switches `site.ini` to the workspace mode, and checks that the engine is reachable with the site's own settings.

**If it fails:** read the message, fix the cause, and run **the same command again**. It resumes after the last completed step (`MIGRATION=` in `$SITES/demo/provision.state`); the backup is not taken twice. A failed placement import writes no placement. Do not delete anything in `$SITES/demo/migration/`. The site stays suspended and still on `tt_demo_engine` until step 6 succeeds.

## 5. Acceptance (while still suspended)

```sh
grep '^ENGINE_' "$SITES/demo/site.ini"
#   ENGINE_MODE = "workspace"
#   ENGINE_DB_NAME = "tt_demo"
#   ENGINE_DB_USER = "tt_demo_engine_app"
cmp "$SITES/demo/migration/before.json" "$SITES/demo/migration/after.json" && echo records-unchanged
ls -l "$BACKUP/demo-single-db"                                  # two .dump files and the site.ini copy
pg_restore --list "$BACKUP"/demo-single-db/tt_demo-pre-single-db-*.dump | head -3
psql -d tt_demo_engine -Atc "SELECT count(*), md5(string_agg(id, ',' ORDER BY id)) FROM actividad"
psql -d tt_demo -Atc "SELECT count(*), md5(string_agg(engine_id, ',' ORDER BY engine_id)) FROM ancora_placement"
                                                                # the same two values
psql -d tt_demo -Atc "SELECT nspowner::regrole FROM pg_namespace WHERE nspname = 'scheduler'"
                                                                # tt_demo_engine_owner
psql -d postgres -Atc "SELECT rolname, rolcanlogin, rolcreaterole FROM pg_roles WHERE rolname LIKE 'tt\_demo\_%' ORDER BY 1"
#   tt_demo_app|t|f  tt_demo_engine_app|t|f  tt_demo_engine_owner|f|t  tt_demo_jobs|f|f  tt_demo_owner|t|f
site list                                                       # demo  suspended  workspace  ...
```

## 6. Resume and restart the worker

```sh
site resume demo
```

Start demo's worker again with **the same command as before** (`ancora-site.sh work demo …`, as `ancora`). Nothing in the unit or cron entry changes:

- the site file now names `tt_demo`, the workspace mode and the same worker role `tt_demo_engine_app`;
- the role's password file `engine-db-password` is unchanged;
- PHP-FPM reads `site.ini` on every request, so it needs no reload.

## 7. After the switch

- Open demo's timetable as a planner. It must show the same placements as before: in the workspace mode the timetable reads the placement entities.
- Request a timetable for a scope that already had one. The job must succeed and **continue** from the migrated placements: it keeps them and adds only the missing ones, as legacy does (D7, #95).
  - This is a visible change for a scope whose last run was a draft. In the two-database mode a new request **replaced** the scope's placements and computed a fresh timetable (#71). Now it keeps the draft's placements and places the rest around them ([tenant-migration.md](tenant-migration.md), section 6).
  - To start such a scope from scratch, its placements have to be removed first; that is outside this change.
- After the job:

```sh
psql -d tt_demo -Atc "SELECT count(*) FROM pg_namespace WHERE nspname LIKE 'scheduler\_job\_%'"   # 0
psql -d postgres -Atc "SELECT count(*) FROM pg_roles WHERE rolname ~ '^tt_demo_job_[0-9]+_[0-9]+$'"  # 0
```

## 8. Rollback

### a. The rollback command (preferred)

Use it at any point after step 4, even a partial one:

```sh
# stop demo's worker (step 2)
site suspend demo
site rollback-single-db demo
```

It:

- writes `site.ini` back to `tt_demo_engine`;
- deletes the placement entities through the Entity API (they are a copy of `tt_demo_engine.actividad`);
- removes the engine schemas, the job roles, `tt_demo_jobs` and `tt_demo_engine_owner` from `tt_demo`;
- checks the Drupal records against `before.json`. Jobs that ran after the migration are allowed; the earlier history must be identical.

Placements made by workspace runs **after** the migration exist only as entities. The command refuses to delete them and prints how many there are. Either migrate forward again, or accept losing them with `site rollback-single-db demo --discard-new-placements`.

After a discarding rollback, the timetable of such a scope shows the engine database's placements under the newest job, until a new request runs.

Then:

```sh
site resume demo
# start demo's worker again (same command)
```

You can migrate forward again later with step 4, which takes a fresh backup.

### b. Restoring the backups (last resort, destructive)

Use this only if `tt_demo` itself is damaged. It discards everything written after the backup.

```sh
# demo's worker stopped, demo suspended
psql -d postgres -c "SELECT pg_terminate_backend(pid) FROM pg_stat_activity WHERE datname = 'tt_demo'"
psql -d postgres -c 'ALTER DATABASE tt_demo RENAME TO tt_demo_damaged'
psql -d postgres -c "CREATE DATABASE tt_demo OWNER tt_demo_owner ENCODING 'UTF8' TEMPLATE template0"
pg_restore -d tt_demo --exit-on-error "$BACKUP"/demo-single-db/tt_demo-pre-single-db-*.dump
cp "$BACKUP"/demo-single-db/demo-site.ini-pre-single-db-* "$SITES/demo/site.ini"
sed -i '/^MIGRATION/d' "$SITES/demo/provision.state"
# Re-apply the database grants and checks (idempotent; the site file now
# says two_database, so it keeps tt_demo_engine):
site provision demo --name "<demo's site name>" --admin-mail "<the address used at provisioning>"
```

`tt_demo_engine` was never changed by the migration. If it is damaged too, restore its dump the same way, into a new `tt_demo_engine` owned by `ancora_operator`, its original owner. Keep `tt_demo_damaged` until the site is verified, then drop it.

The restore of the dumps themselves is verified in disposable clusters (`tenant-verify.sh`, step 7). This full sequence has not been rehearsed end to end; prefer 8a.

## 9. Later: retiring `tt_demo_engine` (not part of this change)

After an acceptance period, and only when no rollback is wanted any more:

1. take a final `pg_dump` of `tt_demo_engine`;
2. revoke `tt_demo_engine_app`'s CONNECT on it;
3. drop the database.

`ancora-site.sh` has no command for this yet. `deprovision` drops it with the site.
