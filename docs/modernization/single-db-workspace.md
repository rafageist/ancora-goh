# Single-database, job-scoped SQL workspace: disposable proof (#93)

Issue: #93, under #91 and #66. This document builds on [engine-dependency-closure.md](engine-dependency-closure.md) (#92). It covers a prototype and its tests, in disposable clusters only. The production path is unchanged: `EngineSync`, the worker, the deployed two-database flow and PR #89 stay the reference. No legacy file is changed, nothing is renamed, and **0 LBR bytes are claimed**.

## 1. Result

**The original algorithm runs inside the Drupal tenant database without a behaviour change, in a job-scoped physical schema.** The engine functions are byte-identical to `dev/sql/install.sh`: 196 functions compared by name, arguments and source. The RULEs, triggers, foreign keys, views and inheritance links have the legacy definitions, regenerated from the catalog so that they bind to the job's tables.

Every scenario gives the same placements, inserted rows (normalized), per-table DML counters and metrics as its legacy oracle:

- the eight-activity E2E organization, digest `4199818b55d4f1c44f2cba24f0367e29`;
- two creators;
- one creator with two plans;
- a teacher shared by two specialties in one period;
- a partial run followed by a continuation in a new job;
- the entry-order scenario.

Two changes to the database layout are needed. Neither touches the algorithm:

1. the engine is installed into schema `scheduler` instead of `public`, where Drupal's tables are;
2. the shared table `generador.cancelar` becomes a per-job updatable view.

**Assessment: proceed with the job-scoped schema model** (section 9). TEMP tables are not a viable primary model (section 6).

## 2. How to run it

```text
dev/sql/tools/with-ephemeral-pg.sh modern/scripts/workspace-proof.sh
dev/sql/tools/with-ephemeral-pg.sh modern/scripts/parity-diff.sh --skip-composer --workspace [scenario...]
```

| Path (the SQL sources are under `dev/`: the production build refuses `.sql` files under `modern/`) | Role |
| --- | --- |
| `dev/workspace/install.sh` | Installs the unchanged legacy bundle (`dev/sql/compilasql.php`, 268 entries) into a tenant database with `search_path = scheduler`, then `workspace.sql` and `measure.sql`. |
| `dev/workspace/workspace.sql` | Control schema `scheduler_control`; per-job cancellation view; workspace generator `create_workspace`; the #92 closure (`closure_keep`, `prune`); lifecycle (`drop_workspace`, `cleanup`); the placement store; privileges. |
| `dev/workspace/measure.sql` | `wsproof.run()`: one measured `generar()` that returns the placements, normalized inserted rows, DML counters and metrics. It resolves through the `search_path`, so it measures a full legacy database and a workspace with the same code. Test code. |
| `modern/scripts/workspace-proof.sh` | SQL-level proof: resolution model, E2E and #92 world scenarios against full legacy databases, concurrency, cancellation, timeout, killed backend, cleanup, privileges, TEMP alternative, measurements. **91 checks.** |
| `modern/scripts/lib/parity/workspace-driver.php`, `workspace-oracle.php`, `workspace-compare.php` | The `--workspace` mode of the differential harness: the Drupal-sourced path (section 5). |

CI runs the proof in the PostgreSQL characterization workflow (PostgreSQL 16). It runs `parity-diff.sh --workspace` in the parity job (PHP 8.4 + PostgreSQL 16, PHP 8.5 + PostgreSQL 18). The existing comparisons of the harness are unchanged and run first.

## 3. The model

```text
tenant database (one per Drupal site)
├── public                     Drupal tables (owner: the site role). No job role has any privilege.
├── scheduler                  the legacy engine: 157 functions, types, and the 54 legacy tables
│                              as EMPTY shape templates (no job role has any privilege on them)
├── matrix, prioriza, tiempo,  fixed-name legacy schemas, unchanged (pure functions, and
│   seguridad, generador,      seguridad.usuario: the tenant's engine users)
│   portal
├── scheduler_control          cancel_request, workspace registry, placement store, generator
└── scheduler_job_<n>          one per job, owned by the job's login role:
                               54 tables, 2 views, 26 RULEs, 12 triggers, 134 FKs, 12 inheritance links
```

### 3.1 How unqualified names reach the job's tables

The job role connects with `search_path = scheduler_job_<n>, scheduler, pg_temp` (set in the connection options).

