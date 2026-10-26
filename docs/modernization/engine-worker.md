# Engine worker: bounded MPI/Prioriza jobs controlled by Drupal (issue #71)

Status: **first slice implemented**; date 2026-10-25. It implements the contract proposed in `docs/saas/engine-contract.md` (PR #82) and answers the review of that PR: mid-flight cancellation evidence, partial outcome semantics, idempotent claims, recovery of a lost worker, two-site negative tests.

The scheduler is still the characterized PL/pgSQL engine (`dev/sql/generador`, `dev/sql/prioriza`). Nothing in it changed. The new parts are:

| Part | Where |
| --- | --- |
| Mid-flight cancellation test (two connections) | `dev/sql/tests/concurrency/cancel_midflight.sh` |
| Drupal module: job table, worker, Drush commands, status pages | `modern/web/modules/custom/ancora_engine` |
| Engine database provisioning for one site | `modern/scripts/engine-db.sh` |
| Worker integration test | `modern/scripts/engine-integration.sh` |
| SQL equivalent of the E2E data entered over HTTP | `dev/e2e/fixture-http-steps.sql` |
| CI | `.github/workflows/engine.yml` (PHP 8.4 + PostgreSQL 16, PHP 8.5 + PostgreSQL 18) |

## 1. Cancellation evidence

The review asked whether a cancellation row inserted by another connection *while* `generar()` runs is seen. Case 062 only shows a row that exists before the run starts.

### How generar() checks

`generar()` calls `generador.fue_cancelado(esp, nivel, periodo)` at 40 points of `generador/generar.sql`: during preparation, and inside the placement loops, after each placement. `fue_cancelado()` is a VOLATILE PL/pgSQL function. It SELECTs the row and, if it finds it, DELETEs it and returns TRUE. `generar()` then returns normally.

Under **READ COMMITTED**, each SQL statement inside a PL/pgSQL function takes a new snapshot. So a row committed by another session is visible at the next check, even though the call of `generar()` is still one statement. Under **REPEATABLE READ**, the whole transaction uses one snapshot, and the row is never seen.

### Test

`dev/sql/tests/concurrency/cancel_midflight.sh` runs `generar()` on connection A in the worker's model: one autocommit statement, the server-default READ COMMITTED, and a `statement_timeout`. The data is the E2E organization (8 demands). A test-only trigger sleeps 0.5 s in each placement and counts the placements in a sequence. Sequences are not transactional, so connection B sees the count while A's transaction is open. B waits for N placements, then acts.

Results (PostgreSQL 16, three consecutive runs, the same numbers each time):

| Scenario | Outcome |
| --- | --- |
| full run, no cancellation | returns normally, 8/8 placed, digest `4199818b…`, 6.8 s |
| soft cancel after 1, 3 and 5 placements (B: `INSERT` + `COMMIT` of the `generador.cancelar` row) | returns normally in 0.8 s, 2.1 s and 3.3 s. It keeps **exactly** 1, 3 and 5 placements, committed and visible to B. The row is consumed. The latency from the cancel commit to the return is about 0.5 s, that is, one placement. |
| the same soft cancel under REPEATABLE READ | **not seen**: all 8 placed, and the row is left behind |
| `pg_cancel_backend(A)` from B | SQLSTATE 57014, nothing kept (rollback) |
| `pg_terminate_backend(A)` from B | connection lost, nothing kept |
| `statement_timeout` mid-run | SQLSTATE 57014, nothing kept (case 066 too) |
| a cancel row committed **after** the run finished | not consumed; it stops the **next** run at its first check, with nothing placed |
| a cancel row consumed by a run that then rolls back | the DELETE rolls back too, and the row comes back |

**Finding.** Mid-flight cooperative cancellation is observed reliably under READ COMMITTED, with a latency of one placement step. It is **not** observed under REPEATABLE READ. The cooperative check cannot interrupt a long single step, such as one Prioriza computation. So the hard stops remain `statement_timeout` and `pg_cancel_backend`, and both roll back. A leftover row is a hazard for the next run, so the worker deletes the scope's row before each run and after any run that rolls back.

## 2. Job model (site's Drupal database)

The `ancora_engine_job` table is created by `hook_schema()`. It lives in the site's Drupal database. The engine database holds only the legacy schema. The integration test checks both directions: no Drupal table in the engine database, and no engine table in the Drupal database.

| Column | Meaning |
| --- | --- |
| `id`, `uuid` | identity |
| `scope_specialty`, `scope_level`, `scope_period` | engine ids. They are validated: non-empty, at most 255 characters, no control characters. |
| `options` | JSON with forma ∈ {1, 2}, parche bool, compactar 0–10, parche1 bool and buscalugar bool. Unknown keys are refused (`JobOptions`). |
| `time_limit` | the `statement_timeout` in seconds. The default and the maximum come from server settings (Free plan: 120 s). |
| `state` | queued, running, succeeded, partial, cancelled, timed_out or failed |
| `cancel_requested`, `cancel_mode`, `cancelled_by` | the cancellation request: soft or hard |
| `requested_by`, `retry_of` | audit |
| `created`, `changed`, `started`, `finished`, `heartbeat`, `worker_id` | lifecycle |
| `attempts` | the number of claims; also the **fencing token** of a run |
| `result` | JSON: demands, placed, unplaced, collisions, overflow, digest, duration_ms, replaced, attempt, partial, kept |
| `error` | a one-line summary, at most 500 characters. It never holds a password, host or socket path. |

**At most one running job per site.** A partial unique index (`… (state) WHERE state = 'running'`, created in `hook_install()`) enforces it. Claims are also serialized with a transaction-level advisory lock.

## 3. Outcome semantics

| State | When | What stays in the engine database | Final timetable? |
| --- | --- | --- | --- |
| `succeeded` | `generar()` returned normally, no cancellation was requested, every demand placed, 0 collisions and 0 overflow | the new placements (committed) | **yes**, the only such state |
| `partial` | returned normally without cancellation, but some demands are unplaced (over-constrained input, as in case 063) | the placements, as a **draft** | no |
| `cancelled`, kept = `partial` | soft cancellation: `generar()` stopped at its next check | the placements made before the stop, as a **draft** | no |
| `cancelled`, kept = `none` | hard cancellation (`--hard`, or a soft request not honoured within `cancel_grace`), or a queued job cancelled before it ran | nothing of the run (rollback) | no |
| `timed_out` | `statement_timeout`, or the worker's wall-clock guard (limit + 10 s) | nothing of the run (rollback) | no |
| `failed` | any other error; placements that break an invariant (collisions or overflow > 0 → explicit ROLLBACK); unknown scope; engine unreachable; a lost worker after its last attempt | nothing of the run | no |

Rules:

- **Success only when generar() returns normally AND no cancellation was requested.** If a cancellation arrives while the outcome is being written, the final `UPDATE` turns succeeded or partial into cancelled (`CASE WHEN cancel_requested = 1 …`). A cancelled run is never presented as a final timetable. The status page labels it "Cancelled: draft with the placements made before the cancellation".
- **Replace semantics.** Every run, the first one or a retry, runs in **one transaction**:
  1. delete the scope's previous placements;
  2. run `generar()`;
  3. read the metrics;
  4. COMMIT.

  The scope is the activities whose template belongs to a subject of that specialty and level, in that period. Placements of other scopes stay. They are the occupancy `generar()` must respect. The orchestration layer adds this replacement; the engine function is unchanged. The legacy UI continued from the existing placements instead.
- **Retry.** `drush ancora-engine:retry <id>` queues a **new** job with the same scope and options (`retry_of` = id). Under replace semantics, the retry removes the cancelled run's partial draft (the integration test checks `result.replaced` = the partial count) and starts fresh.
- **A rolled-back run leaves the scope as it was before.** The DELETE rolls back with the run. A timeout over an existing final timetable therefore keeps that timetable (the test checks it with the digest). A timeout on an empty scope keeps zero placements.
- **Read-back rule for Drupal.** A scope's placements are a final timetable only when the latest job of that scope that changed placements is `succeeded`. Jobs that change placements are succeeded, partial, and cancelled with kept = partial. Drupal reads the engine through explicit SQL with bound parameters on the engine connection (`EngineClient`).

## 4. The worker

`drush ancora-engine:work --once`, or a loop: `--max-jobs`, `--max-runtime` and `--idle-sleep`. SIGTERM and SIGINT stop the loop after the current job. It runs only from the CLI, in a systemd unit with `MemoryMax`, `CPUQuota` and `RuntimeMaxSec` per the contract. No web request runs `generar()`.

1. **Recovery** of stale jobs (see §5).
2. **Claim.** In one Drupal transaction:
   - `pg_advisory_xact_lock`;
   - if a job is running, stop;
   - `SELECT id … WHERE state = 'queued' ORDER BY id LIMIT 1 FOR UPDATE SKIP LOCKED`;
   - `UPDATE … SET state = 'running', attempts = attempts + 1 … WHERE id = :id AND state = 'queued'`.
3. **Connect** to the site's engine database with the PHP pgsql extension. The connection uses a new session (`PGSQL_CONNECT_FORCE_NEW`), `application_name = ancora_engine_job_<id>_<attempt>`, `client_min_messages = warning`, `statement_timeout = time_limit`, and `client_connection_check_interval = 5s`, so an orphaned backend stops by itself. The credentials come only from server-side settings (§6), never from job data.
4. **Check** that the scope exists. Then delete any leftover `generador.cancelar` row of the scope, in its own autocommit statement, before the run holds any lock.
5. **Run.** `BEGIN ISOLATION LEVEL READ COMMITTED`, delete the scope's placements, then `pg_send_query_params('SELECT generar($1, …, $8)')` with bound parameters. The worker polls about every 100 ms:
   - a heartbeat every `heartbeat_interval` seconds. It is fenced: if the job was recovered meanwhile, the run is cancelled and its outcome is discarded;
   - the cancellation flag, every 0.5 s. When a soft request is first seen, the worker inserts the `generador.cancelar` row on a **separate** connection (`ON CONFLICT DO NOTHING`, with `lock_timeout = 1s`, so it never waits on a row the run has just consumed). A hard request, or `cancel_grace` seconds without effect, leads to `pg_cancel_backend`. The backend is matched by pid **and** `application_name`;
   - the wall-clock guard.
6. **Outcome.**
   - If `generar()` returned: in the same transaction, delete any unconsumed cancellation row and compute the **metrics** and the **digest**. ROLLBACK if collisions or overflow > 0, else COMMIT.
   - On an error: ROLLBACK, then delete the scope's cancellation row (a consumed row comes back with the rollback).
   - Finally, the fenced `finish()`.

**Metrics.** They use the definitions of `tst.metrics` (`dev/sql/tests/fixtures/helpers.sql`), reimplemented as read-only queries in `EngineClient::METRICS`. The test helpers are never loaded in production. The queries are restricted to the job's scope: collisions are counted in the period when at least one placement of the scope is involved. On a single-scope database they equal `tst.metrics`, and the integration test compares the two.

**Digest.** The md5 of the `e2e_placements` list (`dev/e2e/lib.sh`): template/group/teacher name/place/day/slot, comma-joined in template and group order, restricted to the scope. On the E2E organization it equals `dev/e2e/expected-placements.md5`.

## 5. Cancellation and recovery

- **Cancel command:** `drush ancora-engine:cancel <id> [--hard]`.
  - A queued job becomes `cancelled` at once and never runs.
  - For a running job, the command flags it in the Drupal database. It then forwards the request itself on a separate engine connection: the soft row, or with `--hard` `pg_cancel_backend` by application name. The worker forwards the flag too (idempotent).
- **Web form:** `/admin/ancora/engine/{id}/cancel` only flags the job, so no engine connection is opened inside a web request. The worker forwards the flag within 0.5 s and escalates after `cancel_grace` (15 s by default). Access: the requester with "request ancora engine jobs", or "administer ancora engine". Ended jobs cannot be cancelled.
- **Stale worker:** a job is stale when it is `running` and its heartbeat is older than `stale_after` (default 60 s, and at least 2 × `heartbeat_interval`). `drush ancora-engine:recover` checks for stale jobs, and every worker iteration does too. Recovery:
  1. terminates the engine backends of that attempt (`pg_terminate_backend` by `application_name`), so its transaction rolls back;
  2. waits until none is left;
  3. clears the scope's cancellation row;
  4. **requeues** the job while `attempts < max_attempts` (default 2: one requeue), **fails** it after its last attempt, or **cancels** it if a cancellation was requested.

  If the old backend cannot be confirmed stopped, the job fails instead of being requeued, so two runs of a scope never overlap. Every update is fenced by the attempt number, so a slow worker that comes back can no longer write a heartbeat or an outcome.

## 6. Settings and provisioning

`$settings['ancora_engine']` in the site's `settings.php` takes precedence. It holds `database` (host, port, database, username, `password_file`, sslmode), `time_limit`, `max_time_limit`, `heartbeat_interval`, `stale_after`, `max_attempts` and `cancel_grace` (see `EngineSettings`). Without it, the module reads `ANCORA_ENGINE_DB_HOST`, `_PORT`, `_NAME`, `_USER`, `_PASSWORD_FILE`, `_SSLMODE` and `ANCORA_ENGINE_<SETTING>` from the environment. The prefix is `$settings['ancora_engine_env_prefix']`, by default `ANCORA_`, the convention of `settings/ancora.settings.php`. The multisite work (#72) sets `$settings['ancora_engine']` per site. This change does not edit the shared settings files.

`modern/scripts/engine-db.sh create [--with-data] [--admin-install] [--password-file FILE] <database> <role>`:

1. installs the legacy schema with `dev/sql/install.sh`, into a new database, in one transaction;
2. creates the runtime role: LOGIN, no superuser, no CREATEDB or CREATEROLE. The password is read by psql from a file, never from the command line;
3. revokes everything on the database from PUBLIC, and grants the role CONNECT and TEMP on this database, USAGE on its schemas, DML on its tables, sequence use, and EXECUTE on its functions.

The target must be a disposable cluster unless `--admin-install` is given, which also needs `AGOH_ADMIN_INSTALL_CONFIRM=<database>`. `engine-db.sh check-isolation <db> <own role> <other role>` proves the boundary.

## 7. Tests and results

| Suite | Command | Result |
| --- | --- | --- |
| Cancellation evidence | `dev/sql/tools/with-ephemeral-pg.sh dev/sql/tests/concurrency/cancel_midflight.sh` | 35 passed, 0 failed |
| Module Kernel + Functional tests (with the rest of the site), phpcs, config determinism, HTTP smoke | `dev/sql/tools/with-ephemeral-pg.sh modern/scripts/install-disposable.sh` | OK, 36 tests and 296 assertions for the whole site; 28 of the tests are this module's: 25 Kernel cases (with the data provider) and 3 Functional tests. Every check passed. |
| Worker integration | `dev/sql/tools/with-ephemeral-pg.sh modern/scripts/engine-integration.sh` | 39 passed, 0 failed (about 50 s) |
| Engine regression suite, before and after | `dev/sql/tools/with-ephemeral-pg.sh dev/sql/tests/run.sh` | 26 pass, 10 xfail, 0 fail (unchanged) |
| HTTP E2E | `dev/sql/tools/with-ephemeral-pg.sh dev/e2e/run.sh` | 28/28, digest `4199818b55d4f1c44f2cba24f0367e29` (unchanged) |

What the integration test covers:

- **Success** on the E2E scope: the state is succeeded and the job is the final timetable. The metrics are demands 8, placed 8, unplaced 0, collisions 0 and overflow 0, equal to `tst.metrics`. The digest equals `dev/e2e/expected-placements.md5`.
- **Soft cancel mid-flight:** cancelled, kept = partial. 3 ≤ placed < 8, committed. The run returns within 5 s of the request, and the row is consumed.
- **Retry:** succeeded with the E2E digest; `replaced` equals the partial count.
- **One running job per site:** a second worker claims nothing.
- **Queued cancel:** the job never runs.
- **Hard cancel:** cancelled, kept = none, 0 placements.
- **Two timeouts:** timed_out. One leaves the previous final timetable untouched; the other, on an empty scope, keeps 0 placements.
- **Stale worker:** the worker is killed with `kill -9` while its backend is still running. Recovery terminates the backend and rolls it back, and requeues the job. Attempt 2 succeeds with the E2E digest. The next stale job fails after its last attempt.
- **Unknown scope:** failed.
- **Two sites:** the role of site B is refused by site A's engine database ("permission denied for database"), and the reverse. A worker configured with B's role on A's database fails, without its password in the error.
- **Option validation:** done from the CLI and the form.

The Kernel tests (`JobRepositoryTest`, `JobValidationTest`) cover the state machine, ordered and exclusive claims, the partial unique index, cancellation precedence over success, queued and hard cancellation, fencing, stale recovery, option, scope and settings validation. The Functional test (`JobPagesTest`) covers the permissions of every route, that queueing never runs the engine, validation errors and cancellation access.

## 8. Limits and open risks

- **Cancellation latency** is one cooperative step. A single long step, such as a big Prioriza computation, delays the soft stop, and the hard stops bound it: `cancel_grace`, then `pg_cancel_backend`, and `statement_timeout`.
- **Synchronization from Drupal entities** is not part of this slice. Today the scope's data must already be in the engine database; the tests load it with SQL (`dev/e2e/fixture.sql`). The integration point is a sync step that takes the output of the academic module (#70, PR #85): `AcademicExport::export(Plan, ?Period)` (schema `ancora_academic.export.v1`) and `LegacyRows::build($export, $creator, $prefix)`, which yields rows for the 17 legacy engine tables in FK order. That step is NOT duplicated here. It belongs before `generar()`, in the same engine transaction as the run, so that a rolled-back run also rolls back its input. It must reuse the `desglose` rows that the legacy triggers create instead of inserting the exported ones, and it must record the export's version or hash in the job (`result` or a new column) as the versioned input snapshot. Remaining work.
- **The worker runs one site** per process, with that site's settings (one Drush invocation per site, `--uri`). The scheduler across sites (systemd template units, global concurrency, daily quotas of #55) belongs to provisioning (#72) and capacity (#76).
- **Replace semantics** changes the legacy "continue from existing placements" behaviour at the orchestration level only. A future "keep placements" mode would need its own outcome rules.
- The engine role gets DML on every table of its engine database, including the legacy `seguridad` schema. Narrower grants need a per-table review of what `generar()` touches.
- `client_connection_check_interval` needs PostgreSQL 14 or later; the targets are 16 and 18.

## LBR impact

**0 bytes retired.** This change adds the worker and its tests and replaces no legacy responsibility yet. The legacy files are not moved or modified (#68). The only addition outside `modern/` is test and fixture code under `dev/` and a workflow.
