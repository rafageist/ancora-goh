# Interactive timetable editing: characterization and design (#123)

Status: characterization and design (§1–§8). **The first slice is implemented (#130, §9)**; the later proposals (§6) are not. Parents: #74 and #70. Depends on #95 (placement persistence), #119 (explorer) and #128 (interaction conventions).

Sources:
- VB6: `rafageist/ancora-vb6` at `6c7038d`, inspected from a shallow clone; files are cited by path and line.
- PHP/SQL: this repository.
- SQL evidence: characterization cases 148–150 in `dev/sql/tests/cases/`, run on disposable clusters. The legacy SQL is unchanged.

## 1. Evidence matrix

Classification:
- **(a)** the 2010 PHP/PostgreSQL product did it: parity;
- **(b)** VB6-only capability, a reference and not parity;
- **(c)** net-new enhancement.

No candidate is (a). The PHP application has no wired manual-edit screen. The generated handlers `control/data/{add,set,del}/{add,set,del}_Actividad.php` are referenced by no template or script, and `bin/security.conf` gives them no permission, so only `todos` holders could post them. F32 in `functional-inventory.md` says the same.

| Interaction | VB6 (procedure, form) | PHP | PostgreSQL rules (cases) | F-row | Class |
|---|---|---|---|---|---|
| **Move one activity** | Engine primitive `TAncora.moveAsignacion` (`cls/ancora_goh_traditional_TAncora.cls:3049-3091`): frees the old slots in the teacher, room, brigade and resource matrices, sets `dia`/`turno`, marks the new slots. It validates nothing. The UI that calls it is **not in the repository**; nothing under `frm/`, `ctl/` or `bas/` calls it. | `set_Actividad.php` is an UPDATE of every column (`model/data/ActividadModel.php:82`). It is unwired. | UPDATE fires **no** rule: occupancy stays at the old cell, collisions and starts outside the period are accepted (**case 148**). DELETE + INSERT rebuilds the occupancy, and the unique keys refuse collisions and manual restrictions (**case 149**). | F32 | (b) primitive without a known UI; in Drupal (c) |
| **Candidate starts display** | `frmDatosActividad.frm` grid `tablaPosiblesInicios` (Enter/LeaveCell at lines 325 and 334). No code that fills it was found. `modDataGenerator.PosibleInicio` (`bas/ancora_goh_traditional_modDataGenerator.bas:1000-1064`) is the likely source, but that is not confirmed. It checks the day bounds, all restrictions over the whole duration, priority zones, free teachers, free and allowed rooms, and free resources. | none | `posible_inicio()` is **not usable**: it never reads a template's only teacher or room (6.2), so it accepted a cell where the teacher was busy (**case 150**). The `valor_restriccion_*` functions with the activity's occupancy excluded are a sound check (**case 150**). | F32, F34 ("common free slots": `DameHuecosComunes`) | (b) / (c) |
| **Swap (`permuta`)** | `TAncora.permuta` (`TAncora.cls:7339-7564`). It refuses a fixed activity and respects `RespetarOrden` day ranges. Its possible-start check is vacuous: `listaidbrg` is never assigned, so `AND_MPI` returns all true. It checks the teacher on the first slot only and never checks the room. It moves whole brigade groups. Trigger unknown. | none | A swap is two DELETE + INSERTs in one transaction. The unique keys still apply, so order matters: delete both, then insert both. | F32 | (b) |
| **Fix (`Fija`)** | Field `fija` of the assignment record (`insertAsignacionAct`, `TAncora.cls:858-877`), saved in the project file (`TAncora.cls:8746`, read back at 9316). `TAncora.Fijar` (`TAncora.cls:5536-5590`) **toggles** it for the whole activity across the group's brigades. The generator does not read it. | none | `actividad` has **no** fixed column (`dev/sql/actividad/actividad.sql`). | F32 | (b); needs a new persisted flag |
| **Undo generation keeping fixed (`degenerar`)** | `TAncora.degenerar` (`TAncora.cls:7261-7264`) calls `clearAsignacionesOfPeriod`, which deletes every non-fixed assignment of the specialty, level and period (`TAncora.cls:2073-2111`). No prompt. | none | DELETE cascades every occupancy and real-date row (cases 145, 148, 149). | F33 | (b); in Drupal it collides with D7 (§5) |
| **Close gaps (`QuitaHuecos`)** | `TAncora.QuitaHuecos` (`TAncora.cls:2542-2709`): for one brigade and period it pulls each non-fixed activity to the earliest (or latest) feasible slot of the **same day**, using `PosibleInicio`. It asks before changing teacher or room (MessageDlg at 2615, 2630-2653). It checks only the triggering brigade, not the others of the group, and not capacity. | none | — | F32 | (b) |
| **Manual add** | `insertAsignacionAct` (`TAncora.cls:858-877`) only appends a record; it does not even book occupancy. | `add_Actividad.php`, unwired | INSERT books the whole duration and refuses collisions and manual restrictions (23505). It does not check period, activity-type or subject restrictions, overflow, capacity or hygiene (**case 149**). `unique (plantilla, grupo)` holds. | F32 | (b) |
| **Manual delete** | `eliminaAsignacion` (`TAncora.cls:2028`), which does not check `fija`; `destruyeAsignacion(es)` (`TAncora.cls:3009`, `3021`) for the whole group | `del_Actividad.php`, unwired | DELETE cascades occupancy, real-date rows and resource use (cases 145, 148). The next `generar()` places the demand again (continuation). | F32 | (b) |
| **Analyses** | `AnalisisPrecentRestricc`, `DameHuecosComunes`, `goTurnosExtremos`, `AnalizaOptimo` | none | — | F34 | (b), out of scope here |

## 2. What the database enforces (cases 148–150)

- **Occupancy is written only on INSERT.** `actividad` has one RULE, `actualiza_restricciones_por_actividad` (`dev/sql/ancoragoh.sql:126-129`), and it is ON INSERT. It calls `restringe_segun_actividad`, which writes one nominal row per slot of the duration for the teacher, the room and every brigade of the group (`por_actividad = TRUE`, linked through `restriccion_por_actividad_de_*`). Real-date rows follow through the `crea_restriccion_real_de_*` rules. Other resources are booked by the ON INSERT rule of `otros_recursos_usados` (`ancoragoh.sql:131-134`).
- **DELETE frees everything.** The nominal rows, their real-date rows and the resource use are removed through `ON DELETE CASCADE`.
- **UPDATE of `dia`/`turno` corrupts occupancy** (case 148).
  - The old cell stays booked, as a ghost that blocks others.
  - The new cell is not booked.
  - Collisions and a start of 9 in a 3-slot period are accepted.
  - Continuation still counts the row as placed.
  - A move must therefore never be an UPDATE of the engine row.
- **DELETE + INSERT is a correct move** (case 149).
  - In one transaction it rebuilds the occupancy at the new cell, including the real-date rows, as long as the other resources are re-inserted.
  - The unique keys `(object, periodo, dia, turno)` of `restriccion_de_{profesor,lugar,brigada,otro_recurso}` refuse a collision with another activity, or with a **manual** restriction of the same object (23505). The whole move then rolls back.
- **The database trusts the caller on the rest** (case 149):
  - period, activity-type and subject restrictions;
  - inherited and conditional restrictions;
  - a duration that overflows the last slot (slot 4 of 3 is booked);
  - room capacity;
  - hygiene (daily load) limits;
  - the `respetar_orden` day ranges.
- **No cross-plan check.** The unique keys are per period, and the restriction functions read the period's own rows. A teacher shared with another plan's period on the same real dates can be double-booked, and neither the database nor the functions see it (case 150). This is the same view `generar()` has (s03: "the first plan's placements do not make them busy for the second").
- **Continuation (D7).** `generar()` keeps any row whose `(plantilla, grupo)` exists (`esta_colocada`, `generar.sql:1004-1012`), wherever it is. A moved activity is kept by the next run, and nothing re-places it.
- **No fixed flag.** `actividad` cannot record one.

## 3. Current Drupal architecture for edits

- **`ancora_placement`** (`ancora_scheduling/src/Entity/Placement.php`):
  - fields: period, template, group, teacher, room, day, first slot, other resources, `placed_at`, `engine_id`, `job`;
  - access through `AcademicAccessControlHandler` (owner via the plan), admin permission `administer academic data`.
  - It exists in **workspace mode** only. In two-database mode the placements live in the engine database (`TimetableReader`, `EngineClient::placements()`).
- **Publication** (`PlacementPublisher`, #95) runs in one Drupal transaction under advisory lock `2026095`, with:
  - a job fence (heartbeat);
  - a **stale-input check**: the site's rows and stored placements must still hash to the version taken at the snapshot (`SiteRows::version()`, `WorkspaceSync::currentVersion()`);
  - an engine-id replay check;
  - creator checks.
  - It never changes or deletes earlier placements.
- **Workspace input** (`WorkspaceSync`): the next job re-inserts every stored placement with its engine id, so the legacy rules rebuild their occupancy at the stored day and slot, and then its resources. An entity-level move is therefore booked correctly by the next run, and continuation keeps it.
- **Draft vs final.** A placement's state follows its job (`JobRepository::changedPlacements`): a partial or soft-cancelled run is a draft. Placements of a running job are not visible.
- **Two-database mode.**
  - `EngineSync` rewrites the engine rows, and **D7 still deletes the scope's placements before `generar()`** (`EngineClient.php:187-191`), so a move there would be lost at the next run.
  - Those placements have no entity, no Drupal access control and no audit.
- **Read models.** The explorer and viewer (`TimetableExplorer`, `TimetableReader`) read the entities in workspace mode. Entity saves invalidate their list cache tags.
- **Concurrency.**
  - One running job per site (workspace mode).
  - The stale-input version is **site-wide**, so any manual change to a placement while a job is queued or running makes that job's publication refuse as stale. A move must not race a job.

## 4. A reliable server-side validation primitive?

**Yes, and it reuses the engine.** Case 150 builds the destination check from the engine's own functions. The check is evaluated in the placement's **period**, inside a subtransaction where the placement's engine row has been deleted (its occupancy excluded), and then rolled back:

1. Bounds: `1 ≤ day ≤ cant_dias`, and `slot + duration − 1 ≤ cant_turnos`.
2. For every slot of the duration, all of these must read 0:
   - `valor_restriccion_periodo` / `_clasif_act` / `_asignatura`;
   - `valor_restriccion_brigada` for every brigade of the group;
   - `valor_restriccion_profesor` for its teacher and `valor_restriccion_lugar` for its room;
   - `valor_restriccion_otro_recurso` for each resource it uses.

   These read nominal, inherited and conditional restrictions as well as every other activity's occupancy.
3. The commit itself is DELETE + INSERT, so the unique keys are the last guard.

Case 150 shows that every destination this check accepts is accepted by the database. The check additionally refuses period restrictions and overflow. `posible_inicio()` must not be used for this: it accepts a busy teacher (6.2) and an overflowing start (6.17).

Advisory checks (warnings, not refusals), because `generar()` applies them when it places but the database does not:
- `es_higienico_profesor` / `es_higienico_brigada`;
- the `rango()` day range under `respetar_orden`.

Capacity, fixed and forbidden rooms do not change in a time-only move, because the room is kept.

**Where it runs.** The functions need the legacy tables of the placement's period, and in workspace mode those exist only inside a job-scoped workspace. The primitive is therefore a **check job**:
- the worker opens a workspace;
- the unchanged `WorkspaceSync` loads the site, without the prune, because a teacher may be shared by several scopes of the period;
- the destination grid is evaluated for one placement;
- the workspace is dropped.

Measured cost of the parts (`single-db-workspace.md` §8):
- 0.2–0.37 s to create the workspace;
- 0.2–0.3 s for the input;
- milliseconds for the check (the whole of case 150, 6 cells × 2 probes plus its fixture, runs in 0.3 s).

That is about **0.6–1 s per check**, plus queue latency. Web requests open no engine connection (`engine-worker.md`), so the check goes through the worker. Two alternatives were rejected:
- a PHP re-implementation over entities: it approximates the inherited, conditional and real-date logic, which the issue rules out;
- a workspace opened from the web request: this is a new privilege for the web role and needs approval.

## 5. Proposed first slice: move one saved placement

**Scope.**
- **Workspace mode only.** Placements are entities there, continuation keeps a moved one, and the worker can host the check. In two-database mode D7 would delete the move at the next run, and placements have no entity. The editor is hidden in that mode with an explanation.
- **Time-only move**: the day and first slot of one placement. Group, teacher, room and resources are kept.

**Commands and contract** (`ancora_scheduling`):

```text
PlacementModification::candidates(Placement $p, AccountInterface $by): CheckTicket
  - access: $p update access (the plan's owner, or "administer academic data");
    refused while a job of the SITE is queued or running
  - enqueues a check job {placement id, engine id, site version V}; returns its ticket
CheckResult (from the worker): {version V, cells: [{day, slot, ok, reasons[], warnings[]}]}
PlacementModification::preview(Placement $p, day, slot, CheckResult $r): Preview
  - the destination must be ok in $r; shows from -> to, the warnings, the affected
    read models (teacher, room, brigade views)
PlacementModification::commit(Placement $p, day, slot, string $version, AccountInterface $by): Result
  - one Drupal transaction under the publication's advisory lock (2026095):
    1. no site job queued or running (re-checked under the lock)
    2. WorkspaceSync::currentVersion() === $version (the check's snapshot), else "changed, check again"
    3. the placement still has the checked day/slot, plan, owner
    4. Entity API save of day/slot (validated as the plan's creator, as the publisher does)
    5. an audit record (below)
  - nothing else is written; any error rolls back everything
PlacementModification::undo(PlacementChange $c, ...): the same commit back to the previous cell,
  with a new check; refused if the cell is no longer valid
```

The engine row is never UPDATEd. The next job's input re-inserts the placement at its new cell, so the rules book it (cases 148 and 149).

**Atomicity.** The check is read-only and always rolled back. The commit is one Drupal transaction. Entity-level validation repeats the publisher's checks: inside the period, the first slot and the whole duration inside the period, the owner's records.

**Concurrency.**
- While any site job is queued or running, a move is refused. Queuing it was considered and rejected: a queued move would make the running job's publication stale.
- A version mismatch at commit means the data changed after the check, and the move is refused.
- The check job and the commit use the same advisory lock as publication.

**Security.**
- Owner: plan creator or `administer academic data`. The viewer permission never grants edits.
- Tenant: one site per database, and no cross-site id resolves.
- Forged placement, day or slot ids give 404 or a refusal.
- Every input is an integer from a server-issued check result.

**Audit.** A new `ancora_placement_change` record:
- placement, from and to (day, slot), user, time, check version, reason "manual move";
- shown on the placement and kept for undo.

**Draft vs final.** A moved placement keeps its `job`, so its draft or final state is unchanged. The audit marks it as manually moved, and the explorer shows a "moved" marker.

**UI** (the #128 conventions).
- Select a placement in the plan timetable or explorer, using a button per placement.
- A "Move" dialog lists the server-checked destinations as a grid of buttons; invalid cells are disabled, with their reasons.
- Preview, then "Move here" (confirm), then "Undo".
- Dragging onto a valid cell is a convenience that submits the same command.
- Status and alert regions, focus return, mobile buttons.
- While the check job runs, a loading state with polling; on timeout, the user can retry.

**Regression fixture and tests.**
- Fixture: the synthetic fixture plus a second specialty sharing teacher TA in the same period (s05), a manual teacher restriction, a period restriction, and a 2-slot activity.
- Kernel tests:
  - the check job's grid equals case 150's semantics, on the same fixture through `WorkspaceSync`;
  - commit, version mismatch, a job running, another owner, forged ids, audit, undo.
- A workspace-journey extension: after a move, the next job loads the placement at its new cell and continues (no duplicate, occupancy at the new cell).
- Functional tests: keyboard and button path, a refusal message, a stale version.
- Parity: the cases stay characterization; no legacy SQL changes.

## 6. Later proposals, each with the contract it needs

- **Swap (`permuta`):** two placements of the same period. The check deletes both and validates each at the other's cell (case 150 semantics). The commit is atomic over both, with the same guards. VB6's checks are defective, so this is not parity.
- **Fix (`Fija`):** a persisted Drupal field on `ancora_placement`. The engine has no column, so its only effect would be on "clear keeping fixed". It needs a decision on whether fixed placements block the editor and gap closing. It is a toggle in VB6; propose an explicit set.
- **Clear keeping fixed (`degenerar`):** deletes a scope's non-fixed placements. It contradicts legacy continuation (no D7) unless approved as a separate action ("clear the scope", PAR-085 target).
- **Close gaps (`QuitaHuecos`):** a batch of same-day moves for one brigade, each validated by the check job (all brigades of the group, unlike VB6), committed atomically with one audit batch.
- **Change teacher or room:** requires capacity, fixed and forbidden rooms and the assignment lists; out of the first slice.
- **Cross-plan conflict warning:** the engine does not see a teacher shared with another plan's period (case 150). A warning that reads the other periods' placements on the same real dates would be net-new (c).

## 7. Downstream impact

- **#95 persistence:** entity updates only. The engine id is kept, so the publisher's replay check is unaffected. The site version changes, so a move is refused while jobs run.
- **D7 continuation:** workspace mode keeps the moved placement (no delete). Two-database mode would erase it, so the feature is not offered there until D7 is reconciled.
- **D14 explorer and viewer:** they read the entities, so cache tags refresh them; they need a "moved" marker. The viewer stays read-only.
- **Audit:** a new record type, and nothing else changes.
- **LBR accounting:** **0 credit.** No legacy component is retired, because PHP had no wired manual edit and VB6 is not the baseline.

## 8. Blocking gaps

1. The VB6 UI callers of `moveAsignacion`, `permuta`, `degenerar`, `QuitaHuecos` and `frmDatosActividad` are not in the repository, so their UI semantics (resultbutton handling, fecha/hora updates) cannot be characterized.
2. There is no persisted fixed flag in the engine, and the generator never reads one.
3. Two-database mode cannot keep moves (D7).
4. The site-wide stale version forbids moves while any job runs.
5. Checks need a workspace, which means the worker and about 1 s of latency.
6. Teachers shared across plans are invisible to every engine check.

## 9. Implemented: the first slice (#130)

Authorized by the maintainer and implemented as specified in §5, with the checkpoint's conditions. It applies to workspace mode only.

**What the destinations mean.** A valid cell is valid **within the placement's period, as the scheduler sees it**. That covers the period, the activity type, the subject and their restrictions, and every activity of the period for the group's cohorts, the teacher, the room and the other resources.
- It is **not** checked against other plans whose dates overlap. A teacher or room shared with another plan may be busy there (case 150).
- The move page says so in a visible note and again in the preview. Nothing is labelled "conflict-free".
- A cross-plan check stays a documented follow-up (§6).

**Routes**
- `ancora_scheduling.placement_move`, `/ancora/timetables/placements/{ancora_placement}/move`, with `?check=<id>[&undo=<change id>]` (`PlacementMoveForm`).
  - It requires `edit academic data` or `administer academic data`, and update access to the placement: the plan's owner, or an administrator.
  - It is linked as "Move" from the plan timetable (start cell and mobile list) and from the explorer. The viewer has no link and shows no marker.
- In two-database mode the page explains why moves are not offered, and no link is shown.

**Command contract** (`PlacementModification`)
- `requestCheck(Placement, account): int`
  - It queues a check, or reuses a queued or running check by the same user.
  - It is never refused because a generation job is queued.
- `check(Placement, id): ?array` returns the state, the grid and whether the check has expired.
- `commit(Placement, check id, day, slot, account, ?undo): PlacementChange`
  - It runs in one Drupal transaction under the publication's advisory lock (`PlacementPublisher::LOCK`).
  - It refuses with `PlacementModificationException` (reason codes `mode`, `access`, `check`, `expired`, `job`, `version`, `placement`, `cell`, `same`, `undo`) and writes nothing in that case.
  - It sets only the day and the first slot. It validates and saves as the plan's creator, and writes an `ancora_placement_change`.
- `undo(change, check id, account)` is a commit back to the change's origin. It allows only the placement's last change, with a new check.

**The check is not a generation job**
- It has its own table, `ancora_placement_check`, with states queued, running, done and failed. It is never in the job queue, and the one-running-job rule never counts it.
- The worker runs pending checks at the start of every cycle, before it claims a generation job (`ancora_engine.check_runner`, `CheckRunnerInterface`), so a queued generation job does not block a check.
- `PlacementCheckRunner` works as follows:
  - it opens a workspace under its own key range (900000000000 + check id; generation job ids never reach it);
  - it loads the whole site with the unchanged `WorkspaceSync` (`loadForCheck()`: no closure prune);
  - in a transaction that is rolled back, it deletes the placement's row and evaluates every cell with the case-150 functions;
  - it drops the workspace.
- It writes only its own row, which is not part of the generation input, so it can never make a run's publication stale.
- A check that cannot run fails, with its reason. A check left running for 120 s is marked failed.

**Guards and their tests**

| Guard | Kernel `PlacementModificationTest` | Functional `PlacementMoveUiTest` | `workspace-journey.sh` step 11 (real engine) |
|---|---|---|---|
| Destinations come from the engine's functions, in a workspace | — | — | own cell valid; shared teacher (A1/G2), cohort (B1/G1), manual teacher restriction and period restriction refused; 2-slot overflow refused |
| Only a done, unexpired check of this placement; only its valid cells | `testRefusals` | disabled cells with reasons | — |
| No generation job queued or running | `testChecksAndGenerationJobs` (queued, running) | alert after a job is queued | a job queued after the check refuses the commit |
| Input version and placement signature | `testRefusals`, stale after a run | — | — |
| A check is not blocked by, and does not block, anything | a running check does not block a commit; a check request while a job is queued; no job created | — | a check and a queued job both run in one cycle |
| Owner, viewer and anonymous access; forged ids | `testRefusals` | 403 / 404 | — |
| Time-only: group, teacher, room, resources, engine id and job kept | `testMoveIsTimeOnlyAndAudited` | — | the next run keeps it at the new cell, publishes nothing, duplicates nothing, and books its occupancy there (A1/G2's check sees TA busy at the new cell, not at the old one) |
| Audit, cascade and plan access | `testMoveIsTimeOnlyAndAudited` | history list | — |
| Undo | `testUndo` | keyboard undo | undo returns it |
| Two-database mode | `testRefusals` | explanation, no button | — |

**Accessibility** (the #128 conventions)
- The grid of buttons is the primary path. A disabled cell carries its reasons as text, linked by `aria-describedby`.
- The preview is a labelled group with a focused confirm button.
- Feedback goes to `role="status"` and `role="alert"` regions.
- While the check runs, a loading state (`aria-busy`) with a Refresh link. The script reloads every 2 s, at most 30 times.
- Dragging the activity onto a valid cell presses that cell's button.
- On mobile the grid scrolls inside its wrapper.

**Deploy:** `drush updatedb` runs `ancora_scheduling_update_11006`, which creates the check table and the change entity type. Then `drush cr`. The worker must run, as it already does for generation.

**Planned reuse in the planning workspace** (#141; planned and **not authorized**; see `docs/ux/planning-workspace.md` §7)
- The workspace would run this same flow in place: the same command contract, the same `PlacementMoveForm` and token, and the same refusals.
- The only planned form changes are:
  - a validated `destination` back to the workspace URL;
  - an AJAX callback that returns the affected regions.
- Nothing changes in `PlacementModification`, the check runner, the worker or the tables. No validation moves to JavaScript.
- **Check latency is unmeasured.** It is the request, plus the queue wait (one worker cycle, idle sleep 5 s by default), plus the run, plus the client's 2 s poll.
  - It will be measured on representative fixtures (§7.7 of the UX document) before any threshold is set.
  - Until then the UI must give immediate progress feedback.

## 10. Atomic swap of two saved placements: characterization and design (#136)

Status: **characterization and design only**. The implementation is the separate issue "[UI-TIMETABLE-3a]", and it needs maintainer approval.

### 10.1 Characterization

- **VB6 `TAncora.permuta`** (`cls/ancora_goh_traditional_TAncora.cls:7339-7564`, `rafageist/ancora-vb6@6c7038d`).
  - What it does:
    - It refuses a fixed (`fija`) activity (`:7358`).
    - It checks the `RespetarOrden` day range of each new day (`:7389-7458`, `rango` at `:7413`, `:7451`).
    - It moves every activity of each one's brigade group to the other's day and slot (`moveAsignacion` at `:7546`, `:7550`). Teacher and room are kept.
  - Its checks are **vacuous**:
    - It passes `listaidbrg` to `AND_MPI` (`:7521`, `:7526`), but only `listaidbrg1` and `listaidbrg2` (declared with it at `:7353`) are ever assigned. `AND_MPI` then loops `For i = 1 To brgs.cant` over zero brigades and returns all true (`bas/ancora_goh_traditional_modDataGenerator.bas:671`, loop at `:683`).
    - The teacher check reads only the first slot (`:7535`, `:7541`), with each activity still occupying its own slot, so two activities with the same teacher always fail it.
    - Rooms are never checked.
  - Its caller is not in the repository.
  - **It is not reproduced**, only used as a reference.
- **PHP** has no swap and no wired manual edit (§1). The generated `set_Actividad` is an unwired UPDATE (case 148).
- **Classification:** a net-new Drupal enhancement, with 0 LBR and 0 parity credit.
- **SQL (case 152, characterization).** A swap is DELETE of both rows plus INSERT of both at the swapped cells.
  - The unique keys refuse every collision (23505) all or nothing. They trust period restrictions and overflow (as for a move, case 149).
  - The pair check removes **both** rows in one rolled-back subtransaction. It then checks:
    - each activity at the other's cell over its whole duration, with the case-150 functions: bounds, period, activity type, subject, every brigade, teacher, room and resources, including manual restrictions;
    - the two activities against each other at their new cells, the **mutual check**: a shared teacher, room, cohort or resource with overlapping slots on the same day.
  - Results:
    - **A:** two activities of one teacher on two days: each move alone is refused, because the target is the other's cell, yet the swap is valid and accepted. *Individually invalid moves can make a valid swap.*
    - **B, E, F:** one teacher (B), one cohort (E) or one resource (F); different durations on one day. Each candidate is valid with both rows removed, yet the two collide at their new cells, and the database refuses. *Removing both rows is not enough: the mutual check is required.*
    - **C:** a 2-slot activity swapped across a break books both slots (real hours 480–570 and 600–690). C2: its last slot would pass the period, so the check refuses and the database accepts.
    - **D:** a manual teacher restriction at a destination is refused by both. A period restriction is refused by the check only.
    - **G:** with nothing shared, the swap equals two moves alone.

### 10.2 Design

**Pair check** (reuses the #130 architecture)
- `ancora_placement_check` gets a `kind` column: `move`, or `swap` with a second `partner_id`. A new update hook adds both columns, with `move` as the default.
- `PlacementCheckRunner` runs a swap check in **one** workspace:
  1. load the whole site (`loadForCheck()`, no prune);
  2. in a rolled-back transaction, DELETE both engine rows;
  3. evaluate X at Y's cell and Y at X's cell with the case-150 grid query for one cell each;
  4. run the mutual check of case 152 (same day, overlapping `[slot, slot + duration)`, a shared teacher, room, brigade from `dame_lista_id_brigadas_de_grupo`, or resource from `otros_recursos_usados`);
  5. store the verdict, with reasons per side (`x`, `y`, `mutual`), the site version, and both signatures.
- It is still not a generation job. It is never blocked by one and never blocks one.

**Commands** (`PlacementModification`)
- `requestSwapCheck(x, y, account): int`:
  - same plan, same period and the same owner; update access to both;
  - x ≠ y, and their cells differ;
  - it reuses an open check of the same pair by the same user, in either order.
- `swap(x, y, checkId, account): array{PlacementChange, PlacementChange}`:
  - one Drupal transaction under the publication advisory lock;
  - it refuses while any generation job is queued or running, when the site version differs from the check's, when either signature differs, when the check is not a done, unexpired swap check of exactly this pair, or when its verdict is not valid;
  - it sets X to Y's (day, slot) and Y to X's, validates and saves both as the plan's creator, and writes two `ancora_placement_change` records with one shared `pair` id;
  - any exception rolls both back;
  - ids, engine ids, job, status, group, teacher, room and resources are kept, and no engine row is written.
- `undoSwap(pair, checkId, account)`:
  - a **new validated atomic swap** back, with a fresh check of the same pair;
  - allowed only while both records of the pair are the last change of their placement;
  - never half an undo: both go back or neither does.
- Refusal codes: those of `commit()`, plus `pair` (not the same plan or period, or the same placement), `cell` (identical starting cells) and `partner` (the second placement is missing or forged).

**Decisions**

| Case | Decision | Reason |
|---|---|---|
| Different durations | Allowed when each new end fits the period and the two do not overlap each other (mutual check) | case 152 B, C2 |
| Identical starting cells | Refused (`cell`) | Nothing would change; two placements of one cell do not share resources anyway |
| Draft and final | Allowed for the placements the timetable shows; the status stays that of each placement's job | as for a move (§9) |
| Stale check | Refused (version or signature); duplicate requests reuse the open check, and `requestSwapCheck(y, x)` is the same pair | as for a move |
| Scope | Same plan and same period only; never across plans in this slice | the engine checks one period |
| #133 warnings | `forCandidates()` for X at Y's cell and Y at X's cell are shown in the preview, non-blocking | as for a move |
| Linked entities | Kept: group, cohorts, teacher, room, resources, duration, template, engine id, job | time-only, like the move |

**Audit:** `ancora_placement_change` gets a nullable `pair` field (a UUID shared by the two records) and `reason` `swap` or `undo_swap`. Each record keeps its own from and to. The history shows both placements of a pair.

**Access:** both placements belong to one owner's plan, and the user has update access to both. Forged, missing or cross-owner ids give 404 or `partner`. Other sites are separate databases.

**UI** (#128 and #130 conventions)
- A "Swap with…" button on the move page opens a list of the period's other placements as buttons: label, group, day and slot; the keyboard and mobile path.
- Choosing one requests the pair check (loading state).
- The preview shows both destinations ("X: day a, slot b → day c, slot d" and the reverse), any engine refusal per side or mutual, and the #133 warnings for both.
- An explicit "Swap them" confirm.
- Errors go to `role="alert"` with zero writes. "Undo the swap" sits in the history.
- Dragging one placement onto another in the grid is an optional convenience that presses the same button.

**Continuation:** the next run loads both placements at their swapped cells, and the legacy rules rebuild their occupancy. Continuation keeps both, nothing is duplicated, and no other placement changes.

### 10.3 Regression test plan

- **Kernel** (`PlacementModificationTest` and a new `PlacementSwapTest`):
  - a reciprocal swap with the same teacher (case 152 A);
  - all refusals with zero writes: the generation-job guard, version, either signature, a stale or expired check, a check of another pair, a forged or missing partner, another owner, a viewer, different periods or plans, identical cells, an invalid verdict (x, y, mutual);
  - linked entities kept;
  - the pair audit (two records, one `pair`) and the undo swap; a refused half-undo when one placement moved since;
  - **rollback of both on a partial error**: a failure injected in the second save, e.g. a test-only hook that throws on the second entity's presave, leaves both placements unchanged and writes no change record;
  - the #133 warnings for both destinations.
- **Functional:** the swap flow by buttons (pick, preview with both destinations and warnings, confirm); the refusal alerts; access 403 and 404; the two-database explanation.
- **workspace-journey:** real pair checks on the case-152 fixture:
  - A is valid, B/E/F mutual, C2 overflow, D manual and period restrictions;
  - a committed swap;
  - the next run keeps both at the swapped cells, books their occupancy there (a check of a third placement sees them), publishes nothing new and duplicates nothing;
  - the queued-job refusal; the undo swap; no workspace left.
- **Real browser:** pick, swap preview and confirm by keyboard; the optional drag; the undo; mobile overflow.
- **SQL:** case 152 stays as characterization.
