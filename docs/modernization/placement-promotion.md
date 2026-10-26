# Placement promotion, continuation and recovery (#95)

Issue: #95, under #91 and #66; characterization in PR #102 (part a). This builds on [single-db-workspace.md](single-db-workspace.md) (#93) and [engine-dependency-closure.md](engine-dependency-closure.md) (#92).

The loop now works end to end in the **workspace mode**:

1. The input is read from Drupal's academic entities.
2. It is loaded, with the stored placements, into a job-scoped workspace of the site database.
3. The unchanged MPI/Prioriza runs there.
4. The new placements are published as Drupal entities.
5. The next request reads them back and continues as legacy does.

The legacy engine SQL is unchanged and no academic or scheduling rule was added. **0 LBR bytes are claimed.**

## 1. Legacy semantics characterized first (PR #102)

| Evidence | Legacy behaviour the loop must keep |
| --- | --- |
| cases 061, 128, 134 | Continuation (D7, PAR-085): a run keeps every existing placement and places only the missing (template, group) demands. Existing placements anchor the order range of their breakdown. A second run with other options adds nothing. |
| case 132 | Cancellation (D12, PAR-086): a request arriving mid-run keeps what was placed (committed). The next run continues from it with the same ids and cells, and the continued timetable differs from one uncancelled run. A request for another scope has no effect. |
| case 133 | D12 quirk: a request left over after its run stops the next run of that scope at its first check. That run places nothing and consumes the row. |
| cases 063, 128 | Partial runs (D8, PAR-087): unplaced demands are only a NOTICE. What was placed is kept and continued. |
| case 134, s05 | D11: the legacy window posts `compactar = 1` and `buscalugar = true` by default. Two specialties of one plan share a teacher in one period, and a repeated request adds nothing. |

## 2. Entities

No existing entity fits a scheduling result:

- the academic entities are the input;
- `TimetableReader` read the placements from the engine database of the two-database mode;
- no placement or resource-usage entity existed.

One entity type is added, **`ancora_placement`** (`ancora_scheduling/src/Entity/Placement.php`), mapping legacy `actividad`:

| Field | Meaning |
| --- | --- |
| `plan`, `period`, `template`, `student_group`, `teacher`, `room` | References. `teacher` and `room` are empty only when the template requires none. |
| `day`, `slot` | Day of the period and first slot. |
| `placed_at` | The engine value `momento_de_colocacion`. |
| `engine_id` | The id the engine gave the row. Unique at database level. It is the identity of the placement when it is loaded back into a workspace. |
| `job` | The job whose run created it. |

Other properties:

- **Uniqueness:** (`template`, `student_group`) is unique at database level, as `UNIQUE (plantilla, grupo)` is in legacy (`PlacementStorageSchema`).
- **Owner and access:** the owner is the plan's creator (`getCreatorId()`), the same user as legacy `actividad.creador` (the subject's creator, PAR-008). Access uses the academic `creador` handler.
- **Deletes cascade:** deleting a plan, period, template, group, teacher or room deletes its placements (`PlacementCascade`), as the legacy foreign keys do (PAR-101..103). Placements never block a delete.

No resource-usage entity is added. Other resources do not exist in Drupal yet (D9), so `otros_recursos_usados` is always empty in the workspace. The publisher has no row type for it. When other resources are added, a usage entity keyed like `engine_id` is the matching step.

Existing sites get the table through `ancora_scheduling_update_11001()`, which creates the empty table only. The two-database mode does not use it.

## 3. Loading existing placements (`WorkspaceSync`)

1. **One snapshot.** In one REPEATABLE READ transaction of Drupal's connection, the worker reads the site's legacy rows (`SiteRows::site()`, the same code EngineSync uses) and the stored placements (`SiteRows::placements()`). The snapshot is identical at the start and the end of the read.
   - Placements are read in entity id order. That is the order the publisher created them in, which is the workspace's insertion order (`ORDER BY ctid`): legacy entry order.
   - Each row id is the placement's `engine_id`.
   - The transaction is not READ ONLY, because loading entities writes Drupal's entity cache.
