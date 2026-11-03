# Next functional priorities after the planning journey (#105)

Issues: #105 (UI integration), #70 (legacy parity). Input: the planning journey of PR #155 (`docs/ux/planning-journey.md`), the functional completeness matrix of PR #152 (`docs/modernization/functional-completeness.md`, gaps G5 and G7), and the integrated tree `integration/functional-acceptance` (`50fc47d`: master + #149–#155).

This is a **proposal document**. It changes no code and no data. Each proposal needs the maintainer's approval before it is implemented.

**Rules every proposal keeps.**
- The legacy code is the specification. No business rule, validation or generator behaviour is redefined.
- No change to `dev/sql/**` (MPI/PriorizA and the rest of the engine) and no new engine output.
- No schema change: no new entity field, table or column.
- Every write goes through a service that already exists and is tested.
- No broad redesign: each proposal is a set of small slices, and the first one stands alone.

Paths: `A/` = `modern/web/modules/custom/ancora_academic/`, `S/` = `…/ancora_scheduling/`, `E/` = `…/ancora_engine/`. Line numbers are from `50fc47d`.

## Contents

1. [P1: a more efficient assignment editor, same semantics](#1-p1-a-more-efficient-assignment-editor-same-semantics)
2. [P2: failed runs and unplaced activities in understandable terms](#2-p2-failed-runs-and-unplaced-activities-in-understandable-terms)
3. [P3: cohort and other-resource perspectives in the workspace](#3-p3-cohort-and-other-resource-perspectives-in-the-workspace)
4. [Order, dependencies and summary](#4-order-dependencies-and-summary)

---

## 1. P1: a more efficient assignment editor, same semantics

### 1.1 Current semantics (what must not change)

**Legacy PHP** (the specification; details in `docs/modernization/assignment-editor.md` §1.1):

| Action | Legacy code | What is stored |
|---|---|---|
| Assign the checked teachers (or rooms) | `www/js/app/client/forms/ancoragoh_client_forms_pxact.js:16-37` (`agregar`: one AJAX call per checked id), `…_lxact.js:115-130`; `www/js/app/server/ancoragoh_server_recursos.js:2-9,44-51` | one `profesor_para_actividad` / `lugar_para_actividad` row each, **priority 1**, no group |
| Edit a teacher assignment: groups and priority in one dialog | `ancoragoh_client_forms_pxact.js:38-60` (`modificar`): delete the groups (broken, Q-16), add each checked group, then set the priority | `profesor_para_grupo` rows; `prioridad` |
| Set a room priority | `actualizaPrioridadLxact` (`ancoragoh_server_recursos.js`) | `lugar_para_actividad.prioridad` |
| Remove | `…_pxact.js:61-67`, `…_lxact.js:131-137`, after a confirmation | row deleted; room priorities relevelled by trigger (PAR-055) |

The PHP screens make **no full page load**: each action is an AJAX call from a modal window.

**Drupal today** (`A/src/Form/AssignmentEditorForm.php`, `A/src/TemplateAssignments.php`):

- One operation per form submit. `submitForm()` (`:136-171`) reads the triggering button, checks update access on the template, compares the form's `state` token with `TemplateAssignments::state()` (`:151-153`, the concurrent-change guard), runs **one** operation in `apply()` (`:175-283`), then `finish()` (`:308-326`) stores the message and **redirects** to the editor. Every change is therefore a full page load.
- Operations and their service calls:
  - `assign` → `TemplateAssignments::assign()` (`:178`): priority 1, no group, as legacy;
  - `priority` → `setPriority()` (`:225`): the value must be one of `priorityOptions()` (`:160`), which depend on the current number of assignments (1..max + 1, or 1..max);
  - `add_group` / `remove_group` → `setGroups()` (`:250`): the eligible groups of `groups()` (`:120`). Q-16 (unchecking never removes) is intentionally not reproduced;
  - `remove` (a confirmation step, `setRebuild()`) then `confirm_remove` → `remove()` (`:272`), with an undo snapshot in private tempstore; `undo` → `restore()` (`:297`);
  - drag and drop only fills hidden `drop[*]` fields and presses a hidden button (`dropOperation()`, `:291-306`), so it is the same path.
- Constraints on every save: `AncoraUniqueInScope`, `AncoraSameCreator`, `AncoraEligibleGroups`; room priority upkeep by `A/src/Hook/RoomPriorityHooks.php` (D9c).

**Semantics to preserve, stated as invariants.**
- S1. The stored rows after a sequence of operations are the rows the current editor stores for the same sequence, in the same order: `ancora_teacher_assignment` (template, teacher, priority, groups), `ancora_room_assignment` (template, room, priority), and the engine rows exported from them (`profesor_para_actividad`, `profesor_para_grupo`, `lugar_para_actividad`).
- S2. Each operation is validated by the same `TemplateAssignments` method against the state left by the previous one (priority options depend on the count).
- S3. A refused operation changes nothing and shows the same message as today.
- S4. A change made meanwhile in another window is refused (the `state` token).
- S5. A removal is confirmed first, and only the last removal can be undone.

### 1.2 Measured cost today

From the journey of #155, step 4 (`dev/browser/tests/planning-journey.spec.js:354-405`): three templates of PRG, each with two teachers (groups and priorities) and two rooms (priorities).

| Task | Form submits (each a full page load) | Notes |
|---|---|---|
| Assign one teacher, add one group, set priority 2 | 3 | PHP: 2 modal saves (add; edit), 0 page loads |
| Assign one teacher with two groups and priority 2 | 4 | |
| Assign one room with priority 2 | 2 | |
| Remove one assignment (without JavaScript) | 2 | the confirmation step is its own post; the drag path confirms in a dialog: 1 |
| One lecture template of the journey (2 teachers, 2 rooms) | 8 | plus 2 loads to reach the editor |
| One lab template of the journey (2 teachers with 2 groups, 2 rooms) | 10 | plus 2 |
| **Journey step 4, three templates** | **26** | **31 page loads measured in the editor** (`assignment_editor_page_loads`), against 0 in PHP |

### 1.3 Proposal

**Slice P1-a (smallest first slice): the existing single operations, submitted in place.**
- Every existing button (and the hidden drop button) submits through core Form API AJAX (`#ajax`) instead of a full post. The server path stays the same: `submitForm()` → `apply()` → `TemplateAssignments`.
- For an AJAX submit, `finish()` rebuilds the form instead of redirecting. The response replaces the feedback region, the two assignment panels and the available panel. It always includes the **fresh `state` token**, so the next change is not refused by the guard (S4).
- Focus returns to the changed card (today: URL fragment), and the message is announced through the existing `role="status"` / `role="alert"` regions.
- Without JavaScript, the current redirect flow is unchanged.

**Slice P1-b: the two batched actions legacy already had.**
- **Assign the checked ones**: checkboxes on the available panel and one "Assign selected" button. The server calls `assign()` once per checked id, in the order shown (legacy `agregar` loop, priority 1, no group).
- **Save groups and priority** on a teacher card: one button that calls `setGroups()` and then `setPriority()`, in the legacy dialog's order (`modificar`).
- In both cases the server applies the sub-operations **in order, each validated as today (S2)**. It **stops at the first refusal**, keeps the earlier ones (exactly what posting them one by one does today), and reports which were applied.
- Removal keeps its confirmation and stays one at a time (S5).
- With P1-a and P1-b, the journey's step 4 drops from 26 submits to 15, all in place.

**Files likely touched**
- `A/src/Form/AssignmentEditorForm.php` (`#ajax` on `button()`, rebuild in `finish()`, the two batch operations in `apply()`).
- `A/js/assignment-editor.js` (drop path through the AJAX button; `once` re-attachment after a replace).
- `A/ancora_academic.libraries.yml` (`core/drupal.ajax`), possibly `A/css/assignment-editor.css`.
- Tests: `A/tests/src/Functional/AssignmentEditorUiTest.php`, `A/tests/src/Kernel/TemplateAssignmentsTest.php`, `dev/browser/tests/planning-journey.spec.js` (step 4 counts), possibly a new `dev/browser/tests/assignment-editor.spec.js`.
- Docs: `docs/modernization/assignment-editor.md`.
- `A/src/TemplateAssignments.php` stays **unchanged**: the batch calls its existing methods.

**Out of scope**
- Any change to what an operation stores, to priority options, group eligibility or room priority upkeep.
- An unsaved client-side session with a final "Save" (VB6 "Guardar y terminar"). Every Drupal change is saved at once (`assignment-editor.md` §1.2).
- An all-or-nothing transaction around a batch. That would differ from posting the same operations one by one, so it needs its own decision.
- Batched removal; VB6 "Clonar" (copy to other templates); other-resource groups (`SharedResourcesForm`).
- Reproducing Q-16.

**Risks**
- A stale `state` token after an in-place change would make every second change fail. The acceptance criteria test this explicitly.
- The JavaScript behaviours (drag, dialog, focus) must re-attach to replaced markup.
- FunctionalJavascript tests cannot run in CI (no WebDriver, `assignment-editor.md` §4). The in-place path is covered by the Playwright browser workflow.
- A batch that stops halfway must say clearly what was applied.

**Acceptance criteria**
1. **Identical stored rows.** A Kernel or Functional test runs the journey's step-4 sequence twice on fresh fixtures: (a) as single full posts (today's path), and (b) through the in-place path and, after P1-b, through the batch actions. The normalized rows are equal: teacher assignments (template, teacher, priority, sorted group ids), room assignments (template, room, priority), and the exported `profesor_para_actividad`, `profesor_para_grupo` and `lugar_para_actividad` rows. Entity ids and timestamps are excluded.
2. **Identical refusals.** An out-of-range priority, a foreign group, a duplicate assignment, another planner's teacher or room, and a forged id each give the same message as today and change no row, in place and without JavaScript.
3. **Guard.** Two consecutive in-place changes both succeed. A change made in a second session between them is refused with the current message, and the panels then show the current state.
4. **Batch order.** "Assign selected" with three ids stores three rows at priority 1, as three single assigns do. A "Save groups and priority" whose priority is refused keeps the groups and reports the refusal, as two single posts do.
5. **Page loads.** In the journey's step 4, the editor causes no full page load after it opens: 31 measured loads become at most 6 (opening the editor for each template). After P1-b, the number of submits is at most 15.
6. **No regression.** `AssignmentEditorUiTest` passes unchanged without JavaScript. Removal still asks first, and undo still restores the last removal with its priority and groups.

**Estimated size.** P1-a: medium (form and script changes, about 2 days with tests). P1-b: small to medium.

**Smallest first slice.** P1-a for the teacher and room `assign` and `priority` buttons only. Groups, removal and drop follow in the same pattern.

---

## 2. P2: failed runs and unplaced activities in understandable terms

### 2.1 What the system actually records

| Source | Field | Available when | Content |
|---|---|---|---|
| `ancora_engine_job` (`E/ancora_engine.install`) | `state` | always | queued, running, succeeded, partial, cancelled, timed_out, failed (`E/src/Job/JobState.php`) |
| | `error` (varchar 1024) | failed, timed_out | `"[SQLSTATE] primary message"` (`Worker::outcome()`, `E/src/Worker.php:268-311`). The message is the **first line of `PGSQL_DIAG_MESSAGE_PRIMARY`, at most 500 characters** (`EngineClient::summary()`, `E/src/Engine/EngineClient.php:547-550`; read at `:216-217` and `WorkspaceClient.php:109-110`). Invariant failure (two-database mode): a fixed sentence (`EngineClient.php:226-228`). Timeout: a fixed sentence with the limit |
| | `result` (JSON) | always after a run | `duration_ms`, `replaced`, `attempt`, `mode`, `partial`, `kept` (`placements`, `partial`, `none`). If the run returned: `demands`, `placed`, `unplaced`, `collisions`, `overflow`, `digest` (`EngineClient::METRICS`, `:52-84`). For a synced job: `input_hash`, `input` (rows loaded, site version) |
| | `cancel_requested`, `cancel_mode`, `time_limit`, `attempts`, `started`, `finished` | always | |
| Drupal data | the scope's demands | always | the (template, group) pairs of the scope. The definition is the `demand` CTE of `EngineClient::METRICS`: template of a breakdown of the scope's subjects in the period × group of the same activity type, specialty, level and plan |
| | placements | succeeded, partial, soft-cancelled | `TimetableReader::read()` (`S/src/Timetable/TimetableReader.php:72`): placements of the shown job, per scope, with `final` and `stale` (the job's `input_hash` against the current hash, `:101`) |

**Not available (and not to be added by P2):**
- **Which demand failed, and why.** `generar()` raises only NOTICEs: `generar.sql:1436` ("falta de lugar"), `:1455` ("falta de profesor"), `:1488-1490` ("no se pudo colocar", `-- TODO: registrar`). They do not name the template or the group, they repeat per cell and zone, and the worker does not capture them (6.20; `scheduler-contract.md` §4.4).
- **Where an error was raised.** The PL/pgSQL context (for example `dia_minimo_impacto() line 59` for Q-39) is in the PostgreSQL server log only. The worker does not read `PGSQL_DIAG_CONTEXT`.
- **Metrics of a failed, timed-out or hard-cancelled run.** The transaction rolls back before the metrics are read, so `demands`, `placed`, `unplaced` and `overflow` are absent.
- **Which of several known causes applied** when they share a SQLSTATE (see 2.2, 2202E).

### 2.2 Known causes by recorded error (documented only)

These are the documented legacy behaviours that end a run with each recorded code. A page may list them as **possible** causes. The run never records which one applied.

| Recorded | Documented causes | Evidence |
|---|---|---|
| `[2202E] array subscript out of range` | Q-39: a multi-slot activity overflows the day, then `dia_minimo_impacto()` fails. Quirk 3 of `engine-dependency-closure.md` §10: a brigade restriction of another period outside this period's size. Quirk 2: a subject of the scope without a template in the period | journey #155 (MA/Y2); case 127 F1, F2 |
| `[23502]` (not-null violation) | Q-05: a template that does not require a teacher or a room | case 123 |
| `[23505]` (unique violation) | Q-31/Q-32: an other resource under contention, or a manual other-resource restriction at the chosen cell (D-05) | cases 083, 142 |
| `[22004]` | a period with a periodicity and a NULL start hour | case 130 |
| `[P0001] generar: activity template … has no orden` | a template without an order | `generar.sql:207` |
| state `timed_out` | the run exceeded its time limit (`57014`) | `Worker.php` |
| `[42804]`, `[42703]` | Q-28, Q-29. Not reachable from Drupal: those inputs are not provided | cases 070–072, 100 |
| failed, "broke an engine invariant" | two-database mode only: collisions or overflow, rolled back (D12) | `EngineClient.php:226-228` |
| state `succeeded` with `overflow` > 0 | workspace mode keeps legacy overflow (6.17) | `scheduler-contract.md` §4.4 |

### 2.3 Proposal

**Slice P2-a (smallest first slice): an explanation on the run page.**
- `S/src/Controller/SchedulingController.php::job()` (`:61-138`) adds a "What happened" block above the table. It is built only from the job row:
  - the state in words, and what was kept (`kept`): for example "The run stopped with an engine error. Nothing of this run was kept; the timetable shown before is unchanged";
  - for `failed`: the recorded code with the catalogue of 2.2 as "known causes of this error in the legacy engine". It says plainly that the run does not record which one applied. An unknown code shows "no documented cause";
  - for `succeeded` with `overflow` > 0: "N activities extend past the last slot of the day", labelled as legacy behaviour;
  - for `partial`: "N of M activities could not be placed. The engine does not record why."
- The catalogue is a static PHP map in one small class (for example `S/src/Timetable/RunExplanation.php`). Each entry cites its case number or quirk id.

**Slice P2-b: the latest run's failure is visible where the timetable is.**
- Today the timetable and the workspace show the last job that changed placements (`TimetableReader.php:84-92`). A newer failed or timed-out run of the same scope is visible only on the runs list and the run page.
- The scope status (`SchedulingController::scopeStatus()`, the workspace context bar) adds "The latest run of this scope (job N) failed: see why", from `JobRepository::forSource()`, which the reader already calls.

**Slice P2-c: the list of activities that were not placed.**
- For `partial` and soft-cancelled runs: the scope's demands minus the shown placements, listed as subject, template, group, activity type, and the teachers and rooms assigned (existing data). The demand definition must match the `demand` CTE; it is computed in PHP from the legacy rows `A/src/Export/LegacyRows.php` already builds for the engine (and that `TimetableReader::read()` already loads), so there is no second definition to drift.
- For `failed` and `timed_out` runs: the same list, introduced as "this run kept nothing; these are the scope's N activities".
- If the scope is `stale`, the list is labelled "computed from the current data, which changed after this run".
- For `succeeded` with overflow: the placements whose last slot passes the period's slots (slot + duration − 1 > slots, the `overflow` definition of `METRICS`).

**Files likely touched.** `S/src/Controller/SchedulingController.php`, a new `S/src/Timetable/RunExplanation.php` (P2-a), `S/src/Workspace/PlanWorkspace.php` (P2-b context bar), a new small read service for P2-c in `S/src/Timetable/`, `E/src/Controller/JobController.php` (the operator page may reuse P2-a). Tests: `E/tests/src/Functional/JobPagesTest.php`, `S/tests/src/Functional/SchedulingPagesTest.php`, a Unit or Kernel test for the catalogue and the demand list, and `dev/browser/tests/planning-journey.spec.js` step 6.

**Out of scope**
- Any change to `dev/sql/**`, to `generar()` NOTICEs, or to what the worker records. Reading `PGSQL_DIAG_CONTEXT` or the NOTICEs is a worker change, and it would still give no per-demand reason. If wanted, it needs its own decision.
- A reason per unplaced activity (6.20, FC-34: an enhancement that needs a decision).
- Input validation that refuses a request because of a known hazard (Q-05, Q-31, Q-39). That would be a new rule.
- Any change to the job states or to `kept`.

**Risks**
- A "possible cause" can be read as a diagnosis. The wording must say "known causes", never "the cause".
- The catalogue can drift from the SQL. A test pins each entry to an existing case file or quirk id.
- Computing demands in PHP must equal the engine's count. Acceptance criterion 4 compares them.
- P2-b touches `PlanWorkspace`, which #148 also changes.

**Acceptance criteria**
1. For every terminal state (succeeded, succeeded with overflow, partial, cancelled with and without kept placements, timed_out, failed for each SQLSTATE of 2.2, failed for an unknown code), a Functional test with a synthetic job row shows the expected explanation. No engine run is needed.
2. The page shows only data present in the job row and the Drupal data. A failed run shows no `demands`/`placed` figures that the row does not have.
3. In the journey (step 6), the MA/Y2 run page shows the 2202E explanation listing Q-39 and the two closure quirks. The CS/Y1 run page shows "1 activity extends past the last slot".
4. P2-c: for the partial-run fixtures (case 063, harness s05, a journey partial run), the number of listed activities equals `result.unplaced` when the scope is not stale, and no listed (template, group) has a placement.
5. `git diff` touches nothing under `dev/sql/**`, no `*.install` schema, and no worker file.
6. Access is unchanged: the run page keeps `jobAccess()` (`SchedulingController.php:171-184`). Another planner's run is still refused.

**Estimated size.** P2-a: small. P2-b: small. P2-c: medium.

**Smallest first slice.** P2-a: the static explanation on the run page, from the job row only.

---

## 3. P3: cohort and other-resource perspectives in the workspace

### 3.1 Existing readers and access controls

| Piece | Behaviour |
|---|---|
| `S/src/Timetable/TimetableReader.php::read()` | The placements of a plan's period, per scope, both engine modes. Each placement has `group_id`, `teacher_id`, `room_id` and `resource_ids` (`:163` engine rows, `:226` placement entities) |
| `S/src/Timetable/TimetableExplorer.php` (#119, PAR-091) | `SELECTORS` (`:50-55`): `brigade` → `ancora_cohort`, `resource` → `ancora_other_resource`. `options()` (`:97-124`): cohorts and other resources **of the period's plan owner**. `explore()` (`:145-213`): period refused unless of a plan the account may view; selector refused unless offered. Cohort match: the placement's group is one of the groups the cohort is in now (`groupsOf()`, `:221-228`). Resource match: the id is in `resource_ids` |
| Explorer route `ancora_scheduling.explorer` | permission `view academic data` or `administer academic data`. A cohort or resource is one form submit (about 3 page loads per lookup in the journey) |
| `S/src/Workspace/PlanWorkspace.php` (S1a) | `LENSES` = all, teacher, group, room (`:56`). `state()` (`:81-188`) returns 404 for any period, lens, record or placement the plan does not offer. `options()` (`:263-286`): the owner's teachers and rooms, the plan's groups. `shown()` (`:288-299`) filters by one placement field |
| Workspace routes `ancora_scheduling.plan_workspace` and `.regions` | permission `view academic data` and `_entity_access: ancora_plan.view`. In-place navigation fetches `/regions` (0 full loads after the first) |

Both read the same placements, and both restrict the lists to the plan owner's records. A cohort lens and a resource lens in the workspace therefore need no new access rule.

**Case 142 / Q-31.** `generar()` takes the resources of a placed activity from every resource group of its template **except the last** (`generar.sql:1366-1393`). With one resource group per template, no resource is booked. In the journey no placement uses the projector, so the other-resource view is empty (#155 finding 4). A resource lens will often be empty, and that is legacy behaviour.

### 3.2 Proposal

**Slice P3-a (smallest first slice): a cohort lens.**
- `PlanWorkspace::LENSES` gains `cohort`. Its options are `TimetableExplorer::options($period)['brigade']`, the same list the explorer offers. A cohort id that is not offered is a 404, like the other lenses.
- The placements shown are the ones `TimetableExplorer` matches for that cohort. To keep one definition, the matching in `explore()` moves into a public method of `TimetableExplorer` (for example `matches(string $selector, int $id, array $placements)`). Both the explorer and the workspace call it. This is a refactor of a reader with no change in result.
- The nav gets a "Cohorts" entry; the inspector of a placement links to the week of each cohort of its group.

**Slice P3-b: an other-resource lens.**
- `resource`, with the same pattern: options from `TimetableExplorer::options()['resource']`, matching on `resource_ids`.
- Empty state: "No activity of this period uses this resource." Optionally, after the maintainer reviews the wording, a sentence that legacy generation does not book the last resource group of a template (Q-31).

**Files likely touched.** `S/src/Workspace/PlanWorkspace.php` (lenses, options, `shown()`, nav, inspector pivots), `S/src/Timetable/TimetableExplorer.php` (extract the matcher), possibly `S/js/plan-workspace.js` (only if the nav is enumerated there; today it is not). Tests: `S/tests/src/Kernel/PlanWorkspaceTest.php`, `S/tests/src/Kernel/TimetableExplorerTest.php`, `S/tests/src/Functional/PlanWorkspaceUiTest.php`, `dev/browser/tests/planning-journey.spec.js` step 7 (cohort C1A and PRJ in the workspace), `dev/browser/tests/workspace.spec.js`. Docs: `docs/ux/planning-workspace.md` §8.

**Out of scope**
- Changing the explorer's lists (Q-23: every user's teachers and rooms) or the workspace's teacher and room lists.
- Making other resources bookable (Q-31/Q-32 are engine behaviour).
- Teacher or room lenses for other owners' records; viewer (`/ancora/timetables/viewer`) changes; moving from a cohort lens beyond what S1b already allows.
- Removing the explorer: it stays the legacy-parity screen (FC-36).

**Risks**
- #148 (S1b) changes `PlanWorkspace`. P3 should start after #148 is merged, as FC G5 already says.
- Cohort membership is read "now" (`groupsOf()`), not at the time of the run. The workspace must show exactly what the explorer shows, not something more clever.
- An empty resource lens can look like a bug. The empty state must be clear.

**Acceptance criteria**
1. **Same result as the explorer.** For every cohort and other resource offered in a fixture period (the journey's dataset and `WorkspaceFixtureTrait`), the workspace lens shows the same placements, cell for cell, as `TimetableExplorer::explore()` for that period and record. Kernel test.
2. **Same access.** Another owner's cohort or resource id, a non-numeric id, or a lens value not offered returns 404. Another planner and an anonymous user get 403, as today. Functional test.
3. **In place.** In the journey's step 7, cohort C1A and other resource PRJ are reached in the workspace with 0 full page loads after the first. Their counts equal the server's (PRJ: 0, case 142). Reload, back and forward restore the lens and the record.
4. **Read-only.** No write route is added; the explorer's tests pass unchanged.

**Estimated size.** P3-a: small to medium. P3-b: small.

**Smallest first slice.** P3-a, the cohort lens, with the shared matcher extracted from `TimetableExplorer`.

---

## 4. Order, dependencies and summary

| Order | Proposal | First slice | Depends on | Size of first slice |
|---|---|---|---|---|
| 1 | P1 assignment editor | P1-a: `assign` and `priority` in place | none | medium |
| 2 | P2 failed runs, unplaced | P2-a: run page explanation from the job row | none (P2-b after #148) | small |
| 3 | P3 perspectives | P3-a: cohort lens over the explorer's matcher | #148 merged | small to medium |

**Related record.** The MA/Y2 failure of the journey is now quirk **Q-39** in `docs/modernization/legacy-parity-inventory.md`. It is a reproduced historical defect, and no engine fix is authorized. P2 only explains it.

**Decisions this document does not take.**
- Whether to read `PGSQL_DIAG_CONTEXT` or the NOTICEs in the worker (2.3, out of scope).
- Whether a batch of assignment changes should be all-or-nothing (1.3, out of scope).
- Whether to show the Q-31 sentence in the resource lens (3.2).
