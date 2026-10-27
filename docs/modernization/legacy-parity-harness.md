# Differential legacy parity harness (issue #70)

Issue: #70, under the maintainer decision that **the legacy implementation is the functional specification** (#66, #70). The inventory of legacy responsibilities is [legacy-parity-inventory.md](legacy-parity-inventory.md); its ids (PAR-nnn, quirks Q-nn, open questions A-n, deviations D1–D15) are used here.

The harness enters one synthetic scenario twice:

- through the **legacy application**, over HTTP, with its real handlers;
- through **Drupal**, with the Entity API, as the scenario's users.

It then compares, for every scenario:

- the 17 legacy academic tables of the legacy database with those of the site's **engine database** after the Drupal → engine sync (`EngineSync`, `LegacyRows`), normalized;
- the row counts of every other table of the legacy schema (triggers and rules write some of them);
- the owners: how many legacy users own each person's rows;
- the **scheduling outcome**: `generar()` with the same options on both sides, compared by metrics (`tst.metrics`) and by a digest of the normalized placements of the run's scope.

```text
dev/sql/tools/with-ephemeral-pg.sh modern/scripts/parity-diff.sh [--skip-composer] [scenario...]
```

Without arguments every scenario of `modern/tests/parity/scenarios/` runs. A scenario can be named by its path or its base name. The exit status is 0 only if no scenario has a FAIL. `ANCORA_PARITY_KEEP=1` keeps the work directory (logs, canonical JSON of both sides). CI runs it in the `parity` job of `.github/workflows/modern.yml`, on PHP 8.4 + PostgreSQL 16 and PHP 8.5 + PostgreSQL 18.

## Files

| Path | Role |
| --- | --- |
| `modern/scripts/parity-diff.sh` | The launcher: databases, servers, drivers, comparison, report. |
| `modern/scripts/lib/parity/src/Scenario.php` | Loads and validates a scenario; natural keys. |
| `modern/scripts/lib/parity/legacy-driver.php`, `src/LegacyDriver.php` | Legacy driver (HTTP). |
| `modern/scripts/lib/parity/drupal-driver.php` | Drupal driver (`drush php:script`). |
| `modern/scripts/lib/parity/normalize.php`, `src/Normalizer.php` | Canonical JSON of one database. |
| `modern/scripts/lib/parity/compare.php` | PASS / KNOWN / FAIL report with diffs. |
| `modern/tests/parity/scenarios/*.json` | The scenarios. |
| `modern/tests/parity/known-deviations.json` | The register of known deviations. |

Only test code is added. No legacy file is changed: the legacy side runs on a temporary copy of the git-tracked files, as `dev/e2e/run.sh` does.

## How each side runs

Everything runs against a verified disposable cluster (`with-ephemeral-pg.sh`, `disposable-guard.sh`), never port 5432. Both servers bind to 127.0.0.1 only.

### Legacy

1. A legacy database is installed once with `dev/sql/install.sh --with-data` and copied per scenario (`CREATE DATABASE … TEMPLATE`).
2. The application is copied from the git-tracked files, with a throwaway `bin/ancoragoh.conf`, and served by the PHP built-in server on 127.0.0.1, exactly as in `dev/e2e/run.sh`.
3. Users:
   - the scenario's administrator is the seeded `admin` (`admin-superuser`), with a random password from `bin/setpassword.php`;
   - every other user is created with `bin/adduser.php` (nick = scenario user key). `adduser.php` assigns no role, and the generated role handler `control/data/add/add_RolDeUsuario.php` fails with a fatal error (class `RolDeUsuario` not found). The harness therefore inserts the role row `superusuario`, the only seeded role with a permission (`todos`), with SQL. This is setup, not data entry.
