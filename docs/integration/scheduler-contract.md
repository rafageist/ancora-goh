# Scheduler interoperability contract and data ownership boundaries (#144)

Status: **design for approval**. Nothing here is implemented. No API, endpoint, module, schema change or Odoo integration is authorized by this document.

Issue: #144, under the vision #142 (gate D2). Related: #66 (Drupal architecture), #70 (academic domain), #71 (engine worker), #95 (placement promotion), #133 (temporal semantics), #130/#141 (editing), #143 (workload assessment, PR #149), #145 (release readiness, PR #150).

Binding constraints from #142 and #144:
- GOH works **standalone** and as an **optional scheduling subsystem** of another application. It is never a student information system or an ERP. Odoo is one possible caller, never a dependency.
- Drupal is one UI and hosting implementation; MPI/PriorizA is one generator. Neither is assumed by the contract.
- No universal solver framework (decision 5). GOH keeps its academic vocabulary.
- No remote users, arbitrary tenants, microservices or Odoo-specific schema are presumed.

Code citations are `path:line` at commit `f299f10`. Module paths are shortened:
- `academic/` = `modern/web/modules/custom/ancora_academic/`;
- `engine/` = `modern/web/modules/custom/ancora_engine/`;
- `scheduling/` = `modern/web/modules/custom/ancora_scheduling/`.

Contents:
1. Boundary and responsibility matrix
2. Boundary diagram
3. Problem contract (`ancora_scheduler.problem.v1`)
4. Job lifecycle contract
5. Result and exchange contract (`ancora_scheduler.timetable.v1`)
6. Idempotent import and upsert
7. Minimal paths compared; the recommended slice
8. Threat model for external synchronization
9. Incremental roadmap and reusable contracts
10. Decisions needed from the maintainer

---

## 1. Boundary and responsibility matrix

### 1.1 Who owns what

"Owns" means: is the source of truth, decides the lifecycle, and is the only side allowed to change the record. The other side holds at most a copy.

| Data | Owner when GOH is a subsystem | Owner when GOH is standalone | GOH today (evidence) |
|---|---|---|---|
| Students: identity, enrolment, lifecycle, grades | **caller** (SIS). GOH never holds students. | not held | No student entity. Only aggregate **cohort enrolment counts** (`ancora_cohort.enrollment`, `academic-domain-mapping.md` §`brigada`) |
| Staff and teacher profiles: identity, contracts, HR, payroll | **caller** (HR) | GOH holds a minimal record (code, name) | `ancora_teacher` has code and label only (`academic-domain-mapping.md` §`profesor`) |
| Facilities: rooms, buildings, equipment inventory | **caller** (facilities or asset system) | GOH holds a minimal record | `ancora_room` (code, label, capacity), `ancora_other_resource` (code, label) |
| Academic catalogue: programmes, levels, subjects, cohorts | **caller** (curriculum) | GOH | `ancora_specialty`, `ancora_level`, `ancora_subject`, `ancora_cohort`, owned by a user (D1, `academic-domain-mapping.md:47-52`) |
| Finance, tuition, inventory, purchasing | **caller** | out of scope | none |
| Planning definitions: plans, periods, slots, repetitions, breaks, activity types, templates, groups for scheduling, assignments | **GOH** | GOH | `ancora_plan`, `ancora_period`, `ancora_activity_template`, `ancora_student_group`, `ancora_teacher_assignment`, `ancora_room_assignment`, `ancora_breakdown`, `ancora_break` |
| Availability and restrictions | **GOH** (may be seeded from the caller, then edited in GOH) | GOH | `ancora_time_restriction` (`academic/src/Entity/TimeRestriction.php:78`), `ancora_load_limit`, `ancora_group_room` |
| Preferences and weights | **GOH** | GOH | activity type priority, assignment priorities, `ancora_priority_zone`, `ancora_travel_cost`, resource group priority |
| Solver runs, options, diagnostics | **GOH** | GOH | `ancora_engine_job` (`engine/src/Job/Job.php`), `RunResult` (`engine/src/Engine/RunResult.php:67`) |
| Candidate solutions (drafts) | **GOH** | GOH | placements of a `partial` or soft-`cancelled` job (`engine/src/JobRepository.php:185`) |
| Canonical schedule decisions | **GOH** | GOH | `ancora_placement` (`scheduling/src/Entity/Placement.php:71-95`) |
| Manual edits, audit, undo | **GOH** | GOH | `ancora_placement_change` (`scheduling/src/Entity/PlacementChange.php:53-85`) |
| Publication of the timetable | **GOH** publishes; the caller **consumes** and may republish in its own channels | GOH | final versus draft read model (`scheduling/src/Timetable/TimetableReader.php:96-97`); no separate "published" state (§5.1) |
| Users, roles, permissions of GOH | **GOH's host** (Drupal), optionally federated from the caller's identity provider | Drupal | `*.permissions.yml`; `AcademicAccessControlHandler` |

