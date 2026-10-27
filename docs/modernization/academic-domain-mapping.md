# Academic domain mapping (issue #70, first slice)

Issue: #70, under the replatforming decision #66. Depends on the Drupal foundation (#69, `docs/modernization/drupal-foundation.md`).

The Drupal module `ancora_academic` (`modern/web/modules/custom/ancora_academic`) lets an organization enter the smallest planning dataset that the MPI scheduling engine needs for one run. This document covers:

- the legacy tables it replaces for data entry;
- the Drupal entities and fields that map to them;
- what is deliberately not modelled yet;
- the export contract that the engine sync (#71) consumes.

The legacy SQL schema stays where it is, unchanged. The engine keeps running on its own tables (#66, point 3). This module never writes to an engine database.

## Glossary

The legacy schema is in Spanish. The new code is in English. Each legacy term maps to one English term.

| Legacy (Spanish) | English | Drupal entity type |
| --- | --- | --- |
| planificación | plan | `ancora_plan` |
| período | period | `ancora_period` |
| periodicidad | days of the plan (of a period) | `ancora_period.plan_days` |
| turno | time slot | `ancora_period.slots_per_day` |
| clasificación de actividad | activity type | `ancora_activity_type` |
| gasto (de una clasificación) | cost | `ancora_activity_type.cost` |
| especialidad | specialty | `ancora_specialty` |
| nivel / año | level | `ancora_level` |
| brigada | cohort | `ancora_cohort` |
| matrícula | enrolment | `ancora_cohort.enrollment` |
| grupo | student group | `ancora_student_group` |
| brigada de grupo | cohorts of a group | `ancora_student_group.cohorts` |
| asignatura | subject | `ancora_subject` |
| desglose | subject breakdown (subject × period) | `ancora_breakdown` (D4), kept by the module; see below |
| plantilla de actividad | activity template | `ancora_activity_template` |
| orden (de plantilla) | sequence (number) | `ancora_activity_template.sequence` |
| lugar | room | `ancora_room` |
| profesor | teacher | `ancora_teacher` |
| profesor para actividad | teacher assignment | `ancora_teacher_assignment` |
| profesor para grupo | groups of a teacher assignment | `ancora_teacher_assignment.groups` |
| lugar para actividad | room assignment | `ancora_room_assignment` |
| actividad | activity (a placement) | not here: produced by the engine (#71) |
| creador | owner (creator) | `uid` of plans and catalogue records; see "Ownership" |

## Model decisions

- **Content entities, base fields, no bundles.** Each type is a `#[ContentEntityType]` with base fields only. The schema is code: it is versioned, testable and identical on every site. The field UI is not used, and none of the data lives in configuration.
- **Owned catalogues, plan records (#70 D1–D3).** As in the legacy schema (legacy-parity-inventory.md PAR-001, PAR-003):
  - plans, activity types, specialties, levels, cohorts, subjects, rooms and teachers belong to the user who created them (`uid`, the legacy `creador`). The catalogue records have **no plan**: they serve every plan of their owner;
  - periods, student groups, templates and the two kinds of assignment belong to a plan (`plan`); their creator is the plan's owner (legacy `dame_creador_de_planificacion`, `dame_creador_de_plantilla`);
  - codes are unique among the owner's records of a type (catalogues, plans) or in the plan (plan records): entity constraint `AncoraUniqueInScope`. Two users of a site may both have a teacher `T1` or a plan `P`;
  - references name records of the same creator, and plan records of the same plan (`AncoraSameCreator`), as the legacy choice lists offer only the user's own records;
  - until #70, catalogues were copied per plan and plan codes were unique on the site. `ancora_academic_update_11001()` converts existing data (see "Update path").
- **Codes are the human keys; UUIDs are the stable keys.**
  - Every record that has a name has a code, for example `G1` or `PER`: the Drupal handle of the record (legacy records have no code). It is required and unique per owner or plan, and takes any text of up to 255 characters, the Drupal column size; names too (#70, D6; before, codes were `[A-Za-z0-9._-]+` of at most 32 characters and names at most 128).
  - The export identifies records by UUID (`key`), which survives renames.
  - The legacy row builder derives legacy IDs from the codes.
- **Breakdown (desglose) is an entity kept by the module** (#70, D4). Legacy triggers create one `desglose` row for every subject and every period of the same creator, with defaults (`min_dias` 1, `max_dias` 5, `respetar_orden` true, `mismo_dia` false), and the foreign keys delete it with its subject or period. `ancora_breakdown` does the same (`BreakdownMaintainer`, `Hook/BreakdownHooks.php`): it is created when a subject or period is created (and when an owner changes), deleted with its subject, period or plan, never created or deleted by a user, and it does not block deletes. The owner edits its four options (`min_days`, `max_days`, `respect_order`, `same_day`) at `/admin/ancora/breakdowns/{id}/edit`, as in the legacy form; any integer is accepted, as the legacy handler stores it. Each template still references its subject and period directly.
- **Template length comes from the activity type.** The legacy template has no duration of its own: an activity lasts `clasificacion_de_actividad.duracion` slots. This is `ancora_activity_type.duration_slots` (≥ 1).
- **Assignments are entities, not multi-value fields.** A teacher assignment carries a priority and a set of groups. A room assignment carries a priority. Both need their own validation, listing and lifecycle.
- **No cascade.** A record that other records reference cannot be deleted, through any API.
  - Entities: `AcademicEntityBase::preDelete` throws, using `ReferenceGuard`.
  - UI: the delete form lists the dependents and offers no Delete button.
  - So a plan cannot be deleted while it has any record. Dependents are deleted first, explicitly.
  - The legacy schema uses `ON DELETE CASCADE` everywhere, which can silently delete a whole plan's data. That behaviour is intentionally not reproduced.

## Mapping: legacy table and column → Drupal entity and field

Columns not listed are not modelled. The "Not modelled" section explains why.

### `planificacion` → `ancora_plan`

| Legacy column | Drupal field | Notes |
| --- | --- | --- |
| `id` | `code` (export: `key` = UUID) | unique on the site |
| `descripcion` | `label` | required |
| `fecha_inicial` (legacy day number) | `starts_on` (date, optional) | `LegacyRows::legacyDayNumber()` ports `tiempo.date_to_number`, quirks included. Empty gives `0`, like the fixture. |
| `cant_dias` | `duration_days` | 1 to 3660, default 30 |
| `activa` | `active` | default true (the legacy UI never posts it, so UI-created rows have NULL: PAR-010) |
| `creador` | `uid` (owner) | the creating user; see "Ownership" |

### `periodo` and `periodicidad` → `ancora_period`

| Legacy column | Drupal field | Notes |
| --- | --- | --- |
| `periodo.id` | `code` | unique in the plan |
| `descripcion` | `label` | |
| `cant_dias` (CHECK > 0) | `days` | 1 to 31, default 5 |
| `cant_turnos` (CHECK > 0) | `slots_per_day` | 1 to 48, default 6 |
| `hora_inicial` | `start_hour` | decimal from 0 to 24, default 8 |
| `duracion_del_turno` | `slot_duration` | decimal from 0.01 to 24 hours, default 1 |
| `planificacion` | `plan` | |
| `periodicidad(periodo, dia)` (CHECK dia > 0) | `plan_days` (multi-value integer ≥ 1) | Days of the plan on which the period applies. The fixture has day 1. |

### `clasificacion_de_actividad` → `ancora_activity_type`

| Legacy column | Drupal field | Notes |
| --- | --- | --- |
| `id`, `descripcion` | `code`, `label` | |
| `creador` | `uid` | the owner; no plan |
| `duracion` | `duration_slots` | 1 to 48, default 1 |
| `prioridad` | `priority` | ≥ 1, default 1 |
| `gasto` | `cost` | ≥ 0, default 1 |

### `especialidad`, `nivel` → `ancora_specialty`, `ancora_level`

| Legacy column | Drupal field |
| --- | --- |
| `id`, `descripcion` | `code`, `label` |
| `creador` | `uid` (no plan) |

### `brigada` → `ancora_cohort`

| Legacy column | Drupal field | Notes |
| --- | --- | --- |
| `id`, `descripcion` | `code`, `label` | |
| `especialidad`, `nivel` | `specialty`, `level` | required |
| `matricula` | `enrollment` | any integer, default 0 (#70, D6) |
| `creador` | `uid` | the owner; no plan. A cohort can be in groups of several of its owner's plans |

### `grupo` and `brigada_de_grupo` → `ancora_student_group`

| Legacy column | Drupal field | Notes |
| --- | --- | --- |
| `grupo.id`, `descripcion` | `code`, `label` | |
| `clasificacion` | `activity_type` | the kind of activity the group attends together |
| `especialidad`, `nivel` | `specialty`, `level` | required |
| `planificacion` | `plan` | |
| `brigada_de_grupo(brigada, grupo)` | `cohorts` (multi-value) | Legacy membership (#70, D10; `GroupMembership`): a cohort is in one group per plan, activity type, specialty and level, and adding it to another group of that slot moves it; adding is refused when the group or the group it leaves has placed activities (`AncoraGroupMembership`), or when they cannot be read right now (fail closed); removing is never refused. No check that a cohort shares the group's specialty and level (legacy has none, `dame_grupos` goes by the cohorts); the form offers the group's specialty and level |

### `asignatura` → `ancora_subject`

| Legacy column | Drupal field | Notes |
| --- | --- | --- |
| `id`, `descripcion` | `code`, `label` | |
| `especialidad` (nullable) | `specialty` (nullable; required by the form) | As the column (#70, D6). The legacy subject tree always gives a specialty, so the form requires one; data loaded otherwise may have none, and such a subject is never scheduled (case 136). |
| `nivel` | `level` | required |
| `creador` | `uid` | the owner; no plan |

### `desglose` and `plantilla_de_actividad` → `ancora_activity_template`

| Legacy column | Drupal field | Notes |
| --- | --- | --- |
| `desglose.asignatura`, `desglose.periodo` | `subject`, `period` | required |
| `plantilla.orden` | `sequence` | 1 to 99, required. Unique per subject and period (`AncoraUniqueInPlan` on subject, period and sequence). Legacy: `UNIQUE (desglose, orden)`, and #23 makes it required by the generator. |
| `plantilla.clasificacion` | `activity_type` | required |
| `exige_profesor`, `exige_lugar` | `requires_teacher`, `requires_room` | default true |
| `desglose.min_dias`, `max_dias`, `respetar_orden`, `mismo_dia` | `ancora_breakdown.min_days`, `max_days`, `respect_order`, `same_day` | defaults 1, 5, TRUE, FALSE; integers in the PostgreSQL range, no other check (PAR-032) |
| `plantilla.actualizacion_interna` | — | an internal flag of the legacy triggers |

The template has no name or code of its own. Its label is "Subject #sequence (period code)". Its legacy ID is the subject code plus the sequence (`A1`), as in the fixture. When an export holds more than one period, `@` and the period code are appended.

### `profesor`, `lugar` → `ancora_teacher`, `ancora_room`

| Legacy column | Drupal field | Notes |
| --- | --- | --- |
| `profesor.id`, `nombre` | `code`, `label` | |
| `lugar.id`, `nombre` | `code`, `label` | |
| `lugar.capacidad` | `capacity` | ≥ 0, default 0 |
| `profesor.creador`, `lugar.creador` | `uid` | the owner; no plan |

### `profesor_para_actividad` and `profesor_para_grupo` → `ancora_teacher_assignment`

| Legacy column | Drupal field | Notes |
| --- | --- | --- |
| `plantilla`, `profesor` | `template`, `teacher` | required, unique together (`UNIQUE (plantilla, profesor)`) |
| `prioridad` | `priority` | ≥ 1, default 1 |
| `profesor_para_grupo(profesor_para_actividad, grupo)` | `groups` (multi-value) | `AncoraEligibleGroups`: each group has the template's activity type and the specialty and level of its subject (legacy `dame_grupos_de_plantilla`) |

The legacy UI flow in `dev/e2e/lib.sh` has three steps: `add_Profesor`, `add_ProfesorParaActividad` per template, then `custom_ActualizaProfesorParaGrupo` per group. Here the same is one teacher record and one assignment form per template, with the groups as checkboxes.

### `lugar_para_actividad` → `ancora_room_assignment`

| Legacy column | Drupal field | Notes |
| --- | --- | --- |
| `plantilla`, `lugar` | `template`, `room` | required, unique together (legacy primary key) |
| `prioridad` | `priority` | ≥ 1, default 1 |

## Not modelled yet (intentionally)

| Legacy area | Tables | Why not now |
| --- | --- | --- |
| Time restrictions (nominal, real, conditional, inherited) | `restriccion_*`, `restriccion_real_*`, `restriccion_condicional`, `herencia_de_restricciones`, `excepcion_de_hrt` | Not needed for a basic run: the fixture has none. They are the biggest remaining domain area and need their own design (#70, next slice). |
| Placement preference matrix | `zpriori` | Optional. The engine defaults every cell to 1. |
| Breaks between slots | `receso` | Only affects the displayed hours (`dame_hora_inicial_turno`), not placement. |
| Other resources and resource groups | `otro_recurso`, `otro_grupo_de_recursos_para_actividad`, `otro_recurso_para_actividad`, `uso_de_otros_recursos`, `costo_de_traslado` | Not used by the fixture or R1. |
| Fixed and forbidden rooms per group | `lugar_fijo`, `acceso_prohibido` | Not used by the fixture. They will be references on the group. |
| Breakdown rules | `desglose.min_dias`, `max_dias`, `respetar_orden`, `mismo_dia` | Exported from `ancora_breakdown` (export `breakdowns`); defaults for a pair without one. |
| Placements and generation state | `actividad`, `generador.*` | Engine output and engine state (#71). |
| Ownership and users | `creador`, `seguridad.*` | Drupal users, roles and permissions replace them. The tenant boundary is the site (#72). |

## Ownership and permissions

The legacy rule (legacy-parity-inventory.md PAR-001 to PAR-005), characterized by the parity harness ([legacy-parity-harness.md](legacy-parity-harness.md), scenarios s02 and s03):

- every add handler sets `creador` to the session user (`control/data/fdms/fdm_<Class>_creador.php`);
- every data entry list loads `creador = $_SESSION['user']` (`get_minibrowser_*`, `get_browser_*`, `get_data_*`): a user sees only their own plans and catalogues, and the records of their plans. No role sees other users' records there, not even the seeded superuser;
- the breakdown triggers pair a subject with the periods of every plan of its creator, and the placements belong to the subject's creator (PAR-008, PAR-031);
- the timetable explorer lists **every** user's teachers and rooms (`custom_getui_ExploradorListProfesores.php`, `..Lugares.php` use `getAll()`; quirk Q-23, A-5);
- the handlers update and delete by id without checking `creador` (Q-12), and an update rewrites `creador` to the editing user (Q-11). The harness records both; they are not reproduced (Q-12 is a security behaviour; Q-11 only happens through crafted requests, since the screens show a user only their own records).

Drupal:

| Operation | Rule (`AcademicAccessControlHandler`) |
| --- | --- |
| view | `view academic data` and being the record's creator. Teachers and rooms: `view academic data` only (Q-23). |
| update, delete | `edit academic data` and being the record's creator. |
| create | `edit academic data`, for every type, plans included: each user creates their own plans. |
| everything | `administer academic data` (restricted): the operator's access to every record. No legacy screen has it. |
| owner field | edited only with `administer academic data`. |

`AcademicQueryAccess` applies the same filter to every access-checked query (tag `<entity type>_access`): the lists, the overview counts, the options of every reference widget (so the `ValidReference` constraint refuses another user's record), and Views. Teachers and rooms are filtered there too: the data entry lists and the assignment choices are the creator's, as in legacy. The entity types declare the `user` list cache context, so cached lists and Views results are per user. The tenant boundary stays the site (#72).

`drush ancora-academic:reassign-owner <from> <to>` gives every record of one user to another (an operator tool; nothing is changed if a code would collide).

## User interface (Drupal core only)

- **Planning overview** at `/ancora/planning` (menu: Administration › Planning). It lists the 13 record types in data entry order, with a count, a description and an "Add" link when the user may create. The module template is `ancora-academic-planning.html.twig`.
- **Lists.** `/admin/ancora/<type>` (plans, periods, activity-types, specialties, levels, cohorts, groups, subjects, templates, rooms, teachers, teacher-assignments, room-assignments) use `AcademicListBuilder`, with the columns declared by each entity class. `?plan=<ID>` narrows a list to one plan.
- **Forms.** Add, edit and delete forms use `AcademicEntityForm` and `AcademicDeleteForm`. A new record is assigned to the plan in `?plan=<ID>`, or to the only plan when there is just one. Every validation rule is an entity or field constraint, so it applies to forms, Drush, the fixture loader and any future API alike.
- **Views.** `views.view.ancora_academic_templates` at `/ancora/planning/activities` lists the weekly activities: plan, subject, number, period and activity type.
- **Navigation.** Menu links, tabs (View, Edit, Delete) and "Add …" actions are derived for every type (`Plugin/Derivative/*`).

## Synthetic fixture

`SyntheticFixture::load()` creates the dataset of `dev/e2e/fixture.sql` through the Entity API, validating every record and in one transaction, as one user (the fixture rows belong to `admin-superuser`; here to the given owner, by default the site's first account). It is also available as `drush ancora-academic:load-fixture [--owner=<account>]`.

- By default it also applies the HTTP data entry steps of `dev/e2e/lib.sh`: Teacher Beta (`TB`) is assigned to B1 and B2 for G1 and G2. That is the state in which the legacy engine places 8 of 8 activities.
- `--fixture-only` stops at the exact content of `fixture.sql`. The export then reports that B1 and B2 have no teacher.
- It refuses to run when the owner already has a plan `P`, so repeated runs never mix data.

`install-disposable.sh` loads the fixture into the freshly installed site, exports it and checks that it has 8 demands (4 templates × 2 groups) and 8 teacher–group links. The HTTP smoke test then reads the fixture through the overview, the teacher list and the Views page.

## Export contract (`AcademicExport`, schema `ancora_academic.export.v6`)

`AcademicExport::export(Plan $plan, ?Period $period = NULL): array` returns plain PHP data (scalars and lists, JSON-serializable): the plan's own records and **all the catalogue records of the plan's owner** (they serve every plan of that owner). `AcademicExport::catalogue(int $owner)` returns the catalogues of a user who may have no plan (`plan` NULL, no plan records). Both read with access checks disabled; the caller decides who may export. The command `drush ancora-academic:export <plan code> [--owner=<account>] [--period=<code>] [--legacy]` prints the export, or its legacy rows, as JSON.

```text
schema               "ancora_academic.export.v6"
owner                user ID of the plan's creator
plan                 {key, id, code, label, starts_on (Y-m-d|null), duration_days, active}
periods[]            {key, id, code, label, days, slots_per_day, start_hour, slot_duration, plan_days[]}
student_groups[]     {key, id, code, label, activity_type, specialty, level, cohorts[]}
activity_templates[] {key, id, subject, subject_code, period, period_code, sequence, activity_type,
                      requires_teacher, requires_room}
activity_types[]     {key, id, code, label, duration_slots, priority, cost}      (owner's)
specialties[]        {key, id, code, label}                                      (owner's)
levels[]             {key, id, code, label}                                      (owner's)
cohorts[]            {key, id, code, label, specialty, level, enrollment}        (owner's)
subjects[]           {key, id, code, label, specialty, level}                    (owner's)
rooms[]              {key, id, code, label, capacity}                            (owner's)
teachers[]           {key, id, code, label}                                      (owner's)
teacher_assignments[] {key, id, template, teacher, teacher_code, priority, groups[]}
room_assignments[]   {key, id, template, room, room_code, priority}
breakdowns[]         {key, id, subject, subject_code, period, period_code,
                      min_dias, max_dias, respetar_orden, mismo_dia}
time_restrictions[]  {key, id, period, kind, target, day, slot}
breaks[]             {key, id, period, after_slot, length}
priority_zones[]     {key, id, period, activity_type, day, slot, zone}
load_limits[]        {key, id, period, kind, target, max_daily_load}
group_rooms[]        {key, id, kind (fixed|forbidden), group, room}
travel_costs[]       {key, id, origin, destination, cost}          (owner's)
issues[]             {type, key, message}
```

v2 (#70) adds `owner` and the entity `id` of every record, and the catalogue lists are the owner's instead of the plan's. v6 (#70, D9c) adds `group_rooms` (to `lugar_fijo` / `acceso_prohibido`) and the owner's `travel_costs` (to `costo_de_traslado`). v5 (#70, D9b) adds `breaks` (to `receso`), `priority_zones` (to `zpriori`; only zones other than 1 exist) and `load_limits` (to `higiene_para_profesor` / `_brigada`). v4 (#70, D9a) adds `time_restrictions`: one record per restricted cell of the exported periods; `kind` is period, activity_type, subject, cohort, teacher or room, `target` the key of the object (NULL for the period). `LegacyRows` writes them to `restriccion_de_{periodo,clasif_act,asignatura,brigada,profesor,lugar}` with `valor` 1 and, on the last three, `por_actividad` false. v3 (#70, D4) adds `breakdowns`: the stored breakdowns of the exported periods, sorted by subject and period code. `LegacyRows` gives every subject × period its options, or the legacy defaults when it has no breakdown.

Rules:

- **Keys.**
  - Every `key` is the entity UUID: stable across edits, unique across plans and sites. `id` is the entity ID, which grows in creation order; the engine ids are numbered with it.
  - Every reference (`specialty`, `groups[]`, `template`, ...) is the key of the referenced record.
  - Codes are unique per owner or plan, but they can be edited, so they are not stable keys.
- **Scope.**
  - With a period, `periods` holds only that period, and templates and their assignments are only those of that period.
  - Groups and the owner's catalogues are not narrowed.
  - A period of another plan throws `InvalidArgumentException`.
- **Order.**
  - Lists are sorted by code.
  - Templates are sorted by subject code, period code and sequence.
  - Assignments are sorted by template order, priority and resource code.
  - Multi-value references are sorted by the referenced code.
  - Equal data therefore gives an identical export.
- **Numbers.** `start_hour` and `slot_duration` are floats. Every other number is an integer.
- **Issues.** These are data the engine cannot use as is. The export reports them and never fails for them:
  - `template_without_groups`: no group has the template's activity type and the specialty and level of its subject;
  - `template_without_teacher` / `template_without_room`: the template requires a teacher or room and has no assignment;
  - `group_without_teacher`: an eligible group is not covered by any teacher assignment of the template.
- **Versioning.** A breaking change of the contract changes `schema`. Consumers must check it.

### Legacy rows (`LegacyRows::build($export, $creator = 'admin-superuser', $numbered = FALSE)`)

This is a pure translation of an export into rows of the 17 legacy engine tables, in foreign key order:

1. `planificacion`, `periodo`, `periodicidad`
2. `clasificacion_de_actividad`, `especialidad`, `nivel`
3. `brigada`, `grupo`, `brigada_de_grupo`
4. `asignatura`, `desglose`, `plantilla_de_actividad`
5. `profesor`, `profesor_para_actividad`, `profesor_para_grupo`
6. `lugar`, `lugar_para_actividad`

`$creator` is the legacy `creador` of every owned row. The engine sync writes the rows of the whole site: every plan with its owner's catalogues, and the catalogues of users without a plan (see [r1-scheduling-journey.md](r1-scheduling-journey.md), "Engine ids and owners").

- **IDs.** The base id of a record is:
  - its code, for coded records;
  - `<subject code><sequence>` for templates, with `@<period code>` appended when the plan has several periods;
  - `<template>@<teacher code>` for teacher assignments;
  - `<period id>@<day>` for periodicidad rows, `<subject id>@<period id>` for desglose rows.
- **Numbered ids.** With `$numbered` (the engine sync), the id of every record is `LegacyRows::engineId()`: the entity id zero-padded to ten digits, `:`, and the base id, for example `0000000012:G1`. Legacy ids are generated replicas (inventory PAR-110) that are unique in the database and **sort in entry order**, and `generar()` reads some tables in id order: its result changes with that order (characterized in [legacy-parity-harness.md](legacy-parity-harness.md), scenario s04). Entity ids grow in creation order and are unique per type on the site, so numbered ids keep the legacy order, never collide between users or plans, and stay stable when a code is edited. The per-plan prefix `p<plan id>:` of R1 (#89) is gone: it sorted ids in code order and was needed only while catalogues were copied per plan.
- Without `$numbered` the ids are the base ids, which reproduces `dev/e2e/fixture.sql` exactly (`FixtureExportTest`).
- **Triggers.** The legacy triggers `nuevo_desglose_cuando_nueva_asignatura` and `nuevo_desglose_cuando_nuevo_periodo` insert desglose rows by themselves. A loader that keeps them enabled must reuse those rows (by subject and period) instead of inserting the desglose rows given here.
- **Equivalence.** `FixtureExportTest` reads `dev/e2e/fixture.sql` and compares it with the rows built from the Entity API fixture:
  - the 11 tables that the fixture lists literally match row for row, IDs included;
  - periodicidad, profesor_para_actividad and profesor_para_grupo, whose IDs are generated, match on content;
  - desglose, the templates and the room assignments match the semantics of the trigger and of the `INSERT … SELECT` statements.

## Tests

| Test | Kind | What it proves |
| --- | --- | --- |
| `AcademicEntityTest` (6) | Kernel | CRUD of the 13 types through the fixture. Catalogue records have an owner and no plan. A second plan of the same owner uses the owner's catalogues (groups, templates, assignments). Required fields and ranges. Codes unique per owner or plan, not across users (plans too). References to another user's records or another plan refused. Group cohorts and assignment groups eligible. Deletion refused while referenced. |
| `AcademicAccessTest` (3) | Kernel | For every type: the owner may view, edit and delete; another editor or viewer may not (teachers and rooms: view only, Q-23); no permission nothing; the administrator everything; every editor creates, plans included. Access-checked queries return only the current user's records. The owner field is editable by administrators only. |
| `FixtureExportTest` (6) | Kernel | Shape, owner, counts, references and determinism of the export. Issues of the fixture-only dataset. Legacy rows equal to `dev/e2e/fixture.sql`. Teacher Beta as in `dev/e2e/lib.sh`; numbered ids in creation order. Port of the legacy day number. A second plan shares the owner's catalogues; another user's catalogue is empty; refusal to load the fixture twice. |
| `PlanningUiTest` (3) | Functional (HTTP) | Anonymous users get 403 everywhere. The owner reads their lists and the Views page; another viewer sees none of them (a teacher page itself: 200, Q-23; its edit form: 403). An editor creates their own plan with a code another user has, then periods and teachers through validated forms (range and duplicate code errors), edits and deletes, and cannot edit or delete another user's plan. A plan with records shows its dependents and no Delete button. The `?plan=` filter. |

Results on the workstation (PHP 8.4.27, PostgreSQL 16, `dev/sql/tools/with-ephemeral-pg.sh modern/scripts/install-disposable.sh --skip-composer`):

- 23 PHPUnit tests (8 foundation + 15 academic), 892 assertions;
- phpcs: 80 files clean;
- configuration determinism: 87 files;
- HTTP smoke test: 25 of 25 checks;
- total about 105 s.

## LBR impact

**0 bytes retired.** This is target code; the legacy data entry screens (`control/data/add/*`, `custom_*`) are not retired. Retirement credit needs an accepted comparison (#77).

Since #70 (ownership slice) the parity harness ([legacy-parity-harness.md](legacy-parity-harness.md)) verifies against the legacy application over HTTP, at table and outcome level: the creator of plans, catalogues and placements, the visibility of the data entry lists, the sharing of catalogues across a creator's plans, the breakdown rows per creator, and the id order. Everything else in the inventory is not verified yet.

The module adds no file to the frozen LBR baseline, and it modifies no legacy file.

## Update path (#70)

`ancora_academic_update_11001()` converts a site installed before #70 (for example the private demo site, which has the fixture loaded):

1. it adds the owner field to the eight owned types; existing records get the site's first account (uid 1), because the earlier schema recorded no creator;
2. it merges catalogue records that were copies in several plans (same type, owner and code, same values) into the oldest one and moves every reference to it; copies with the same code and different values stop the update with a message, and nothing changes;
3. it removes the plan field of the seven catalogue types.

`ancora_academic_update_11007()` (D9c) installs the empty `ancora_group_room` and `ancora_travel_cost` entity types. The planner request form now sends `buscalugar` TRUE by default (the nearest room, the legacy window's default, A-2); a request can choose the room the group uses most.

`ancora_academic_update_11006()` (D9b) installs the empty `ancora_break`, `ancora_priority_zone` and `ancora_load_limit` entity types.

`ancora_academic_update_11005()` (D9a) installs the empty `ancora_time_restriction` entity type; timetables do not change until a planner restricts a cell.

`ancora_academic_update_11004()` (D6) widens the name and code columns to 255 characters in place (the stored values are unchanged) and reports any cohort that earlier data has in two groups of one slot, without changing it: move it in the group form.

`ancora_academic_update_11003()` (D4) installs the `ancora_breakdown` entity type and creates the breakdown of every subject and period of the same owner with the legacy defaults, the values the engine received before; timetables do not change until an option is edited. It can run again safely (it creates only missing ones).

`ancora_academic_update_11002()` re-saves the activities view, so its results are cached per user. Then give the records to their planner, for example `drush ancora-academic:reassign-owner admin planner`. The first engine run afterwards replaces the engine rows of the earlier `p<plan>:` scheme (and, through the legacy cascades, their placements); request each timetable again. Re-provisioning the site is the alternative.

## Open questions

The earlier open questions are withdrawn (#70, maintainer decision): the legacy implementation answers them.

1. **Shared catalogues across plans.** *Answered by the legacy schema:* catalogues belong to their creator and serve all the creator's plans (PAR-003). Implemented in #70 (D1).
2. **Breakdown rules.** Answered from the legacy code (#70, D4): editable per subject and period, as the legacy form does; see PAR-032..034.
3. **Restrictions.** How should the time restrictions be modelled: entities per target type, or one entity with a target reference? Should the legacy "real restriction" materialization stay in the engine?
4. **Cohort membership.** Answered from the legacy code (#70, D10): one group per plan, activity type, specialty and level, with moves and the activity guard (PAR-018).
5. **Capacity.** Rooms smaller than the enrolment of a group's cohorts are filtered by the engine (`quita_segun_capacidad`). Should the UI warn about this as an export issue?
6. **Engine IDs.** *Answered in #70 (D3, D15):* numbered ids (`0000000012:code`) in creation order, owned by the creating user's legacy user; the per-plan prefix and the per-plan owner `p<id>:owner` are gone.
