# Visual assignment editor (#122, first slice)

The teachers, groups and rooms of one activity template, edited in one screen: `/ancora/planning/assignment-editor?template=<id>` (`ancora_academic.assignment_editor`). Without `template`, the page lists the user's templates.

## 1. Characterized actions

The PHP application of 2010 is the specification. The VB6 program is a reference for the affordances only.

### 1.1 PHP: the template windows of the breakdown browser

| Action | Where | Values |
|---|---|---|
| Open the teachers, rooms or other resources of a template | `view/custom/asignacion_recursos.tpl:4-6` (three icons) | — |
| List the assigned teachers | `control/custom/custom_getui_ProfesoresAsignados.php` (table order), `view/custom/profesores_asignados.tpl:10-25` | name, priority (a click opens the priority picker), `x` |
| List the assigned rooms | `control/custom/custom_getui_LugaresAsignados.php` (`ORDER BY prioridad`), `view/custom/lugares_asignados.tpl:7-24` | name, priority, `x` |
| Offer new teachers or rooms | `custom_getui_NuevoProfesorParaActividad.php` (`DameProfesoresNoAsignadosA`), `custom_getui_NuevoLugarParaActividad.php` (`DameLugaresNoAsignadosA`) | the template owner's teachers or rooms that the template does not have yet (PAR-051, PAR-055) |
| Assign the checked ones | `www/js/app/client/forms/ancoragoh_client_forms_pxact.js:16-37`, `…_lxact.js:115-130`; `www/js/app/server/ancoragoh_server_recursos.js:2-9,44-51` | priority **1**, no group for a teacher |
| Set a priority | `get_form_edit_pxact_prioridad.php`, `custom_getui_EditarPrioridadLxact.php`; `editar_prioridades_pxact.tpl:5`, `editar_prioridades_lxact.tpl:15` | a pick among 1..max + 1, or 1..max when max equals the count; ties are allowed. There are no up/down swaps |
| Set the groups of a teacher | `custom_getui_EditarProfesorParaActividad.php` (`DameGruposDePlantilla`: same plan as the period, activity type, specialty and level, `dev/sql/organizacion/dame_grupos_de_plantilla.sql`), `form_edit_pxact.tpl`, `…_pxact.js:38-60` | checkboxes; unchecking never removes (Q-16, a defect that is not reproduced) |
| Remove | `profesores_asignados.tpl:28-41`, `lugares_asignados.tpl:28+`; `…_pxact.js:61-67`, `…_lxact.js:131-137` | **after a confirmation** ("¿Está seguro que desea eliminar?") |
| Room priority upkeep | triggers `nivela_prioridad_lxact*` (PAR-055, Q-07) | in Drupal, `RoomPriorityHooks` (D9c) |
| Template order | `custom_SubePlantilla` / `custom_BajaPlantilla` (PAR-035) | in Drupal, `OrderUpkeep` (D13) |

