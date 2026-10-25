# Proposed role and permission matrix for events

Status: **proposal only.** Nothing here is applied: `bin/security.conf`,
the seed data and the code are unchanged. Issue #14 / PR #20 follow-up.

Maintainer decision this answers: *"Retain deny-by-default. Prepare a
proposed role/permission matrix covering the approximately 145 reachable
events. Do not grant broad permissions merely to restore old behavior."*

Tags: **[verified]** = read in the code on this branch; **[inferred]** =
deduced, not traced end to end.

Related: `php-auth-hardening.md` §2 (authorization model) and §6 (SQL built
by interpolation), `architecture.md` §3 (event wiring, on branch
`docs/issue-2-modernization-assessment`).

## 1. Method and the reachable event set

### 1.1 How the set was built

1. **Handlers.** `Application::$events_folders` lists six folders under
   `control/`. Each `<name>.php` there is the handler of event `<name>`
   (POST key `app_event_<name>`). There are **406** handlers: `data/add`
   61, `data/set` 61, `data/del` 61, `view/gallery` 61, `view/combo` 61,
   `custom` 101. No name occurs in two folders [verified].
2. **Client references.** Every literal `app_event_<name>` under `www/js/app`
   was collected: 149 distinct names [verified]. No event name is built
   dynamically in `www/js/app`, `view` or `control` (no `"app_event_" +`
   concatenation) [verified]. The only other client occurrence is
   `view/custom/form_restricciones.tpl:56` (`name="app_event_<?php echo
   $evento; ?>"`); that template is included by nothing, so it adds no
   event [verified].
3. **Loaded scripts.** Every JS file that posts an event is imported by
   `view/custom/header.tpl` [verified].
4. **Join.** 149 referenced names ∩ 406 handlers = **145 reachable events**
   [verified]: `custom` 97, `data/add` 16, `data/set` 15, `data/del` 17,
   `view/gallery` 0, `view/combo` 0.
5. **Call sites.** For every JS function that posts an event, a caller was
   searched in `www/js/app`, `view` and `control` (by function name, one
   level deep). Three of the 145 have a JS wrapper that nothing calls:
   `custom_getui_NuevoGrupo` (`forms.nuevoGrupo`),
   `custom_getui_OtrosRecursosAsignadosDetalles`
   (`forms.rxact.editGrupoRecursos`) and `del_ProfesorParaGrupo`
   (`server.recursos.eliminarProfesorParaGrupo`) [verified by grep]. Deeper
   call chains (a caller that is itself never called) were not traced, so
   "reachable" means "a loaded script posts it", an upper bound on what the
   UI really uses [inferred].
6. **Menus.** `view/custom/app.tpl:170-175` shows six start-menu entries,
   each only if `$permisos[<p>]` or `$permisos['todos']` is set [verified].
   Each entry opens one window, which is how events were grouped into
   modules (§3). The desktop shell itself posts no event: there is no
   `app_event_` literal in `app.tpl`, `ancoragoh.js` or
   `ancoragoh_client.js` [verified].

### 1.2 Result

- **145 reachable events**, matching the ≈145 of `architecture.md` §3
  exactly (it counted 16 + 15 + 17 + 97 with the same method).
- 6 of the 145 are the public timetable viewer
  (`Application::$anonymous_events`, `browsers/ancoragoh_client_browsers_publico.js`).
  The viewer is opened only from the login page, and only when
  `public_viewer_anonymous = true` (`view/custom/authform.tpl`). No script
  of the logged-in desktop posts these events [verified].
- **261 handlers are unreachable** from the client: `data/add` 45,
  `data/set` 46, `data/del` 44, `view/gallery` 61, `view/combo` 61,
  `custom` 4 (`custom_getui_DesgloseSelectPlanificacion`,
  `custom_getui_NuevaEspecialidad`, `custom_set_Restricciones`,
  `get_minibrowser_desgloses_planificaciones`) [verified]. They include 18
  generated handlers over the `seguridad` tables: `{add,set,del}_` ×
  `Usuario`, `Rol`, `RolDeUsuario`, `Permiso`, `Host`, `Traza` [verified].
  They import `model.data.<X>Model`, but these models live in
  `model/security/`, so the handlers are probably broken anyway
  [verified: file missing; inferred: effect]. This proposal lists **none**
  of the 261: they stay denied to everyone but `todos`.
- 4 names are posted by the client but have **no handler**:
  `custom_getui_DesglosePropiedades`, `custom_getui_EditarGrupo`,
  `custom_getui_EditarPlantillaDeActividad`, `get_browser_Especialidades`
  [verified]. Their JS wrappers have no caller either [verified by grep].
  The dispatcher includes nothing for them. They need no entry.

## 2. Existing permission vocabulary

| Source | Names | Notes |
| --- | --- | --- |
| Seed data `dev/sql/datos/datos_por_defecto.sql` [verified] | roles `superusuario`, `administrador`, `planificador`, `profesor`, `invitado`; one permission row: `todos` → `superusuario` | The four non-super roles have **no** permission rows. One seeded user holds `superusuario`. `dev/sql/result.sql` carries the same single row. |
| Start menu `view/custom/app.tpl:170` [verified] | `ver_horarios` (Explorador de horarios), `gestionar_planificaciones` (Configuración del tiempo), `gestionar_brigadas` (Organización de los estudiantes), `gestionar_periodos` (Configuración de actividades), `gestionar_recursos` (Recursos), `generador` (Generar horarios) | Only gates menu visibility, not events. |
| `bin/security.conf` (2010 entries, renamed in #14) [verified] | `planificar`, `organizar_estudiantes`, `editar_especialidad`, `configurar_tiempo`, `editar_planificacion` | Two entries, each needing three permissions. |
| Bypass `Application::isEventAllowed()` [verified] | `todos` | Exact, case-sensitive. |
| `seguridad.tiene_permiso()` / `SeguridadFunctions::tiene_permiso` [verified] | any | Not used by the dispatcher. |

Mismatches:

1. **The menu names and the `security.conf` names do not overlap.** Neither
   set is seeded. A non-`todos` user with `gestionar_planificaciones` sees
   the "Configuración del tiempo" menu but can open no window. A user with
   the three `security.conf` names for `get_form_edit_Planificacion` can
   open that one form but sees no menu to reach it [verified].
2. **The 2010 entries never matched a handler** (`get_form_editEspecialidad`
   vs `get_form_edit_Especialidad`). So the three-permission scheme was
   never enforced or tested, and no other event used it [verified].
3. **`gestionar_periodos` is misnamed.** It gates "Configuración de
   actividades" (`browsers.desgloses.show`: breakdowns, subjects, activity
   templates, resource assignment). Periods are edited under "Configuración
   del tiempo" (`gestionar_planificaciones`) [verified].
4. `organizar_estudiantes` ≈ `gestionar_brigadas` and `configurar_tiempo` ≈
   `gestionar_planificaciones` by meaning. `planificar`,
   `editar_especialidad` and `editar_planificacion` have no menu
   counterpart [inferred].
5. Case: the dispatcher is case-sensitive. `dev/tests/security/run.php`
   checks that `TODOS` does not bypass [verified].