4. `LegacyDriver` logs every user in through the login form and enters each record **as its user**, with the events and fields the browser client sends (`www/js/app/server/*.js`):

   | Record | Events |
   | --- | --- |
   | plan | `add_Planificacion` with two `d/m/Y` dates (`fdm_Planificacion_fecha_inicial.php` computes `fecha_inicial`, `cant_dias`); `activa` is not sent, as in the client |
   | period | `add_Periodo` (the client posts `cant_dias` as `hora_inicial`, Q-20, and the slot length in minutes, A-1), then `custom_set_Periodicidades` |
   | activity type, specialty, level, cohort, subject, teacher, room | `add_ClasificacionDeActividad`, `add_Especialidad`, `add_Nivel`, `add_Brigada`, `add_Asignatura`, `add_Profesor`, `add_Lugar`; their `fdm_*_creador.php` hooks set `creador` from the session |
   | group | `add_Grupo`, then `custom_MoverBrigadaDeGrupo` per cohort |
   | template | `add_PlantillaDeActividad` on the breakdown row the triggers created |
   | teacher assignment | `add_ProfesorParaActividad` (priority 1), `set_prioridad_pxact`, `custom_ActualizaProfesorParaGrupo` per group |
   | room assignment | `add_LugarParaActividad` (priority 1), `set_LugarParaActividad` |
   | breakdown options (D4) | the subject's breakdown browser `get_browser_DesgloseDetalles` (the period link gives the `desglose` id), the form `get_form_edit_desglose` (hidden id, subject and period), then `set_Desglose` with the day counts as text and the checkboxes as `1`/`0`, as `ancoragoh_client_forms_desglose.js` posts them |
   | run | `generar` (`control/custom/generar.php`) with the scenario's options |

   Legacy ids are generated by the application. The driver reads the id of each new row from the database (rows before and after the request), only to send it in later requests, as the browser takes it from the page.
5. Visibility checks call the data entry lists (`get_minibrowser_planificaciones`, `_profesores`, `_lugares`, `_clasifact`, `_especialidades`, `_niveles`, `_asignaturas`) as the user and look for the labels.
6. After the comparison, the **characterization** steps run (legacy only, they change data). They record handler behaviour that the screens never show (quirks Q-11, Q-12, Q-23). Each prints a `CHAR` line and fails if the legacy application no longer behaves as recorded.

PHP warnings of the legacy side are reported, not compared (for example the undefined `field_Planificacion_activa`).

### The id order matters: one row per table per second

`generar()` reads several tables in id order, and its result depends on it. Characterized with the E2E fixture (`dev/e2e/fixture.sql` with group ids renamed, PostgreSQL 16):

| Group ids | Placements (template/group/day/slot) |
| --- | --- |
| `G1`, `G2` (fixture) | `A1/G1/1/1 A1/G2/1/4 …` (E2E digest `4199818b…`) |
| `Gb` for G1, `Ga` for G2 | `A1/Ga/1/1 A1/Gb/1/4 …`: the group whose id sorts first gets slot 1 |

Renaming the brigades, teachers or teacher assignments did not change the result here; renaming the groups did.

A legacy replica id (`getReplica()`, `lib/basic/string/StringUtils.php`, PAR-110) is the table name, the time as `d/m/y h:i:s`, `time()`, then random characters. So rows entered in different seconds sort in entry order, and rows entered in the **same** second sort randomly. The first harness run entered G1 and G2 in the same second and got the swapped result. The driver therefore enters the rows of one table in different seconds, as a person does, and fails the scenario if the generated ids of any table do not sort in entry order (the 12-hour clock or the day of month wrapping during a run). With this, the legacy side is deterministic.

The Drupal side must give the same order. Before #70 its engine ids were `p<plan>:` + code, which sort in code order: scenario s04 (codes that do not sort in entry order) fails there. Since the ownership slice they are numbered with the entity id (`0000000012:G1`, `LegacyRows::engineId()`), which sorts in creation order, and s04 passes.

### Drupal

