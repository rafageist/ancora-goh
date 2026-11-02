# Functional completeness: the PHP scheduling journey against Drupal (#70, Phase 2)

Issues: #70 (parity inventory), #74 and #105 (planner workflow and UI integration). Authorization: the functional completion package of 2026-11-01 on #70, #74 and #105, item 2. This is a documentation deliverable: no code, schema, engine or configuration change.

This file is a **companion of [legacy-parity-inventory.md](legacy-parity-inventory.md)**, not a second inventory. The inventory answers "does Drupal compute what legacy computes?", row by row (PAR ids, quirks Q-nn, deviations Dn). This file answers a different question: **can a planner complete, through the Drupal UI, every scheduling workflow that the PHP application actually wired?** Each row below cross-references the PAR rows that hold the business-rule evidence, and adds only what the inventory does not record: the screen journey, its Drupal entry points and the next action.

## Contents

1. [Sources and rules](#1-sources-and-rules)
2. [How the PHP application is wired](#2-how-the-php-application-is-wired)
3. [The matrix](#3-the-matrix)
4. [Summary by classification](#4-summary-by-classification)
5. [Journeys: page transitions and context re-entry](#5-journeys-page-transitions-and-context-re-entry)
6. [Gaps ranked by their effect on a planner](#6-gaps-ranked-by-their-effect-on-a-planner)
7. [Proposed Phase 3 slices](#7-proposed-phase-3-slices)
8. [Inventory drift found while building the matrix](#8-inventory-drift-found-while-building-the-matrix)
9. [Historical data exposure: remediation recommendation](#9-historical-data-exposure-remediation-recommendation)
10. [Verification of this document](#10-verification-of-this-document)

## 1. Sources and rules

**Binding rules** (authorization record, #70, 2026-11-01):
- the legacy implementation is the specification;
- MPI/PriorizA stays unchanged: no algorithm fix, rule, heuristic or state-transition change;
- historical authentication or security defects are never reproduced.

**Source precedence**, and what was available:

| Order | Source | Available? | How it is used here |
|---|---|---|---|
| 1 | The 2010 thesis, *Áncora GOH 2.0: Generación y Organización de Horarios Docentes* | **No.** The original document is not in this repository or in the wiki repository. The wiki is an English engineering adaptation of it, "not a literal page-by-page translation" (wiki `Home.md:7`, `Source-to-Wiki-Map.md:3,38`, `Historical-Research-and-References.md:42`). | The adaptation is cited as **design intent** only (wiki `Use-Cases.md`, `Analytics-and-Reporting.md`). Where the intent exceeds what PHP wired (printing, analyses, manual timetable management, guest publication), the row says so and is classified by the PHP behaviour. The wiki itself warns that the thesis "documents a proposed design; this is not an audit of today's code" (`Source-to-Wiki-Map.md:31`). |
| 2 | Verified PHP/PostgreSQL behaviour | Yes: `control/`, `view/`, `www/js/app/`, `bin/security.conf`, `dev/sql/**` | The journey evidence (file:line) of every row. |
| 3 | #70 parity documentation and tests | Yes: the inventory, the SQL characterization cases, the differential harness scenarios s01–s15 | Cross-referenced by PAR id and case or scenario. |
| 4 | Verified Drupal behaviour | Yes: `modern/web/modules/custom/**` on `master` at `f299f10`, plus PR #148 (unmerged) where marked | Routes, forms, services and tests per row. |
| 5 | VB6 | Context only | Mentioned only to explain a deferred enhancement. |

Abbreviations: `A/` = `modern/web/modules/custom/ancora_academic/src/`, `S/` = `…/ancora_scheduling/src/`, `E/` = `…/ancora_engine/src/`. PHP paths are repo-relative. "Admin list" means the entity collection under `/admin/ancora/<segment>` (`A/AcademicEntityTypes.php::SEGMENTS`), with its add, edit and delete forms (`A/Form/AcademicEntityForm.php`, `A/Form/AcademicDeleteForm.php`).

## 2. How the PHP application is wired

What the PHP application really offers a planner, as wired code (not as designed):

- **One document, many windows.** After login, `www/index.php` renders `view/custom/app.tpl`. Navigation is a start menu of six entries, each gated by one permission (`app.tpl:169-195`, the `$menu_items` array at `:172`):

  | Start-menu entry | Permission | Window and browser |
  |---|---|---|
  | Explorador de horarios | `ver_horarios` | `view/wins/explorador_de_horarios.php`, `view/custom/explorador.tpl` |
  | Configuración del tiempo | `gestionar_planificaciones` | `view/wins/planificaciones.php`, `browser_planificaciones.tpl`, `browser_periodos.tpl` |
  | Organización de los estudiantes | `gestionar_brigadas` | `view/wins/organizacion.php`, `browser_organizacion_*.tpl`, `browser_brigadas.tpl`, `browser_grupos_filtrados.tpl` |
  | Configuración de actividades | `gestionar_periodos` | `view/wins/desgloses.php`, `browser_desgloses.tpl`, `desglose_detalles.tpl`, `asignacion_recursos.tpl` |
  | Recursos | `gestionar_recursos` | `view/wins/recursos.php`, `browser_recursos.tpl` |
  | Generar horarios | `generador` | `win_generador.tpl`, `www/js/app/client/wins/ancoragoh_client_wins_generador.js` |

  The forms are 34 more modal windows registered in `app.tpl:10-72`. Every window loads its content with an AJAX `app_event_*` post (`www/js/app/lib/ancoragoh_ajax_util.js`).
- **The wired events.** The client scripts and templates post **149 distinct events** (`git grep -ohE 'app_event_[A-Za-z0-9_]+' www/js/app view/custom view/wins`, deduplicated). Among them there is **no** event for an activity (`add/set/del_Actividad`), a user, a role, a report, a print view or an export. The other handlers exist (generated) but no screen posts them (#139 §2.2: 272 of 417).
- **Authorization.** Events are deny by default (`control/Application.php:230-247`, `isEventAllowed`): only holders of `todos` run an event that `bin/security.conf` does not list, and it lists two. The anonymous viewer events need `public_viewer_anonymous = true` (`Application.php:29-36,70-72`), off by default.
- **Output.** A search of `view/custom`, `view/wins`, `www/js/app`, `control/custom`, `www/*.php` and `www/styles` for `print`, `imprim`, `export`, `excel`, `csv`, `xls`, `pdf`, `report`, `descarg` and `download` finds **no print view, no print stylesheet, no `window.print`, no export handler and no report template**. The only non-grid visual output is the **Gantt diagram of the owner's plans** (`get_diagram_gantt_planificaciones.php`, §3 FC-05). This agrees with F36 in [functional-inventory.md](functional-inventory.md) ("None" in PHP; RTF, HTML and Excel output were VB6 features).

## 3. The matrix

Classifications (one per row):

| Code | Meaning |
|---|---|
| **IA** | Implemented and accessible: a planner can complete it through the Drupal UI. |
| **HD** | Implemented, but hard to discover or complete through the UI (context lost, records re-entered, no route between the steps). |
| **P** | Partial: some of the PHP workflow is missing or behaves differently without an approved decision. |
| **M** | Missing despite verified PHP functionality. |
| **ID** | Intentionally different: a security rule or an approved decision (P-2, #121, #123/#130, #133, #105/#141). |
| **HX** | Historical defect or enhancement deferred: PHP did not provide it, or provided it only through a defect that is not reproduced. |

"Tests" names the characterization case (`dev/sql/tests/cases/NNN_*`), the harness scenario (`modern/tests/parity/scenarios/sNN-*`), and the PHPUnit or browser test. "Next" is the next action; "none" means nothing is needed for completeness.

### 3.1 Access and ownership

| ID | Workflow | PHP evidence | Drupal | Tests | PAR | Class | Next |
|---|---|---|---|---|---|---|---|
| FC-01 | Sign in; menus and actions gated by permission | `view/custom/authform.tpl`; `control/Application.php:230-247`; `bin/security.conf` (2 entries); start menu `view/custom/app.tpl:169-195` | Drupal login; permissions `view/edit/administer academic data`, `request/view ancora engine jobs`, `view timetable viewer`; main menu `ancora_scheduling.links.menu.yml`, `ancora_academic.links.menu.yml` (Overview, Plans & periods, Academic catalogues, Assignments & templates, Generate, Timetables) | Functional `PlanningUiTest`, `WorkspacePagesTest`, `ViewerTest::testAccessPolicy`; `dev/tests/security` (legacy) | 006 | **ID** (Drupal permissions; deny by default kept) | None. Role mapping stays the proposal of `permission-matrix-proposal.md`. |
| FC-02 | A planner sees and edits only their own plans and catalogues; catalogues serve every plan of their owner | `fdm_*_creador.php`; `creador = $1` in `get_minibrowser_*.php`, `get_browser_*.php`, `get_data_*.php` | `A/AcademicAccessControlHandler.php`, `A/Hook/AcademicQueryAccess.php`, `A/AcademicEntityTypes.php::OWNED` (#99, `4fb9d76`); teachers and rooms visible to all viewers (Q-23) | Kernel `AcademicAccessTest`; harness s02, s03; case 110, 121 | 001–003, 008, 050, 054 | **IA** | Reconcile the stale inventory cells (§8). |
| FC-03 | Edit or delete another user's record by id; owner rewritten on edit | Q-11, Q-12, Q-26, Q-33 (handlers load by id only) | Entity access per record; owner never changes on edit | Kernel `AcademicAccessTest::testEditNeverTransfersOwner`; harness s02 `foreign_update` | 004, 005 | **ID** (security, not reproduced) | None. |

### 3.2 Time configuration: plans, periods, calendars

| ID | Workflow | PHP evidence | Drupal | Tests | PAR | Class | Next |
|---|---|---|---|---|---|---|---|
| FC-04 | Create, edit and delete a plan with its first and last day | `browser_planificaciones.tpl:9-21`; `form_planificacion.tpl`; `minibrowser_planificaciones.tpl:18` (inline delete); `fdm_Planificacion_fecha_inicial.php` | Admin list `plans`; Overview `/ancora` (`S/Controller/WorkspaceController.php::landing`); plan page `/ancora/planning/{plan}` (`::plan`, `:137-264`) | Kernel `TimeInputsTest`, `AcademicEntityTest::testDeleteCascades`; Functional `PlanningUiTest`; every harness scenario | 010, 103 | **IA** | None. |
| FC-05 | See the owner's plans on a timeline (Gantt) | `browser_planificaciones.tpl:10,19-21` (Gantt button and panel); `control/custom/get_diagram_gantt_planificaciones.php:6-9` (owner's plans); `view/custom/diagrama_gantt_planificaciones.tpl` (one bar per plan, first to last day); `ancoragoh_client_browsers_planificaciones.js:19-29` | None. The plan page shows first day and length (`WorkspaceController.php:145-148`); the Overview lists plans without dates | None | 010 | **M** (read-only view) | Low priority (§6, G8). If wanted: a read-only timeline of the owner's plans on the Overview, from `starts_on`/`ends_on`; no engine input. |
| FC-06 | Periods: days, slots, start hour, slot length | `browser_periodos.tpl:15,33`; `form_periodo.tpl`; `ancoragoh_server_periodos.js:8,20` (Q-20) | Admin list `periods`; plan page period rows link Edit (`WorkspaceController.php:181-184`) | Case 110, 146; Kernel `TimeInputsTest` | 011 | **IA** | None. (The period record page itself is not linked, #139 §2.3; its edit form is.) |
| FC-07 | Periodicity: plan days on which the period repeats | `browser_periodos.tpl:19`; `form_periodo_periodicidad.tpl`; `custom_set_Periodicidades.php` | Period form field `plan_days` (`A/Entity/Period.php:134`), exported to `periodicidad` | Harness, every scenario (`periodicidad` PASS) | 012 | **IA** | None. |
| FC-08 | Period restrictions: day × slot grid | `browser_periodos.tpl:22`; `form_periodo_restricciones.tpl:24-56`; `ancoragoh_client_forms_periodo.js:96-124`; `custom_set_Restricciones_Periodo.php` | `/ancora/planning/restrictions` (`A/Form/RestrictionGridForm.php`, `A/TimeRestrictionGrid.php`), linked per period from the plan page (`WorkspaceController.php:186-192`) and the menu | Case 139; Kernel `TimeRestrictionTest`; Functional `RestrictionGridUiTest`; s08 | 070, 071 | **IA** | None. |
| FC-09 | Breaks between slots | No screen (only the generated `add_Receso`) | `/ancora/planning/breaks` (`A/Form/PeriodInputsForm.php`), linked per period | Case 140; `PeriodInputsUiTest`; s09 | 013 | **ID** (approved P-2, D9b) | None. |
| FC-10 | Real-date restrictions and cross-plan coupling | Engine RULEs only, no screen | Engine unchanged; net-new read-only cross-plan warnings (#133, `S/Timetable/CrossPlanConflictDetector.php`) on the timetable and the workspace | Case 151; Kernel `CrossPlanConflictDetectorTest`; Functional `CrossPlanConflictUiTest` | 072 | **ID** (approved #133; engine unchanged) | None. |

### 3.3 Student organization

| ID | Workflow | PHP evidence | Drupal | Tests | PAR | Class | Next |
|---|---|---|---|---|---|---|---|
| FC-11 | Specialties and levels | `browser_organizacion_left.tpl:20,29`; `form_especialidad.tpl`; `form_nivel.tpl` | Admin lists `specialties`, `levels` | Harness s02, s07 | 014 | **IA** | None. |
| FC-12 | Brigades (cohorts) with enrolment | `browser_brigadas.tpl:29`; `form_brigada.tpl:32`; `ancoragoh_client_forms_brigada.js:10-30` | Admin list `cohorts` | Case 136; s07 | 015 | **IA** | None. |
| FC-13 | Activity classification: fields, priority up/down, priority zones | `browser_desgloses.tpl:34-35`; `form_clasif_act.tpl:28-36`; `set_ClasifAct_{sube,baja}prioridad.php`; `form_zpriori.tpl`; `custom_set_zpriori.php` | Admin list `activity-types` with Move up / Move down (`A/AcademicListBuilder.php:187-205`, `A/Controller/OrderController.php`); `/ancora/planning/priority-zones`, linked from the type page (`A/Hook/RestrictionHooks.php:89`) | Case 112, 140, 144; Functional `OrderUiTest`, `PeriodInputsUiTest`; s09, s12 | 019, 020, 077 | **IA** | None. |
| FC-14 | Groups per classification; brigade membership (drag-and-drop move, "no group") | `browser_grupos_filtrados.tpl:1,29-61,88`; `custom_MoverBrigadaDeGrupo.php`; `custom_EliminaBrigadaDeGrupo.php` | Admin list `groups`: the group form's cohort checkboxes; `A/GroupMembership.php`, `A/Hook/MembershipHooks.php`, constraint `AncoraGroupMembership` | Case 113, 136; Kernel `GroupMembershipTest`; Functional `GroupMembershipUiTest`; s07 | 016, 018 | **IA** (checkboxes instead of drag-and-drop) | None. |

### 3.4 Activities: subjects, breakdowns, templates and their resources

| ID | Workflow | PHP evidence | Drupal | Tests | PAR | Class | Next |
|---|---|---|---|---|---|---|---|
| FC-15 | Subjects in a specialty > level tree | `minibrowser_asignaturas.tpl:20`; `form_asignatura.tpl:29-33` | Admin list `subjects` | Case 136; Kernel `LegacyValidationTest` | 030 | **IA** | None. |
| FC-16 | Breakdown options (min/max days, order, same day) of a subject in a period | `desglose_detalles.tpl:8` (the period label opens the form); `form_desglose.tpl:19-26`; `set_Desglose.php` | `entity.ancora_breakdown.collection` (`?subject`, `?period`), `A/Form/BreakdownForm.php`; linked from the subject and period pages (`A/Hook/BreakdownHooks.php:69-83`) and per period from the plan page (`WorkspaceController.php:202-205`) | Case 110, 135; Kernel `BreakdownTest`; Functional `BreakdownUiTest`; s06 | 031–034 | **IA** | None. |
| FC-17 | **The templates of a breakdown, in place:** add a template to this breakdown, move it left or right, open its teachers, rooms and other resources | `desglose_detalles.tpl:17-38`: per breakdown, its templates in order (`getAllBy('desglose = $1 ORDER BY orden')`), ← when `i > 1`, → when `i < count`, the teacher, room and other-resource icons, and "+" for a new template **of this breakdown**; `get_form_new_plantilla.php:13-29` (order = count + 1, subject and period taken from the breakdown); `custom_{Sube,Baja}Plantilla.php`; `ancoragoh_client_browsers_desgloses.js:55-107` (all in one window) | The pieces exist apart: the breakdown list shows "Activity templates, in order" as **plain text with no action** (`A/AcademicListBuilder.php::templatesOf`, `:165-182`); Move up / Move down are on the templates admin list only; a new template is created from `/admin/ancora/templates/add`, where **subject and period are chosen again**; the teacher and room editor and the other-resource editor are linked from the template page only (`A/Hook/SharedResourceHooks.php:76,85`) | Case 111, 144; Functional `OrderUiTest`, `BreakdownUiTest`; s06, s12 | 035, 036 | **HD** | **Phase 3 slice B** (§7.2). |
| FC-18 | Teachers eligible for a template: add, priority 1..max+1, groups, remove | `desglose_detalles.tpl:30` → `profesores_asignados.tpl:2-35`; `editar_prioridades_pxact.tpl`; `ancoragoh_client_forms_pxact.js:38-60`; `set_prioridad_pxact.php` | `/ancora/planning/assignment-editor?template=` (`A/Controller/AssignmentEditorController.php`, `A/Form/AssignmentEditorForm.php`, `A/TemplateAssignments.php`, #122); menu and template page | Kernel `TemplateAssignmentsTest`; Functional `AssignmentEditorUiTest`; case 020 | 051, 052 | **IA** (reached from the template page; from the breakdown see FC-17) | None beyond slice B. |
| FC-19 | Rooms eligible for a template, with priority | `desglose_detalles.tpl:31` → `lugares_asignados.tpl:3-35`; `custom_getui_EditarPrioridadLxact.php` | The assignment editor's room column; `A/Hook/RoomPriorityHooks.php` | Case 112, 141; Kernel `RoomConstraintsTest::testRoomPriorities` | 055 | **IA** | None. |
| FC-20 | Other-resource groups of a template: needed, essential, members, priority | `desglose_detalles.tpl:32` → `otros_recursos_asignados.tpl:11-40`; `form_rxact.tpl`; `set_GrupoRecursos_{necesarios,imprescindibles}.php`; `custom_set_PrioridadRxact.php` | `/ancora/planning/template-resources?template=` (`A/Form/SharedResourcesForm.php`, `A/SharedResources.php`), linked from the template page and the assignment editor (`AssignmentEditorForm.php:406`) | Case 142; Kernel `SharedResourcesTest`; Functional `SharedResourcesUiTest`; s11 | 057, 058 | **IA** | None. |

### 3.5 Resources

| ID | Workflow | PHP evidence | Drupal | Tests | PAR | Class | Next |
|---|---|---|---|---|---|---|---|
| FC-21 | Teachers | `browser_recursos.tpl:8`; `minibrowser_profesores.tpl:1`; `form_profesor.tpl` | Admin list `teachers` | Harness s02, s05 | 050 | **IA** | None. |
| FC-22 | Rooms with capacity | `browser_recursos.tpl:9`; `form_lugar.tpl:30-37` (Q-17 not reproduced) | Admin list `rooms` | Case 122 | 054, 062 | **IA** | None. |
| FC-23 | Travel costs between rooms | `form_edit_lugar_costos_de_traslado.tpl`; `custom_set_costotraslado.php` (the PHP form fails on PHP 8: `get_form_edit_lugar_costos_de_traslado.php:24`) | `/ancora/planning/travel-costs?room=`, linked from the room page (`A/Hook/RestrictionHooks.php:79`) | Case 141; `RoomConstraintsUiTest`; s10 | 063 | **IA** | None. |
| FC-24 | Other (generic) resources | `browser_recursos.tpl:10`; `form_otro_recurso.tpl` | Admin list `other-resources` | Case 142; s11 | 057 | **IA** | None. |

### 3.6 Scheduling inputs that PHP had no screen for

| ID | Workflow | PHP evidence | Drupal | Tests | PAR | Class | Next |
|---|---|---|---|---|---|---|---|
| FC-25 | Availability of activity types, subjects, cohorts, teachers and rooms (manual restrictions) | Dead or empty editors: `custom_set_Restricciones.php` (missing include), `form_restricciones.tpl`, `ancoragoh_server_brigadas.js:27` (A-4) | The restriction grid's other kinds (P-2, D9a) | Case 139; s08 | 070, 071 | **ID** (approved P-2) | None. |
| FC-26 | Fixed and forbidden rooms per group | No screen (generated `add_LugarFijo`, `add_AccesoProhibido` only) | `/ancora/planning/group-rooms` (P-2, D9c); the form says forbidden rooms never take effect (Q-30) | Case 122, 141; s10 | 060, 061 | **ID** (approved P-2) | None. |
| FC-27 | Daily workload limits of teachers and cohorts | No screen (generated `add_HigieneParaProfesor`, `_Brigada` only) | `/ancora/planning/load-limits` (P-2, D9b), linked per period | Case 124, 140; s09 | 078 | **ID** (approved P-2) | None. |
| FC-28 | Restriction inheritance (HRT), conditional restrictions, manual other-resource restrictions, specialty restrictions | No screen; the engine fails on HRT and conditional rows (42804, 42703); other-resource and specialty restrictions never steer `generar()` | Not offered (Q-28, Q-29, Q-32; specialty: no effect) | Case 070–072, 100 (xfail), 139, 142 | 070, 073, 074 | **HX** (availability exceptions) | None until an approved engine fix. |

### 3.7 Generation: configuration, execution, status, outcomes

| ID | Workflow | PHP evidence | Drupal | Tests | PAR | Class | Next |
|---|---|---|---|---|---|---|---|
| FC-29 | Choose the scope (specialty, level, period) and the options (priorize, compaction, room choice) | `win_generador.tpl:14-106`; `ancoragoh_client_wins_generador.js:12-35,286-357`; `get_data_{especialidades,niveles,planificaciones}.php`; `generar.php:3-19` | `/ancora/scheduling/request` (`S/Form/RequestRunForm.php`): the plan comes from `?plan` (`:68-75`), but **period, specialty and level are always chosen again** (`:76-107`, no default from the query); linked from the plan page, the timetable and the workspace bar with `?plan` only | Case 060, 141, 143; Functional `SchedulingPagesTest`; Kernel `JobValidationTest`; s10 | 080–084 | **HD** | **Phase 3 slice A** (§7.1). |
| FC-30 | Queue several scopes | `ancoragoh_client_wins_generador.js:120-215` (queue), stops after the first job (Q-18) | One job per request; the worker runs queued jobs in order (`E/Worker.php`); the runs list `/ancora/scheduling/jobs` | Kernel `JobRepositoryTest` | 080 | **IA** (Q-18 not reproduced) | None. |
| FC-31 | Run, wait, and know when it finished | Synchronous request with a busy indicator (`generador_emo_working`, `ancoragoh_client_wins_generador.js:297-299,353-357`); nothing is echoed (`generar.php`) | Background worker; run page `/ancora/scheduling/jobs/{job}` (`S/Controller/SchedulingController.php::job`, `:61-138`) with state and metrics, `max-age 0`, **no self-refresh** and no link to the workspace; it links the classic timetable and the plan page | Functional `JobPagesTest`, `SchedulingPagesTest`; `modern/scripts/engine-integration.sh`, `r1-journey.sh` | 080, 087 | **HD** | **Phase 3 slice A** (§7.1). |
| FC-32 | Stop a run; failure handling | "Detener": `ancoragoh_client_wins_generador.js:361-380`, `cancelar_generacion.php` (soft: what was placed is kept); a SQL error rolls the whole run back | `/admin/ancora/engine/{job}/cancel` (`E/Form/CancelJobForm`), linked from the run page; soft cancel matches, but after the grace time the worker escalates to `pg_cancel_backend` and keeps nothing; runs with collisions or overflow are rolled back | Case 062, 066 | 086, 088 | **P** (D12 not approved) | Maintainer decision on D12 (approve or remove the escalation and the invariant rollback). |
| FC-33 | Partial outcome and continuation: a later run keeps earlier placements | `generar.sql:1004-1010` (already placed demands skipped); `generar.php` deletes nothing | **Workspace mode:** legacy continuation, partial = draft (`E/Worker.php:48-56`). **Two-database mode, the default** (`E/EngineSettings.php:161-165`): the scope's placements are deleted before `generar()` (`E/Engine/EngineClient.php:187-191`, D7) | Case 061, 063; s05; `workspace-journey.sh`; Kernel `PlacementPublisherTest` | 085, 087 | **P** (D7 in the default mode) | Maintainer decision on the default engine mode (#95/#96); a topology change is outside the current authorization. |
| FC-34 | Which demands were not placed, and why | None: `generar.sql:1488-1492` (`-- TODO: registrar`, a NOTICE) | The run page shows the count "Not placed" and labels the result a draft | Case 063 | 087 | **HX** (F30; VB6 had a rejected-activities list) | None for parity. A list with reasons is an enhancement and needs a decision. |
| FC-35 | Stored placements | `actividad` rows | Placement entities (workspace mode, `S/Timetable/PlacementPublisher.php`) or engine rows (two-database mode), read by `S/Timetable/TimetableReader.php` | Kernel `PlacementPublisherTest`, `TwoDatabaseImportTest`; s11 | 059, 076, 085 | **IA** | None. |

### 3.8 Timetables: explorer, viewer, workspace

| ID | Workflow | PHP evidence | Drupal | Tests | PAR | Class | Next |
|---|---|---|---|---|---|---|---|
| FC-36 | Explore a period's timetable by brigade, teacher, room or other resource | Start menu `app.tpl:172`; `explorador.tpl:21-29`; `custom_getui_ExploradorHorarios.php`; `get_minibrowser_horarios.php:41-72`; `minibrowser_horarios.tpl` | `/ancora/timetables/explorer` (`S/Controller/ExplorerController.php::page`, `S/Timetable/TimetableExplorer.php`), menu Timetables and every plan timetable | Case 147; s14; Kernel `TimetableExplorerTest`; Functional `ExplorerTest` | 091 | **IA** | None for parity. The object is chosen by form submit, and the timetable's link carries only the period (#139 T2). |
| FC-37 | Public viewer | `get_browser_publico.php:13-24`; `get_minibrowser_publico.php:44-51`; `Application.php:29-36,70-72`; `bin/security.conf` (`public_viewer_anonymous = false`) | `/ancora/timetables/viewer`, authenticated only, `view timetable viewer` | s15; Functional `ViewerTest` | 092 | **ID** (security, Q-37; public mode waits for #62) | None. |
| FC-38 | Plan timetable and planning workspace (perspectives, multi-slot blocks, inspector, Resume) | No PHP counterpart beyond the explorer | `/ancora/planning/{plan}/timetable` (`SchedulingController::timetable`); `/ancora/planning/{plan}/workspace` and `/regions` (S1a, `S/Controller/PlanWorkspaceController.php`, `S/Workspace/PlanWorkspace.php`); lenses all, teacher, group, room (`PlanWorkspace::LENSES`) | Kernel `PlanWorkspaceTest`; Functional `PlanWorkspaceUiTest`; browser `workspace.spec.js`, `history.spec.js`, `review.spec.js` | 091 | **ID** (approved #105/#141) | The brigade and other-resource perspectives exist only in the explorer (§6, G5). |

### 3.9 Manual editing, audit and output

| ID | Workflow | PHP evidence | Drupal | Tests | PAR | Class | Next |
|---|---|---|---|---|---|---|---|
| FC-39 | Move one placement with a server check, undo, and the record of manual moves | None wired: `add/set/del_Actividad.php` are posted by no screen (`timetable-editing.md` §1; F32) | Move page `/ancora/timetables/placements/{id}/move` (`S/Form/PlacementMoveForm.php`, `S/Timetable/PlacementModification.php`, `PlacementCheckRunner`), undo, history entity `ancora_placement_change` (#130); **in place in the workspace: PR #148 (S1b), not merged** | Case 148–150; Kernel `PlacementModificationTest`; Functional `PlacementMoveUiTest`; #148: Functional `PlanWorkspaceMoveTest`, browser `move.spec.js` | — (F32) | **ID** (approved net-new #123/#130; in place depends on #148) | Merge decision on #148. |
| FC-40 | Swap, fix, add, remove, close gaps | None in PHP (VB6 only) | Swap designed (#136), on hold (#138) | Case 152 | — (F32, F33) | **HX** | None (held). |
| FC-41 | Print, export or reports of a timetable | **None in PHP** (§2, "Output"). Thesis intent: "HTML reports and printable timetables directly from the browser" (wiki `Analytics-and-Reporting.md`, "Publishing and printing"). VB6 had RTF, HTML and Excel output | None (no print stylesheet, no export route) | None | — (F36) | **HX** (thesis intent and VB6 only; not in PHP) | None for parity. Any format needs its own decision. |
| FC-42 | Analyses: extreme slots, occupied time, restriction share, common free slots, quality | None in PHP; thesis use cases (wiki `Use-Cases.md`, "Analysis use cases"); VB6 analyses | None | None | — (F34) | **HX** | None for parity. |
| FC-43 | Deleting records, with what goes with them | Inline "¿Está seguro?" (`minibrowser_planificaciones.tpl:18-19`, `profesores_asignados.tpl:20,35`); generated `del_*` handlers; foreign-key cascades | `A/Form/AcademicDeleteForm.php` (says what goes), `A/ReferenceGuard.php::cascade()`, placement cascade | Case 114, 145; Kernel `AcademicEntityTest::testDeleteCascades`; s13 | 100–105 | **IA** | None. |
| FC-44 | Users, roles and permissions | Command line only: `bin/adduser.php`, `bin/setpassword.php`, `bin/deluser.php`, `bin/showusers.php`; no screen posts a user or role event. Thesis use cases list them as administrator screens | Drupal core People, Roles and Permissions pages | Functional `ViewerTest`, `PlanningUiTest` (permissions) | 006 | **ID** (Drupal accounts replace `seguridad`) | None. |
| FC-45 | Audit traces and host checks | Tables `seguridad.traza`, `check_host`; no PHP writes or calls them | None (the manual-move history of FC-39 is the only audit record) | — | 007 | **HX** (unused in PHP) | None. |

## 4. Summary by classification

| Classification | Rows | Count |
|---|---|---|
| **IA** implemented and accessible | FC-02, 04, 06, 07, 08, 11, 12, 13, 14, 15, 16, 18, 19, 20, 21, 22, 23, 24, 30, 35, 36, 43 | **22** |
| **HD** implemented but hard to discover or complete | FC-17, 29, 31 | **3** |
| **P** partial | FC-32, 33 | **2** |
| **M** missing despite verified PHP functionality | FC-05 | **1** |
| **ID** intentionally different | FC-01, 03, 09, 10, 25, 26, 27, 37, 38, 39, 44 | **11** |
| **HX** historical defect or enhancement deferred | FC-28, 34, 40, 41, 42, 45 | **6** |
| **Total** | | **45** |

The business rules behind these workflows are equivalent or recorded in the inventory (55 equivalent, 0 missing PAR rows). The completeness gaps are therefore **journey gaps**: the planner can reach every wired PHP capability except the plans timeline (FC-05), but three steps of the core loop lose context (FC-17, FC-29, FC-31), and two run semantics wait for maintainer decisions (FC-32, FC-33).

## 5. Journeys: page transitions and context re-entry

The method is the #139 journey method ([planning-workspace.md](../ux/planning-workspace.md) §2.4): count every full document request ("page load") and every record the planner has to choose again. The Drupal counts below are **read from the route links on `master`** (the controllers and templates cited in §3), not measured on the demo; #139's measured journeys T1–T3 are reused where they apply. The PHP application is one document: after login it makes **no** full page load, so its column counts window and panel loads (AJAX), which keep the window's selection.

| Journey | PHP (window/panel loads; re-entries) | Drupal on `master` (page loads; re-entries) | With the proposed slices |
|---|---|---|---|
| **J1. Configure one subject's activities in a period:** add two templates, order them, assign a teacher and a room to each | Start menu › Configuración de actividades (1) › subject in the tree (1) › plan (1): every period's breakdown with its templates is shown (`desglose_detalles.tpl`). Per template: "+" (1 modal), save; teacher icon (1 panel) › add (1 modal); room icon (1 panel) › add (1 modal). **0 page loads; subject and plan chosen once; 0 re-entries.** | Subject page (1) › Subject breakdowns (1): templates as text only. Add: Assignments & templates (1) › templates add (1), **choose subject and period again**, save (1). Second template: add (1), **subject and period again**, save (1). Order: templates list (1) › Move (1). Per template: template page (1) › assignment editor (1) › teacher (1 POST) › room (1 POST). Back to the breakdown: no link (subject page › breakdowns, 2). **About 19 page loads; 4 re-entries.** | Subject page (1) › Subject breakdowns (1) › "Add activity template" (1) prefilled and locked, save returns (1), ×2; Move (1); per template "Teachers and rooms" (1) › 2 POSTs › back (1). **About 15 page loads; 0 re-entries.** The gain is mostly the re-entries and the lost route back, not the number of loads. |
| **J2. Generate one scope of a period and look at the result** | Start menu › Generar horarios (1): specialty, level, plan, period chosen in the window › add to the queue › Generar: busy until done (`generador_emo_working`). Start menu › Explorador (1) › period (1, **re-entered**) › brigade (1). **0 page loads; 1 re-entry.** | Plan page (1) › Request a timetable (1): plan preselected, **period, specialty and level chosen again** › submit (1) › run page, reloaded by hand until done (≥ 1) › Timetable of this plan (1) › filter or open the workspace (1). **≥ 6 page loads; 3 re-entries.** | Plan page period row › "Generate" (1): plan and period prefilled, specialty and level chosen once › submit (1) › run page **updates itself** › "Open in the workspace" (1, period kept). A rerun from the timetable's "Run this scope again" prefills all four. **3 page loads + automatic refreshes; 0 re-entries.** |
| **J3 = #139 T3.** Fix why an activity has no room, then regenerate | Configuración de actividades (template's room panel, 1) › Generar horarios (1). **0 page loads; scope chosen in the generator.** | ≥ 9 page loads, 3 re-entries (plan, scope, filter) | Scope re-entry removed by slice A (1 fewer re-entry); the route from the template to the breakdown by slice B. |
| **J4 = #139 T2.** Same activity from the room's and the brigade's point of view | Explorer window: one click per perspective, the period kept. **0 page loads; 0 re-entries.** | Explorer: ≥ 2 page loads and ≥ 2 re-entries per switch. Workspace (S1a): teacher, group, room in place, 0 full loads; **no brigade or other-resource lens** | Unchanged (G5). |
| **J5 = #139 T1.** Move one activity, then undo | Not possible in PHP | ≥ 13 page loads, 2 re-entries (move page) | With #148 (S1b): 1 full page load (measured, `move.spec.js` T1) |

## 6. Gaps ranked by their effect on a planner

The ranking weighs how often the planner meets the gap in a real scheduling task, end to end (configure, generate, inspect, adjust, regenerate), and how many steps or re-entries it costs (§5).

| Rank | Gap | Rows | Effect | Next action |
|---|---|---|---|---|
| **G1** | Templates are configured away from their breakdown: no in-place add, order or resource links; subject and period re-entered for each new template | FC-17 (FC-18–20 reached only from the template page) | Every subject of every period of every plan; J1 costs about 19 page loads and 4 re-entries against PHP's 0 page loads and 0 re-entries | **Slice B** (§7.2) |
| **G2** | The generation scope is not carried into the request, the run page must be reloaded by hand, and it does not lead back to the workspace | FC-29, FC-31 | Every run; J2 ≥ 6 page loads and 3 re-entries; T3's scope re-entry | **Slice A** (§7.1) |
| **G3** | A rerun in the default two-database mode deletes the scope's placements (D7), so partial results and earlier runs are not continued | FC-33 | Every regeneration after a partial run | Maintainer decision on the default engine mode (#95/#96); not a UI slice, and a topology change is not authorized now |
| **G4** | Move and undo in place need PR #148 | FC-39 | Every manual adjustment (T1: ≥ 13 page loads → 1) | Maintainer merge decision on #148 |
| **G5** | Brigade (cohort) and other-resource timetables only in the explorer, chosen by form submit, context dropped | FC-36, FC-38 | Checking a brigade's week (the PHP explorer's first perspective) | A later workspace lens over the existing `TimetableExplorer` read model, after #148; not proposed now |
| **G6** | Cancel escalation and invariant rollback have no approval (D12) | FC-32 | Only when a run is cancelled or breaks an invariant | Maintainer decision on D12 |
| **G7** | No list of the unplaced demands | FC-34 | After a partial run; PHP had none either | None for parity (enhancement) |
| **G8** | No plans timeline (Gantt) | FC-05 | Rare: comparing plan dates; the dates are on the plan page | Optional read-only slice later |

Not ranked as gaps: print or export (FC-41) and analyses (FC-42), which PHP never provided, and the intentional differences (§4, ID).

## 7. Proposed Phase 3 slices

Both slices stay inside the planner-workflow and UI-integration scopes of #74/#105. Neither adds a schema, a domain rule, an engine change, a route or a dependency, and **neither depends on PR #148**. They touch different files, so they can ship in either order.

### 7.1 Slice A: generate from the context, and follow the run

**PHP behaviour.** The generator window offers the owner's specialties, levels and periods (`get_data_*.php`) and generates a (specialty, level, period) scope with the window's options (`win_generador.tpl:14-106`, `ancoragoh_client_wins_generador.js:286-357`). The planner sees the run in progress until it ends (`generador_emo_working`, `:297-299`, `:353-357`), in the same document, and goes on to the explorer without leaving it.

**Drupal gap.** `RequestRunForm` takes only the plan from the query (`:68-75`); the period, specialty and level are chosen again on every request (`:76-107`), from the plan page, the timetable or the workspace bar. The run page (`SchedulingController::job`) does not refresh while the run is queued or running, and links the classic timetable, not the workspace (#139 T3; §5 J2).

**Reuse.** `RequestRunForm` (its options and validation are unchanged), the scopes that the plan timetable shows (`TimetableReader::read()` builds them from the plan's runs, and each run's `source` carries the period, specialty and level ids), `SchedulingController::job` and `::timetable`, `WorkspaceController::plan`, the existing `ancora_scheduling.plan_workspace` route, and the progressive-refresh pattern of `js/placement-move.js` (#128 conventions: a "Refresh" link first, the script only shortens the wait).

**Smallest complete change.**
1. `RequestRunForm::buildForm()`: a `#default_value` for `period`, `specialty` and `level` from `?period`, `?specialty`, `?level`, **only when the id is among the options the form already offers** (the same rule as `?plan`); anything else is ignored and the form behaves as today. The options, their defaults (`buscalugar` TRUE, `forma` 1, `compactar` 1) and `validateForm()` are unchanged.
2. Links that carry the scope: a "Generate" action on each period row of the plan page (`WorkspaceController::plan`, with `plan` and `period`), and "Run this scope again" next to each scope's state on the plan timetable (`SchedulingController::timetable`, with `plan`, `period`, `specialty`, `level` from the scope's run), shown only when the user may request runs.
3. The run page, while the state is queued or running: a "Refresh" link to itself, and a small library that reloads the page every few seconds with a bounded number of attempts; nothing is attached once the run has finished. An "Open in the workspace" link (`ancora_scheduling.plan_workspace` with `period`) next to "Timetable of this plan", under the same plan access check.

**Tests.**
- Functional (`SchedulingPagesTest` or a new `RequestPrefillTest`): the form is prefilled from the query for the owner; a foreign or unknown period, specialty or level is ignored (no error, no information in the page); the options' defaults are unchanged; the plan page and timetable links carry the scope; the run page shows "Refresh" and the library only while queued or running, and "Open in the workspace" only to a viewer of the plan; a run of another user's plan stays refused.
- Browser (`dev/browser`, disposable site with the worker): plan page › Generate › submit › the run page updates itself to the final state › Open in the workspace, with the period kept; page loads counted; axe in each state.

**MPI inputs unchanged.** The slice changes defaults of a form and links only. Verify that:
- a request made from a prefilled form and one made by choosing the same values by hand produce the same `JobScope` and the same input snapshot hash (`input_hash`, shown on the run page) on the same data;
- the job options posted are the legacy defaults unless the planner changes them (a Functional assertion);
- the parity harness (`dev/sql/tools/with-ephemeral-pg.sh modern/scripts/parity-diff.sh`) still gives 0 FAIL and 0 KNOWN in both engine modes, and the E2E digest (`dev/e2e/expected-placements.md5`, `r1-journey.sh`) is unchanged.

### 7.2 Slice B: the templates of a breakdown, in place

**PHP behaviour.** `desglose_detalles.tpl:17-38` lists, for each breakdown of the selected subject and plan, its templates in order, each with ← and → (`custom_SubePlantilla.php`, `custom_BajaPlantilla.php`), its teachers, rooms and other resources, and a "+" that creates a template **of that breakdown**: `get_form_new_plantilla.php:13-29` takes the subject and period from the breakdown and sets the order to count + 1.

**Drupal gap.** The breakdown list renders the templates as plain text (`AcademicListBuilder::templatesOf`); ordering is on the templates list; a new template re-enters subject and period; the resource editors are reached only from the template page (§3 FC-17; §5 J1).

**Reuse.** `AcademicListBuilder` (breakdown rows), `OrderController` and `OrderUpkeep` (the moves, already Move up / Move down with a destination), `AcademicEntityForm` (it already prefills `plan` from the query with an access check, `defaultPlanId()`, and already locks subject and period when editing a template), the assignment editor (`?template=`) and the template-resources editor (`?template=`), `BreakdownMaintainer` (unchanged).

**Smallest complete change.**
1. Breakdown list: render each template in order as a link to the template, with "Move up" and "Move down" (the existing `ancora_academic.order.move` route, type `template`, with a destination back to the list), "Teachers and rooms" (assignment editor) and "Other resources" (template-resources editor), each shown only when the user may update the template; and per breakdown an "Add activity template" link to the template add form with `?breakdown=<id>` and a destination.
2. `AcademicEntityForm`: for a **new** activity template, when `?breakdown=<id>` names a breakdown the user may update, set the subject, period and plan from it and lock subject and period, as the edit form already does. Anything else is ignored (the plain form). The order stays appended by `OrderUpkeep` (count + 1), as legacy.

**Tests.**
- Functional (`BreakdownUiTest`, `OrderUiTest`): the owner sees the templates in order with the four actions; an account without `edit academic data` sees the templates without write actions; Move up from the breakdown list renumbers as on the templates list; "Add activity template" prefills and locks subject and period, and the new template is last; a foreign or unknown breakdown id gives the plain form and reveals nothing; the destination returns to the breakdown list.
- Kernel: a template created through `?breakdown=` equals one created through the plain form with the same values (subject, period, plan, `sequence`).

**MPI inputs unchanged.** Every write still goes through the existing entity forms and `OrderController`, so the rows that reach `plantilla_de_actividad`, `profesor_para_actividad`, `lugar_para_actividad` and the resource tables are unchanged. Verify with `FixtureExportTest` (the `LegacyRows` export of the synthetic fixture stays as asserted), a comparison of the export of a fixture whose templates are entered through the breakdown list and through the plain forms, harness s06 and s12 (breakdown options and order upkeep, both engine modes), and the unchanged E2E digest.

### 7.3 Not proposed now

- A workspace lens for brigades and other resources (G5): it touches `PlanWorkspace`, which PR #148 changes; propose it after #148.
- Slice A's "Generate this scope" in the workspace bar: the bar is built by `PlanWorkspace` too; add it after #148 if wanted.
- Anything about D7, D12, unplaced-demand lists, print or export: decisions, not UI slices (§6).

## 8. Inventory drift found while building the matrix

Several "Drupal today" cells of [legacy-parity-inventory.md](legacy-parity-inventory.md) describe the state before the ownership slice (#99, `4fb9d76`, "legacy creador ownership and shared catalogues (#70 D1-D3)") and the D15 engine ids, while the code, `academic-domain-mapping.md:47,391` and `legacy-parity-harness.md:86` describe the state after it:

| Row | The cell says | The code on `master` |
|---|---|---|
| PAR-001 | the synthetic owner `p<id>:owner` | `creador` is the legacy user of the record's owner (`S/ScopeMapper.php:31,54`, `A/Export/LegacyRows.php`) |
| PAR-002 | any member with `view academic data` sees every record | owner filter on every list and reference widget (`A/Hook/AcademicQueryAccess.php`), teachers and rooms visible to all (Q-23) |
| PAR-003 | a required `plan` on every catalogue | catalogues are owned and shared by the owner's plans (`A/AcademicEntityTypes.php::OWNED`) |
| PAR-008 | the owner is `p<id>:owner` | the subject owner's legacy user |
| PAR-050, PAR-054 | teachers and rooms per plan | owned by the user (`OWNED`) |
| PAR-110 | `p<plan>:` + code | numbered ids `0000000012:code` in creation order (`LegacyRows::engineId()`) |

The summary there (55 equivalent, 12 deviation, 0 missing, 7 n/a) counts these rows as deviations. This document does not change those rows or the counts, which are reported on #70. **Next action:** a short docs-only reconciliation of the seven cells and the summary, if the maintainer agrees; it changes no behaviour.

## 9. Historical data exposure: remediation recommendation

This section is based only on the existing records: [security-privacy.md](security-privacy.md) §1, §2, §4 and §7; #143's assessment (`docs/evidence/workload-assessment.md` §1.3–§1.4, G12 and DEC-7, on PR #149's branch); and repository metadata. **No personal record, hash or credential was opened, printed, copied or quoted** for it. Nothing was changed: no visibility, no history, no credential.

**What is exposed (counts and locations only).**

| Item | Where | Status |
|---|---|---|
| The 2010 developer database dump (one account row with an unsalted MD5 hash, teacher names, related rows) | `dev/backup/ancoragoh.{sql,custom,tar}` in this repository: current tree and every commit since `b42aa03` | Recorded in `security-privacy.md` §2 as private historical evidence, not a fixture, never read by CI. History decision 2026-10-25: option A (rotate, keep history) |
| VB6 faculty semester dataset files | `rafageist/ancora-vb6` | `security-privacy.md` §2 classes them "keep private" and "do not use or redistribute until reviewed and anonymized"; the GitHub API reports the repository **public** (checked again 2026-11-02: public, not archived, 0 forks). DEC-7 is open |
| Earlier credentials C1–C4 | History of this repository | Option A: rotation is a maintainer action (`security-privacy.md` §4 checklist) |

**Existing mitigations.** The Docker image refuses `dev/backup` (`docker/e2e.sh:103`); the LBR sets exclude it (`dev/lbr/sets/historic-2010/rules.tsv:22`); `dev/ci/check-secrets.sh` treats the three dumps as documented exceptions; `bin/security.conf` keeps `legacy_md5_login = false`, so a historical MD5 hash cannot sign in to a legacy instance unless an operator turns it on.

**Recommendations, in order (maintainer actions; none is authorized for an agent).**

1. **VB6 datasets (DEC-7), first.** The cheapest containment that matches the existing classification is to **make `rafageist/ancora-vb6` private** until the faculty files are reviewed. If the VB6 code should stay public, the alternative is to remove the faculty dataset files from that repository and its history (a rewrite there, which is small and isolated, with 0 forks reported) and keep them in private storage; keeping them public is not consistent with "keep private". Either way, confirm the rights to the files and record the decision privately.
2. **Release archives of this repository.** Add `dev/backup/** export-ignore` to `.gitattributes`, so `git archive` and GitHub's generated source archives leave the dumps out, as the Docker image already does. This changes no history and no file content, and fits a small hygiene PR when the maintainer wants it (it is configuration, so not part of this docs-only PR).
3. **Current tree.** Decide whether the dumps must stay in the working tree as the reference oracle (#6/#8). The harness and the SQL suite install from `dev/sql/` and do not read them, so moving them to private storage, with a provenance note in `dev/backup/README` style, would remove them from every new checkout without touching history.
4. **History.** Keep option A unless the maintainer reopens it. A rewrite that drops `dev/backup/ancoragoh.{sql,custom,tar}` (option B) would change every commit id, and with them the frozen LBR baselines (`historic-2010` at `eca8aa7`, `pre-drupal-cut` at `f532e91`), the PR references and the commit ids quoted throughout the docs and issues; GitHub caches and existing clones would keep copies. If chosen, it needs its own plan: freeze open PRs, regenerate the LBR set references, force-push all refs, ask GitHub Support to purge cached views.
5. **Credentials.** Whatever is decided above, treat the historical account hash as known: no live system should accept that password, and any reuse of it elsewhere must be changed (`security-privacy.md` §4 checklist, item 3). Keep `legacy_md5_login = false` everywhere, and keep restored historical databases off any network.
6. **Fixtures.** Keep #143's synthetic workload ladder as the only source of large fixtures; any aggregate use of the historical files waits for DEC-2.

## 10. Verification of this document

- **SQL characterization suite**, run on a disposable socket-only cluster: `dev/sql/tools/with-ephemeral-pg.sh dev/sql/tests/run.sh` on PostgreSQL 16.15, 2026-11-02: **63 pass, 11 xfail, 0 fail, 0 xpass, 0 xwrong** (74 cases, up to `152_placement_swap_checker`). No other database, server or port was used.
- **Event count:** `git grep -ohE 'app_event_[A-Za-z0-9_]+' -- www/js/app view/custom view/wins | sort -u` gives 149 events; none is an `Actividad`, user, role, report, print or export event.
- **Output search:** see §2 "Output"; the only hit is the plans Gantt.
- **Drupal references:** the routing files (`*.routing.yml`), menus (`*.links.menu.yml`) and the classes cited per row, on `master` at `f299f10`; PR #148's files for FC-39 only.
- The PHPUnit, harness and browser tests cited per row were not re-run for this document; their last results are recorded in the inventory and in [planning-workspace.md](../ux/planning-workspace.md) §8.4.
