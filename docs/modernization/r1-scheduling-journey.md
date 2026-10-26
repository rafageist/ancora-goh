# R1 scheduling journey: from Drupal records to a timetable (issues #70, #71, #72)

Status: **implemented and verified end to end**; date 2026-10-26. Under the replatforming decision #66.

The maintainer's goal for R1 is "a functional Drupal academic module, the first organization site, and a verified connection to the scheduling engine". The three pieces existed separately:

- the academic module (`ancora_academic`, [academic-domain-mapping.md](academic-domain-mapping.md));
- the engine worker (`ancora_engine`, [engine-worker.md](engine-worker.md));
- the organization sites (multisite, [drupal-multisite.md](drupal-multisite.md)).

This change connects them. A planner of an organization site picks a plan, a period, a specialty and a level. The worker loads that plan's records into the site's own engine database and runs the unchanged PL/pgSQL engine. The site then shows the timetable.

| Part | Where |
| --- | --- |
| Engine database per site, provisioned and removed with the site | `modern/scripts/ancora-site.sh`, `modern/scripts/engine-db.sh` |
| Per-site engine settings (from `site.ini`, never from request or job data) | `modern/settings/ancora.org.settings.php` |
| Job source columns, input hook, digest without the plan prefix, read API, cache tag | `modern/web/modules/custom/ancora_engine` |
| Request form, status page, sync, timetable page | `modern/web/modules/custom/ancora_scheduling` (new) |
| End-to-end journey on Caddy + PHP-FPM | `modern/scripts/r1-journey.sh`, helpers in `modern/scripts/lib/stack.sh` |
| CI | `.github/workflows/modern.yml`, job `journey` (PHP 8.4 + PostgreSQL 16, PHP 8.5 + PostgreSQL 18) |

The engine SQL (`dev/sql`) and the legacy application are not changed.

## The flow

```
planner (browser)            site's Drupal DB (tt_<slug>)        worker (drush, CLI)                 engine DB (tt_<slug>_engine)
-----------------            ----------------------------        -------------------                 ----------------------------
GET  /ancora/scheduling/request
POST plan, period,      -->  INSERT ancora_engine_job
     specialty, level        (queued; scope + source)
     + form token
                                                            claim (running)
                                                            BEGIN READ COMMITTED ------------------>  sync the plan's rows (diff)
                                                            export -> hash -> LegacyRows            delete the scope's placements
                                                                                                     generar(...)
                                                            metrics + digest <--------------------   read-only SQL
                                                            COMMIT (or ROLLBACK) ------------------>
                             UPDATE job: succeeded | partial | cancelled | timed_out | failed
                             invalidate cache tag ancora_engine_job_list
GET  /ancora/planning/{plan}/timetable
                             latest placement-changing job --> read-only session -------------------> SELECT placements of the scope
                             map engine ids back to records
```

1. **Request** (`/ancora/scheduling/request`, form `RequestRunForm`). It needs the permissions `request ancora engine jobs` **and** `view academic data`.
   - The form lists the plans, and the periods, specialties and levels grouped by plan. Only records the user may view are listed.
   - The validation refuses:
     - a record of another plan;
     - a scope that already has a queued or running job.
   - Submitting the form only **inserts a queued job**. Nothing is exported, synced or generated in the web request.
   - The form redirects to the job's status page.