1. The site is installed once from `config/sync` (`drush site:install --existing-config`) and its database copied per scenario. Each scenario gets a fresh engine database (`CREATE DATABASE … TEMPLATE` of the legacy template, then `scripts/engine-db.sh create --if-missing` for the runtime role and grants).
2. The scenario's administrator is the site's first account. Every other user is a Drupal account with the role `planner` (`view academic data`, `edit academic data`, `view ancora engine jobs`, `request ancora engine jobs`), as in `r1-journey.sh`.
3. The driver creates each record while the current account is switched to its user, validates it with every entity and field constraint, and saves it. A violation is a FAIL and the dependent records are skipped.
4. Visibility checks run as the user: an access-checked entity query, plus `access('view')` and `access('update')` on each record.
5. Each run is queued as a timetable request with its job source (as the request form does), and `drush ancora-engine:work --once` runs it: the worker syncs the site into the engine database inside the run's transaction and calls `generar()`.

## Normalization

Legacy ids are random. Drupal engine ids are derived. Neither is comparable, so `Normalizer` replaces every id by a stable key:

- A row that is a scenario record is found by its **natural key**: owner and name for owned records, plan and name for plan-scoped records, subject, period and sequence for templates, template and resource for assignments. It gets the record's scenario key. Labels must therefore be unique per owner (or plan) and type; `Scenario` refuses a scenario where they are not.
- Rows the legacy triggers or handlers create get keys from what they reference: a breakdown is `<subject>@<period>`, a plan day `<period>@<day>`.
- `creador` becomes the scenario user key, through the user map each driver writes (legacy user id or engine user id → user key).
- A row that matches no scenario record keeps a visible `?…` key, so it shows up in the diff.

Every column of the 17 tables is compared, except the generated ids themselves. Nothing is dropped silently.

## Results

| Status | Meaning |
| --- | --- |
| PASS | identical |
| KNOWN `<ids>` | the rows differ only in columns that `known-deviations.json` lists for this table and scenario. The diff is printed. It does not fail. |
| FAIL | any other difference, with `- legacy` / `+ Drupal` lines |

A run passes when both sides have the same metrics and placement digest, and each side meets the scenario's expectations (`metrics`, and for scenario 1 the E2E digest).

The register is evidence, not a decision. An entry stays until a slice reconciles its deviation or the maintainer rules on it. A slice must not add an entry for its own deviation: it must make the table PASS.

## Scenario format (`ancora.parity.scenario.v1`)

```json
{
  "schema": "ancora.parity.scenario.v1",
  "id": "s01-e2e-organization",
  "title": "…",
  "users": [{"key": "admin", "name": "Administrator", "legacy": "seeded-admin"}, {"key": "alice", "name": "Alice Planner"}],
  "records": [
    {"type": "plan", "key": "P", "as": "admin", "label": "Plan", "starts_on": "2026-09-07", "duration_days": 30},
    {"type": "period", "key": "PER", "as": "admin", "plan": "P", "label": "Week", "days": 5, "slots_per_day": 4, "start_hour": 8, "slot_duration": 1, "plan_days": [1]}
  ],
  "runs": [{"key": "E2E", "as": "admin", "plan": "P", "period": "PER", "specialty": "E", "level": "N",
            "options": {"forma": 1, "parche": false, "compactar": 0, "parche1": false, "buscalugar": false},
            "expect": {"metrics": "demands=8 placed=8 unplaced=0 collisions=0 overflow=0"}}],
  "visibility": [{"as": "alice", "type": "teacher", "visible": ["a.T1"], "hidden": ["b.T2"]}],
  "characterize": [{"kind": "foreign_update", "as": "bob", "record": "a.T2", "label": "…", "legacy_expect": {…}, "ref": "Q-11, Q-12"}]
}
```