- **Relations** resolve to the workspace first. The template tables in `scheduler` come second, and the job role has no privilege on them. A name that fell through would fail with 42501 instead of reading shared data. The proof shows the guard is armed: reading a template table as the job role fails with 42501. A full run writes nothing to the template tables (their DML counters stay 0).
- **Functions** resolve to `scheduler`: the workspace contains no function, type or sequence (checked).
- **Safety of the path:**
  - PUBLIC has CREATE on no schema in the path, nor on `public`;
  - the workspace is owned by the job role, and only the job role (and superusers) can create in it;
  - the job role cannot create in `scheduler` or `public`;
  - `pg_temp` is listed **last**, so a TEMP object can never shadow an engine relation or function.
- No function has `SET search_path` (#92), so the engine always uses the caller's path.

### 3.2 How views, RULEs, triggers and foreign keys bind to the workspace

`scheduler_control.create_workspace(job, schema, role)` works in two steps.

1. It creates every table with `LIKE scheduler.<t> INCLUDING DEFAULTS INCLUDING CONSTRAINTS INCLUDING INDEXES` and restores the inheritance links.
2. It prints the FKs, views, RULEs and triggers of the template with `search_path = scheduler`. The printed names are unqualified, except `seguridad.usuario` (`pg_get_constraintdef`, `pg_get_viewdef`, `pg_get_ruledef` and `pg_get_triggerdef`, all in pretty mode). It then executes the same text with the workspace first in the path.

The objects therefore bind by OID to the workspace tables. Their function calls bind to the unchanged functions in `scheduler`. Checked on the catalog:

- no view or RULE of the workspace depends on a table outside it;
- every RULE and trigger function is in `scheduler`;
- every FK targets a workspace table or `seguridad.usuario`.

The two OID-bound views (`asignaturas_cant_act`, read by `max_cant_act`, and `uso_de_otros_recursos`) exist per workspace. Without that, `max_cant_act` reads the template tables (section 6).

### 3.3 How `generador.cancelar` is isolated per job

`generar()` calls `generador.fue_cancelado`, schema-qualified, 51 times, and that function reads and deletes the schema-qualified `generador.cancelar`. Neither can be shadowed.

The table is replaced by an auto-updatable view:

```sql
CREATE VIEW generador.cancelar AS
  SELECT especialidad, nivel, periodo FROM scheduler_control.cancel_request WHERE job_role = current_user
  WITH LOCAL CHECK OPTION;
```

`generador.fue_cancelado` is unchanged: it still selects and deletes the scope's row, but it only sees its own job's rows. Measured effects:

- two jobs of the **same** scope never see each other's request;
- a canceller (the orchestrator, on another connection) inserts `(job_role, especialidad, nivel, periodo)` into `scheduler_control.cancel_request`;
- dropping a workspace removes its job's requests.

For contrast, the legacy table is keyed by scope only: another run of the same scope consumes the request (verified in the proof).

### 3.4 Engine users

`creador` references `seguridad.usuario` from `actividad`, `otros_recursos_usados`, `planificacion` and nine catalogue tables. The engine users are tenant-level (`u<uid>`, no personal data), and `LegacyTableSync::ensureCreator` writes them schema-qualified. All workspaces therefore reference the one tenant table. Job roles may SELECT, INSERT and REFERENCES; they may not UPDATE or DELETE.

This is the only shared row set a job writes, and the rows are the same for every job. The `seguridad.usuario` defaults and the `tiempo` functions call unqualified engine functions, for example `tiempo.date_to_number` calls `bool_to_int`. A session without `scheduler` in its path therefore cannot insert a user (42883, found while building the proof).

## 4. Input, run and output of one job

1. **Workspace.** The orchestrator, a provisioning role (the cluster superuser in the proof), creates the job's login role and the workspace (about 200–370 ms).
2. **Snapshot.**
   - The proof reads the site's legacy rows (`AcademicExport`, `LegacyRows`, the same code as `EngineSync::siteRows()`) inside **one** Drupal-connection transaction, `REPEATABLE READ READ ONLY`.
   - `pg_current_snapshot()` is identical at the start and the end of the read.
3. **Load.**
   - The job role writes those rows into its workspace with the unchanged `LegacyTableSync`, with the legacy triggers enabled and bound to the workspace.
   - It adds the placements kept by earlier jobs of the tenant (`scheduler_control.placement_store`, a stand-in for the #95 promotion), in their original order. Their occupancy rows are rebuilt by the legacy RULEs.
   - It then keeps exactly the #92 closure of the request: `closure_keep` (the rules of `tst.cl_keep`) and `prune`. The job role owns the tables, so it disables their user triggers while pruning, which keeps the order and priority renumbering away from kept rows.
   - Last, it runs `ANALYZE`.
   - The prototype loads the whole site and then prunes. Computing the closure before loading is left to the production design.
4. **Run.** `generar()` runs as one autocommit statement in READ COMMITTED, the worker's model, measured by `wsproof.run`.
5. **Output.** The job's new placements are appended to the store for later jobs.

The workspace never writes Drupal tables: no job role has any privilege on them (checked per job), and the checksums of the 20 canonical tables (`ancora_*` and `users*`) are equal before and after.

## 5. Differential results

### 5.1 Drupal-sourced: `parity-diff.sh --workspace`

Each run is compared with two oracles, both measured by a rolled-back rerun on a full database (`workspace-oracle.php`):

- **engine oracle**: the full two-database engine database, synced from the same Drupal data;
- **legacy oracle**: the legacy application's database, with the records entered over HTTP.

For the legacy oracle, the rerun is first shown to reproduce the HTTP runs' placements.

| Scenario | Jobs | Snapshot rows → closure | = engine oracle | = legacy oracle | Result |
| --- | --- | --- | --- | --- | --- |
| s01 E2E organization | 1 | 44 → 44 | placements (8), inserted (80), DML, metrics | placements, DML, metrics; inserted KNOWN | **15 PASS, 1 KNOWN**, digest `4199818b…` |
| s02 two creators | 2 | 50 → 22 / 28 | all equal | inserted KNOWN | **26 PASS, 2 KNOWN** |
| s03 one creator, two plans (shared teacher and room; run 2 receives run 1's placements) | 2 | 38 → 26 / 35 | all equal | inserted KNOWN | **25 PASS, 2 KNOWN** |
| s04 entry order | 1 | 44 → 44 | all equal | inserted KNOWN | **15 PASS, 1 KNOWN**, digest `4199818b…` |

The KNOWN rows are expected. The inserted rows differ from the legacy oracle only in the hours of the real-date rows. These follow from the registered input deviation of `periodo.hora_inicial` and `duracion_del_turno` (Q-20, A-1), which the existing comparison already reports KNOWN. Against the engine oracle, which has the same inputs, the inserted rows are identical. Any other difference would FAIL.

### 5.2 SQL-level: `workspace-proof.sh`

Each job runs in its own workspace with the #92 closure. Each is compared with a full legacy database (`dev/sql/install.sh --with-data`) running the same sequence. Placements, inserted rows, DML counters and metrics are all equal in every row of the table.

| Scenario | Jobs (input rows → closure) | Result |
| --- | --- | --- |
| E2E organization | 1 (fixture) | 8/8, digest `4199818b…`, 80 inserted rows, DML equal |
| #92 world, other creator (EB, NB, QB) | 91 → 17 | equal |
| #92 world, same specialty in plan 2 (E, N, QER) | 101 → 38 | equal |
| #92 world, other specialty sharing teacher TA (F, N, PER) | 144 → 17 | equal |
| #92 world, the scope under test (E, N, PER), after the three jobs above | 154 → 63 | equal. Its closure holds job F's placement (teacher TA) and the plan-2 placements (room usage, `buscalugar = false`). It holds no row of the other creator, and the other creator's job holds none of Alice |
| the same, with the legacy window's options (compactar 1, buscalugar true) | 154 → 63 | equal |
| partial run (cohort B2 restricted everywhere), then continuation in a new job | 84 → 64, 84 → 60 | equal; 4/8, then 8/8. The second job kept the first job's 4 placements with the same ids and cells. The continued timetable equals the oracle's |

## 6. The TEMP alternative: where it breaks and what it costs

Measured with a job role in one session:

| Point | Measured |
| --- | --- |
| Copying the shapes | TEMP tables can only be created by the job's own session. `LIKE scheduler.<t>` needs SELECT on the templates, which the workspace model never grants (42501 without it). |
| The safe `search_path` (`pg_temp` last) | Unqualified names **silently read the shared template** instead of the TEMP table: 0 rows instead of 1. TEMP only works with `pg_temp` first, the unsafe order. |
| FK to `seguridad.usuario` | Refused: a temporary table may reference only temporary tables. A TEMP copy of the user table is needed. |
| OID-bound views | Not recreated: `max_cant_act` reads the **empty shared template** through `asignaturas_cant_act`, and `generar()` fails with 2202E. Recreated as TEMP views: works. |
| With all of the above (`pg_temp` first, TEMP user table, TEMP views, RULEs, triggers) | E2E: the same placements, inserted rows and DML as the oracle. |
| Visibility | Another connection, even a superuser, cannot read the job's output (0A000). The promotion step, which uses Drupal's own connection, diagnosis and recovery cannot see it, and it vanishes with the session. |
| Cost | DDL, input and run in one session: 3.3 s, against 0.3 s workspace creation + 0.1 s load + 2.0 s run for the schema model, on the same E2E input. |

## 7. Concurrency, cancellation, timeout, crash, cleanup and privileges

The proof adds a test-only slow-placement trigger (0.3 s per placement) to make runs overlap. It writes no table and does not change the DML counters.

| Case | Outcome |
| --- | --- |
| Two jobs at once, different scopes (E2E and the world's F scope) | Both active at the same time. Each equals its oracle: digest, inserted rows, DML. |
| Two jobs at once, the **same** scope (E, N, PER); job A cancelled from another connection after 3 placements | A returns normally and keeps its 3 placements, committed and without collision; its request is consumed. B never sees the request and places 8/8 with the E2E digest. Cancel latency 0.25–0.31 s. |
| `statement_timeout = 1s` | 57014; nothing kept. |
| Backend killed mid-run (`pg_terminate_backend`) | The client sees the connection end; nothing kept; the registry still says `running`. |
| Cleanup and recovery | `scheduler_control.cleanup()` marks jobs without a backend as `failed`, then drops failed and expired workspaces (it dropped exactly the timed-out and the killed job, 0.2 s). Finished and kept workspaces stay. A new job from the same input gives 8/8 and the E2E digest. |
| Least privilege | Job B is refused (42501) all of the following: <ul><li>reading, inserting, dropping or creating in job A's workspace;</li><li>reading `scheduler_control.cancel_request`;</li><li>reading or updating Drupal tables;</li><li>creating in `public` or `scheduler`.</li></ul> It does not see A's cancellation request; A sees its own. |
| Drupal tables | Checksum unchanged across all jobs; no job role holds any privilege on them. |

## 8. Lifecycle and measurements

**Lifecycle** (`scheduler_control.workspace.state`):

```text
created → loaded → running → ran → kept (keep_until) → dropped
                       └─────→ failed (no backend, or error) → dropped by cleanup()
```

- A workspace is dropped after its output is accepted: today it is copied to the store, later it is promoted (#95).
- It can instead be kept for diagnosis until `keep_until`. `cleanup()` drops it after that time and keeps it before (both verified).
- Dropping a workspace removes its schema and its job's cancellation requests.
- The job's login role is created per job and can be dropped with the workspace. Roles are cluster-wide, so the harness names them `agoh_<tenant db>_j<job>`. The group role is per tenant since #96: `<tenant db>_jobs`, and the worker's job roles are `<tenant db>_job_<job>_<attempt>` ([tenant-provisioning.md](tenant-provisioning.md)).

**Measurements** on one laptop, PostgreSQL 16.15, synthetic data. No speed claim beyond these numbers.

| Quantity | Two-database layout | Single database (job workspace) |
| --- | --- | --- |
| Setup | one engine database per site: `dev/sql/install.sh` 0.5–0.7 s | engine install into the tenant once: 0.8–1.1 s; then 0.2–0.37 s per workspace |
| Rows written per job | the sync writes the whole site (44–50 rows in s01–s04) | the whole site is loaded, then pruned to the closure: 44 → 44, 50 → 22/28, 38 → 26/35 (s01–s04); 91–154 → 17–63 (#92 world) |
| Per-job overhead | — | snapshot 44–139 ms, input + closure + prune 0.2–0.3 s, `ANALYZE` 0.06 s |
| Size | engine database about 11.05 MB (s01–s04), most of it PostgreSQL's own catalog | empty workspace tables 1.0 MB, 1.5 MB after a job, 124 relations per workspace |
| `generar()` on E2E | 2.6 s (oracle) | 1.5–2.0 s |

## 9. Proceed / no-go

**Proceed with the job-scoped physical schema**, on these conditions:

1. **Engine code schema.** Install the legacy engine into `scheduler` (or its English successor). Keep the template tables empty and without privileges for job roles: this is the fall-through guard.
2. **Cancellation.** Replace `generador.cancelar` with the per-job view over `scheduler_control.cancel_request`, and point the worker's soft cancel at it (keyed by job role).
3. **Engine users.** Keep `seguridad.usuario` as the tenant-level engine user table: SELECT, INSERT and REFERENCES only.
4. **Closure before loading.** Compute the #92 closure from the Drupal snapshot before writing the workspace. The prototype loads everything and then prunes, which is correct but not minimal in I/O.
5. **Roles.** Provision roles per tenant and per job, owned by a non-superuser provisioning role. Run `cleanup()` from the worker loop. Done in #95 and #96 ([tenant-provisioning.md](tenant-provisioning.md)).
6. **Placements as input.** Promote placements through #95. The placement store is only a stand-in for "accepted placements as input".

TEMP tables: **no-go** as the primary model. They need `pg_temp` first in the path and a read grant on the templates, a TEMP copy of the user table and TEMP views. Their output is invisible to the promotion connection and lost with the session (section 6).

## 10. Compatibility changes for the next issues (not done here)

**#94 (English naming).**

- The workspace generator copies whatever the template schema holds. An English schema `scheduler` with English tables, views, RULEs and triggers needs no change to the generator. The legacy oracle stays the byte-identical legacy install.
- The fixed legacy schemas (`matrix`, `prioriza`, `tiempo`, `seguridad`, `generador`, `portal`) are global per tenant database. `tiempo.*` and the `seguridad.usuario` defaults call unqualified engine functions, so callers need the code schema in their path: rename them together with qualified calls.
- The names `generador.fue_cancelado`/`generador.cancelar` (schema-qualified in `generar()`), `seguridad.usuario` (schema-qualified in `LegacyTableSync::ensureCreator` and in 12 FKs), and the measurement and closure functions all use legacy names.
- ~~The role naming per tenant: `agoh_engine` is cluster-wide.~~ Done in #96: `<tenant db>_jobs`, owned and administered by the tenant's engine owner, without a superuser ([tenant-provisioning.md](tenant-provisioning.md)).

**#95 (promotion).**

- The provisional output is the workspace rows that were not input: `actividad` and `otros_recursos_usados`, plus their occupancy and real-date rows (`scheduler_control.store_output` identifies them).
- Promotion maps engine ids back to entities (`%010d:code`) and saves through the Entity API, idempotently per (job, placement id).
- Later jobs must then receive accepted placements from the Drupal snapshot, in their original order. That needs `LegacyRows`/`LegacyTableSync` to export and load placements, which today sync 17 tables and no placement. Rebuilding their occupancy rows through the legacy RULEs on load is proven equal (continuation scenarios).
- Partial and soft-cancelled outputs are committed in the workspace and can be promoted as legacy keeps them (D8, D12). The workspace path does not delete the scope's placements (no D7).

## 10b. Since #95: the worker's workspace mode

The prototype of this document now runs in the real worker: `EngineSettings::mode() = workspace`, `WorkspaceClient`, `WorkspaceSync` and `PlacementPublisher`. The output is published as Drupal placement entities, and the next request loads them back (legacy continuation). The model, the states and the recovery are in [placement-promotion.md](placement-promotion.md).

The changes to the layer of this document are these:

- **Worker provisioning.** `dev/workspace/install.sh --worker <role>` adds the SECURITY DEFINER functions `open_job_workspace`, `mark_workspace`, `close_job_workspace` and `worker_cleanup`, and grants the worker only those plus soft cancellation (`worker.sql`).
- **Job roles.** One NOLOGIN role per job attempt, named after the database and the attempt. The worker is a member WITH INHERIT FALSE and must `SET ROLE` to it.
- **Lost attempts.** `cleanup()` also finds an attempt's backend by its application name, and drops the job role with the workspace.

## 11. LBR impact

**0 bytes retired.** The prototype and the harness mode are test code. The legacy tree is not moved or renamed.
