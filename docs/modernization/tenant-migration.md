# Migrating a two-database site to the single-database layout (#96)

Issue: #96, part 2. Part 1 ([tenant-provisioning.md](tenant-provisioning.md)) gives new sites one database. This part moves an **existing** two-database site (#71), such as the private `demo`, to the same layout, without changing its records. The step-by-step procedure for `demo` on the host is [runbook-demo-single-db.md](runbook-demo-single-db.md).

The legacy engine SQL is unchanged and no business rule was added. **0 LBR bytes are claimed.**

## 1. Commands

```
ancora-site.sh migrate-single-db <slug> [--check] [--backup-dir <dir>]
ancora-site.sh rollback-single-db <slug> [--discard-new-placements]
```

Both are run by the non-superuser operator. They need nothing the provisioning does not already need.

## 2. What is migrated, and what is not

In the two-database layout, the engine database `tt_<slug>_engine` holds:

| Data | Origin | Migration |
| --- | --- | --- |
| Academic rows (plans, periods, subjects, groups, teachers, rooms, templates, …) | Projection of Drupal's records, rewritten by every sync (`LegacyTableSync`) | Not migrated: a workspace run writes them again from the entities (#95) |
| `seguridad.usuario` (`u<uid>`) | Written by the sync (`ensureCreator`) | Not migrated: written on demand in the tenant database |
| Occupancy and derived rows (RULEs, triggers) | Derived from `actividad` | Not migrated: the legacy RULEs rebuild them when a workspace loads the placements (#93) |
| **`actividad`** | **Written by `generar()`: the placements** | **Imported as `ancora_placement` entities** |
| `otros_recursos_usados` | Written by `generar()` | Must be empty: other resources do not exist in Drupal (D9). The import refuses otherwise |
| `generador.cancelar` | Soft cancellation requests | Reported, not carried over: in the workspace mode a request reaches only its own job (D12, #95) |

The Drupal records, including the jobs (`ancora_engine_job`), are **not changed**. Checksums before and after prove it.

## 3. The placement import (`TwoDatabaseImport`)

`ancora_scheduling/src/Migration/TwoDatabaseImport.php`, run by `site-tasks.php placements-check` and `placements-import` while the site is still configured for its engine database.

- **Read:** one read-only connection, with the site's worker role. The rows are read in physical order (`ORDER BY ctid`), the order in which legacy reads them.
- **Order:** the entities are created in that order. The entity id order, in which a workspace run loads the stored placements (`SiteRows::placements()`), is therefore the legacy order.
- **Identity:** `engine_id` is the row's id, unchanged. `placed_at` is `momento_de_colocacion`.
- **Mapping and checks:** each row is mapped back to the records and checked as a publication is (`PlacementMapper`, shared with `PlacementPublisher`): the template of the period, and the group, teacher and room of the plan's creator. It also checks the creador, the day and slot inside the period, a required teacher and room, and no template placed twice for one group. The placement is validated through the Entity API as the plan's creator.
- **Job link:** the two-database layout never recorded which job placed a row. Each placement is linked to the job whose placements the two-database timetable showed for its scope: the latest job of the scope (plan, period, specialty and level of the template) that changed placements (`JobRepository::changedPlacements()`, as `TimetableReader` reads them). This is the read model's existing rule, not a new one. The placing moment is kept in `placed_at`.
- **Refusals (nothing imported):** any of the following stops the import. `--check` lists every problem:
  - a row that does not map or fails a check;
  - a scope without such a job;
  - rows in `otros_recursos_usados`;
  - placement entities that are not rows of the engine database.
- **Transaction:** one transaction of Drupal's connection, under the publication's advisory lock. A failure imports nothing. An existing entity with the row's engine id is kept, so the re-run is the recovery.

## 4. Steps of `migrate-single-db`

The site must be **suspended**, with no job queued or running. Each step is recorded in `provision.state` (`MIGRATION=`), so a re-run resumes after the last completed step.

1. **backup:** `pg_dump -Fc` of `tt_<slug>` and `tt_<slug>_engine`, and a copy of `site.ini`, into `--backup-dir`.
2. **checksums:** `site-tasks.php checksums` writes `migration/before.json`. It holds the row count and md5 of every table of every content entity type except the placements, of `config` and `users_data`, and of the job history up to the current highest job id.
3. **engine:** `workspace_ensure`, exactly as provisioning does: the engine owner, the job group, and the engine in schema `scheduler` of `tt_<slug>`, in one transaction.
4. **placements:** the import (section 3).
5. **compare:** `after.json` must equal `before.json`, or the site is not switched.
6. **switch:** `site.ini` gets `ENGINE_MODE=workspace`, `ENGINE_DB_NAME=tt_<slug>` and the same worker role. Then a cache rebuild, and `engine-check`: the worker role's cleanup runs in the site database.

`tt_<slug>_engine` is left in place, **unchanged**, and the worker role keeps its CONNECT on it. Retiring it is a separate, later step.

`--check` runs the import's read and plan only, on a site in any status, and prints the report.

## 5. Rollback

`rollback-single-db` can run after a complete or a partial migration:

1. It writes `site.ini` back to the engine database.
2. It counts the placement entities that are not rows of the engine database: they were made by workspace runs **after** the migration. It refuses if there are any, unless `--discard-new-placements` is given.
3. It deletes the placement entities (Entity API, one transaction).
4. It removes the engine from `tt_<slug>` as the engine owner: the workspaces, the engine schemas, the job roles and the job group, the grants. Then it drops the engine owner.
5. It compares the checksums with `before.json`. The job history is compared up to the job id recorded before the migration: later jobs are allowed. Then a cache rebuild and `engine-check` on the engine database.

A later `migrate-single-db` starts over, with a fresh backup. The dumps of step 1 are the last resort (runbook, 8b).

## 6. What changes for the site's users

In the workspace mode, a run **continues** from the stored placements, as legacy does (D7, PAR-085, #95).

In the two-database mode, a new request on a scope **replaced** that scope's placements (#71, `r1-journey.sh` step 7). After a draft, for example a run that was soft-cancelled, a new request therefore gave a fresh timetable.

After the migration it keeps the draft's placements and places the rest around them. This is the legacy behaviour the #95 workspace mode reproduces; the verification compares it with the legacy oracle.

## 7. Defects found and fixed on the way

- **Placement moment precision.** `placed_at` was a single-precision `real` (Drupal's default `float` size), which keeps only about 7 digits: `8463176.47` was stored as `8463176`, losing the hour part. It is now double precision, like `momento_de_colocacion` (`PlacementStorageSchema`, update `ancora_scheduling_update_11002`).
- **First publication after a cache rebuild.** Validating a placement as the plan's creator runs access-checked entity queries. Their access alter computes the creator's permissions, which writes the role and permission caches when they are cold. Inside a transaction, the pgsql driver runs each SELECT in a savepoint named `mimic_implicit_commit`, and a cache write from within it fails: `A transaction named mimic_implicit_commit is already in use`. The first workspace publication after any cache rebuild therefore failed (#95), and the migration's import could fail the same way.
  - `PlacementMapper::warmAccess()` now runs the same queries as the same user **before** the transaction.
  - Kernel test: `PlacementPublisherTest::testPublishWithColdCaches`, with database cache bins. It fails without the fix.
- **Out-of-range engine ids.** A ten-digit numbered id above the PostgreSQL integer range made the mapping throw a database error, instead of reporting the row. It is now reported.

## 8. Verification: `tenant-verify.sh` sections 6 to 9

These sections run in disposable clusters only, with the non-superuser operator, on PostgreSQL 16 and 18 in CI (job `tenant`).

6. **A two-database site populated like `demo`:** omega is provisioned with `--engine-layout two-database`, with a planner and the E2E organization. A run is cancelled mid-flight (test-only slow-placement trigger in `tt_omega_engine`), which leaves a draft of n placements (3 to 7) and its job.
7. **Migration:**
   - `--check` reports n rows, all of that job, and no problem;
   - an active site is refused;
   - a failure after 2 imported placements (test hook `ANCORA_MIGRATION_TEST_FAIL_AFTER`) leaves no entity, the engine installed, and the site still on its engine database;
   - the backups exist;
   - the re-run resumes and completes, with equal checksums before and after;
   - the workspace mode is on, and the engine database is unchanged;
   - the entities have the engine ids in `ctid` order, the job, and day, slot and placing moment equal to the rows;
   - the Drupal dump restores into a scratch database;
   - a second run says "already migrated".
8. **Continuation:** the first workspace run loads the n migrated placements, publishes 8 − n and keeps the n unchanged. Its timetable equals the **legacy oracle**: `generar()` with the same scope in `tt_omega_engine`, in a transaction that is rolled back.
9. **Rollback and forward again:**
   - the rollback refuses to discard the 8 − n newer placements, then succeeds with `--discard-new-placements`;
   - after it: the two-database mode, no entity, no engine schema, engine owner or job role in `tt_omega`, the checksums of before the migration, and the engine database still unchanged;
   - a two-database run works again;
   - `migrate-single-db` forward again links all 8 placements to that run's job, with the E2E digest;
   - a workspace run after it adds nothing;
   - omega's and alpha's worker roles cannot reach each other's databases.

**Result on the development workstation** (PHP 8.4, PostgreSQL 16.15): **84 checks, 84 PASS, 0 FAIL** in about 150 s (54 of part 1, 30 of sections 6 to 9). The other suites stay green: `workspace-journey.sh`, `parity-diff.sh --workspace`, `r1-journey.sh`, `engine-integration.sh`, `install-disposable.sh` (with the new Kernel tests `TwoDatabaseImportTest` and the cold-cache publication), `multisite-verify.sh` and `workspace-proof.sh`.

## 9. Limits and open risks

- **Job link granularity.** Placements made by several two-database runs of one scope were not distinguishable. They are all linked to the run the timetable showed. `placed_at` keeps each placement's own moment.
- **The two-database draft semantics end with the migration** (section 6). A scope whose latest two-database run was a draft continues from that draft.
- **The site is suspended** during the migration. With a large engine database, the two dumps dominate the time.
- **Retiring `tt_<slug>_engine`** is not automated. Until it is retired, the worker role can still connect to it. It is needed for the rollback.
- **The full restore from the dumps** (runbook 8b) is documented but not rehearsed end to end. The dumps' restorability is verified.