- Record types: `plan`, `period`, `activity_type`, `specialty`, `level`, `cohort`, `student_group`, `subject`, `activity_template`, `teacher`, `teacher_assignment`, `room`, `room_assignment`, and `breakdown`: an edit of the breakdown of a `subject` in a `period` (`min_days`, `max_days`, `respect_order`, `same_day`). A breakdown is not created: on both sides it exists already (legacy triggers, `BreakdownMaintainer`). Fields use the Drupal names. References name earlier records by key.
- `key` is unique in the scenario. `code` is the Drupal code (default: the key), so two owners can both have a specialty `E`.
- `as` is the user who enters the record. Plans and the catalogues belong to that user (legacy `creador`). Periods, groups, templates and assignments belong to their plan.

## Scenario 1: the E2E organization

`s01-e2e-organization` is `dev/e2e/fixture.sql` plus the HTTP steps of `dev/e2e/lib.sh` (Teacher Beta), entered entirely through the legacy UI and entirely through Drupal, as the administrator. The record keys are the fixture ids, so the E2E digest can be computed on both sides. The legacy UI cannot create a plan without dates, so the plan starts on 2026-09-07 for 30 days (`fixture.sql` has `fecha_inicial = 0`; that does not change the result).

Result on `master` (`0e4cf55`), PHP 8.4, PostgreSQL 16: **22 PASS, 2 KNOWN, 0 FAIL**.

- Outcome: PASS. Both sides place 8 of 8 (no collision, no overflow) with the same placements, and **both give the E2E digest `4199818b55d4f1c44f2cba24f0367e29`**.
- Tables: 15 of 17 PASS, including every breakdown, template order, assignment and priority row. `fecha_inicial` agrees (9747 on both: `LegacyRows::legacyDayNumber()` matches `TimeUtilsCommon::date_to_number`).
- Owners: PASS (one legacy user each). On `master` the Drupal owner is the artificial per-plan user `p<plan>:owner` (D3), which the driver maps to the plan's creator; with a single plan it cannot show.
- Other tables: 37 tables with the same row counts (the restriction rules and triggers write the same rows).
- Table-level differences (KNOWN):

  | Table | Legacy (UI) | Drupal | Reference |
  | --- | --- | --- | --- |
  | `planificacion.activa` | NULL: the client never posts it, and `add_Planificacion.php` reads an undefined key (PHP warning) | TRUE | PAR-010, D6 |
  | `periodo.hora_inicial` | 5: the client posts `cant_dias` | 8 | Q-20, A-1 |
  | `periodo.duracion_del_turno` | 60: minutes from the form | 1 (hours) | A-1 |

## Findings of the first runs