2. **Load in the workspace.** In one workspace transaction, the job role:
   - writes the rows with the unchanged `LegacyTableSync`;
   - inserts the stored placements with their engine ids. The legacy RULEs rebuild their occupancy and real-date rows, which #93 proved equal.
   - keeps the #92 closure (`scheduler_control.closure_keep`, `prune`) and commits;
   - runs `ANALYZE`.
3. **Nothing is deleted or recomputed** (no D7). A placement whose template, group, teacher or room is gone cannot be loaded: its reference would be NULL. The input then fails and nothing runs. The cascade above keeps that from happening silently.
4. **Closure before loading: not done.** The worker still loads the whole site and prunes, as in #93. The legacy breakdown triggers create a breakdown for every subject and period of a creator while the rows are written, so rows outside the closure appear in the workspace anyway. A closure computed in PHP would also be a second implementation of the #92 rules. There is one implementation, in `dev/workspace/workspace.sql`.

## 4. Publication (`PlacementPublisher`, Entity API)

The publication runs in one transaction of Drupal's connection, serialized per site by an advisory lock. The account is switched to the plan's creator, so the Entity API reference validation applies that user's access. The steps:

1. **Fence.** A heartbeat in the same transaction must find the job running in this attempt. Otherwise the reason is `fenced`.
2. **Stale input.** The version of the site rows and stored placements must equal the snapshot's. Otherwise the reason is `stale`.
3. **Each new placement**, in run order:
   - an existing record with the same `engine_id` is a replay and is skipped;
   - otherwise the engine ids are mapped to entities (the numbered part is the entity id) and checked;
   - then it is created and validated with `validate()` and `save()`.

The checks: the template belongs to the job's period; the group, teacher and room exist and belong to the plan's creator (D1–D3); the row's creator is that user; the day and the first slot are inside the period; a required teacher and room are present; the (template, group) pair is not already placed. Any failure (`invalid`) or a save error (`failed`) rolls everything back.

Collisions and overflow are not refused. Legacy keeps what `generar()` placed, so the workspace mode does not apply the two-database mode's invariant rollback (D12, proposal P-3).

## 5. States: execution, partial (draft), final

| Concept | Where it lives |
| --- | --- |
| Job execution | `ancora_engine_job.state`: queued, running, succeeded, partial, cancelled, timed_out, failed. Unchanged. |
| Draft timetable | The placements of a scope whose latest placement-changing job is `partial`, or `cancelled` with kept `partial` (`JobRepository::changedPlacements`). Shown as a draft. |
| Final timetable | The same, when that job `succeeded` (every demand placed). |

As in legacy, what was placed is the timetable: a draft is stored and continued like any other placements. `TimetableReader` reads placement entities in workspace mode and never shows the placements of a job that is still running.