**Proposal: use the six menu names as the module permissions.** Then the
server and the menu agree, and a user sees exactly the windows they can
use. Retire the five 2010 names: replace the two existing entries with
`gestionar_*` (rows marked in §4). Keep `gestionar_periodos` for
compatibility; the rename is open question Q2.

## 3. Proposed permissions and roles

### 3.1 Permissions

| Permission | Status | Grants |
| --- | --- | --- |
| `ver_horarios` | existing (menu) | Timetable explorer: read timetables of groups, teachers, places, resources |
| `gestionar_planificaciones` | existing (menu) | Plans and periods: browse, create, edit |
| `gestionar_brigadas` | existing (menu) | Specialties, levels, groups of students (`brigada`), activity groups (`grupo`) and their membership |
| `gestionar_periodos` | existing (menu; misnamed, Q2) | Breakdowns (`desglose`), subjects, activity classifications, activity templates, assignment of teachers/places/resources and their priorities |
| `gestionar_recursos` | existing (menu) | Teachers, places, other resources |
| `generador` | existing (menu) | Generator window, run and cancel generation |
| `editar_restricciones` | **NEW** | Time/space constraints the generator honours: period availability (`restriccion_de_periodo`), periodicity, priority zones (`zpriori`), travel cost between places. Always required **together with** the module permission of the window that holds the form. |
| `eliminar_datos` | **NEW** | Deleting master records (12 `del_*` events), required **together with** the module permission. These deletes cascade (§3.3). |
| `administrar_seguridad` | **NEW, reserved** | No reachable event exists. Users, roles and permissions are managed only by CLI scripts (`bin/adduser.php`, `bin/setpassword.php`, `bin/deluser*.php`, `bin/showusers.php`) and SQL [verified: no client script posts a `seguridad` event; `bin/adduser.php` assigns no roles]. Reserve the name for a future admin UI. Do not list any `seguridad` handler now. |
| `todos` | existing | Unchanged: superuser bypass of every handler, including the 261 unreachable ones. Proposed for break-glass use only (Q5). |

### 3.2 Roles

A role is a bundle of `seguridad.permiso` rows. A user may hold several
roles. The dispatcher takes the union and then requires **all** permissions
of an event.

| Proposed role | Permissions | Maps to seed role |
| --- | --- | --- |
| R1 Timetable viewer | `ver_horarios` | `invitado`, `profesor` |
| R2 Time planner | `gestionar_planificaciones` | part of `planificador` |
| R3 Organization data editor | `gestionar_brigadas`, `gestionar_recursos` | part of `planificador` |
| R4 Activity configurator | `gestionar_periodos` | part of `planificador` |
| R5 Restrictions editor | `editar_restricciones` (add-on: effective only with R2, R3 or R4) | part of `planificador` |
| R6 Generator operator | `generador`, `ver_horarios` | part of `planificador` |
| R7 Data steward | `eliminar_datos` (add-on to R2/R3/R4) | `administrador` |
| R8 Security administrator | `administrar_seguridad` (reserved, no event). Today: shell and DB access | `administrador` (future) |
| Superuser | `todos` | `superusuario` (unchanged) |

Proposed seed bundles (illustration only, not applied): `planificador` =
R1 + R2 + R3 + R4 + R5 + R6. `administrador` = `planificador` + R7 (+ R8
when an admin UI exists). `profesor` = `invitado` = R1.

### 3.3 Rules applied in the matrix

- **Module permission per window.** Each event takes the permission of the
  start-menu window whose script posts it. Opening a window needs the same
  permission as its menu entry.
- **Reads and writes share the module permission.** There is no read-only
  access to configuration windows (Q3).
- **Deletes.** The 12 `del_*` events that remove a master record need
  `eliminar_datos` plus the module permission. Through `ON DELETE CASCADE`
  they remove dependent data: e.g. `periodo` → `desglose`,
  `restriccion_de_periodo`, `higiene_*`; `profesor` → its assignment rows
  [verified in `dev/sql/**/*.sql`]. The 5 `del_*ParaActividad` /
  `del_*ParaGrupo` events delete an **assignment row**, not a master
  record. They are ordinary assignment edits in the "Configuración de
  actividades" window and need only `gestionar_periodos` (Q4).
- **Generator.** `generar` and `cancelar_generacion` need `generador`, a
  permission specific to the generator (never `array()`).
- **Security.** No reachable event touches `seguridad.*` [verified: no
  `seguridad` reference in `control/custom`, `view/custom` or
  `model/data/PublicFunctions.php`; the 18 `seguridad` handlers are
  unreachable]. None is listed.
- **`array()` (any authenticated user): none proposed.** The only candidate
  would be a lookup that every logged-in screen needs. None exists: the
  desktop shell posts no event (§1.1 step 6), and every lookup belongs to
  exactly one window. The public viewer's 6 events are not posted by the
  logged-in desktop. With the switch off they are better left unlisted
  than opened to all users.
- **Unused events stay unlisted.** The 3 events with no caller (§1.1 step
  5) are not listed. If a missed call path exists, the request fails closed
  and the event can be added with the permission shown in the matrix.

### 3.4 What permissions do not fix

These findings affect the value of any matrix. They are reported, not
solved, here.

- **No per-record ownership check.** Data is partitioned by a `creador`
  column. Reads filter by `creador = '$_SESSION[user]'`, and `add_*` forces
  `creador` to the session user through `control/data/fdms/fdm_*_creador.php`
  [verified]. But `set_*`, `del_*` and `get_form_edit_*` take the id from
  POST and never check `creador` (e.g. `del_Profesor.php`,
  `set_Profesor.php`) [verified]. So any user holding the module permission
  can read, edit or delete another user's records by id. `set_*` also
  rewrites `creador` to the editor, which moves the record to them
  [verified for `set_Profesor` via `fdm_Profesor_creador.php`; inferred for
  the other entities that have an fdm].
- **No tenant filter.** `custom_getui_ExploradorListProfesores` and
  `custom_getui_ExploradorListLugares` use `getAll()` without the
  `creador` filter that the other lists use [verified].
- **SQL injection.** Nearly every write event still interpolates POST
  values into SQL (`php-auth-hardening.md` §6). A permission narrows *who*
  can inject, not *whether*. `generar` interpolates five numeric parameters
  unquoted (`PublicFunctions.php:1555`) and runs with `set_time_limit(0)`
  [verified].

## 4. Matrix

Columns: event | handler file | class (read = renders a window, form or
list; write = changes data; delete = removes a master record; generate =
runs or stops the generator) | proposed required permissions (all
required) | notes. Notes come from grep of the handler file only [verified];
SQL in included templates was not traced unless named. "(§6)" =
`php-auth-hardening.md` §6.

Totals: 145 rows; 136 listed; 0 as `array()`; 6 public-viewer events and 3
uncalled events unlisted. By class: 79 read, 52 write, 12 delete, 2
generate.

