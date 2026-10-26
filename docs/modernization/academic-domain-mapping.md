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
| desglose | subject breakdown (subject × period) | implied by the templates; see below |
| plantilla de actividad | activity template | `ancora_activity_template` |
| orden (de plantilla) | sequence (number) | `ancora_activity_template.sequence` |
| lugar | room | `ancora_room` |
| profesor | teacher | `ancora_teacher` |
| profesor para actividad | teacher assignment | `ancora_teacher_assignment` |
| profesor para grupo | groups of a teacher assignment | `ancora_teacher_assignment.groups` |
| lugar para actividad | room assignment | `ancora_room_assignment` |
| actividad | activity (a placement) | not here: produced by the engine (#71) |
| creador | owner | not modelled; see "Ownership" |

## Model decisions

- **Content entities, base fields, no bundles.** Each type is a `#[ContentEntityType]` with base fields only. The schema is code: it is versioned, testable and identical on every site. The field UI is not used, and none of the data lives in configuration.
- **One plan per record.** Every record except a plan has a required `plan` reference.
  - Codes are unique within a plan (entity constraint `AncoraUniqueInPlan`). Plan codes are unique on the site.
  - Every reference must point to a record of the same plan (`AncoraSamePlan`).
  - In the legacy schema, specialties, levels, subjects, rooms, teachers and activity types belong to a user (`creador`) and are shared by all of that user's plans. Here they are copied per plan. This keeps the export of one plan self-contained, and each plan can change without affecting the others. See open question 1.
- **Codes are the human keys; UUIDs are the stable keys.**
  - Every record that has a name has a short code (`[A-Za-z0-9._-]+`, at most 32 characters), for example `G1` or `PER`.
  - The export identifies records by UUID (`key`), which survives renames.
  - The legacy row builder derives legacy IDs from the codes.
- **Breakdown (desglose) is implied.** Legacy triggers create one `desglose` row for every subject and period, with defaults (`min_dias` 1, `max_dias` 5, `respetar_orden` true, `mismo_dia` false). The fixture never changes them. Here each template references its subject and period directly. The legacy row builder emits one desglose row per subject and period with those defaults. Editable breakdown rules are an open question (2).
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
| `activa` | `active` | default true |
| `creador` | — | see "Ownership" |

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
| `duracion` | `duration_slots` | 1 to 48, default 1 |
| `prioridad` | `priority` | ≥ 1, default 1 |
| `gasto` | `cost` | ≥ 0, default 1 |

### `especialidad`, `nivel` → `ancora_specialty`, `ancora_level`

| Legacy column | Drupal field |
| --- | --- |
| `id`, `descripcion` | `code`, `label` |

### `brigada` → `ancora_cohort`

| Legacy column | Drupal field | Notes |
| --- | --- | --- |
| `id`, `descripcion` | `code`, `label` | |
| `especialidad`, `nivel` | `specialty`, `level` | required |
| `matricula` | `enrollment` | ≥ 0, default 0 |

### `grupo` and `brigada_de_grupo` → `ancora_student_group`

| Legacy column | Drupal field | Notes |
| --- | --- | --- |
| `grupo.id`, `descripcion` | `code`, `label` | |
| `clasificacion` | `activity_type` | the kind of activity the group attends together |
| `especialidad`, `nivel` | `specialty`, `level` | required |
| `planificacion` | `plan` | |
| `brigada_de_grupo(brigada, grupo)` | `cohorts` (multi-value) | `AncoraGroupCohorts`: each cohort has the group's specialty and level, as the legacy `dame_grupos` assumes |

### `asignatura` → `ancora_subject`

| Legacy column | Drupal field | Notes |
| --- | --- | --- |
| `id`, `descripcion` | `code`, `label` | |
| `especialidad` (nullable) | `specialty` (required) | The generator selects subjects by specialty and level, so a subject without one is never scheduled. |
| `nivel` | `level` | required |

### `desglose` and `plantilla_de_actividad` → `ancora_activity_template`

| Legacy column | Drupal field | Notes |
| --- | --- | --- |
| `desglose.asignatura`, `desglose.periodo` | `subject`, `period` | required |
| `plantilla.orden` | `sequence` | 1 to 99, required. Unique per subject and period (`AncoraUniqueInPlan` on subject, period and sequence). Legacy: `UNIQUE (desglose, orden)`, and #23 makes it required by the generator. |
| `plantilla.clasificacion` | `activity_type` | required |
| `exige_profesor`, `exige_lugar` | `requires_teacher`, `requires_room` | default true |
| `desglose.min_dias`, `max_dias`, `respetar_orden`, `mismo_dia` | — | legacy defaults in the export; open question 2 |
| `plantilla.actualizacion_interna` | — | an internal flag of the legacy triggers |

The template has no name or code of its own. Its label is "Subject #sequence (period code)". Its legacy ID is the subject code plus the sequence (`A1`), as in the fixture. When an export holds more than one period, `@` and the period code are appended.

### `profesor`, `lugar` → `ancora_teacher`, `ancora_room`

| Legacy column | Drupal field | Notes |
| --- | --- | --- |
| `profesor.id`, `nombre` | `code`, `label` | |
| `lugar.id`, `nombre` | `code`, `label` | |
| `lugar.capacidad` | `capacity` | ≥ 0, default 0 |

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
| Breakdown rules | `desglose.min_dias`, `max_dias`, `respetar_orden`, `mismo_dia` | Legacy defaults are exported (open question 2). |
| Placements and generation state | `actividad`, `generador.*` | Engine output and engine state (#71). |
| Ownership and users | `creador`, `seguridad.*` | Drupal users, roles and permissions replace them. The tenant boundary is the site (#72). |

## Ownership and permissions

The legacy `creador` column and the `seguridad` schema are replaced by Drupal accounts and three permissions:

| Permission | Grants |
| --- | --- |
| `view academic data` | The planning overview `/ancora/planning`, every list under `/admin/ancora/*`, every record, and the Views page `/ancora/planning/activities`. |
| `edit academic data` | Create, edit and delete every record except plans; edit plans. Grant it together with the view permission. |
| `administer academic data` (restricted) | Everything, including creating and deleting plans. |

`AcademicAccessControlHandler` enforces these on every entity operation, and the routes check them as well (collection permission, `_entity_access`, `_entity_create_access`). Organizations are separated by site (#72), so no plan-level ownership is modelled. The export identifies no user. The legacy row builder takes the legacy `creador` ID as an argument.

## User interface (Drupal core only)

- **Planning overview** at `/ancora/planning` (menu: Administration › Planning). It lists the 13 record types in data entry order, with a count, a description and an "Add" link when the user may create. The module template is `ancora-academic-planning.html.twig`.
- **Lists.** `/admin/ancora/<type>` (plans, periods, activity-types, specialties, levels, cohorts, groups, subjects, templates, rooms, teachers, teacher-assignments, room-assignments) use `AcademicListBuilder`, with the columns declared by each entity class. `?plan=<ID>` narrows a list to one plan.
- **Forms.** Add, edit and delete forms use `AcademicEntityForm` and `AcademicDeleteForm`. A new record is assigned to the plan in `?plan=<ID>`, or to the only plan when there is just one. Every validation rule is an entity or field constraint, so it applies to forms, Drush, the fixture loader and any future API alike.
- **Views.** `views.view.ancora_academic_templates` at `/ancora/planning/activities` lists the weekly activities: plan, subject, number, period and activity type.
- **Navigation.** Menu links, tabs (View, Edit, Delete) and "Add …" actions are derived for every type (`Plugin/Derivative/*`).

## Synthetic fixture

`SyntheticFixture::load()` creates the dataset of `dev/e2e/fixture.sql` through the Entity API, validating every record and in one transaction. It is also available as `drush ancora-academic:load-fixture`.

- By default it also applies the HTTP data entry steps of `dev/e2e/lib.sh`: Teacher Beta (`TB`) is assigned to B1 and B2 for G1 and G2. That is the state in which the legacy engine places 8 of 8 activities.
- `--fixture-only` stops at the exact content of `fixture.sql`. The export then reports that B1 and B2 have no teacher.
- It refuses to run when plan `P` already exists, so repeated runs never mix data.

`install-disposable.sh` loads the fixture into the freshly installed site, exports it and checks that it has 8 demands (4 templates × 2 groups) and 8 teacher–group links. The HTTP smoke test then reads the fixture through the overview, the teacher list and the Views page.

## Export contract (`AcademicExport`, schema `ancora_academic.export.v1`)

`AcademicExport::export(Plan $plan, ?Period $period = NULL): array` returns plain PHP data (scalars and lists, JSON-serializable). It reads with access checks disabled; the caller decides who may export. The command `drush ancora-academic:export <plan code> [--period=<code>] [--legacy]` prints the export, or its legacy rows, as JSON.

```text
schema               "ancora_academic.export.v1"
plan                 {key, code, label, starts_on (Y-m-d|null), duration_days, active}
periods[]            {key, code, label, days, slots_per_day, start_hour, slot_duration, plan_days[]}
activity_types[]     {key, code, label, duration_slots, priority, cost}
specialties[]        {key, code, label}
levels[]             {key, code, label}
cohorts[]            {key, code, label, specialty, level, enrollment}
student_groups[]     {key, code, label, activity_type, specialty, level, cohorts[]}
subjects[]           {key, code, label, specialty, level}
activity_templates[] {key, subject, subject_code, period, period_code, sequence, activity_type,
                      requires_teacher, requires_room}
rooms[]              {key, code, label, capacity}
teachers[]           {key, code, label}
teacher_assignments[] {key, template, teacher, teacher_code, priority, groups[]}
room_assignments[]   {key, template, room, room_code, priority}
issues[]             {type, key, message}
```

Rules:

- **Keys.**
  - Every `key` is the entity UUID: stable across edits, unique across plans and sites.
  - Every reference (`specialty`, `groups[]`, `template`, ...) is the key of the referenced record.
  - Codes are unique in the plan, but they can be edited, so they are not stable keys.
- **Scope.**
  - With a period, `periods` holds only that period, and templates and their assignments are only those of that period.
  - Everything else is plan-wide.
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

### Legacy rows (`LegacyRows::build($export, $creator = 'admin-superuser', $prefix = '')`)

This is a pure translation of an export into rows of the 17 legacy engine tables, in foreign key order:

1. `planificacion`, `periodo`, `periodicidad`
2. `clasificacion_de_actividad`, `especialidad`, `nivel`
3. `brigada`, `grupo`, `brigada_de_grupo`
4. `asignatura`, `desglose`, `plantilla_de_actividad`
5. `profesor`, `profesor_para_actividad`, `profesor_para_grupo`
6. `lugar`, `lugar_para_actividad`

The engine sync (#71) decides how to write them.

- **IDs.** `$prefix` is prepended to every legacy ID, to keep several plans apart in one engine database. Legacy IDs are:
  - the codes, for coded records;
  - `<subject><sequence>` for templates, with `@<period>` appended when there are several periods;
  - `<subject>@<period>` for desglose rows;
  - `<period>@<day>` for periodicidad rows;
  - `<template>@<teacher>` for teacher assignments.
- **Triggers.** The legacy triggers `nuevo_desglose_cuando_nueva_asignatura` and `nuevo_desglose_cuando_nuevo_periodo` insert desglose rows by themselves. A loader that keeps them enabled must reuse those rows (by subject and period) instead of inserting the desglose rows given here.
- **Equivalence.** `FixtureExportTest` reads `dev/e2e/fixture.sql` and compares it with the rows built from the Entity API fixture:
  - the 11 tables that the fixture lists literally match row for row, IDs included;
  - periodicidad, profesor_para_actividad and profesor_para_grupo, whose IDs are generated, match on content;
  - desglose, the templates and the room assignments match the semantics of the trigger and of the `INSERT … SELECT` statements.

## Tests

| Test | Kind | What it proves |
| --- | --- | --- |
| `AcademicEntityTest` (5) | Kernel | CRUD of the 13 types through the fixture. Required fields and ranges (days, slots, slot length, start hour, plan days, duration, priority, cost, capacity, code pattern). Codes unique per plan but not across plans, plan codes unique on the site, unique template numbers and assignments. References within the plan. Group cohorts and assignment groups eligible. Deletion refused while referenced. |
| `AcademicAccessTest` (1) | Kernel | For every type, the view, update, delete and create matrix of no permission, viewer, editor and administrator. Plans are created and deleted by administrators only. |
| `FixtureExportTest` (6) | Kernel | Shape, counts, references and determinism of the export. Issues of the fixture-only dataset. Legacy rows equal to `dev/e2e/fixture.sql`. Teacher Beta as in `dev/e2e/lib.sh`. Port of the legacy day number. Plan scope and refusal to load the fixture twice. |
| `PlanningUiTest` (3) | Functional (HTTP) | Anonymous users get 403 on the overview, lists, forms, records and Views page. Viewers read but cannot add. Editors create through validated forms (range and duplicate code errors), edit and delete, and cannot create or delete plans. A plan with records shows its dependents and no Delete button. The `?plan=` filter. |

Results on the workstation (PHP 8.4.27, PostgreSQL 16, `dev/sql/tools/with-ephemeral-pg.sh modern/scripts/install-disposable.sh --skip-composer`):

- 23 PHPUnit tests (8 foundation + 15 academic), 892 assertions;
- phpcs: 80 files clean;
- configuration determinism: 87 files;
- HTTP smoke test: 25 of 25 checks;
- total about 105 s.

## LBR impact

**0 bytes retired.** This is new target code. It replaces no legacy responsibility yet:

- the legacy data entry screens (`control/data/add/*`, `custom_*`) and tables are still the ones the engine runs on;
- retirement credit needs the engine to run on the exported data (#71) and an accepted comparison (#77).

The module adds no file to the frozen LBR baseline, and it modifies no legacy file.

## Open questions

1. **Shared catalogues across plans.** Should specialties, levels, subjects, rooms and teachers be shared by all the plans of a site, as with legacy `creador` ownership, or copied per plan as now? A "copy plan" action would make per-plan copies cheap.
2. **Breakdown rules.** Should `min_dias`, `max_dias`, `respetar_orden` and `mismo_dia` be edited per subject and period? The legacy `max_dias` default (5) ignores the period's `cant_dias`.
3. **Restrictions.** How should the time restrictions be modelled: entities per target type, or one entity with a target reference? Should the legacy "real restriction" materialization stay in the engine?
4. **Cohort membership.** Legacy `mueve_o_crea_brigada_de_grupo` moves a cohort between groups. Should a cohort be in at most one group per activity type? This is not enforced yet.
5. **Capacity.** Rooms smaller than the enrolment of a group's cohorts are filtered by the engine (`quita_segun_capacidad`). Should the UI warn about this as an export issue?
6. **Engine IDs.** Is a per-plan `$prefix` on legacy IDs enough for #71, or should the engine tables carry the UUID keys? *Answered by the R1 journey:* the prefix `p<plan id>:` is used, with a legacy owner per plan; see [r1-scheduling-journey.md](r1-scheduling-journey.md).