Rule: **GOH never becomes the master of a caller-owned attribute.** When the caller owns a record, a GOH user may not edit the caller-owned attributes of it. The import overwrites them (§6). GOH-owned attributes of the same record (for example a teacher's time restrictions) are never written by a reference-data import.

### 1.2 Imported references versus local snapshots

GOH needs some caller-owned attributes **at run time**, inside the engine transaction, without calling the caller. Those are copied and versioned. Everything else stays a reference.

| Attribute | Kind | Why | Refresh |
|---|---|---|---|
| External id of a teacher, room, cohort, subject, specialty, level, other resource | **imported reference** (mapping only) | identity across systems | at import |
| Display name (teacher name, room name, subject title) | **local snapshot** | timetables and exports must render without the caller | each import; never edited in GOH while caller-owned |
| Room capacity | **local snapshot** | the engine filters rooms by capacity (`quita_segun_capacidad`, #143 §2.1) | each import |
| Cohort enrolment count | **local snapshot** (aggregate number only, no student list) | capacity check against enrolment (#143 §2.1) | each import |
| Specialty and level of a cohort or subject | **local snapshot** | they decide the run scope and group eligibility (`ScopeMapper::scope`, `scheduling/src/ScopeMapper.php:74`) | each import |
| Teacher contract hours, HR availability | **not imported** as such. A caller may translate availability into GOH time restrictions or load limits, which then are GOH-owned | GOH has no contract model; scheduling restrictions are the only input the engine reads | — |
| Student lists, personal contact data, salaries | **never imported** | out of scope, privacy | — |

Every snapshot is part of the plan's input hash (`ScopeMapper::hash`, `scheduling/src/ScopeMapper.php:95`) and of the site version (`SiteRows::version`, `scheduling/src/Sync/SiteRows.php:145`). Any refresh therefore marks the existing timetable `stale` (`TimetableReader.php:97`) and refuses the publication of a run whose input changed (`PlacementPublisher.php:88-90`). That behaviour is what makes snapshots safe. It is reused, not redesigned.

### 1.3 Standalone operation

Standalone is the **default** and requires nothing of this document:
- every record is entered in Drupal forms, loaded through the Entity API (`SyntheticFixture::load`, `academic/src/Fixture/SyntheticFixture.php:70`), or, after the recommended slice, imported from a file;
- every record has `uid` as owner, a `code` unique in its scope, and a UUID (`academic-domain-mapping.md:53-56`);
- no external id is required. The `ext` fields of the contract (§3) are optional, and absent means "GOH-owned".

A subsystem deployment is the same installation with records whose `ext` is set and whose caller-owned attributes are read-only for GOH users. There is no second code path for the engine, the worker or the timetable.

---

## 2. Boundary diagram

Solid arrows exist today; dashed arrows (`- - >`) are proposed by this document.

```text
 CALLER (optional: SIS / HR / facilities / ERP such as Odoo)          STANDALONE OPERATOR
 owns: students, staff, facilities, catalogues, HR, finance           (browser, drush)
        |                                   ^                                 |
        | problem.v1 JSON (file)            | timetable.v1 JSON (file)        | forms, drush
        v - - - - - - - - - - - - - - - - - | - - - - - - - - - - - - - - - - v
 +------------------------------ Áncora GOH site (one Drupal site, one database) --------------------+
 |  INTEROP ADAPTER (proposed, drush first)                                                          |
 |   import: schema check -> resolve codes/ext -> Entity API validate() -> one transaction          |
 |   export: TimetableReader + CrossPlanConflictDetector + PeriodInputs -> deterministic JSON       |
 |        |                                                     ^                                    |
 |        v                                                     |                                    |
 |  ancora_academic (planning input, owned by uid)              |  ancora_scheduling (results)       |
 |   plans, periods, catalogues, templates, assignments,        |   ancora_placement (canonical)     |
 |   restrictions, breakdowns, breaks, zones, limits ...        |   ancora_placement_change (audit)  |
 |   AcademicExport v7 (academic/src/Export/AcademicExport.php:73)  TimetableReader / ConflictDetector|
 |        |                                                     ^                                    |
 |        | SiteRows::site() + placements(), one REPEATABLE READ snapshot, site_version              |
 |        v                                                     | PlacementPublisher: fence, stale,  |
 |  ancora_engine: job queue (JobRepository), Worker (drush)    |   replay by engine_id, validate    |
 |   JobState / JobOptions / JobSource / RunResult              |   as plan creator                  |
 |        |  WorkspaceSync::prepare() -> LegacyRows rows        |                                    |
 |        v                                                     |                                    |
 |  job-scoped SQL workspace (schema + NOLOGIN role per job attempt, dev/workspace/workspace.sql)    |
 |   unchanged MPI/PriorizA generar() (dev/sql/generador/generar.sql)  -- output rows -------------+  |
 +--------------------------------------------------------------------------------------------------+
```

Trust and ownership lines:
- The caller never writes Drupal tables, the engine tables or a workspace. It only hands over a file, or later a request to an authenticated adapter.
- The engine never sees caller ids. It sees numbered engine ids `0000000012:G1` built from entity ids (`LegacyRows::engineId`, `academic/src/Export/LegacyRows.php:68`).
- The worker is the only process that runs the generator (`engine/src/Worker.php:20-23`), one job per site (`JobRepository::claim`, `engine/src/JobRepository.php:231`).

---

## 3. Problem contract (`ancora_scheduler.problem.v1`)

### 3.1 Principles

- **Engine-agnostic, not domain-agnostic.** The contract describes the academic problem: time, resources, activities, constraints and preferences. It does not mention `generar()`, legacy tables or engine ids. Generator options live in the **job request** (§4), in a block named after the generator.
- **One plan per document**, one owner. This matches the scope of `AcademicExport::export` (`AcademicExport.php:102`) and the ownership rule D1–D3.
- **References by code** inside the document, as codes are unique per owner or per plan (`AncoraUniqueInScope`, `academic-domain-mapping.md:50`). An optional `ext` carries the caller's id (§6).
- **Derived, not sent:** demands (activity template × eligible group), breakdown rows the legacy triggers create, legacy ids, engine ids, the closure of a run.
- **Versioned.** `schema` is checked before anything else. A breaking change makes `v2`, as `AcademicExport` does (`academic-domain-mapping.md:303`).

### 3.2 Shape and example (the synthetic fixture, plan P)

This is the dataset of `dev/e2e/fixture.sql` plus the teacher the HTTP harness adds (`academic-domain-mapping.md:239`). Identical data must give an identical document: lists are sorted as the export sorts them (`academic-domain-mapping.md:292-297`).

```json
{
  "schema": "ancora_scheduler.problem.v1",
  "source": {
    "system": "registrar-demo",
    "document_id": "2026-11-01T09:00:00Z#17",
    "base_version": null
  },
  "plan": {
    "code": "P", "label": "Plan", "ext": "term/2026-1",
    "starts_on": null, "ends_on": null
  },
  "calendar": {
    "time_basis": "floating-local",
    "periods": [
      { "code": "PER", "label": "Week", "days": 5, "slots_per_day": 4,
        "start_hour": 8.0, "slot_duration": 1.0, "plan_days": [1], "breaks": [] }
    ]
  },
  "catalogues": {
    "specialties":    [ { "code": "E", "label": "Program", "ext": "prog/E" } ],
    "levels":         [ { "code": "N", "label": "Year 1" } ],
    "activity_types": [ { "code": "C1", "label": "Lecture", "duration_slots": 1, "priority": 1, "cost": 1 } ],
    "subjects": [
      { "code": "A", "label": "Subject Alpha", "specialty": "E", "level": "N" },
      { "code": "B", "label": "Subject Beta",  "specialty": "E", "level": "N" }
    ]
  },
  "resources": {
    "teachers": [ { "code": "TA", "label": "Teacher Alpha", "ext": "hr/4711" },
                  { "code": "TB", "label": "Teacher Beta" } ],
    "rooms":    [ { "code": "L1", "label": "Room 1", "capacity": 50 },
                  { "code": "L2", "label": "Room 2", "capacity": 50 } ],
    "cohorts":  [ { "code": "B1", "label": "Cohort 1", "specialty": "E", "level": "N", "enrollment": 20 },
                  { "code": "B2", "label": "Cohort 2", "specialty": "E", "level": "N", "enrollment": 20 } ],
    "other_resources": []
  },
  "student_groups": [
    { "code": "G1", "label": "Group 1", "activity_type": "C1", "specialty": "E", "level": "N", "cohorts": ["B1"] },
    { "code": "G2", "label": "Group 2", "activity_type": "C1", "specialty": "E", "level": "N", "cohorts": ["B2"] }
  ],
  "activities": [
    { "subject": "A", "period": "PER", "sequence": 1, "activity_type": "C1",
      "requires_teacher": true, "requires_room": true,
      "teachers": [ { "teacher": "TA", "priority": 1, "groups": ["G1", "G2"] } ],
      "rooms":    [ { "room": "L1", "priority": 1 }, { "room": "L2", "priority": 2 } ],
      "resource_groups": [] }
  ],
  "constraints": {
    "breakdowns": [
      { "subject": "A", "period": "PER", "min_days": 1, "max_days": 5, "respect_order": true, "same_day": false }
    ],
    "time_restrictions": [
      { "period": "PER", "kind": "teacher", "target": "TA", "day": 5, "slot": 4 }
    ],
    "load_limits": [],
    "group_rooms": []
  },
  "preferences": {
    "priority_zones": [],
    "travel_costs": []
  }
}
```

The example shows one of the four activities (A1); A2, B1 and B2 follow the same pattern, with TB for B1 and B2. The time restriction is illustrative; the fixture has none.

Field rules:
- `starts_on`, `ends_on`: `YYYY-MM-DD` or `null`. `null` keeps the calendar **unknown** (§5.4); it is never replaced by a default date.
- `time_basis` is always `"floating-local"` in v1 (§5.4). Any other value is refused.
- Numbers follow the export: `start_hour`, `slot_duration` and break lengths are decimals with 2 places; everything else is an integer (`academic-domain-mapping.md:298`).
- An activity is identified by (`subject`, `period`, `sequence`). Its key in results is the template's UUID (§5).
- `ext` is an opaque string of at most 255 characters, unique per (`source.system`, record type).

### 3.3 Mapping onto the export schema and the legacy tables

| `problem.v1` | `ancora_academic.export.v7` (`academic-domain-mapping.md:249-277`) | Drupal entity | Legacy table (`LegacyRows::TABLES`, `LegacyRows.php:75`) |
|---|---|---|---|
| `plan` | `plan` (+ `ends_on`, not exported today, §3.6) | `ancora_plan` | `planificacion` |
| `calendar.periods[]` | `periods[]` | `ancora_period` | `periodo`, `periodicidad` (one row per `plan_days` value) |
| `calendar.periods[].breaks[]` | `breaks[]` | `ancora_break` | `receso` |
| `catalogues.specialties[]`, `levels[]` | `specialties[]`, `levels[]` | `ancora_specialty`, `ancora_level` | `especialidad`, `nivel` |
| `catalogues.activity_types[]` | `activity_types[]` | `ancora_activity_type` | `clasificacion_de_actividad` |
| `catalogues.subjects[]` | `subjects[]` | `ancora_subject` | `asignatura` |
| `resources.teachers[]` | `teachers[]` | `ancora_teacher` | `profesor` |
| `resources.rooms[]` | `rooms[]` | `ancora_room` | `lugar` |
| `resources.cohorts[]` | `cohorts[]` | `ancora_cohort` | `brigada` |
| `resources.other_resources[]` | `other_resources[]` | `ancora_other_resource` | `otro_recurso` |
| `student_groups[]` | `student_groups[]` | `ancora_student_group` | `grupo`, `brigada_de_grupo` |
| `activities[]` | `activity_templates[]` | `ancora_activity_template` | `plantilla_de_actividad` (+ `desglose` by trigger) |
| `activities[].teachers[]` | `teacher_assignments[]` | `ancora_teacher_assignment` | `profesor_para_actividad`, `profesor_para_grupo` |
| `activities[].rooms[]` | `room_assignments[]` | `ancora_room_assignment` | `lugar_para_actividad` |
| `activities[].resource_groups[]` | `resource_groups[]` | `ancora_resource_group`, `ancora_resource_member` | `otro_grupo_de_recursos_para_actividad`, `otro_recurso_para_actividad` |
| `constraints.breakdowns[]` | `breakdowns[]` | `ancora_breakdown` | `desglose` options |
| `constraints.time_restrictions[]` | `time_restrictions[]` | `ancora_time_restriction` | `restriccion_de_{periodo,clasif_act,asignatura,brigada,profesor,lugar}` (`LegacyRows.php:92`) |
| `constraints.load_limits[]` | `load_limits[]` | `ancora_load_limit` | `higiene_para_profesor`, `higiene_para_brigada` |
| `constraints.group_rooms[]` | `group_rooms[]` | `ancora_group_room` | `lugar_fijo`, `acceso_prohibido` |
| `preferences.priority_zones[]` | `priority_zones[]` | `ancora_priority_zone` | `zpriori` |
| `preferences.travel_costs[]` | `travel_costs[]` | `ancora_travel_cost` | `costo_de_traslado` |
| — (derived) | `issues[]` | — | — |
| — (job request, §4) | — | `ancora_engine_job.options` | `generar()` parameters 4–8 (`JobOptions::toParameters`) |

The two schemas differ on purpose:
- **export v7** is GOH's internal, keyed (UUID) and complete projection, consumed by `LegacyRows::build` (`LegacyRows.php:120-121`, which refuses any other schema) and hashed for versioning. It stays unchanged.
- **problem v1** is the external, code-keyed projection for callers. An importer converts v1 into entities. `AcademicExport` then produces v7 as today. There is no second path to the engine.

### 3.4 What the engine actually honours

A contract that accepts a constraint must say whether the current generator enforces it. The marks come from #143 §2 (`docs/evidence/workload-assessment.md` on PR #149) and the cases it cites.

| Contract element | Mark | Meaning for a caller | Evidence |
|---|---|---|---|
| Teacher, room, cohort, other resource busy at most once per (period, day, slot) | **hard** | never violated inside one period; a violating insert fails the run | #143 §2.1; cases 012, 060, 083, 149 |
| `time_restrictions` kinds `period`, `activity_type`, `subject`, `cohort`, `teacher`, `room` | **hard** | the cell is not used | #143 §2.1; case 139, harness s08 |
| `activities[].teachers[].groups` (eligibility) | **hard** | no assignment, no placement | case 060 |
| room eligibility and `capacity` against cohort `enrollment` (0 = unlimited) | **hard** | | cases 122, 141 |
| `load_limits` (daily) | **hard**, with the legacy cost semantics (6.21) | the limit is `(1 + activities that day) × cost`; limit 0 places nothing | case 140, harness s09 |
| `breakdowns` | **hard**, with quirks Q-04, Q-22, Q-24 | | case 135, harness s06 |
| `group_rooms` kind `fixed` | **ordering**: the first fixed room is tried first when free | not a guarantee that the room is used | case 141 |
| `group_rooms` kind `forbidden` | **inert** (Q-30) | a forbidden room may be used | case 141 |
| `activities[].resource_groups[].essential` | **not guaranteed** (6.3/6.13) | an essential group may be ignored | case 024 (xfail) |
| `other_resources` under contention | **hazard** (Q-31/Q-32) | a busy chosen resource fails the **whole run** (23505) | cases 083, 142 |
| priorities: activity type `priority`, assignment `priority`, resource `priority` | **ordering only** | they order a greedy search; there is no objective value | #143 §2.2 |
| `preferences.travel_costs`, room strategy | **ordering only** (`buscalugar`) | | case 141, harness s10 |
| `preferences.priority_zones` | **inert** unless the job option `parche1` is true, which no legacy screen sends (Q-19) | | case 140, harness s09 run F |
| activity type `cost` | used by the load-limit formula only | | 6.21 |
| `calendar.periods[].breaks` | **display only**: they shift the slot times, not the placement | | `academic-domain-mapping.md:196`; `temporal-semantics.md:26` |
| `plan.starts_on`, `plan_days` (real dates) | **warning only** across plans; not read by the generator (the real-date branch is dead, Q-01) | | `temporal-semantics.md` §6; case 129 |
| Cross-plan and cross-period teacher or room overlap | **warning only**, for dated plans of one owner | never refused | `CrossPlanConflictDetector` (`scheduling/src/Timetable/CrossPlanConflictDetector.php:107`) |
| Restriction kinds `specialty`, inherited (HRT), conditional, real-date, manual other-resource | **unavailable**: not in v1 | refused by validation (Q-28/Q-29 fail every run; Q-32 not provided) | cases 070–072, 100; `TimeRestriction::KINDS` (`TimeRestriction.php:78`) has six kinds |
| Activity whose `duration_slots` exceeds the slots of the day | **not prevented** (6.17) | placements past the last slot are committed and, in workspace mode, published | case 025 (xfail), 138 |
| Activity with `requires_teacher` or `requires_room` false | **hazard** (Q-05) | can never be placed and fails the whole run (23502) | case 123 |
| Groups of another plan with the same specialty and level | **leak** (Q-02) | a run can place other plans' groups into this period | case 121, harness s03 |

Validation (§4.1) turns this table into diagnostics:
- **unavailable** → `error`, nothing is imported or queued;
- **inert**, **not guaranteed**, **hazard**, **leak** → `warning` with the code above;
- **ordering only** → `info` on first use, so that a caller does not read a weight as a guarantee.

### 3.5 Preferences and weights

The contract carries **ordinal** priorities (integers ≥ 1, lower first), as the entities store them. It does not carry numeric weights of a cost function, because the generator computes no objective (#143 §2.2: "The engine computes no objective value"). A caller that sends a weighted preference model must map it to priorities, restrictions or limits before export. A future generator with an objective would add an optional `weights` block in `v2`. That needs the second-generator evidence of #142 decision 5.

### 3.6 Gaps found while grounding the contract

- `ends_on` exists on the plan (`academic/src/Entity/Plan.php:106`) but is not in export v7 (`AcademicExport.php:123-131`). The problem contract carries it; the export needs a `v8` to round-trip it (decision D-9).
- The export reports four issue types (`AcademicExport.php:575-619`). The validator of §4.1 adds the engine-support diagnostics of §3.4; it does not change the export's `issues`.

---

## 4. Job lifecycle contract

The lifecycle reuses the #71/#95 job model unchanged. The contract only names the steps and the documents exchanged.

### 4.1 Validate (synchronous, no job)

Input: a `problem.v1` document, or an existing plan and period.

Steps, all read-only:
1. JSON well-formed, size and depth bounded (§8), `schema` known.
2. Structural validation against a JSON Schema file versioned with the contract.
3. Reference resolution: every code resolves inside the document or to a record of the same owner or plan (as `AncoraSameCreator` does, `academic-domain-mapping.md:51`).
4. Entity validation in a transaction that is **rolled back** (dry run): the entity and field constraints apply "to forms, Drush, the fixture loader and any future API alike" (`academic-domain-mapping.md:231`).
5. The export's `issues` (`AcademicExport.php:575`) and the engine-support diagnostics of §3.4.

Output:

```json
{
  "schema": "ancora_scheduler.validation.v1",
  "valid": true,
  "changes": { "create": 23, "update": 0, "unchanged": 0 },
  "diagnostics": [
    { "severity": "warning", "code": "forbidden_room_inert", "path": "/constraints/group_rooms/0",
      "message": "Forbidden rooms are stored but the current generator does not apply them (Q-30)." },
    { "severity": "info", "code": "priority_is_ordinal", "path": "/activities/0/rooms/1/priority",
      "message": "Priorities order the search; they are not weights and nothing measures them." }
  ]
}
```

`valid` is false when any diagnostic is an `error`. Nothing is written in either case.

### 4.2 Start a bounded job

A job request names a scope and options. It maps onto the existing types:

| Request field | Existing type | Rule |
|---|---|---|
| `plan`, `period`, `specialty`, `level` (codes of one owner) | `JobSource` (`engine/src/Job/JobSource.php:23`), built by `ScopeMapper::source` (`scheduling/src/ScopeMapper.php:82`) | the period belongs to the plan, the catalogues to the plan's creator (`ScopeMapper.php:102`) |
| — | `JobScope` (`engine/src/Job/JobScope.php:10`) | derived engine ids |
| `generator.id` | — | `"mpi-prioriza"`, the only value in v1 |
| `generator.options` | `JobOptions::fromArray` (`engine/src/Job/JobOptions.php:49`) | unknown keys refused; `forma` 1–2, `compactar` 0–10 |
| `time_limit` | `JobRepository::enqueue` (`engine/src/JobRepository.php:66-68`), `EngineSettings::timeLimit/maxTimeLimit` (`engine/src/EngineSettings.php:121-128`) | positive, capped by the site maximum |
| `requested_by` | `ancora_engine_job.requested_by` | the authenticated account, never a value from the document |

```json
{
  "schema": "ancora_scheduler.job_request.v1",
  "plan": "P", "period": "PER", "specialty": "E", "level": "N",
  "generator": { "id": "mpi-prioriza",
                 "options": { "forma": 1, "parche": false, "compactar": 1, "parche1": false, "buscalugar": true } },
  "time_limit": 120
}
```

Today a sourced job is queued by the planner request form (`scheduling/src/Form/RequestRunForm.php`) and by the journey helper `modern/scripts/lib/workspace-journey/enqueue.php:39-45`. `drush ancora-engine:enqueue` (`engine/src/Drush/Commands/AncoraEngineCommands.php:39`) queues only source-less engine scopes.

Bounds that already hold: one running job per site (`JobRepository::claim`, `JobRepository.php:231`); a statement timeout and a wall-clock guard (`engine-worker.md` §3); the run is a CLI process, never a web request (`Worker.php:20-23`).

### 4.3 States, cancel, retry

States are `JobState` as is (`engine/src/Job/JobState.php:30-36`): `queued`, `running`, `succeeded`, `partial`, `cancelled`, `timed_out`, `failed`. Transitions: `JobState::canTransitionTo` (`JobState.php:55`). Terminal: everything but `queued` and `running` (`JobState.php:41`).

| Operation | Existing implementation | Contract semantics |
|---|---|---|
| cancel (soft) | `JobRepository::requestCancel($id, $uid, FALSE)` (`JobRepository.php:335`); `drush ancora-engine:cancel` (`AncoraEngineCommands.php:135`) | a queued job becomes `cancelled` with `kept: none`; a running job stops at its next check and keeps what it placed (`kept: partial`, a draft) |
| cancel (hard) | same, `$hard = TRUE`; `Worker::cancel` (`engine/src/Worker.php:364`) | rolled back, `kept: none` |
| retry | `drush ancora-engine:retry` (`AncoraEngineCommands.php:148`) | a **new** job with `retry_of`; never a mutation of a terminal job |
| status | `drush ancora-engine:job <id>` prints JSON (`AncoraEngineCommands.php:175`) | §4.4 |
| recover | `drush ancora-engine:recover` (`AncoraEngineCommands.php:164`); stale heartbeat → requeued once (`JobState.php:26`) | idempotent: a replayed attempt publishes nothing twice (`PlacementPublisher.php:104-108`) |

### 4.4 Outcome, partial results and diagnostics

The outcome document is the job row plus `RunResult` data as the worker records it (`Worker::outcome`, `Worker.php:273-311`): metrics `demands`, `placed`, `unplaced`, `collisions`, `overflow` (`engine/src/Engine/EngineClient.php:52-84`), `duration_ms`, `replaced`, `attempt`, `mode`, `input_hash`, `input` (site version, snapshot, rows loaded), `partial`, `kept`.

```json
{
  "schema": "ancora_scheduler.job_outcome.v1",
  "job": { "id": 12, "uuid": "8b0f…", "state": "partial", "attempts": 1,
           "created": 1793523600, "started": 1793523601, "finished": 1793523603 },
  "scope": { "plan": "P", "period": "PER", "specialty": "E", "level": "N" },
  "metrics": { "demands": 8, "placed": 6, "unplaced": 2, "collisions": 0, "overflow": 0 },
  "kept": "partial",
  "input": { "input_hash": "3f9c…", "site_version": "a41e…", "schema": "ancora_academic.export.v7" },
  "unplaced_reasons": null,
  "qualifications": [
    "unplaced_reasons_not_recorded",
    "cross_plan_conflicts_are_warnings"
  ],
  "error": null
}
```

What the contract must state honestly:

- **Unplaced demands have no recorded reason (6.20).** `generar()` only raises a NOTICE for an unplaced demand (`dev/sql/generador/generar.sql:1488-1492`, "TODO: registrar"); two reasons exist only as NOTICE text (`:1436` no room, `:1455` no teacher). The outcome has **counts, not reasons**. `unplaced_reasons` is `null` in v1, never an empty list, so that "no reason recorded" is not read as "no reason". A list of the unplaced (template, group) pairs can be derived from Drupal data after the run (demands minus placements), but without a cause. A read-only diagnostic is #143 DEC-5.
- **A "succeeded" job can contain overflow (6.17).** In workspace mode the worker does not roll back collisions or overflow (`WorkspaceClient.php:116-124`; `PlacementPublisher.php:44-46`), and the state follows only `unplaced = 0` (`Worker.php:286`, `:294`). The class comment that requires "no collision, no overflow" (`Worker.php:26-27`) holds for the two-database mode only, where `EngineClient.php:226-228` rolls back. The outcome therefore always carries `collisions` and `overflow`, and adds the qualification `overflow_present` or `collisions_present` when either is above 0. A consumer must not equate `state = succeeded` with "valid timetable" (§5.1).
- `partial` and soft-cancelled results are **drafts**: stored and continued, never final (`JobState.php:15-20`; `JobRepository.php:185-188`).
- `timed_out`, `failed` and hard `cancelled` keep nothing of the run (`engine-worker.md` §3).
- Publication failures are reported as `fenced`, `stale` or `invalid` (`PlacementPublisher.php:84-90`; `PublicationException`). `stale` means the input changed during the run; the remedy is a new request (proposal P-1 would requeue automatically, `placement-promotion.md` §11).

---

## 5. Result and exchange contract (`ancora_scheduler.timetable.v1`)

### 5.1 Draft, final, published

| Contract status | Source of truth today | Rule |
|---|---|---|
| `draft` | latest placement-changing job of the scope is `partial`, or `cancelled` with `kept: partial` (`JobRepository::changedPlacements`, `JobRepository.php:185-188`) | always exportable, always labelled draft |
| `final` | that job is `succeeded` (`TimetableReader.php:96`) | exported **with** its metrics; see the qualification below |
| `published` / `approved` | **does not exist** | proposal P-2 (`placement-promotion.md` §11) and #143 DEC-3 are open. v1 does not invent it |

Qualification of `final`: per #143 §2.6, the defensible statement is "valid within its period as the scheduler sees it; cross-plan conflicts: none found among dated plans, N warnings, or not evaluated", and only with `unplaced = 0`, `collisions = 0` and `overflow = 0` shown. The export therefore adds a computed, non-authoritative flag:

```text
within_period_clean = (status == "final") and collisions == 0 and overflow == 0
```

Whether a caller may treat `final` without `within_period_clean` as publishable is decision D-3.

A scope also carries `stale: true` when the plan's data changed after the run (`TimetableReader.php:97`). A stale final timetable is still the stored timetable, as in legacy, but the consumer is told.

### 5.2 Bookings, revisions and conflict evidence

```json
{
  "schema": "ancora_scheduler.timetable.v1",
  "generator": { "application": "ancora-goh", "input_schema": "ancora_academic.export.v7" },
  "plan":   { "key": "5d2c…", "code": "P", "label": "Plan", "ext": "term/2026-1",
              "starts_on": "2026-09-07", "ends_on": "2026-10-04" },
  "period": { "key": "c01e…", "code": "PER", "days": 5, "slots_per_day": 4,
              "start_hour": 8.0, "slot_duration": 1.0, "plan_days": [1, 8, 15, 22] },
  "time_basis": "floating-local",
  "input_version": "3f9c…",
  "scopes": [
    { "specialty": "E", "level": "N", "status": "final", "stale": false,
      "within_period_clean": true,
      "job": { "id": 12, "uuid": "8b0f…", "state": "succeeded" },
      "metrics": { "demands": 8, "placed": 8, "unplaced": 0, "collisions": 0, "overflow": 0 },
      "revision": "sha256:91ab…",
      "placements": [
        { "key": "e7a1…", "engine_id": "0000000031:A1",
          "activity": { "key": "a9d0…", "subject": "A", "sequence": 1 },
          "group": "G1", "teacher": { "code": "TA", "ext": "hr/4711" }, "room": { "code": "L1" },
          "other_resources": [],
          "day": 1, "slot": 1, "slots": 1,
          "start": "08:00", "end": "09:00",
          "occurrences": [ { "date": "2026-09-07", "start": "08:00", "end": "09:00", "after_end": false } ],
          "last_change": null }
      ] }
  ],
  "cross_plan": { "evaluated": true, "unknown": [], "conflicts": [] }
}
```

- **Identity.** A placement is keyed by its UUID; `engine_id` is unique at database level (`Placement.php:84-88`) and survives reloads into a workspace. (`template`, `group`) is unique too (`PlacementStorageSchema`, `placement-promotion.md:45`), so a diff can match placements of two exports by activity key and group even across re-runs.
- **Times.** `start` and `end` come from `PeriodInputs::slotStart` (`academic/src/PeriodInputs.php:58`), breaks included. A multi-slot activity is **one placement with `slots` > 1**, and its occurrences are one interval per slot, as in `temporal-semantics.md` §2 (a break between slots is not booked).
- **Occurrences** are listed only when the calendar is known (§5.4); otherwise `occurrences` is `null`.
- **Revision.** `revision` is the sha256 of the canonical, sorted placement list of the scope (key, day, slot, teacher, room, other resources). It changes with any run or move. It plays for results the role that `ScopeMapper::hash` plays for input. `PlacementModification::signature` (`scheduling/src/Timetable/PlacementModification.php:88`) is the per-placement equivalent and can be reused for it.
- **Manual changes.** `last_change` is the latest `ancora_placement_change` of the placement: `from_day`, `from_slot`, `to_day`, `to_slot`, `uid`, `reason` (`move` or `undo`), `reverts`, and `version`, the input version the destination was checked against (`PlacementChange.php:53-85`). The full history is `PlacementModification::history` (`PlacementModification.php:269`).
- **Conflict evidence.** Two different kinds, kept apart:
  - *inside the period*: the job metrics `collisions` and `overflow`, and the database unique keys (§3.4). A manual move is checked against the period's restrictions and occupancy (`timetable-editing.md` §9);
  - *across plans*: `cross_plan` is the output of `CrossPlanConflictDetector::forPlan` (`CrossPlanConflictDetector.php:107-122`): `evaluated`, `unknown` (undated periods, named only where visible) and `conflicts` (resource, both placements, common parts by date, `after_end`). These are **read-only warnings**. They are never enforced, never block a run or a move (`temporal-semantics.md:102, 110`), and cover only teachers and rooms of one owner.

### 5.3 Effective dates

- The effective range of a timetable is the occurrences it produces: plan first day, period repetitions (`plan_days`), period days (`temporal-semantics.md` §1–§2).
- Occurrences after the plan's last day are kept and flagged `after_end` (maintainer decision, `temporal-semantics.md:83`).
- A revision has no "valid from" date of its own in v1. A caller that needs one stamps the import time on its side. Effective-dated revisions (a change from week 6 onwards) are not modelled by GOH; they would be a new plan or a new period. Decision D-7 if needed.

### 5.4 Time zone and unknown calendars

- **Floating local time** (#133): `starts_on` is a date without zone, hours are wall-clock decimals of the site's organization (`temporal-semantics.md:30`). The export says `"time_basis": "floating-local"` and contains **no offset and no zone**. GOH never converts. A consumer that needs absolute instants applies its own zone and owns the daylight-saving consequences. iCalendar output would use floating `DTSTART` values unless the maintainer adds a site zone (decision D-6).
- **Unknown calendar** (`temporal-semantics.md` §3): no `starts_on`, no `plan_days`, `slot_duration` 0, or no duration. Then `occurrences` is `null`, `cross_plan.evaluated` is `false` with the reason (`CrossPlanConflictDetector.php:109`, `unknownReason` at `:88`), and the document carries the note `"dates_unknown: cross-plan conflicts not evaluated"`. **An unknown calendar is never exported as conflict-free.** The synthetic fixture is undated, so its golden export exercises this path (§7.3).

### 5.5 Source mappings, export, diff and reversals

- **Source mappings.** Each exported reference carries `code` and, when set, `ext` (§6.2), so the caller can join without knowing GOH ids. Entity ids and engine ids are diagnostic only; callers must not store them as keys.
- **Determinism.** The same data gives a byte-identical export: sorted lists (as `AcademicExport`), fixed key order, `JSON_PRESERVE_ZERO_FRACTION` as `ScopeMapper::hash` uses (`ScopeMapper.php:96`). This is an acceptance criterion (§7.3), and also a #145 smoke criterion ("re-export is byte-identical", `readiness-assessment.md` §10 on PR #150).
- **Diff.** Two timetable exports of the same plan and period are compared by placement identity (activity key + group): `added`, `removed`, `moved` (day or slot), `reassigned` (teacher, room or other resources). A diff is a pure function of two documents; no state is needed in GOH. It is proposed as a later slice (§9).
- **Reversals.**
  - A manual move is reversed by `undo` (`PlacementModification::undo`, `PlacementModification.php:255`), which writes a new change with `reverts`. That is the only in-place reversal that exists.
  - A run is **not** reversible today. Its placements stay until a later run continues from them or a record is deleted with its cascade (`placement-promotion.md` §2). There is no "unpublish". The contract does not promise one (decision D-4).
  - An import is reversed by importing the previous document, under the same idempotency and stale rules (§6). Deletions are never implicit (§6.4).

---

## 6. Idempotent import and upsert

### 6.1 Unit of work

- One document = one owner + one plan (+ that owner's catalogues it references).
- One Drupal transaction, as `SyntheticFixture::load` already does (`SyntheticFixture.php:75`, validation at `:224`): all or nothing.
- Serialized against publication and manual moves with the same advisory lock (`PlacementPublisher::LOCK`, `PlacementPublisher.php:60`, also taken by `PlacementModification::commit`, `PlacementModification.php:184`).
- Refused while the site has a queued or running job (`JobRepository::activeForSite`, `JobRepository.php:150`), as a move is refused (`PlacementModification.php:199`). Otherwise the run would be published `stale` anyway and wasted.
- Entities are saved through the Entity API **as the owner** (account switched, as `PlacementPublisher` does, `placement-promotion.md` §4), so `AncoraSameCreator` and the access-checked reference widgets refuse cross-owner references.

### 6.2 External ids and mappings

Two options; the choice is decision D-2.

| Option | How a record is matched | Schema change | Survives a rename in GOH | Fits slice 1 |
|---|---|---|---|---|
| **A. Natural key** | `code` within the owner (catalogues, plans) or within the plan (plan records); templates by (subject, period, sequence) | none | no: a GOH-side code edit breaks the match | **yes** |
| **B. Mapping table** | `(source.system, entity type, ext)` → entity id, in a new table or entity `ancora_external_ref` | yes, one table | yes | no (later slice) |

Recommendation: **A for slice 1**, with `ext` validated and echoed in the validation report but not stored. Under A, the caller is the authority for codes of caller-owned records, and GOH users should not edit those codes. B follows when a real caller needs renames or several source systems. B never replaces the UUID as GOH's own key.

### 6.3 Upsert rules

| Situation | Result |
|---|---|
| record not found by key | create |
| found, all imported fields equal | unchanged (no save, so no cache invalidation and no `stale`) |
| found, imported fields differ | update only the fields of the contract; GOH-owned fields not in the document are kept |
| found, but owned by another user | impossible by construction: lookup is scoped to the owner |
| reference to a code that resolves nowhere | `error` at its JSON path; nothing is written |
| a constraint kind marked **unavailable** (§3.4) | `error` |
| a cohort moved between groups when a group has placements | refused by `AncoraGroupMembership` (`academic-domain-mapping.md:127`); reported as `error` with the constraint message |

**Idempotency:** importing the same document twice gives `create 0, update 0` the second time, and the plan's input hash does not change. A retry after a failure is safe because a failure writes nothing.

### 6.4 Deletions

Not in v1. A record absent from the document is **kept**. Rationale: every delete cascades through the Entity API and removes the placements of the deleted templates, groups, teachers and rooms (`academic-domain-mapping.md:60-62`; `PlacementCascade`). A silent prune would destroy timetables. An explicit `--prune` with a dry-run list is a later slice (decision D-5).

### 6.5 Stale-version protection

- `source.base_version`, when present, must equal the current input hash of the plan, `ScopeMapper::hash(AcademicExport::export($plan))` (`ScopeMapper.php:95`): the same value the job records as `input_hash` and `TimetableReader` compares (`TimetableReader.php:97`). It is computed inside the import transaction, after the lock. A mismatch refuses the import with `stale_base_version`, so an old file cannot overwrite newer edits.
- `null` means "create or overwrite without a base", allowed for a first load and for standalone use.
- Site-wide changes (another owner's data) do not affect this plan's hash. That is correct: they are not in this plan's input. The run's own site-wide check stays `SiteRows::version` at publication (`PlacementPublisher.php:88`).
- `source.document_id` is echoed in the report and the log. It is not a deduplication key: idempotency comes from content (§6.3), not from remembering documents.

### 6.6 Error reporting

The validation document of §4.1 is also the import report, with `applied: true|false`. Each diagnostic has `severity`, a stable `code`, a JSON Pointer `path` into the document, and a message without internal details (no SQL, no stack trace), as `EngineClient::summary` strips connection details for engine errors.

### 6.7 Authentication, authorization and source of truth

| Path | Authentication | Authorization |
|---|---|---|
| drush (slice 1) | shell access to the host; drush runs with the site's full privileges | the operator is trusted; `--owner=<account>` is required and must be an active user; the import runs as that user, so the D1–D3 rules apply to the data, not to the operator |
| admin form (later) | Drupal session | `edit academic data` of the owner, CSRF form token; upload size limit |
| HTTP API (later, only after #142 D5 and the hosting decisions) | Drupal account with a per-client credential (OAuth2 client credentials or a key bound to one account) | one account = one owner; a new permission such as `import academic data`; rate limit; no anonymous access |

Source of truth per attribute follows §1.1. The document's `ownership` is implicit in v1: everything in a `problem.v1` document is authoritative for the plan it names, at the time it is imported. A finer per-field ownership (caller-owned and locked in the UI) needs option B of §6.2 and is a later slice.

---

## 7. Minimal paths compared; the recommended slice

### 7.1 Options

| Path | Effort | New attack surface | Fits standalone | Fits a subsystem | Reuses |
|---|---|---|---|---|---|
| **JSON file via drush** (import and export) | small: two commands, no route, no schema change | none on the web; operator shell only | yes | yes, by file exchange or a scheduled script on the caller's side | `AcademicExport`, Entity API validation, `TimetableReader`, `CrossPlanConflictDetector`, `PeriodInputs`, hashes |
| CSV via drush | small to medium: one CSV per record type, column contracts, encoding and delimiter rules, formula-injection escaping | none on the web | yes (spreadsheet users) | weaker: no nesting (assignments with groups) | same |
| JSON or CSV via an admin form | medium: upload form, file handling, size limits, progress | an upload endpoint behind login and CSRF | yes, best for non-technical users | partially | same + Form API |
| Authenticated REST (synchronous CRUD) | large: routes, serializers, auth provider, rate limits, versioning per endpoint | new public surface | no benefit | yes | Entity API |
| Async job API (submit problem, poll job, fetch result) | large: the REST work plus job status, result retention, webhooks or polling | new public surface; denial of service by job flooding | no benefit | best fit for automation | `JobRepository`, `JobState`, `RunResult` |

The REST and async paths reuse the same documents (§3–§5) and the same validation. The file path is therefore not throw-away: it fixes the documents, the tests and the semantics first, and a later API only adds transport and authentication.

### 7.2 Recommended slice IO-1 (proposal, needs approval)

**One plan, round trip, drush only, JSON only, no schema change:**

1. `drush ancora-academic:import <file.json> --owner=<account> [--dry-run]`: imports a `problem.v1` document for one plan, natural keys (§6.2 A), no deletions, `base_version` check, one transaction, validation report on stdout, non-zero exit status when `applied` is false.
2. The run uses what exists: the planner request form, or the journey helper `enqueue.php`, then `drush ancora-engine:work --once`.
3. `drush ancora-scheduling:export-timetable <plan code> --owner=<account> [--period=<code>]`: prints a `timetable.v1` document (§5.2), read-only, deterministic.

Placement: the import command in `ancora_academic` (it writes academic entities only); the export command in `ancora_scheduling` (it reads placements). No new module, no route, no permission, no entity type.

Scope limits of IO-1:
- supported record types: those of the synthetic fixture plus time restrictions and breakdowns. Other resources, resource groups, zones, load limits, group rooms and travel costs are refused with `unsupported_in_v1` until the importer covers them, so that nothing is silently dropped;
- one owner and one plan per document;
- no `ext` storage (D-2).

### 7.3 Fixture, tests and acceptance criteria (proposal)

**Fixtures**, versioned with the contract:
- `fixtures/scheduler/plan-P.problem.v1.json`: the synthetic fixture of §3.2 (8 demands, Teacher Beta), undated.
- `fixtures/scheduler/plan-P-dated.problem.v1.json`: the same with `starts_on` 2026-09-07, `ends_on` 2026-10-04 and `plan_days` [1, 8, 15, 22], aligned with the dates of `TemporalSemanticsTest`.
- `fixtures/scheduler/plan-P.timetable.v1.json`: the golden export after one run (8 of 8, the E2E digest of `workspace-journey.sh`).
- JSON Schema files `problem.v1.schema.json`, `timetable.v1.schema.json`, `validation.v1.schema.json`.
- No personal or historical data (#143 §6.3, #145 privacy checklist).

**Kernel tests** (no engine needed):
1. Import of `plan-P` into an empty site, then `AcademicExport::export` equals the export of `SyntheticFixture::load()` with keys and ids removed. This ties the importer to the existing `FixtureExportTest` equivalence with `dev/e2e/fixture.sql`.
2. Second import of the same file: `create 0, update 0`, and `ScopeMapper::hash` unchanged.
3. `--dry-run` writes nothing (entity counts unchanged), and gives the same report as the real import.
4. A dangling reference, a value out of range and an unavailable constraint kind each give an `error` at the right JSON path, and nothing is written.
5. `base_version` mismatch → `stale_base_version`, nothing written.
6. Owner isolation: another user's identical codes are neither matched nor changed.
7. Refused while a job is queued.
8. A forbidden room gives the `forbidden_room_inert` warning, and is imported.
9. Timetable export, using placements created as the publisher would: `draft` for a `partial` job, `final` for `succeeded`, `within_period_clean = false` when `overflow > 0`, and `stale` after an edit.
10. Undated plan: `occurrences` null, `cross_plan.evaluated` false, the note present; dated plan: occurrences equal those of `TemporalSemanticsTest`.
11. Two exports of unchanged data are byte-identical; each validates against its JSON Schema.

**Journey** (disposable PostgreSQL, `dev/sql/tools/with-ephemeral-pg.sh`): extend `workspace-journey.sh` or add a sibling:
- install;
- import `plan-P.problem.v1.json`;
- enqueue (`enqueue.php`), then `ancora-engine:work --once`;
- the job is `succeeded`, 8 of 8;
- export, then compare with the golden file after normalizing UUIDs and ids;
- re-import: no changes, and the timetable is not `stale`.

**Acceptance criteria:**
- [ ] A third party can load the fixture problem from JSON, generate, and export the timetable with drush only, on a clean disposable install.
- [ ] Re-import is a no-op; re-export is byte-identical.
- [ ] Every refused input names a JSON path and a stable code; a refusal writes nothing.
- [ ] No constraint is accepted silently if the engine does not honour it: it is refused or warned per §3.4.
- [ ] `final` is never exported without its metrics; an undated plan is never exported as conflict-free.
- [ ] No change to the engine SQL, the database schema, routes or permissions; existing tests, phpcs and the configuration determinism check pass; the LBR baseline is untouched.

---

## 8. Threat model for external synchronization

Scope: the file path of IO-1 and, marked "later", the transports of §7.1. The hosted SaaS trust boundaries are in `docs/saas/threat-model.md` and stay deferred (#142 decision 8).

**Assets:**
- planning data, including personal data: teacher names, and the timetable itself, which says where a person is at what time;
- the integrity of the stored timetable and its audit trail;
- availability of the worker, one job per site;
- isolation between owners of one site (D1–D3) and between sites.

| # | Threat | Vector | Mitigation (existing → proposed) |
|---|---|---|---|
| T1 | Spoofed or unauthorized import | someone without rights supplies a file | drush needs shell access; `--owner` explicit; later transports authenticate an account and check a permission (§6.7) |
| T2 | Cross-owner reference smuggling | a document references another user's teacher or plan by code | lookup scoped to the owner; save as the owner; `AncoraSameCreator`, `ValidReference` (`academic-domain-mapping.md:51, 223`) |
| T3 | Replay of an old document overwrites newer edits | resent or delayed file | `base_version` = plan input hash checked under the lock (§6.5) |
| T4 | Partial application | crash or error midway | one transaction; nothing written on failure (`SyntheticFixture` pattern) |
| T5 | Destructive cascade | import deletes records, cascading placements | no deletions in v1 (§6.4); later prune only explicit with a dry-run list |
| T6 | Interference with a running job | data changes during a run | import refused while a job is active; publication `stale` check already refuses a changed input (`PlacementPublisher.php:88`) |
| T7 | Resource exhaustion by input | huge file, deep nesting, millions of records | size cap, `json_decode` depth limit (as `Job::fromRow` uses depth limits, `engine/src/Job/Job.php:48, 53`), record-count cap per type; drush is a CLI process with its own limits |
| T8 | Resource exhaustion by runs | problems that make `generar()` spin | existing: `duration_slots ≥ 1` (Q-27), statement timeout and wall-clock guard, one running job per site, `maxTimeLimit` |
| T9 | Whole-run failures injected by data | Q-05 templates, Q-31/Q-32 contention, unavailable kinds | validator `error` or `warning` per §3.4, before any job is queued |
| T10 | Stored XSS through labels | names with markup in an imported file | Twig autoescaping, as for form input (`readiness-assessment.md` §5.4 on PR #150); the importer stores text as given, never markup |
| T11 | CSV formula injection (if CSV export is added) | a label beginning with `=`, `+`, `-` or `@` opened in a spreadsheet | prefix-escape on CSV output; JSON is not affected |
| T12 | Data disclosure in exports | export of another owner's plan; cross-plan section naming plans the reader may not see | the export is scoped to one owner's plan; `CrossPlanConflictDetector` names other plans only where visible (`temporal-semantics.md:92`); drush operator is trusted and documented as such |
| T13 | Over-sharing personal data downstream | timetable files copied to third parties | export only codes, labels and `ext`; never contact data; an option to export codes only (decision D-8); privacy note in the release docs (#145) |
| T14 | Identity confusion between systems | the same `ext` from two source systems | `ext` namespaced by `source.system`; option B mapping is unique per (system, type, ext) |
| T15 | Schema confusion | a consumer reads `v2` as `v1`, or GOH reads an unknown version | strict `schema` check on both sides, as `LegacyRows::build` refuses any other export schema (`LegacyRows.php:121`) |
| T16 | Time misinterpretation | a consumer applies a wrong zone to floating times; an undated plan read as free | `time_basis` explicit; no offsets emitted; unknown calendars emitted as `null` with a note, never as empty conflict lists (§5.4) |
| T17 | Log and error leakage | SQL or paths in error messages returned to a caller | diagnostics carry codes and short messages only (§6.6) |
| T18 (later) | Credential theft, CSRF, brute force on an HTTP API | network exposure | not opened by IO-1; prerequisites: TLS, per-client credentials, rate limits, CSRF tokens for session-based forms, the #41 gates |

Residual risks after IO-1:
- the drush operator can import as any owner; this is by design for a self-hosted operator, and documented;
- option A keys can be broken by a GOH-side code edit (D-2);
- the engine-level hazards of §3.4 remain; the validator only warns about them.

---

## 9. Incremental roadmap and reusable contracts

### 9.1 Roadmap (each step a separate issue, after approval)

| Step | Content | Depends on |
|---|---|---|
| IO-0 | This contract (design) | — |
| IO-1 | Recommended slice §7.2: problem import + timetable export via drush, JSON, fixtures, tests | approval; D-2, D-3 |
| IO-2 | Coverage: remaining record types in the importer (other resources, resource groups, zones, limits, group rooms, travel costs); `problem.v1` export from GOH (symmetry, backups); export `v8` with `ends_on` (D-9) | IO-1 |
| IO-3 | External id mapping (option B), caller-owned attributes read-only in the UI, `--prune` with dry run | IO-1; D-2, D-5 |
| IO-4 | Read-only diagnostics: unplaced (template, group) list after a run; timetable diff tool (§5.5) | IO-1; #143 DEC-5 for reasons |
| IO-5 | Admin upload form (JSON first, CSV for reference data) | IO-1/IO-3 |
| IO-6 | Output formats for consumers: iCalendar for dated plans, CSV | IO-1; D-6 |
| IO-7 | Authenticated HTTP API: synchronous validate and export; asynchronous job submission and status | #142 D5, hosting decisions, #41 gates |
| — | A `capabilities` document per generator (§3.4 as data), only if a second generator exists | #142 decision 5 |

### 9.2 What is reused from #71 and #95 (and #70, #130, #133)

| Existing contract | Where | Reused for |
|---|---|---|
| `AcademicExport` schema v7, determinism, `issues` | `academic/src/Export/AcademicExport.php:73, 102, 575` | internal canonical form; problem v1 maps onto it (§3.3); issues in validation |
| `LegacyRows::build`, `engineId`, schema check | `academic/src/Export/LegacyRows.php:68, 120-121` | unchanged path to the engine; schema-check pattern |
| `ScopeMapper::hash` (plan input hash) | `scheduling/src/ScopeMapper.php:95` | `base_version`, `input_version`, `stale` |
| `SiteRows::version` (site version) | `scheduling/src/Sync/SiteRows.php:145` | publication stale check, unchanged |
| `JobState`, `JobOptions`, `JobSource`, `JobScope`, `Job` | `engine/src/Job/*` | job request and outcome (§4) without change |
| `RunResult` and `Worker::outcome` | `engine/src/Engine/RunResult.php:67`; `engine/src/Worker.php:273-311` | outcome document fields |
| `JobRepository` (`enqueue`, `requestCancel`, `claim`, `activeForSite`, `changedPlacements`) | `engine/src/JobRepository.php:66, 150, 185, 231, 335` | lifecycle and draft/final rule; import refusal during a job |
| Drush `ancora-engine:enqueue/work/cancel/retry/recover/job` | `engine/src/Drush/Commands/AncoraEngineCommands.php:39-175` | operator lifecycle; `job` JSON as the model of the outcome document |
| `PlacementPublisher` (lock, fence, stale, replay by `engine_id`, validation as creator) | `scheduling/src/Timetable/PlacementPublisher.php:60, 84-90, 104-108` | idempotency and serialization model for the importer |
| `Placement`, `PlacementChange`, `PlacementModification::signature/undo/history` | `scheduling/src/Entity/*.php`; `scheduling/src/Timetable/PlacementModification.php:88, 255, 269` | result identity, revisions, audit, reversal |
| `TimetableReader::read` | `scheduling/src/Timetable/TimetableReader.php:70-97` | draft/final/stale per scope for the export |
| `CrossPlanConflictDetector::forPlan` | `scheduling/src/Timetable/CrossPlanConflictDetector.php:107-122` | `cross_plan` block; "unknown, never conflict-free" |
| `PeriodInputs::slotStart` | `academic/src/PeriodInputs.php:58` | wall-clock start and end times |
| `SyntheticFixture::load` (transaction, `validate()`, refusal to load twice) | `academic/src/Fixture/SyntheticFixture.php:70-75, 224` | importer structure and its first fixture |
| Entity and field constraints, `AcademicAccessControlHandler`, permissions | `academic-domain-mapping.md:203-225` | validation and authorization for every transport |
| `ancora-academic:export` command conventions (`--owner`, `--period`, JSON on stdout) | `academic/src/Drush/Commands/AcademicCommands.php:64-70` | shape of the new commands |

Not reused, deliberately:
- the two-database `EngineSync` replace semantics (`engine-worker.md` §3), which differ from the workspace continuation;
- the source-less `ancora-engine:enqueue`, because external callers must never name raw engine ids.

---

## 10. Decisions needed from the maintainer

| Id | Decision | Options; recommendation |
|---|---|---|
| D-1 | Approve slice IO-1 as specified in §7.2–§7.3, and the command names | approve; or reduce to export only; **recommended: approve** |
| D-2 | Matching key for import v1 | A natural codes (no schema change) or B mapping table now; **recommended: A, then B in IO-3** |
| D-3 | Meaning of `final` for consumers | as the job state (today), or only with `collisions = 0` and `overflow = 0` (#143 DEC-3, P-3); **recommended: export the raw state plus `within_period_clean`, and decide P-3 separately** |
| D-4 | Explicit approval or publication state, and unpublish | none (today, legacy behaviour), or P-2; **not needed for IO-1** |
| D-5 | Deletions through import | never (v1), or explicit `--prune` with dry run later |
| D-6 | Time zone | floating only (today), or an optional site-level IANA zone used only by iCalendar output |
| D-7 | Effective-dated revisions | not modelled (new plan or period instead), or a future feature |
| D-8 | Personal data in exports | labels by default with a codes-only option, or codes only by default |
| D-9 | Export `v8` with `ends_on` | in IO-2, or leave the plan's last day out of the exchange |
| D-10 | Handling of inert constraint kinds on import (forbidden rooms, priority zones without `parche1`) | import with a warning (recommended, keeps the data for when the engine honours it), or refuse |

---

## LBR impact

**0 bytes retired, 0 bytes added to the frozen baseline.** Documentation only; no legacy or target code changed.