| Event | Handler | Class | Proposed permissions | Notes |
| --- | --- | --- | --- | --- |
| `custom_getui_ExploradorHorarios` | `control/custom/custom_getui_ExploradorHorarios.php` | read | `ver_horarios` | interpolates `$_SESSION['user']`; Explorer window (menu "Explorador de horarios") |
| `custom_getui_TreeBrigadas` | `control/custom/custom_getui_TreeBrigadas.php` | read | `ver_horarios` | explorer tree; `tree_brigadas.tpl` runs SQL interpolating DB values (second-order) |
| `custom_getui_ExploradorListProfesores` | `control/custom/custom_getui_ExploradorListProfesores.php` | read | `ver_horarios` | uses `getAll()`: lists teachers of every `creador`, not only the user's |
| `custom_getui_ExploradorListLugares` | `control/custom/custom_getui_ExploradorListLugares.php` | read | `ver_horarios` | uses `getAll()`: lists places of every `creador` |
| `custom_getui_ExploradorListOtrosRecursos` | `control/custom/custom_getui_ExploradorListOtrosRecursos.php` | read | `ver_horarios` | interpolates `$_SESSION['user']`; filtered by `creador` |
| `get_minibrowser_horarios` | `control/custom/get_minibrowser_horarios.php` | read | `ver_horarios` | reads `$_POST` into SQL via models/functions (§6); 4 SQL strings interpolate POST ids directly; no ownership check |
| `get_browser_publico` | `control/custom/get_browser_publico.php` | read | not listed (anonymous allowlist only) | public viewer; desktop never posts it |
| `get_minibrowser_publico` | `control/custom/get_minibrowser_publico.php` | read | not listed (anonymous allowlist only) | reads `$_POST` into SQL via models/functions (§6); public viewer; bound parameters |
| `get_public_treeBrigadas` | `control/custom/get_public_treeBrigadas.php` | read | not listed (anonymous allowlist only) | public viewer; second-order SQL in template |
| `get_public_list_profesores` | `control/custom/get_public_list_profesores.php` | read | not listed (anonymous allowlist only) | public viewer |
| `get_public_list_lugares` | `control/custom/get_public_list_lugares.php` | read | not listed (anonymous allowlist only) | public viewer |
| `get_public_list_otros_recursos` | `control/custom/get_public_list_otros_recursos.php` | read | not listed (anonymous allowlist only) | interpolates `$_SESSION['user']`; public viewer |
| `get_browser_Planificaciones` | `control/custom/get_browser_Planificaciones.php` | read | `gestionar_planificaciones` | interpolates `$_SESSION['user']`; menu "Configuración del tiempo" |
| `get_minibrowser_planificaciones` | `control/custom/get_minibrowser_planificaciones.php` | read | `gestionar_planificaciones` | interpolates `$_SESSION['user']` |
| `get_diagram_gantt_planificaciones` | `control/custom/get_diagram_gantt_planificaciones.php` | read | `gestionar_planificaciones` | interpolates `$_SESSION['user']` |
| `get_browser_Periodos` | `control/custom/get_browser_Periodos.php` | read | `gestionar_planificaciones` | reads `$_POST` into SQL via models/functions (§6); interpolates `$_SESSION['user']` |
| `get_form_new_Planificacion` | `control/custom/get_form_new_Planificacion.php` | read | `gestionar_planificaciones` |  |
| `get_form_edit_Planificacion` | `control/custom/get_form_edit_Planificacion.php` | read | `gestionar_planificaciones` | reads `$_POST` into SQL via models/functions (§6); replaces current entry `('planificar','configurar_tiempo','editar_planificacion')` |
| `get_form_new_Periodo` | `control/custom/get_form_new_Periodo.php` | read | `gestionar_planificaciones` | reads `$_POST` into SQL via models/functions (§6); interpolates `$_SESSION['user']` |
| `get_form_edit_Periodo` | `control/custom/get_form_edit_Periodo.php` | read | `gestionar_planificaciones` | reads `$_POST` into SQL via models/functions (§6); interpolates `$_SESSION['user']` |
| `add_Planificacion` | `control/data/add/add_Planificacion.php` | write | `gestionar_planificaciones` | POST fields into generated `add()` SQL (§6) |
| `set_Planificacion` | `control/data/set/set_Planificacion.php` | write | `gestionar_planificaciones` | POST id + fields into generated setters (§6); no ownership check |
| `del_Planificacion` | `control/data/del/del_Planificacion.php` | delete | `gestionar_planificaciones`, `eliminar_datos` | POST id into generated `remove()` (§6); no ownership check; cascades to periods and their restrictions |
| `add_Periodo` | `control/data/add/add_Periodo.php` | write | `gestionar_planificaciones` | POST fields into generated `add()` SQL (§6) |
| `set_Periodo` | `control/data/set/set_Periodo.php` | write | `gestionar_planificaciones` | POST id + fields into generated setters (§6); no ownership check |
| `del_Periodo` | `control/data/del/del_Periodo.php` | delete | `gestionar_planificaciones`, `eliminar_datos` | POST id into generated `remove()` (§6); no ownership check; cascades |
| `get_form_edit_Periodo_restricciones` | `control/custom/get_form_edit_Periodo_restricciones.php` | read | `gestionar_planificaciones`, `editar_restricciones` | reads `$_POST` into SQL via models/functions (§6); interpolates `$_SESSION['user']`; form only used to edit restrictions |
| `custom_set_Restricciones_Periodo` | `control/custom/custom_set_Restricciones_Periodo.php` | write | `gestionar_planificaciones`, `editar_restricciones` | reads `$_POST` into SQL via models/functions (§6); day/shift availability of a period |
| `get_form_edit_Periodo_periodicidad` | `control/custom/get_form_edit_Periodo_periodicidad.php` | read | `gestionar_planificaciones`, `editar_restricciones` | reads `$_POST` into SQL via models/functions (§6); interpolates `$_SESSION['user']` |
| `custom_set_Periodicidades` | `control/custom/custom_set_Periodicidades.php` | write | `gestionar_planificaciones`, `editar_restricciones` | reads `$_POST` into SQL via models/functions (§6); weekdays on which a period runs |
| `get_browser_Organizacion` | `control/custom/get_browser_Organizacion.php` | read | `gestionar_brigadas` | interpolates `$_SESSION['user']`; menu "Organización de los estudiantes" |
| `get_minibrowser_especialidades` | `control/custom/get_minibrowser_especialidades.php` | read | `gestionar_brigadas` | interpolates `$_SESSION['user']` |
| `get_minibrowser_niveles` | `control/custom/get_minibrowser_niveles.php` | read | `gestionar_brigadas` | interpolates `$_SESSION['user']` |
| `get_browser_brigadas` | `control/custom/get_browser_brigadas.php` | read | `gestionar_brigadas` | interpolates `$_SESSION['user']` |
| `get_browser_Brigadas_filtradas` | `control/custom/get_browser_Brigadas_filtradas.php` | read | `gestionar_brigadas` | reads `$_POST` into SQL via models/functions (§6) |
| `get_browser_GruposXClasif` | `control/custom/get_browser_GruposXClasif.php` | read | `gestionar_brigadas` | reads `$_POST` into SQL via models/functions (§6); interpolates `$_SESSION['user']` |
| `get_browser_GruposXClasifFiltrados` | `control/custom/get_browser_GruposXClasifFiltrados.php` | read | `gestionar_brigadas` | reads `$_POST` into SQL via models/functions (§6) |
| `custom_getui_GruposXClasif` | `control/custom/custom_getui_GruposXClasif.php` | read | `gestionar_brigadas` | reads `$_POST` into SQL via models/functions (§6); interpolates `$_SESSION['user']` |
| `custom_getui_GruposXClasifFiltrados` | `control/custom/custom_getui_GruposXClasifFiltrados.php` | read | `gestionar_brigadas` | reads `$_POST` into SQL via models/functions (§6) |
| `custom_getui_NuevoGrupo` | `control/custom/custom_getui_NuevoGrupo.php` | read | not listed (no caller found; would be `gestionar_brigadas`) | passes `$_POST` values to the template (template SQL not traced); JS wrapper `forms.nuevoGrupo` has no caller found; includes `../tpl/form_grupo.tpl`, a path that does not exist |
| `get_form_new_Especialidad` | `control/custom/get_form_new_Especialidad.php` | read | `gestionar_brigadas` |  |
| `get_form_edit_Especialidad` | `control/custom/get_form_edit_Especialidad.php` | read | `gestionar_brigadas` | reads `$_POST` into SQL via models/functions (§6); replaces current entry `('planificar','organizar_estudiantes','editar_especialidad')` |
| `get_form_new_Nivel` | `control/custom/get_form_new_Nivel.php` | read | `gestionar_brigadas` |  |
| `get_form_edit_Nivel` | `control/custom/get_form_edit_Nivel.php` | read | `gestionar_brigadas` | reads `$_POST` into SQL via models/functions (§6) |
| `get_form_new_Brigada` | `control/custom/get_form_new_Brigada.php` | read | `gestionar_brigadas` |  |
| `get_form_edit_Brigada` | `control/custom/get_form_edit_Brigada.php` | read | `gestionar_brigadas` | reads `$_POST` into SQL via models/functions (§6) |
| `get_form_new_grupo` | `control/custom/get_form_new_grupo.php` | read | `gestionar_brigadas` | passes `$_POST` values to the template (template SQL not traced) |
| `get_form_edit_grupo` | `control/custom/get_form_edit_grupo.php` | read | `gestionar_brigadas` | reads `$_POST` into SQL via models/functions (§6) |
| `add_Especialidad` | `control/data/add/add_Especialidad.php` | write | `gestionar_brigadas` | POST fields into generated `add()` SQL (§6) |
| `set_Especialidad` | `control/data/set/set_Especialidad.php` | write | `gestionar_brigadas` | POST id + fields into generated setters (§6); no ownership check |
| `del_Especialidad` | `control/data/del/del_Especialidad.php` | delete | `gestionar_brigadas`, `eliminar_datos` | POST id into generated `remove()` (§6); no ownership check |
| `add_Nivel` | `control/data/add/add_Nivel.php` | write | `gestionar_brigadas` | POST fields into generated `add()` SQL (§6) |
| `set_Nivel` | `control/data/set/set_Nivel.php` | write | `gestionar_brigadas` | POST id + fields into generated setters (§6); no ownership check |
| `del_Nivel` | `control/data/del/del_Nivel.php` | delete | `gestionar_brigadas`, `eliminar_datos` | POST id into generated `remove()` (§6); no ownership check |
| `add_Brigada` | `control/data/add/add_Brigada.php` | write | `gestionar_brigadas` | POST fields into generated `add()` SQL (§6) |
| `set_Brigada` | `control/data/set/set_Brigada.php` | write | `gestionar_brigadas` | POST id + fields into generated setters (§6); no ownership check |
| `del_Brigada` | `control/data/del/del_Brigada.php` | delete | `gestionar_brigadas`, `eliminar_datos` | POST id into generated `remove()` (§6); no ownership check |
| `add_Grupo` | `control/data/add/add_Grupo.php` | write | `gestionar_brigadas` | POST fields into generated `add()` SQL (§6) |
| `set_Grupo` | `control/data/set/set_Grupo.php` | write | `gestionar_brigadas` | POST id + fields into generated setters (§6); no ownership check |
| `del_Grupo` | `control/data/del/del_Grupo.php` | delete | `gestionar_brigadas`, `eliminar_datos` | POST id into generated `remove()` (§6); no ownership check |
| `custom_MoverBrigadaDeGrupo` | `control/custom/custom_MoverBrigadaDeGrupo.php` | write | `gestionar_brigadas` | reads `$_POST` into SQL via models/functions (§6); `PublicFunctions::MueveOCreaBrigadaDeGrupo` |
| `custom_EliminaBrigadaDeGrupo` | `control/custom/custom_EliminaBrigadaDeGrupo.php` | write | `gestionar_brigadas` | reads `$_POST` into SQL via models/functions (§6); removes a membership (`removeBy` with POST values), not an entity |
| `get_browser_Desgloses` | `control/custom/get_browser_Desgloses.php` | read | `gestionar_periodos` | interpolates `$_SESSION['user']`; menu "Configuración de actividades" |
| `get_browser_DesgloseDetalles` | `control/custom/get_browser_DesgloseDetalles.php` | read | `gestionar_periodos` | reads `$_POST` into SQL via models/functions (§6) |
| `get_minibrowser_asignaturas` | `control/custom/get_minibrowser_asignaturas.php` | read | `gestionar_periodos` | interpolates `$_SESSION['user']`; `minibrowser_asignaturas.tpl` also interpolates |
| `get_minibrowser_clasifact` | `control/custom/get_minibrowser_clasifact.php` | read | `gestionar_periodos` | interpolates `$_SESSION['user']` |
| `get_form_edit_desglose` | `control/custom/get_form_edit_desglose.php` | read | `gestionar_periodos` | reads `$_POST` into SQL via models/functions (§6) |
| `get_form_new_asignatura` | `control/custom/get_form_new_asignatura.php` | read | `gestionar_periodos` | passes `$_POST` values to the template (template SQL not traced) |
| `get_form_edit_asignatura` | `control/custom/get_form_edit_asignatura.php` | read | `gestionar_periodos` | reads `$_POST` into SQL via models/functions (§6) |
| `get_form_new_clasifact` | `control/custom/get_form_new_clasifact.php` | read | `gestionar_periodos` | interpolates `$_SESSION['user']` |
| `get_form_edit_clasifact` | `control/custom/get_form_edit_clasifact.php` | read | `gestionar_periodos` | reads `$_POST` into SQL via models/functions (§6) |
| `get_form_new_plantilla` | `control/custom/get_form_new_plantilla.php` | read | `gestionar_periodos` | reads `$_POST` into SQL via models/functions (§6); interpolates `$_SESSION['user']` |
| `get_form_edit_plantilla` | `control/custom/get_form_edit_plantilla.php` | read | `gestionar_periodos` | reads `$_POST` into SQL via models/functions (§6); interpolates `$_SESSION['user']` |
| `custom_getui_AsignacionRecursos` | `control/custom/custom_getui_AsignacionRecursos.php` | read | `gestionar_periodos` | passes `$_POST` values to the template (template SQL not traced) |
| `custom_getui_ProfesoresAsignados` | `control/custom/custom_getui_ProfesoresAsignados.php` | read | `gestionar_periodos` | reads `$_POST` into SQL via models/functions (§6) |
| `custom_getui_LugaresAsignados` | `control/custom/custom_getui_LugaresAsignados.php` | read | `gestionar_periodos` | reads `$_POST` into SQL via models/functions (§6) |
| `custom_getui_OtrosRecursosAsignados` | `control/custom/custom_getui_OtrosRecursosAsignados.php` | read | `gestionar_periodos` | reads `$_POST` into SQL via models/functions (§6) |
| `custom_getui_OtrosRecursosAsignadosDetalles` | `control/custom/custom_getui_OtrosRecursosAsignadosDetalles.php` | read | not listed (no caller found; would be `gestionar_periodos`) | reads `$_POST` into SQL via models/functions (§6); JS wrapper `forms.rxact.editGrupoRecursos` has no caller found |
| `custom_getui_NuevoProfesorParaActividad` | `control/custom/custom_getui_NuevoProfesorParaActividad.php` | read | `gestionar_periodos` | reads `$_POST` into SQL via models/functions (§6) |
| `custom_getui_NuevoLugarParaActividad` | `control/custom/custom_getui_NuevoLugarParaActividad.php` | read | `gestionar_periodos` | reads `$_POST` into SQL via models/functions (§6) |
| `custom_getui_EditarProfesorParaActividad` | `control/custom/custom_getui_EditarProfesorParaActividad.php` | read | `gestionar_periodos` | reads `$_POST` into SQL via models/functions (§6) |
| `custom_getui_EditarPrioridadLxact` | `control/custom/custom_getui_EditarPrioridadLxact.php` | read | `gestionar_periodos` | reads `$_POST` into SQL via models/functions (§6) |
| `custom_getui_EditarPrioridadRxact` | `control/custom/custom_getui_EditarPrioridadRxact.php` | read | `gestionar_periodos` | reads `$_POST` into SQL via models/functions (§6) |
| `get_form_add_rxact` | `control/custom/get_form_add_rxact.php` | read | `gestionar_periodos` | reads `$_POST` into SQL via models/functions (§6) |
| `get_form_edit_pxact_prioridad` | `control/custom/get_form_edit_pxact_prioridad.php` | read | `gestionar_periodos` | reads `$_POST` into SQL via models/functions (§6) |
| `add_Asignatura` | `control/data/add/add_Asignatura.php` | write | `gestionar_periodos` | POST fields into generated `add()` SQL (§6) |
| `set_Asignatura` | `control/data/set/set_Asignatura.php` | write | `gestionar_periodos` | POST id + fields into generated setters (§6); no ownership check |
| `del_Asignatura` | `control/data/del/del_Asignatura.php` | delete | `gestionar_periodos`, `eliminar_datos` | POST id into generated `remove()` (§6); no ownership check |
| `add_ClasificacionDeActividad` | `control/data/add/add_ClasificacionDeActividad.php` | write | `gestionar_periodos` | POST fields into generated `add()` SQL (§6) |
| `set_ClasificacionDeActividad` | `control/data/set/set_ClasificacionDeActividad.php` | write | `gestionar_periodos` | POST id + fields into generated setters (§6); no ownership check |
| `del_ClasificacionDeActividad` | `control/data/del/del_ClasificacionDeActividad.php` | delete | `gestionar_periodos`, `eliminar_datos` | POST id into generated `remove()` (§6); no ownership check |
| `set_ClasifAct_subeprioridad` | `control/custom/set_ClasifAct_subeprioridad.php` | write | `gestionar_periodos` | reads `$_POST` into SQL via models/functions (§6) |
| `set_ClasifAct_bajaprioridad` | `control/custom/set_ClasifAct_bajaprioridad.php` | write | `gestionar_periodos` | reads `$_POST` into SQL via models/functions (§6) |
| `set_Desglose` | `control/data/set/set_Desglose.php` | write | `gestionar_periodos` | POST id + fields into generated setters (§6); no ownership check |
| `add_PlantillaDeActividad` | `control/data/add/add_PlantillaDeActividad.php` | write | `gestionar_periodos` | POST fields into generated `add()` SQL (§6) |
| `set_PlantillaDeActividad` | `control/data/set/set_PlantillaDeActividad.php` | write | `gestionar_periodos` | POST id + fields into generated setters (§6); no ownership check |
| `del_PlantillaDeActividad` | `control/data/del/del_PlantillaDeActividad.php` | delete | `gestionar_periodos`, `eliminar_datos` | POST id into generated `remove()` (§6); no ownership check |
| `custom_SubePlantilla` | `control/custom/custom_SubePlantilla.php` | write | `gestionar_periodos` | reads `$_POST` into SQL via models/functions (§6) |
| `custom_BajaPlantilla` | `control/custom/custom_BajaPlantilla.php` | write | `gestionar_periodos` | reads `$_POST` into SQL via models/functions (§6) |
| `add_ProfesorParaActividad` | `control/data/add/add_ProfesorParaActividad.php` | write | `gestionar_periodos` | POST fields into generated `add()` SQL (§6); assignment |
| `set_ProfesorParaActividad` | `control/data/set/set_ProfesorParaActividad.php` | write | `gestionar_periodos` | POST id + fields into generated setters (§6); no ownership check; assignment |
| `del_ProfesorParaActividad` | `control/data/del/del_ProfesorParaActividad.php` | write | `gestionar_periodos` | POST id into generated `remove()` (§6); no ownership check; unassigns (association row), see §3.3 |
| `set_prioridad_pxact` | `control/custom/set_prioridad_pxact.php` | write | `gestionar_periodos` | reads `$_POST` into SQL via models/functions (§6) |
| `custom_ActualizaProfesorParaGrupo` | `control/custom/custom_ActualizaProfesorParaGrupo.php` | write | `gestionar_periodos` | reads `$_POST` into SQL via models/functions (§6) |
| `del_ProfesorParaGrupo` | `control/data/del/del_ProfesorParaGrupo.php` | write | not listed (no caller found; would be `gestionar_periodos`) | POST id into generated `remove()` (§6); no ownership check; unassigns; JS wrapper `server.recursos.eliminarProfesorParaGrupo` has no caller found |
| `custom_EliminarGruposDeProfesor` | `control/custom/custom_EliminarGruposDeProfesor.php` | write | `gestionar_periodos` | reads `$_POST` into SQL via models/functions (§6); unassigns (`removeBy` with POST value) |
| `add_LugarParaActividad` | `control/data/add/add_LugarParaActividad.php` | write | `gestionar_periodos` | POST fields into generated `add()` SQL (§6); assignment |
| `set_LugarParaActividad` | `control/data/set/set_LugarParaActividad.php` | write | `gestionar_periodos` | POST id + fields into generated setters (§6); no ownership check; priority of an assigned place |
| `del_LugarParaActividad` | `control/data/del/del_LugarParaActividad.php` | write | `gestionar_periodos` | POST id into generated `remove()` (§6); no ownership check; unassigns |
| `add_OtroGrupoDeRecursosParaActividad` | `control/data/add/add_OtroGrupoDeRecursosParaActividad.php` | write | `gestionar_periodos` | POST fields into generated `add()` SQL (§6); assignment |
| `del_OtroGrupoDeRecursosParaActividad` | `control/data/del/del_OtroGrupoDeRecursosParaActividad.php` | write | `gestionar_periodos` | POST id into generated `remove()` (§6); no ownership check; unassigns a resource group |
| `add_OtroRecursoParaActividad` | `control/data/add/add_OtroRecursoParaActividad.php` | write | `gestionar_periodos` | POST fields into generated `add()` SQL (§6); assignment |
| `del_OtroRecursoParaActividad` | `control/data/del/del_OtroRecursoParaActividad.php` | write | `gestionar_periodos` | POST id into generated `remove()` (§6); no ownership check; unassigns |
| `custom_set_PrioridadRxact` | `control/custom/custom_set_PrioridadRxact.php` | write | `gestionar_periodos` | reads `$_POST` into SQL via models/functions (§6) |
| `set_GrupoRecursos_imprescindibles` | `control/custom/set_GrupoRecursos_imprescindibles.php` | write | `gestionar_periodos` | reads `$_POST` into SQL via models/functions (§6) |
| `set_GrupoRecursos_necesarios` | `control/custom/set_GrupoRecursos_necesarios.php` | write | `gestionar_periodos` | reads `$_POST` into SQL via models/functions (§6) |
| `get_form_edit_zpriori` | `control/custom/get_form_edit_zpriori.php` | read | `gestionar_periodos`, `editar_restricciones` | reads `$_POST` into SQL via models/functions (§6); interpolates `$_SESSION['user']`; priority zones of an activity classification |
| `custom_set_zpriori` | `control/custom/custom_set_zpriori.php` | write | `gestionar_periodos`, `editar_restricciones` | reads `$_POST` into SQL via models/functions (§6); `removeBy` + `add` with POST values |
| `get_browser_recursos` | `control/custom/get_browser_recursos.php` | read | `gestionar_recursos` | menu "Recursos" |
| `get_minibrowser_profesores` | `control/custom/get_minibrowser_profesores.php` | read | `gestionar_recursos` | interpolates `$_SESSION['user']` |
| `get_minibrowser_lugares` | `control/custom/get_minibrowser_lugares.php` | read | `gestionar_recursos` | interpolates `$_SESSION['user']` |
| `get_minibrowser_otrosrecursos` | `control/custom/get_minibrowser_otrosrecursos.php` | read | `gestionar_recursos` | interpolates `$_SESSION['user']` |
| `get_form_new_profesor` | `control/custom/get_form_new_profesor.php` | read | `gestionar_recursos` |  |
| `get_form_edit_profesor` | `control/custom/get_form_edit_profesor.php` | read | `gestionar_recursos` | reads `$_POST` into SQL via models/functions (§6) |
| `get_form_new_lugar` | `control/custom/get_form_new_lugar.php` | read | `gestionar_recursos` |  |
| `get_form_edit_lugar` | `control/custom/get_form_edit_lugar.php` | read | `gestionar_recursos` | reads `$_POST` into SQL via models/functions (§6) |
| `get_form_new_otrorecurso` | `control/custom/get_form_new_otrorecurso.php` | read | `gestionar_recursos` |  |
| `get_form_edit_otrorecurso` | `control/custom/get_form_edit_otrorecurso.php` | read | `gestionar_recursos` | reads `$_POST` into SQL via models/functions (§6) |
| `add_Profesor` | `control/data/add/add_Profesor.php` | write | `gestionar_recursos` | POST fields into generated `add()` SQL (§6) |
| `set_Profesor` | `control/data/set/set_Profesor.php` | write | `gestionar_recursos` | POST id + fields into generated setters (§6); no ownership check |
| `del_Profesor` | `control/data/del/del_Profesor.php` | delete | `gestionar_recursos`, `eliminar_datos` | POST id into generated `remove()` (§6); no ownership check; cascades to assignments |
| `add_Lugar` | `control/data/add/add_Lugar.php` | write | `gestionar_recursos` | POST fields into generated `add()` SQL (§6) |
| `set_Lugar` | `control/data/set/set_Lugar.php` | write | `gestionar_recursos` | POST id + fields into generated setters (§6); no ownership check |
| `del_Lugar` | `control/data/del/del_Lugar.php` | delete | `gestionar_recursos`, `eliminar_datos` | POST id into generated `remove()` (§6); no ownership check |
| `add_OtroRecurso` | `control/data/add/add_OtroRecurso.php` | write | `gestionar_recursos` | POST fields into generated `add()` SQL (§6) |
| `set_OtroRecurso` | `control/data/set/set_OtroRecurso.php` | write | `gestionar_recursos` | POST id + fields into generated setters (§6); no ownership check |
| `del_OtroRecurso` | `control/data/del/del_OtroRecurso.php` | delete | `gestionar_recursos`, `eliminar_datos` | POST id into generated `remove()` (§6); no ownership check |
| `get_form_edit_lugar_costos_de_traslado` | `control/custom/get_form_edit_lugar_costos_de_traslado.php` | read | `gestionar_recursos`, `editar_restricciones` | reads `$_POST` into SQL via models/functions (§6); interpolates `$_SESSION['user']`; travel costs between places |
| `custom_set_costotraslado` | `control/custom/custom_set_costotraslado.php` | write | `gestionar_recursos`, `editar_restricciones` | reads `$_POST` into SQL via models/functions (§6); `removeBy` + `add` with POST values |
| `get_win_generador` | `control/custom/get_win_generador.php` | read | `generador` | menu "Generar horarios" |
| `get_data_especialidades` | `control/custom/get_data_especialidades.php` | read | `generador` | interpolates `$_SESSION['user']` |
| `get_data_niveles` | `control/custom/get_data_niveles.php` | read | `generador` | interpolates `$_SESSION['user']` |
| `get_data_planificaciones` | `control/custom/get_data_planificaciones.php` | read | `generador` | interpolates `$_SESSION['user']` |
| `generar` | `control/custom/generar.php` | generate | `generador` | reads `$_POST` into SQL via models/functions (§6); `PublicFunctions::Generar` (SQL string, `PublicFunctions.php:1555`); `set_time_limit(0)`; rewrites the timetable of a specialty/level/period [inferred] |
| `cancelar_generacion` | `control/custom/cancelar_generacion.php` | generate | `generador` | reads `$_POST` into SQL via models/functions (§6) |