2. **Worker** (`ancora-site.sh work <slug> --once`, or a loop; it runs `drush ancora-engine:work` with the site's runtime role). It claims the job and opens its engine connection, then runs everything below in **one** engine transaction:
   1. the sync;
   2. the deletion of the scope's previous placements;
   3. `generar()`;
   4. the metrics.
3. **Status page** (`/ancora/scheduling/jobs/{job}`). It needs `view ancora engine jobs` and `view academic data`, and view access to the job's plan (#105), so the URL of another user's run reveals nothing of that plan. It shows:
   - the plan, period, specialty and level;
   - the state, labelled so that a draft never reads as final;
   - the metrics, the placement digest and the input snapshot hash;
   - a link to the timetable, and a cancel link for the requester (the cancel form of `ancora_engine`).
4. **Timetable page** (`/ancora/planning/{plan}/timetable?period=<id>`): see "Timetable read model".

## Scope mapping: from the records a user picks to engine ids

Updated by #70 (D1–D3, D15; see [legacy-parity-harness.md](legacy-parity-harness.md)). One engine database serves one site and holds **every user's data**, as one legacy database held every user's rows. Engine ids are **numbered with the entity id** (`LegacyRows::engineId()`):

```
id        = sprintf('%010d', <entity id>) . ":" . <code>     e.g. "0000000012:E"
specialty = id of the specialty (the plan creator's)           "0000000003:E"
level     = id of the level (the plan creator's)               "0000000004:N"
period    = id of the period (of the plan)                     "0000000001:PER"
owner     = "u" . <Drupal user id>                             "u2" (legacy "creador")
```

- **Why numbered ids.** Legacy ids are generated replicas that sort in entry order, and `generar()` reads some tables in id order: the result depends on it (harness, scenario s04). Entity ids grow in creation order and are unique per type on the site, so the ids keep the legacy order, never collide between users or plans, and do not change when a code is edited. The code after `:` keeps them readable.
- **Owner.** The legacy owner of a record is the legacy user of the Drupal user who created it (`ScopeMapper::creatorOf()`), created by the sync with no personal data and an unusable password. The per-owner triggers then act across that user's plans, as in legacy: a breakdown row for every subject and every period of the user's plans; the activity type priorities of the user.
- **Scope rule.** The period must be the plan's, and the specialty and level the plan creator's (the legacy generator window offers only the user's own).
- **The job records both.** The engine scope, and its **source**: nullable columns `plan_id`, `period_id`, `specialty_id`, `level_id` and `id_prefix` of `ancora_engine_job` (`ancora_engine_update_11001()`). For synced jobs `id_prefix` holds the id marker `:` (see "Digest"). A job without a source (CLI, `engine-integration.sh`) runs on data already in the engine database, as before.
- **Checked again at run time.** The sync loads the four records by id and computes the scope again. If a record was deleted, moved to another plan or user, or the job was queued with the earlier id scheme, the job **fails** ("queue it again").
- Until #70 every plan had its own prefix `p<plan id>:` and its own legacy owner `p<plan id>:owner`, because catalogues were copied per plan. That owner is gone: it split one person's rows over several legacy users, so the per-owner triggers never saw a user's other plans.

## Sync semantics (Drupal → engine, inside the run)

`EngineSync` (service `ancora_engine.job_input`, the optional input hook of the worker, `JobInputInterface`) does the following:

1. Computes the **input snapshot hash** of the job's plan (see below).
2. Builds the rows of the **whole site** with `LegacyRows::build($export, $owner, TRUE)`: every plan (`AcademicExport::export()`, every period, with its owner's catalogues) and the catalogues of users without a plan (`AcademicExport::catalogue()`). A user's catalogue rows appear in each of their plans' exports and are written once. The whole site, because the legacy engine reads data across plans and users: the breakdown triggers pair a subject with every plan of its creator, `dame_grupos` takes the groups of every plan (quirk Q-02), and the activity type order is global (Q-03).
3. Writes them with `LegacyTableSync`.

### Replace semantics

After the sync, the synced rows in the engine database are exactly the built rows. Synced rows are the rows whose id, or owning column, is a numbered id, or an id of the earlier `p<plan id>:` scheme (`LegacyTableSync::SYNCED_ID`), which the first sync after the update replaces. Rows of any other origin are never read or written:

| Table | Owning column |
| --- | --- |
| Most tables | `id` |
| `brigada_de_grupo` | `grupo` |
| `desglose` | `asignatura` |
| `profesor_para_grupo` | `grupo` |
| `lugar_para_actividad` | `plantilla` |

`SchedulingTest::testEveryRowIsNumbered` proves that every row of the fixture is covered.

The sync is a **difference**, not a delete and reinsert:

1. **Deletes, children first.** The plan's rows whose key is no longer exported are deleted. The legacy cascades remove what depended on them, including placements of deleted templates, groups, teachers or rooms.
2. **Inserts and updates, parents first.** Each table is read again, because triggers and cascades may have changed it. Missing rows are inserted, and rows that differ are updated. Unchanged rows are not touched. The retry in the journey reports `0 0 0` (inserted, updated, deleted).

Why a difference: the placements of the other scopes and plans reference these rows. A delete and reinsert would cascade them away, together with the legacy restriction rows that reference placements.

### The legacy triggers stay enabled

The runtime role does not own the tables and cannot disable triggers (`session_replication_role` needs a superuser). They are respected instead:

- **Breakdown rows (`desglose`)** are created by `nuevo_desglose_si_nueva_asignatura` / `_nuevo_periodo`, for every subject and every period of the same owner's plans.
  - The sync **reuses** them. It matches a breakdown by `(asignatura, periodo)` and keeps the trigger's id.
  - It updates the breakdown rules only if they differ (they are the legacy defaults today).
  - It points the templates at that id.
  - It inserts a breakdown row only when no trigger created one.
  - The journey checks this: 0 breakdown rows inserted by the sync, 2 present.
- **Template order (`orden`).** The order triggers bump or renumber `orden` after inserts and deletes. Afterwards, the sync sets the exported sequence with `actualizacion_interna = TRUE`, the flag the triggers skip. It goes through negative values first, so `UNIQUE (desglose, orden)` holds at every step. Then it resets the flag.
- **Room priorities.** The priority triggers compact priorities after a delete. Priorities are set after the deletes, with the same flag.

Every statement uses bound parameters (`EngineClient::rows()` / `affected()`). Table and column names come only from class constants and are checked against `^[a-z_]+$`.

### Transaction and failure

- The sync runs **after `BEGIN` and before the scope's placements are deleted**, in the run's own transaction (READ COMMITTED, `statement_timeout` = the job's limit).
- A failed sync rolls back and the job fails with "The input sync failed and was rolled back: …". The engine database is left as it was, including the previous final timetable.
- So is any later failure: an invariant violation, a timeout, a hard cancellation or a lost worker.
- The journey makes the sync fail on purpose (a test-only trigger that refuses room updates, while a room capacity changed in Drupal). It checks that the engine still has the old capacity and the old placements (same digest), and that the page still shows the final timetable, marked out of date.
- The scope check (`scopeExists`) moved inside the transaction for synced jobs: the scope may exist only after its input is loaded.

### Input snapshot hash

The hash is `sha256` of the canonical JSON of the plan's export (`ScopeMapper::hash()`: `json_encode` with unescaped slashes and Unicode, and zero fractions preserved, so floats stay floats). The export is deterministic: sorted lists, UUID keys and typed numbers.

- **Equal data, equal hash.** Any change of a record of the plan or of its owner's catalogues changes it, a label included.
- **Stored in the job result** as:
  - `input_hash`;
  - `input`: the schema version, the number of export issues, the number of rows, the inserted, updated and deleted counts per table, and `committed`.
- **Out-of-date warning.** The timetable page compares the hash of the run it shows with the hash of the current data. When they differ, it says "The planning data changed after this run".

### Digest

The E2E placement digest (`dev/e2e/expected-placements.md5`) is computed over template, group, teacher name, room, day and slot. For a synced job each id is listed **from after the job's id marker** (`EngineClient::PLACEMENTS`, `$4` = `:`; `''` for other jobs), so the digest uses the codes and compares with the legacy digest. The list is ordered by the engine ids, that is, in creation order, as legacy orders by its replica ids. The fixture synced from Drupal gives `4199818b55d4f1c44f2cba24f0367e29`, the legacy value.

## Timetable read model

`/ancora/planning/{plan}/timetable?period=<id>`, `TimetableReader` and `SchedulingController::timetable()`. It needs `view academic data` and view access to the plan: the plan's creator (or an administrator).

1. **Which runs.** The page takes the jobs of the plan and period (`JobRepository::forSource()`) and groups them by specialty and level. For each scope, it takes the **latest job that changed the placements** (`JobRepository::changedPlacements()`): `succeeded`, `partial`, or `cancelled` with `kept = partial`. A rolled-back run (failed, timed out, hard-cancelled) changed nothing, so it does not hide the timetable before it.
2. **Final or draft.**
   - **Final** only when that job `succeeded`.
   - After a `partial` or a soft-cancelled run, the engine holds a draft: replace semantics removed the earlier final placements. The page then says "No final timetable" and shows the placements under a "DRAFT: not a final timetable" label, in a hatched grid with `data-timetable="draft"`.
   - A queued or running job is mentioned ("Job 4 is queued.").
3. **Reading the engine.** Explicit read-only SQL on the site's engine connection: `EngineClient::placements($scope)`, which returns template, group, teacher, room, day, slot and duration. The session uses `default_transaction_read_only=on` and `statement_timeout=5s`, with `application_name=ancora_engine_read`.
4. **Mapping back.** `LegacyRows` builds the rows of the current export with numbered ids. The same ids then map back to labels:

   | Engine id | Label |
   | --- | --- |
   | template id | "Subject #sequence" |
   | group, teacher, room | the record's label |

   An id that no longer maps (deleted since the run) is shown as its code.
5. **Grid.** Day × slot (days of the period × slots per day, with start and end times from `start_hour` and `slot_duration`). An activity of several slots fills each slot it covers. Filters `?group=`, `?teacher=` and `?room=` take codes of the plan; an unknown code is ignored, and the page says how many placements the filters keep. Below 48rem the grid is replaced by the same placements as a list per day (#105); only the grid cells (`td[data-day]`) count as placements for `scripts/lib/timetable-cells.php`.
6. **Escaping and caching.**
   - Every value is rendered by Twig (`ancora-scheduling-timetable.html.twig`, autoescape).
   - Cache contexts: `user.permissions` and `url.query_args:{period,group,teacher,room}`, plus `user` from the plan access check (#70).
   - Cache tags: the plan, the list tags of the 13 academic types and of the placements, and `ancora_engine_job_list`.
   - `JobRepository` invalidates `ancora_engine_job_list` on every state change: enqueue, claim, finish, cancel, recover.
   - Since #70 the page varies by user: only the plan's creator may see it (the access result has the `user` cache context). Dynamic Page Cache does not cache per-user responses, so the journey checks `UNCACHEABLE` instead of the earlier HIT. It also checks that the page switches to the draft as soon as the cancelled job ends, without a cache clear.
   - If the engine cannot be read, the page says so without details and is not cached.

## Permissions

| Action | Permission(s) |
| --- | --- |
| Request a timetable | `request ancora engine jobs` + `view academic data` |
| Status page of a run | `view ancora engine jobs` + `view academic data`, and view access to the run's plan |
| Cancel a run | the requester with `request ancora engine jobs`, or `administer ancora engine` (existing cancel form) |
| Timetable page | `view academic data` (and view access to the plan) |

Organizations are separated by site (#72). A planner of beta has no session on alpha, and alpha's job and plan ids do not exist on beta (404).

## Provisioning: one engine database per site

`ancora-site.sh provision <slug>` now also does the following, in its database step:

- creates the secret `$ANCORA_SITES_DIR/<slug>/engine-db-password` (0640, web group);
- creates the engine database and role through `engine-db.sh create --admin-install --if-missing`:
  - database `tt_<slug>_engine`;
  - runtime role `tt_<slug>_engine_app`;
- writes `ENGINE_DB_NAME`, `ENGINE_DB_USER` and `ENGINE_DB_PASSWORD_FILE` into the site's `site.ini`.

Details:

- **Idempotent.** `--if-missing` keeps an installed engine database (`dev/sql/install.sh` is one transaction, so an existing database is complete). The role's password and the grants are applied again.
- **Admin install.** The legacy schema is installed with `--admin-install` and `AGOH_ADMIN_INSTALL_CONFIRM=tt_<slug>_engine`, set by the tool: the operator's `provision` is the deliberate choice of the server. `ANCORA_ENGINE_ADMIN_INSTALL=0` restricts it to a verified disposable cluster.
- **No legacy seed.** The schema is installed without the legacy seed (no administrator account in the engine database). The sync creates each plan's owner.
- **Non-superuser operator.** The operator role has CREATEDB and CREATEROLE, as on the real host (PostgreSQL 16+ rules). `engine-db.sh` never restates SUPERUSER, REPLICATION or BYPASSRLS, and it fails if the engine role has any of them, or CREATEDB or CREATEROLE. The journey runs every operator command as such a role (`ancora_journey_operator`); `multisite-verify.sh` uses `ancora_verify_operator`.
- **Settings resolve per site.** `ancora.org.settings.php` sets `$settings['ancora_engine']['database']` from `site.ini` only:
  - the name, role and password file come from `site.ini`;
  - the host and port come from `ANCORA_ENGINE_DB_HOST`/`PORT`, else the shared `ANCORA_DB_HOST`/`PORT`;
  - the limits may come from `ANCORA_ENGINE_*` in the pool environment.

  An organization site never uses the engine module's environment fallback: without `ENGINE_DB_*` its engine is "not configured". So a site can never reach another site's engine database.
- **Deprovision.** `deprovision` takes a final `pg_dump -Fc` of the engine database, then drops it and the engine role, with the site's own database and roles.
- **Existing sites.** A site provisioned before this change gets its engine database by running `provision <slug>` again (idempotent).
- **Worker command.** `ancora-site.sh work <slug> [--once|--max-jobs …]` runs the worker of one active site with the site's runtime role. It takes no lock.

## Verification

| Suite | Command | Result (workstation: PHP 8.4.27, PHP-FPM 8.4, Caddy 2.11.7, PostgreSQL 16.15) |
| --- | --- | --- |
| R1 journey | `dev/sql/tools/with-ephemeral-pg.sh modern/scripts/r1-journey.sh` | **63 passed, 0 failed** (about 60 s) |
| Multisite | `dev/sql/tools/with-ephemeral-pg.sh modern/scripts/multisite-verify.sh` | 167 passed, 0 failed (159 before + 8 engine checks) |
| Engine worker | `dev/sql/tools/with-ephemeral-pg.sh modern/scripts/engine-integration.sh` | 39 passed, 0 failed (unchanged) |
| Drupal install, phpcs, config determinism, PHPUnit, HTTP smoke | `dev/sql/tools/with-ephemeral-pg.sh modern/scripts/install-disposable.sh` | OK: phpcs clean, config/sync deterministic (87 files), PHPUnit 56 tests and 1384 assertions (Kernel and Functional, whole site), HTTP smoke 25/25 |
| Engine regression | `dev/sql/tools/with-ephemeral-pg.sh dev/sql/tests/run.sh` | 26 pass, 10 xfail (unchanged) |
| HTTP E2E (legacy) | `dev/sql/tools/with-ephemeral-pg.sh dev/e2e/run.sh` | 28/28, digest `4199818b55d4f1c44f2cba24f0367e29` (unchanged) |

What the journey proves, in order:

1. **Provisioning as a non-superuser operator.**
   - Control site, alpha and beta, each with its engine database.
   - The engine roles have no elevated attribute.
   - `engine-db.sh check-isolation` passes both ways.
   - A re-run of `provision` is idempotent.
2. **Data entry.** A `planner` role (view and edit academic data, view and request engine jobs) and a planner account are created on both sites. On alpha, `drush ancora-academic:load-fixture --owner=planner-alpha` creates the E2E organization through the Entity API, with the teacher the legacy harness enters over HTTP, **owned by alpha's planner**: since #70 each user sees and schedules only their own plans (legacy `creador`), so the fixture must belong to the planner who requests the timetable.
3. **Request and run.**
   - Alpha's planner logs in over HTTP and submits the request form with its form token.
   - The job is queued and the engine has no data of the plan: nothing ran in the request.
   - The scope is `p1:E / p1:N / p1:PER`, with the Drupal source.
   - `ancora-site.sh work alpha --once`:
     - the job **succeeded**, demands 8, placed 8, unplaced 0, collisions 0, overflow 0;
     - digest `4199818b55d4f1c44f2cba24f0367e29`, also recomputed with SQL;
     - the input hash equals the sha256 of the export computed in Drupal;
     - the trigger's breakdown rows are reused, and the plan's rows have the plan's owner.
4. **Timetable page.**
   - The page lists the 8 placements, each in its day/slot cell with its group, teacher and room names. It is compared field by field with the engine rows.
   - The group, teacher and room filters work.
   - The page is not cached by Dynamic Page Cache: it varies by user (#70).
5. **Isolation.**
   - Beta's planner gets 403 on alpha's timetable, status page, request form and job list.
   - Beta's cookie replayed on alpha gets 403.
   - Alpha's ids on beta give 404, and beta has no job and no engine data.
   - Anonymous users get 403.
   - Beta's engine role is refused by alpha's engine database.
   - The page read alpha's engine as `tt_alpha_engine_app` with the read-only session.
   - Every application login to an engine database (worker and web) used that site's engine role.
6. **Draft.**
   - A second request is queued. A third is refused by the form while the second is queued.
   - With test-only slow placements, the planner cancels the running job over HTTP.
   - The job ends `cancelled`, kept = partial, with 1 to 7 placements (3 on the workstation).
   - The page shows only a labelled draft with those placements and nothing as final. The status page says "Cancelled: draft".
7. **Retry.** A new request succeeds with the E2E digest. It replaced the draft's placements, its sync changed no row, and the page is final again with the 8 placements in their cells.
8. **Failed sync.**
   - The failed sync rolls back: the old room capacity and the old digest are kept.
   - The page still shows job 3's final timetable, marked out of date.
   - After the fix, the next job syncs the one changed room and succeeds.
9. **Logs.** No unexpected permission error in PostgreSQL, and no PHP error.

Kernel and Functional tests (in `install-disposable.sh`):

- `SchedulingTest` (Kernel):
  - scope mapping (numbered ids, the owner's legacy user), and refusal of another plan's period or another user's specialty;
  - every synced row has a numbered id and the owner's legacy user;
  - the hash is stable and changes with the data;
  - source round trip, `activeForScope` and `forSource`;
  - the final and draft rules, including that rolled-back runs never hide or replace what is shown.
- `SchedulingPagesTest` (Functional):
  - access for anonymous users, another viewer (403 on the planner's timetable) and the planner who owns the plan;
  - the form queues without running and refuses a period of another plan and a duplicate request;
  - the timetable page shows a queued job, then the final state as soon as the job ends (cache tag), and fails closed without an engine database.

## Not covered yet

- **Time restrictions** (nominal, real, conditional, inherited), **other resources and resource groups**, **fixed and forbidden rooms**, the **preference matrix** and **breaks**. They are not modelled in Drupal yet (academic mapping, "Not modelled yet"), so the sync writes none of them. The engine tables stay empty for synced plans.
- **Exports** of the timetable (PDF, spreadsheet, iCalendar) and printing.
- **Engine options in the UI.** The form queues the default `generar()` options (`forma` 1, no patches, no compaction) and the site's default time limit. The engine module's own form (`/admin/ancora/engine/add`) still offers every option for raw scopes.
- **Teacher and room timetables across scopes.** The page shows one plan and period, per specialty and level. A teacher's week across levels is the filter applied to each scope, not one merged grid.
- **Retry button.** A retry is a new request, or `drush ancora-engine:retry`. A web "retry" action is not added.
- **Worker scheduling on the host** (systemd units per site, cron) is #72/#76 deployment work.
- **The editor role** (`planner` in the journey) is created by the test, not shipped in `config/sync`. Which roles a site gets is #73.

## Open risks

- **The web pool reads the engine password.** The timetable page connects to the engine database from PHP-FPM, with the site's engine runtime role (it has DML). The session is read only and short, but a code execution bug in a site could reuse the password for writes. The same holds for the Drupal runtime role (#72, one pool for all sites). A dedicated read-only engine role per site is the next hardening step.
- **Concurrent edits.** The sync reads the Drupal records when the run starts, in the worker's Drupal connection, without locking them. An edit committed during the sync can give an input hash that matches neither the data before nor the data after. The stale warning then shows on the next page view. A snapshot read (REPEATABLE READ on the Drupal side) would close this.
- **Plan-wide sync, scope-wide run.** Updating plan-wide rows (for example a room capacity) also changes the input of the plan's other scopes. Their final timetables stay as they were, but are marked out of date.
- **Template ids and the number of periods.** `LegacyRows` appends `@<period>` to template ids only when the plan has several periods. Adding a second period to a plan therefore renames every template id. The next sync deletes the old templates, and with them the plan's placements, and inserts the new ones. Document it to users, or make the suffix unconditional in a future export schema version.
- **Legacy function `dame_max_priori_clasif_act`** hard-codes the owner `admin-superuser`. It is used only by the legacy data entry screens, not by `generar()`, but a future use with synced plans would see no activity types.

## LBR impact

**0 bytes retired.** This change connects new modern code. The legacy data entry screens and the legacy application still exist unchanged. No legacy file is moved or modified (#68). Retirement credit for data entry needs the accepted comparison of #77 on real data.