1. **The legacy outcome depends on the id order** (see above). Opaque ids are not neutral: legacy ids sort in entry order (when entered in different seconds). The Drupal engine ids `p<plan>:` + code sort in code order. Where codes do not follow entry order, the timetables differ. This is new evidence for D15 (PAR-110): the engine id scheme has to preserve the entry order to be legacy-faithful.
2. **Role assignment over HTTP is broken in the legacy application**: `add_RolDeUsuario.php` fails with `Class "RolDeUsuario" not found`. No working tool assigns roles (`bin/adduser.php` does not either).
3. With two creators (`s02`, run on `master` for evidence), the legacy lists show each user only their own plans, teachers, rooms and specialties (16 of 16 visibility checks as expected). Drupal refuses Bob's plan `P` (plan codes are unique on the site), so his catalogues cannot be entered, and Bob sees Alice's records (D2).
4. With one creator and two plans (`s03`, on `master`), the legacy application accepts the second plan's group and templates on the first plan's subjects, cohort and activity type. Drupal refuses them: "belongs to another plan" (D1).
5. The legacy characterization (`s02`) confirms quirks Q-11 and Q-12 (Bob renames Alice's teacher with `set_Profesor`, and becomes its `creador`; Bob deletes Alice's room with `del_Lugar`), and Q-23 (Bob sees Alice's teacher and room in the timetable explorer lists).

## Scenarios of the ownership slice (D1–D3, D15)

| Scenario | What it enters | On `master` (`0e4cf55`) | After the slice |
| --- | --- | --- | --- |
| `s02-two-creators` | Alice and Bob, each with a plan `P` for the same dates and catalogues with the same codes and partly the same names; 7 visibility checks per side; 4 characterization steps | Drupal refuses Bob's plan (`A plan with Code P already exists`), so all of Bob's records fail; Bob sees, views and may edit Alice's records (D2) | **23 PASS, 2 KNOWN, 0 FAIL**; both runs PASS; 16/16 visibility checks as expected on both sides |
| `s03-one-creator-two-plans` | Alice with two plans on the same dates sharing subjects, teacher, room, cohort, activity type, specialty and level | Drupal refuses the second plan's group, templates and assignments: `… belongs to another plan` (D1) | **21 PASS, 2 KNOWN, 0 FAIL**; both runs PASS; the breakdown table has the 4 rows the triggers make for the creator's two periods |
| `s04-entry-order` | s01 with codes that do not sort in entry order (groups `Z1`, `A2`; teachers `TZ`, `TB0`) | outcome FAIL: Drupal swaps the two groups' slots (E2E digest `32ecd3da…` instead of `4199818b…`) | **22 PASS, 2 KNOWN, 0 FAIL**; both sides give `4199818b…` |
| `s05-shared-teacher-continuation` (#95) | Two specialties of one plan share a teacher in one period. Runs F and E1 use the legacy window's default options (D11), and run E2 repeats the E scope with Drupal's options. | — | **24 PASS, 2 KNOWN, 0 FAIL**. Legacy continuation adds nothing in E2. The Drupal regeneration (D7) gives the same cells here, so the end states agree. Workspace mode: 38 PASS, 2 KNOWN. |
| `s06-breakdown-options` (D4) | Four specialties of one plan, each with its own period (5 days × 3 slots), group, teacher, room and a subject of three templates (the triggers make 16 breakdowns). Breakdowns saved with the defaults (SD), without order and with the same day (SO), without order and without the same day (SQ, Q-04), and with a minimum of 2 days (SM); one run per specialty; 5 legacy-only `breakdown_post` characterization steps | the export's fixed defaults (D4), checked by reverting `LegacyRows` to them: `desglose` FAIL, runs O, Q and M FAIL (Drupal places SQ 3 of 3 instead of 1), and the restriction row counts differ | **29 PASS, 2 KNOWN, 0 FAIL**; `desglose` PASS (16 rows, every option); placements equal: SD days 1, 2, 3; SO day 1 slots 1–3; SQ only the first template; SM days 2 and 4 (Q-24). Workspace mode: **59 PASS, 4 KNOWN, 0 FAIL** (each job equal to both oracles; the real worker's placement entities equal the legacy oracle). |

Observations recorded by these scenarios:

- **Owners.** Each person's rows have exactly one legacy owner on both sides (`owners: {"alice":1,"bob":1}`); before the slice the Drupal side had one artificial owner per plan (`p<plan>:owner`).
- **Breakdowns per creator.** The legacy triggers pair each subject with the periods of every plan of its creator and of no other user (PAR-031). The sync now produces the same rows.
- **Shared resources across a creator's plans (s03).** Both plans cover the same days and share the teacher and the room. On both sides the second run places them in the same day and slots as the first run: the first plan's placements do not make them busy for the second.
- **Characterization (legacy only, s02).** `CHAR` lines confirm Q-11 and Q-12 (`set_Profesor` by Bob on Alice's teacher renames it and makes Bob its `creador`; `del_Lugar` by Bob deletes Alice's room) and Q-23 (Bob sees Alice's teacher and room in the timetable explorer). Drupal denies Bob the update and the delete (Q-12 is a security behaviour, not reproduced) and lets him view the teacher and the room (Q-23, reproduced).

## Further deviations noticed (not fixed by the ownership slice)

Numbered after D15 of the inventory.

| # | Drupal | Legacy | Reference |
| --- | --- | --- | --- |
| D16 | `administer academic data` sees and edits every user's records | no screen shows another user's records, not even to the seeded superuser | PAR-002, PAR-006 |
| D17 | deleting a Drupal user does not touch their academic records | `creador … REFERENCES seguridad.usuario ON DELETE CASCADE`: deleting a user deletes all their records | PAR-001 |
| D18 | the sync inserts each table's rows in code order (export order); entity ids keep the id order, but not the physical (heap) order of rows the generator reads without `ORDER BY` | rows are stored in entry order. No scenario shows a difference yet | PAR-110 |
| — | an edit never changes the owner; only an administrator changes it (owner field) | `set_*` handlers rewrite `creador` to the editor (Q-11) | Q-11 |
| — | codes are a Drupal handle, unique per owner or plan | legacy has no codes; ids are generated | D6 |
| — | every list and choice is filtered by owner, including the drill-down lists | the drill-down and timetable handlers filter only by the ids the client posts, with no `creador` check (`get_browser_Brigadas_filtradas.php`, `get_browser_DesgloseDetalles.php`, `get_browser_GruposXClasifFiltrados.php`, `get_minibrowser_horarios.php`, `get_minibrowser_publico.php`). The screens only post ids from the user's own lists, so this matters only for crafted requests. Like Q-12, it is an access defect: recorded, not reproduced | PAR-002, D14 |

Characterization of `set_Desglose` (s06, legacy only): `0`, negative values, a minimum above the maximum and `" 4 "` (trimmed) are stored; an empty value or `2.5` fails with HTTP 500 after the fields before it were written (Q-25: one `UPDATE` per field); Bob's post updates Alice's breakdown (Q-26: no `creador` check). Drupal accepts the same integers, refuses an invalid form as a whole, and lets only the owner edit (Q-25 and Q-26 are not reproduced).

Legacy defects found by the harness (no Drupal counterpart to change): `add_RolDeUsuario.php` fails with a fatal error; `add_Planificacion.php` reads an undefined `field_Planificacion_activa`.

## Workspace mode (`--workspace`, issue #93)

With `--workspace`, each scenario is run a third time. After the comparisons above, which are unchanged, the unchanged legacy engine is installed into the site database itself (schema `scheduler`). Every timetable request then runs in a job-scoped workspace schema:

- the input is read from one REPEATABLE READ snapshot of the Drupal entities;
- it is reduced to the #92 closure;
- the request runs with no engine database.

Its placements, inserted rows and DML counters are compared with the full engine database and with the legacy application's database. The model, the evidence and the results are in [single-db-workspace.md](single-db-workspace.md).

## LBR impact

**0 bytes retired.** The harness is test code; the slice changes target code only.

Parity-verified now (legacy over HTTP = Drupal, table-level and outcome-level, scenarios s01–s04):

- PAR-001 owner of catalogue records and plans (`creador` from the session user);
- PAR-002 visibility of the data entry lists (plans, teachers, rooms, specialties), positive and negative;
- PAR-003 catalogues shared across the creator's plans;
- PAR-008 owner of placed activities (the subject's creator);
- PAR-014, PAR-015, PAR-019, PAR-030, PAR-050, PAR-054 as rows (owned, no plan);
- PAR-031 breakdown rows per subject and period of the creator's plans;
- PAR-032..034 (D4 slice, s06): breakdown options entered through the legacy form handlers and through Drupal give equal `desglose` rows and equal placements, including Q-04 and `min_dias` 2;
- PAR-110 id order (entry order), through numbered engine ids;
- Q-23 recorded on the legacy side and reproduced as view access.

Not parity-verified (no retirement credit): Q-11 and Q-12 (recorded, deliberately not reproduced), the cross-user timetable explorer itself (D14), every other row of the inventory, and every legacy screen: the harness compares data and outcomes, not the UI.