## 5. Proposed `security.conf` snippet (proposed, not applied)

Generated from §4. It would **replace** the two `event_permissions` lines
of `bin/security.conf`; the switches and the header comment stay. It has
136 entries, none `array()`. It was checked with
`lib/basic/file/PropertiesFile.php`: it parses to 136 entries using the 8
permission names of §3.1 [verified]. Applying it also needs seed rows in
`seguridad.permiso` (§3.2), an update of the two tests in
`dev/tests/security/run.php` that use the 2010 names, and a decision on
§3.4.

```ini
# ---------------------------------------------------------------------
# PROPOSED, NOT APPLIED - docs/modernization/permission-matrix-proposal.md
# Deny by default. Each event requires ALL listed permissions.
# No array() entries: no event is open to every authenticated user.
# Not listed on purpose: the 6 public-viewer events (anonymous switch
# only), 3 events without a caller, the 261 handlers the client never
# posts (including all seguridad.* CRUD).
# ---------------------------------------------------------------------

# Explorador de horarios (menu permission ver_horarios)
event_permissions[custom_getui_ExploradorHorarios] = array('ver_horarios')
event_permissions[custom_getui_TreeBrigadas] = array('ver_horarios')
event_permissions[custom_getui_ExploradorListProfesores] = array('ver_horarios')
event_permissions[custom_getui_ExploradorListLugares] = array('ver_horarios')
event_permissions[custom_getui_ExploradorListOtrosRecursos] = array('ver_horarios')
event_permissions[get_minibrowser_horarios] = array('ver_horarios')

# Configuracion del tiempo (menu permission gestionar_planificaciones)
event_permissions[get_browser_Planificaciones] = array('gestionar_planificaciones')
event_permissions[get_minibrowser_planificaciones] = array('gestionar_planificaciones')
event_permissions[get_diagram_gantt_planificaciones] = array('gestionar_planificaciones')
event_permissions[get_browser_Periodos] = array('gestionar_planificaciones')
event_permissions[get_form_new_Planificacion] = array('gestionar_planificaciones')
event_permissions[get_form_edit_Planificacion] = array('gestionar_planificaciones')
event_permissions[get_form_new_Periodo] = array('gestionar_planificaciones')
event_permissions[get_form_edit_Periodo] = array('gestionar_planificaciones')
event_permissions[add_Planificacion] = array('gestionar_planificaciones')
event_permissions[set_Planificacion] = array('gestionar_planificaciones')
event_permissions[del_Planificacion] = array('gestionar_planificaciones', 'eliminar_datos')
event_permissions[add_Periodo] = array('gestionar_planificaciones')
event_permissions[set_Periodo] = array('gestionar_planificaciones')
event_permissions[del_Periodo] = array('gestionar_planificaciones', 'eliminar_datos')
event_permissions[get_form_edit_Periodo_restricciones] = array('gestionar_planificaciones', 'editar_restricciones')
event_permissions[custom_set_Restricciones_Periodo] = array('gestionar_planificaciones', 'editar_restricciones')
event_permissions[get_form_edit_Periodo_periodicidad] = array('gestionar_planificaciones', 'editar_restricciones')
event_permissions[custom_set_Periodicidades] = array('gestionar_planificaciones', 'editar_restricciones')

# Organizacion de los estudiantes (menu permission gestionar_brigadas)
event_permissions[get_browser_Organizacion] = array('gestionar_brigadas')
event_permissions[get_minibrowser_especialidades] = array('gestionar_brigadas')
event_permissions[get_minibrowser_niveles] = array('gestionar_brigadas')
event_permissions[get_browser_brigadas] = array('gestionar_brigadas')
event_permissions[get_browser_Brigadas_filtradas] = array('gestionar_brigadas')
event_permissions[get_browser_GruposXClasif] = array('gestionar_brigadas')
event_permissions[get_browser_GruposXClasifFiltrados] = array('gestionar_brigadas')
event_permissions[custom_getui_GruposXClasif] = array('gestionar_brigadas')
event_permissions[custom_getui_GruposXClasifFiltrados] = array('gestionar_brigadas')
event_permissions[get_form_new_Especialidad] = array('gestionar_brigadas')
event_permissions[get_form_edit_Especialidad] = array('gestionar_brigadas')
event_permissions[get_form_new_Nivel] = array('gestionar_brigadas')
event_permissions[get_form_edit_Nivel] = array('gestionar_brigadas')
event_permissions[get_form_new_Brigada] = array('gestionar_brigadas')
event_permissions[get_form_edit_Brigada] = array('gestionar_brigadas')
event_permissions[get_form_new_grupo] = array('gestionar_brigadas')
event_permissions[get_form_edit_grupo] = array('gestionar_brigadas')
event_permissions[add_Especialidad] = array('gestionar_brigadas')
event_permissions[set_Especialidad] = array('gestionar_brigadas')
event_permissions[del_Especialidad] = array('gestionar_brigadas', 'eliminar_datos')
event_permissions[add_Nivel] = array('gestionar_brigadas')
event_permissions[set_Nivel] = array('gestionar_brigadas')
event_permissions[del_Nivel] = array('gestionar_brigadas', 'eliminar_datos')
event_permissions[add_Brigada] = array('gestionar_brigadas')
event_permissions[set_Brigada] = array('gestionar_brigadas')
event_permissions[del_Brigada] = array('gestionar_brigadas', 'eliminar_datos')
event_permissions[add_Grupo] = array('gestionar_brigadas')
event_permissions[set_Grupo] = array('gestionar_brigadas')
event_permissions[del_Grupo] = array('gestionar_brigadas', 'eliminar_datos')
event_permissions[custom_MoverBrigadaDeGrupo] = array('gestionar_brigadas')
event_permissions[custom_EliminaBrigadaDeGrupo] = array('gestionar_brigadas')

# Configuracion de actividades (menu permission gestionar_periodos)
event_permissions[get_browser_Desgloses] = array('gestionar_periodos')
event_permissions[get_browser_DesgloseDetalles] = array('gestionar_periodos')
event_permissions[get_minibrowser_asignaturas] = array('gestionar_periodos')
event_permissions[get_minibrowser_clasifact] = array('gestionar_periodos')
event_permissions[get_form_edit_desglose] = array('gestionar_periodos')
event_permissions[get_form_new_asignatura] = array('gestionar_periodos')
event_permissions[get_form_edit_asignatura] = array('gestionar_periodos')
event_permissions[get_form_new_clasifact] = array('gestionar_periodos')
event_permissions[get_form_edit_clasifact] = array('gestionar_periodos')
event_permissions[get_form_new_plantilla] = array('gestionar_periodos')
event_permissions[get_form_edit_plantilla] = array('gestionar_periodos')
event_permissions[custom_getui_AsignacionRecursos] = array('gestionar_periodos')
event_permissions[custom_getui_ProfesoresAsignados] = array('gestionar_periodos')
event_permissions[custom_getui_LugaresAsignados] = array('gestionar_periodos')
event_permissions[custom_getui_OtrosRecursosAsignados] = array('gestionar_periodos')
event_permissions[custom_getui_NuevoProfesorParaActividad] = array('gestionar_periodos')
event_permissions[custom_getui_NuevoLugarParaActividad] = array('gestionar_periodos')
event_permissions[custom_getui_EditarProfesorParaActividad] = array('gestionar_periodos')
event_permissions[custom_getui_EditarPrioridadLxact] = array('gestionar_periodos')
event_permissions[custom_getui_EditarPrioridadRxact] = array('gestionar_periodos')
event_permissions[get_form_add_rxact] = array('gestionar_periodos')
event_permissions[get_form_edit_pxact_prioridad] = array('gestionar_periodos')
event_permissions[add_Asignatura] = array('gestionar_periodos')
event_permissions[set_Asignatura] = array('gestionar_periodos')
event_permissions[del_Asignatura] = array('gestionar_periodos', 'eliminar_datos')
event_permissions[add_ClasificacionDeActividad] = array('gestionar_periodos')
event_permissions[set_ClasificacionDeActividad] = array('gestionar_periodos')
event_permissions[del_ClasificacionDeActividad] = array('gestionar_periodos', 'eliminar_datos')
event_permissions[set_ClasifAct_subeprioridad] = array('gestionar_periodos')
event_permissions[set_ClasifAct_bajaprioridad] = array('gestionar_periodos')
event_permissions[set_Desglose] = array('gestionar_periodos')
event_permissions[add_PlantillaDeActividad] = array('gestionar_periodos')
event_permissions[set_PlantillaDeActividad] = array('gestionar_periodos')
event_permissions[del_PlantillaDeActividad] = array('gestionar_periodos', 'eliminar_datos')
event_permissions[custom_SubePlantilla] = array('gestionar_periodos')
event_permissions[custom_BajaPlantilla] = array('gestionar_periodos')
event_permissions[add_ProfesorParaActividad] = array('gestionar_periodos')
event_permissions[set_ProfesorParaActividad] = array('gestionar_periodos')
event_permissions[del_ProfesorParaActividad] = array('gestionar_periodos')
event_permissions[set_prioridad_pxact] = array('gestionar_periodos')
event_permissions[custom_ActualizaProfesorParaGrupo] = array('gestionar_periodos')
event_permissions[custom_EliminarGruposDeProfesor] = array('gestionar_periodos')
event_permissions[add_LugarParaActividad] = array('gestionar_periodos')
event_permissions[set_LugarParaActividad] = array('gestionar_periodos')
event_permissions[del_LugarParaActividad] = array('gestionar_periodos')
event_permissions[add_OtroGrupoDeRecursosParaActividad] = array('gestionar_periodos')
event_permissions[del_OtroGrupoDeRecursosParaActividad] = array('gestionar_periodos')
event_permissions[add_OtroRecursoParaActividad] = array('gestionar_periodos')
event_permissions[del_OtroRecursoParaActividad] = array('gestionar_periodos')
event_permissions[custom_set_PrioridadRxact] = array('gestionar_periodos')
event_permissions[set_GrupoRecursos_imprescindibles] = array('gestionar_periodos')
event_permissions[set_GrupoRecursos_necesarios] = array('gestionar_periodos')
event_permissions[get_form_edit_zpriori] = array('gestionar_periodos', 'editar_restricciones')
event_permissions[custom_set_zpriori] = array('gestionar_periodos', 'editar_restricciones')

# Recursos (menu permission gestionar_recursos)
event_permissions[get_browser_recursos] = array('gestionar_recursos')
event_permissions[get_minibrowser_profesores] = array('gestionar_recursos')
event_permissions[get_minibrowser_lugares] = array('gestionar_recursos')
event_permissions[get_minibrowser_otrosrecursos] = array('gestionar_recursos')
event_permissions[get_form_new_profesor] = array('gestionar_recursos')
event_permissions[get_form_edit_profesor] = array('gestionar_recursos')
event_permissions[get_form_new_lugar] = array('gestionar_recursos')
event_permissions[get_form_edit_lugar] = array('gestionar_recursos')
event_permissions[get_form_new_otrorecurso] = array('gestionar_recursos')
event_permissions[get_form_edit_otrorecurso] = array('gestionar_recursos')
event_permissions[add_Profesor] = array('gestionar_recursos')
event_permissions[set_Profesor] = array('gestionar_recursos')
event_permissions[del_Profesor] = array('gestionar_recursos', 'eliminar_datos')
event_permissions[add_Lugar] = array('gestionar_recursos')
event_permissions[set_Lugar] = array('gestionar_recursos')
event_permissions[del_Lugar] = array('gestionar_recursos', 'eliminar_datos')
event_permissions[add_OtroRecurso] = array('gestionar_recursos')
event_permissions[set_OtroRecurso] = array('gestionar_recursos')
event_permissions[del_OtroRecurso] = array('gestionar_recursos', 'eliminar_datos')
event_permissions[get_form_edit_lugar_costos_de_traslado] = array('gestionar_recursos', 'editar_restricciones')
event_permissions[custom_set_costotraslado] = array('gestionar_recursos', 'editar_restricciones')

# Generar horarios (menu permission generador)
event_permissions[get_win_generador] = array('generador')
event_permissions[get_data_especialidades] = array('generador')
event_permissions[get_data_niveles] = array('generador')
event_permissions[get_data_planificaciones] = array('generador')
event_permissions[generar] = array('generador')
event_permissions[cancelar_generacion] = array('generador')
```