The legacy screens show no capacity warning (PAR-062, a #70 decision). The capacity and the enrolment are shown as information only.

### 1.2 VB6 (`rafageist/ancora-vb6`), as a reference

- **`frm/ancora_goh_traditional_frmDatosAsignacionRxAct.frm`**
  - The window has a template tree view and Rooms, Teachers, Other resources and Tools tabs.
  - Everything is button-driven: `>` `<` `>>` `<<` `^` `v`. **There is no drag-and-drop handler.**
  - Teacher priority is the list order: `^`/`v` swap neighbours (lines 975-1009).
  - The groups of a teacher are a multi-select list, filtered by specialty, level and classification (lines 1422-1444).
  - Removals ask for confirmation.
  - "Clonar profesores / lugares" copies the assignments to other activities with filters (lines 894-914).
  - Nothing is saved until "Guardar y terminar".
- **`frm/ancora_goh_traditional_frmDatosActividad.frm`** edits one placed activity: "Fija", room, teacher and possible starts. It has no assignment logic.

Not taken from VB6:
- the swap priorities, because PHP picks a value;
- the unsaved in-memory session, because every Drupal change is saved at once.

The copy ("Clonar") is a candidate for a later slice.

## 2. Design

- **Panels.**
  - **Template:** a summary, the way back and the template's groups as draggable chips.
  - **Teachers / Rooms:** one lane per offered priority, and one card per assignment with its priority select, groups, details and remove.
  - **Available:** the owner's unassigned teachers (and rooms with capacity), each with an "Assign" button.
  - A search field filters on submit, and the script filters as the user types.
  - The template list keeps the D13 up and down arrows (`ancora_academic.order.move`, CSRF token).
  - The three panels stack on screens narrower than 60rem.
- **One source of truth.**
  - Every change is a submit button of a Drupal form, with the form token as CSRF protection and the template's update access.
  - It runs through `TemplateAssignments` and the Entity API constraints (`AncoraUniqueInScope`, `AncoraSameCreator`, `AncoraEligibleGroups`), on stable ids.
  - The page is rebuilt from the database after every change, and the browser keeps no state of its own.
- **Drag and drop** (`js/assignment-editor.js`, vanilla JS plus core `once`, `drupal.dialog.ajax` and `announce`):
  - a drop fills hidden `drop[op|item|target|value]` fields and presses the same form's hidden button;
  - the server maps the dropped ids to an operation and refuses anything else;
  - nothing happens on hover.
- **Keyboard and touch.**
  - Every drag has a visible button equivalent ("Assign", "Set priority", "Add group", "×", "Remove").
  - Controls have accessible names (`aria-label`), and panels are labelled regions.
  - After a change the page returns to the changed card (URL fragment plus focus).
  - Touch screens use the buttons.
- **Feedback.**
  - Success goes to a `role="status"` region, and failures to a `role="alert"` region and a small dialog. Focus returns to the card when the dialog closes.
  - The details of an assignment open in a core dialog (`use-ajax`), or as a page without JavaScript.
- **Safety.**
  - Removal asks first: a confirm and cancel step on the card, or the dialog for a drop. The question names the record that will be removed.
  - "Undo" re-creates the last removal with its priority and groups (private tempstore). The offer is cleared by the next successful change.
  - The form carries a token of the template's assignments. A change made meanwhile, in another window or by another user, is refused with an explanation, and the page shows the current state.

## 3. Engine input

- The priorities and groups set in the editor are the exported `profesor_para_actividad.prioridad`, `profesor_para_grupo` and `lugar_para_actividad.prioridad` (Kernel `TemplateAssignmentsTest`).
- A room removal closes the template's priority gap, as `nivela_prioridad_lxact` does.
- The D13 template move changes the order the engine receives: the template's engine id follows its order, so a teacher assignment of the moved template reaches the engine on the new order.

## 4. Tests

- **Kernel** `TemplateAssignmentsTest`: the operations with the legacy values; duplicates; out-of-range or malformed priorities; foreign groups; another planner's teacher and room; other templates' assignments; undo; the engine rows.
- **Functional** `AssignmentEditorUiTest`:
  - every action through its button;
  - the drop path through the same fields the script fills: assign, priority, group, remove;
  - invalid drops;
  - the confirmation and the undo;
  - the concurrent-change guard;
  - details, search and the way back;
  - the D13 arrows;
  - another planner, a viewer, anonymous and forged ids (403 or 404).
- **FunctionalJavascript** is not possible in CI: `phpunit.xml.dist` sets no WebDriver (`MINK_DRIVER_ARGS_WEBDRIVER` is empty), and the workflows install no browser. The drag path is therefore covered at the form level, as above, plus a scripted real-browser check: Playwright with Chromium on a disposable site (records in the #122 comment). It runs:
  - real drags: assign, add group, change priority, remove with the dialog;
  - the details dialog;
  - the keyboard path;
  - the conflict dialog;
  - a 390 px mobile view.