| Case | Kept | Discarded | Retry | Evidence |
| --- | --- | --- | --- | --- |
| Soft cancel mid-run | what was placed, published (draft) | — | the next request continues from it | journey 3 (3 of 8, then +5 = the legacy oracle) |
| Hard cancel / timeout | nothing | the run | request again | journey 4 |
| Partial run, then continuation | the placed demands (draft) | — | the next request places the rest | case 132; parity s05; journey 3 |
| Worker killed before persisting | nothing (the run's backend is terminated, its workspace dropped) | the attempt | automatic: the job is requeued and the next attempt publishes once | journey 7 |
| Failure halfway through persistence | nothing (the Drupal transaction rolls back after 2 saves) | the run's output | request again | journey 5; kernel `testFailureHalfwayKeepsNothing` |
| Same job processed twice | the first publication | duplicates: the second attempt loads the 8 and publishes 0; `engine_id` is unique | — | journey 8; kernel `testPublishAndReplay` |
| Data changed between snapshot and publication | nothing | the run's output (stale) | request again (proposal P-1: automatic) | journey 6; kernel `testStaleInputIsRefused` |
| Two jobs at once | — | — | the queue runs one job per site (`JobRepository::claim`); a second worker claims nothing | journey 9 |

## 6. Transaction, retry and recovery model

| Step | Transaction | On failure |
| --- | --- | --- |
| T0 claim | Drupal: claim under an advisory lock, one running job per site | — |
| T1 workspace | control connection: `open_job_workspace` creates a NOLOGIN job role (the worker is a member WITH INHERIT FALSE) and the schema | job failed; the next cycle's `worker_cleanup()` drops leftovers |
| T2 snapshot | Drupal: one REPEATABLE READ read | job failed, nothing ran |
| T3 load | job connection (`SET ROLE`, workspace `search_path`): sync, stored placements, closure, prune, COMMIT | job failed, nothing ran |
| T4 run | `generar()` as one autocommit statement (READ COMMITTED, `statement_timeout`) | rolled back inside the workspace; nothing published |
| T5 publish | Drupal: one transaction, advisory lock, fence, stale check, Entity API saves | rolled back; job failed with the reason; nothing published |
| T6 finish | Drupal: the job outcome (`finish()`); a fenced attempt's outcome is discarded | — |
| T7 close | control connection: `close_job_workspace` drops the schema and the role, or keeps them until `ANCORA_ENGINE_WORKSPACE_RETENTION` seconds | `worker_cleanup()` drops it later |

Recovery runs at the start of every worker cycle:

- stale jobs are found by heartbeat; their backend is terminated (application name) and the job is requeued, or failed after the last attempt;
- the workspaces of attempts without a backend are dropped together with their roles.

A crash between T5 and T6 leaves the placements published and the job `running`. The job is requeued, and the next attempt loads those placements and publishes nothing new. This is tested as journey 8, by forcing the job back to running.

T5 is not atomic with T4. The workspace commit and the Drupal commit are separate transactions on separate connections. The order (T4, then T5, then T6) and the unique `engine_id` make a retry safe. **No cross-connection atomicity is claimed.**

## 7. Worker integration and modes

`EngineSettings::mode()` selects the mode, from `ANCORA_ENGINE_MODE` or `$settings['ancora_engine']['mode']`:

- **`two_database`** (default): the deployed release. Unchanged: EngineSync, the delete-before-run (D7), the invariant rollback. `engine-integration.sh` (39/39), `r1-journey.sh` and `multisite-verify.sh` are untouched.
- **`workspace`**: the engine connection settings name the site's own database and its engine worker role.
  - The engine is installed with `dev/workspace/install.sh --worker <role> <site database>`. That adds `open_job_workspace`, `mark_workspace`, `close_job_workspace` and `worker_cleanup` (SECURITY DEFINER, owned by the installer) and the worker's grants.
  - New disposable installs use this mode: `install-disposable.sh`, `workspace-journey.sh`, and `parity-diff.sh --workspace` in CI.
  - No tenant is migrated (#96).

## 8. Differential verification

`parity-diff.sh --workspace` now also queues every scenario request again, runs it through the **real worker** in workspace mode, and reads the placement **entities** back, placement by placement. Each run is compared with the legacy oracle: the full legacy database, measured by a rolled-back rerun.

| Scenario | Runs | Entities = legacy oracle | Notes |
| --- | --- | --- | --- |
| s01 E2E | 1 | 8 of 8 | E2E digest `4199818b…`; 8 entities, none duplicated |
| s02 two creators | 2 | equal per run | D1–D3: each creator's job holds only its own rows |
| s03 one creator, two plans | 2 | equal per run | shared teacher and room across plans; run 2 loads run 1's placements |
| s04 entry order | 1 | 8 of 8 | E2E digest `4199818b…` |
| s05 shared teacher, legacy options, repeated request | 3 | 1, 1, 1 | run E2 loads 2 stored placements, publishes 0 (continuation) |

`workspace-journey.sh` (37 checks) covers the rest against the real worker:

- the E2E digest from entities, with no workspace or role left after the run;
- continuation;
- soft cancel, then continue, equal to a legacy oracle that cancels after the same number of placements;
- hard cancel, timeout, publication failure halfway, stale input;
- the worker killed mid-run, the same job processed twice, one running job per site;
- tenant and privilege boundaries.

Requirement 6 of #95, item by item:

| # | Requirement | Covered by |
| --- | --- | --- |
| 1 | E2E digest from entities | parity s01/s04, journey 1 |
| 2 | Every placement represented | parity, placement by placement |
| 3 | Placements survive regeneration | journey 2, parity s05 |
| 4 | Partial then continuation | journey 3, case 132 |
| 5 | Two creators, multiple plans | parity s02, s03 |
| 6 | Shared resources | parity s03, s05 |
| 7 | Replay | journey 8, kernel test |
| 8 | Cancel, timeout, failure | journey 3–7 |
| 9 | Tenant and ownership | journey 10, kernel ownership test |
| 10 | Continue from persisted placements | journey 2–3, parity s05 |

## 9. Differences from legacy that remain

| Difference | Reason |
| --- | --- |
| The leftover cancellation row of D12 cannot stop a later job | Requests are per job role (isolation, #93). |
| A result whose input changed meanwhile is not published (stale) | Required by #95. Legacy works on the live database, so it has no counterpart. |
| Placements become visible when the job's outcome is recorded, not one by one | Required by #95: no timetable before persistence. |
| One running job per site | Existing queue rule. |
| A hard cancellation or timeout keeps nothing | As before; legacy has no hard cancellation. |
| The two-database mode keeps D7 (delete before run) and the invariant rollback | Unchanged for the deployed release. The workspace mode follows legacy. |
| Q-20/A-1 input deviations (`hora_inicial`, `duracion_del_turno`) | Still change the hours of real-date rows against the HTTP oracle; reported KNOWN. Placements are equal. |

### Historical quirks kept (legacy behaviour reproduced)

- **D7 continuation:** nothing is deleted, existing placements anchor their breakdown.
- **Partial runs are kept:** D8.
- **Soft cancel keeps placed work:** D12.
- **Collisions and overflow are kept:** 6.17.
- **Groups of other plans are scheduled:** Q-02.
- **The real-date branch is inert:** Q-01, #92.

### Security and isolation corrections (not reproduced from legacy)

- Cancellation requests are per job; a leftover request cannot affect another job.
- The publication validates ownership (D1–D3) and the tenant boundary. Legacy handlers check no owner (Q-12).
- The worker role has no privilege on Drupal tables or on the engine templates. Placements are written only through the Entity API.
- Job roles are per database and per attempt.

## 10. Security limitations and unresolved concurrency cases

- **SECURITY DEFINER provisioning.** The provisioning functions are owned by the installer, a superuser in the disposable setups; production needs a dedicated CREATEROLE owner. The worker is trusted to `SET ROLE` only to the job roles it opened: it is a member of each.
- **Cancellation rows after close.** A soft cancellation that arrives after the workspace closed leaves an inert `cancel_request` row. It is never consumed and is not cleaned up.
- **One job at a time is what is tested.** Concurrency safety rests on one running job per site, plus the stale check. Two workers on two different sites of one cluster are independent, since roles and functions are per database. Overlapping jobs of one site are not run concurrently, so their interleavings are not tested beyond the queue rule.
- **The stale check is site-wide and conservative.** Any change to any record or placement of the site invalidates a running job's result.
- **Entity cache writes during the snapshot.** The REPEATABLE READ snapshot writes Drupal's entity cache, so a concurrent cache write could, in principle, make the snapshot fail with a serialization error. This was not observed.

## 11. Open product choices (proposals, not decided)

- **P-1:** requeue a job automatically when its result is stale, instead of failing it.
- **P-2:** require an explicit acceptance before a draft (partial or cancelled) becomes the timetable. Legacy shows what was placed.
- **P-3:** roll back runs with collisions or overflow in the workspace mode, as the two-database mode does (D12).
- **P-4:** a retention period for closed workspaces in production (default 0, dropped).