## 6. Open questions for the maintainer

1. **Vocabulary.** Should the menu names become the official permission
   names, as proposed? The alternative is the 2010 three-level names
   (`planificar` + area + action). Either way, should `app.tpl` and
   `security.conf` read the permission of each window from one shared
   list, so that they cannot drift apart again?
2. **Rename `gestionar_periodos`** (it gates activity configuration) to
   e.g. `gestionar_actividades`? That needs a data migration of
   `seguridad.permiso` and an `app.tpl` change.
3. **Read-only access to configuration.** Is a role needed that can browse
   plans, organization, activities or resources without editing? Then
   every module needs a `ver_*` / `gestionar_*` pair: 4 NEW names and
   about 80 read rows moved to `ver_*`.
4. **Deletes.** Is `eliminar_datos` wanted, or is the module permission
   enough? Should the 5 assignment deletes need it too?
5. **`todos`.** It still opens all 406 handlers, including the 18 generated
   `seguridad` CRUD handlers. Should these be removed from
   `control/data/*`, or excluded even for `todos`, before any user other
   than the seeded superuser gets `todos`?
6. **Unreachable handlers.** Delete the 261 unused handlers (and the 4
   orphan JS wrappers), or keep them unlisted? Deleting them shrinks the
   surface that `todos` can reach.
7. **Public viewer.** With `public_viewer_anonymous = false`, should a
   logged-in `ver_horarios` user be able to open the public viewer's 6
   events, or is the "Explorador de horarios" enough (proposed: enough)?
8. **Ownership.** Is the `creador` partitioning a real multi-tenant
   boundary (several schools or planners on one instance)? If so, §3.4's
   missing ownership checks matter more than any role split and should be
   fixed before roles other than superuser are handed out.
9. **Seed data.** Should `datos_por_defecto.sql` seed the bundles of §3.2
   for `administrador`, `planificador`, `profesor` and `invitado`? Today
   they have no permissions, so users with those roles can do nothing.
10. **Generator.** Should running the generator be separate from opening
   its window (e.g. NEW `ejecutar_generador`)? Should it be limited per
   plan or period, given that it rewrites timetables and has no time
   limit?
