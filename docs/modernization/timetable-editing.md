# Interactive timetable editing: characterization and design (#123)

Status: **characterization and design only**. Nothing here is implemented or authorized. The first slice is proposed as a separate issue, "[UI-TIMETABLE-1]", and needs maintainer approval. Parents: #74 and #70. Depends on #95 (placement persistence), #119 (explorer) and #128 (interaction conventions).

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
