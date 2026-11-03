# Representative workload assessment: evidence, engine guarantees and benchmark proposal (#143)

Issue: #143, under the vision #142 (decision gate D1). This is an assessment and characterization document. It changes no code, no fixture, no test, no schema and no solver. The fixtures and the benchmark of §6 are a **proposal that needs the maintainer's approval** before any of it is implemented.

**Rules of this document**

- The legacy implementation is the functional specification (#66, #70). Defects are recorded with their ids, not fixed.
- No figure is invented. A fact without a source in the repository, its history or the companion wiki is marked **unknown**.
- No historical personal or identifying data is reproduced. Where a historical file holds such data, only its existence, its size and its privacy implications are stated. The counts quoted from the 2010 dump are row counts and numeric configuration values, never names, ids or free text.
- Historical use (ca. 2005–2010), the current small regression fixtures, and representative scale are kept apart. Nothing here claims that the current Drupal version has been proven at historical scale.

**Evidence labels**

| Label | Meaning |
|---|---|
| **Verified (case NNN)** | asserted by a case of `dev/sql/tests/cases/`, a Kernel/Functional test or a harness check |
| **Source** | read in the code; `file:line` given |
| **Doc** | stated by an existing document of `docs/` (which cites its own evidence) |
| **Historical artefact** | a 2010 file in the repository, or its git history |
| **Maintainer statement** | stated by the maintainer in #142 or #143, without an artefact in the repository |
| **Inference** | derived from counts in an artefact; stated with its derivation and not used as a fact elsewhere |
| **Exploratory** | measured while writing this assessment on one machine (§4.6). Not a benchmark |
| **Unknown** | no source found |

Paths are repo-relative. `vrg` abbreviates `dev/sql/tiempo/restriccion/valor_restriccion_generica.sql`.

---

## 1. Historical evidence matrix

### 1.1 Provenance and use

| Fact | Value | Label and source |
|---|---|---|
| University deployment, ca. 2005–2010, with large and complex timetables | Stated | Maintainer statement (#142 "Historical evidence", #143 "Motivation"). No artefact in this repository records the institution's deployment size, its run times or its outcomes. |
| Versions | VB6 desktop (2005–2010), then PHP 5 + PostgreSQL (2010, thesis) | Doc: `README.md:11,212-213`; wiki `Home.md:7`, `Historical-Research-and-References.md:7` |
| Which version produced the university's timetables | **Unknown.** `README.md:213` labels the PHP version "Tesis - Descontinuado". The VB6 example files named after faculty semesters 2006–2009 (§1.3) suggest that the VB6 version was the one in day-to-day use (inference from file names only) | Doc; inference |
| Thesis | *Áncora GOH 2.0 — Generación y Organización de Horarios Docentes*, June 2010 | wiki `Historical-Research-and-References.md:42` |
| Thesis-era effort estimate (use-case points) | Stated in the wiki; it describes 2010 project effort, not workload scale | wiki `Feasibility-and-Research-Metrics.md:13` |
| PHP snapshot in git | imported as `b42aa03` and `f5cc46e` (commit dates 2010-07-01 and 2010-07-02, labelled "historical import") | Historical artefact (git history) |

### 1.2 Artefacts of the PHP/PostgreSQL version (in this repository)

| Fact | Value | Label and source |
|---|---|---|
| 2010 developer database | `pg_dump` started 2010-10-06 (`dev/backup/ancoragoh.sql:5`), added in `b42aa03`; restores on PostgreSQL 16 | Historical artefact; Doc `database-assessment.md` §4.4 |
| Its row counts | 2 plans, 5 periods, 10 periodicity rows, 2 specialties, 5 levels, 6 brigades, 9 groups, 9 brigade-group links, 6 subjects, 30 breakdowns, 19 templates, 2 classifications, 9 teachers, 11 rooms, 1 other resource, 3 resource groups, 32 teacher-template and 43 teacher-group assignments, 75 room-template assignments, 2 travel costs, 60 `zpriori` cells, **39 placements** | Historical artefact (row counts of the `COPY` blocks of `dev/backup/ancoragoh.sql`); the 39 placements and 61 template×group demands agree with `database-assessment.md` §4.4 |
| Its restrictions | 3 period restrictions; 60 brigade, 39 teacher and 39 room rows, all occupancy (`restriccion_por_actividad_de_*` 60/39/39); 0 subject, activity-type, specialty or other-resource restrictions; 0 HRT, conditional, hygiene, break, fixed-room and forbidden-room rows | Historical artefact (row counts) |
| Its time grid | every period 5 days × 6 slots, slot length 90 (minutes in the legacy form's unit, A-1); two periods store start hour 480 and three store 5 (the day count, Q-20); plan lengths 116 and 30 days | Historical artefact (`COPY periodo` at `dev/backup/ancoragoh.sql:10557`, `COPY planificacion` at `:10572`) |
| Its activity types | 2 classifications, both duration 1 slot and cost 1 | Historical artefact (`COPY clasificacion_de_actividad`, `:10225`) |
| Enrolment and capacity | every brigade has enrolment 0; 5 of 11 rooms have capacity 0 (no limit), the others 20–40 | Historical artefact (numeric columns only) |
| Placement coverage of the dump | 39 placements for 61 template×group demands. Whether this is the result of complete runs, partial runs or manual testing is **unknown** | Historical artefact; Doc `database-assessment.md` §4.4 |
| Nature of the dump | a developer database, not a university dataset. It holds a user row with an unsalted MD5 hash and teacher names | Doc `security-privacy.md:37` (§1.4 below) |
| Generator trace | one `generar()` run, NOTICE output, `Tiempo total de ejecución de la consulta: 212466 ms` (212 s) | Historical artefact `dev/sql/generador/generador.log:4,5117` (added in `f5cc46e`) |
| Size of that run | 7 templates in the scope ("actividades en curso" is the sum of the templates of the scope's subjects, `generar.sql:241-247`); templates with 2 or 5 groups; 2 classifications | Historical artefact (`generador.log:4`; counts of `cantGrupos = 2/5` and `Clasif:` lines) |
| Outcome of that run | 140 group visits ("Colocando grupo") and 14 "no se pudo colocar" notices. The outer pass repeats `cantActEnCurso` = 7 times (`generar.sql:276,1539`), so this is consistent with 20 template×group demands of which 2 were never placed. **Inference only**: the trace has no final count | Historical artefact; inference |
| Hardware, PostgreSQL version and client of that run | **Unknown.** The footer is the format of a GUI query tool. NOTICE output for 5,119 lines was sent to the client, which adds overhead of unknown size | Historical artefact |
| Date of that run | **Unknown** (no timestamp in the log) | — |

### 1.3 Artefacts of the VB6 version (repository `rafageist/ancora-vb6`, not modified, files not opened)

| Fact | Value | Label and source |
|---|---|---|
| Faculty semester files | the 6 files classified as faculty datasets are 20–593 KB each, 5 of them 337–593 KB; the 6 smaller faculty or test files are 6–56 KB | File sizes from the GitHub tree API (metadata only); Doc `security-privacy.md:47-48`, `functional-inventory.md:87` ("up to ~29k lines") |
| Their content | "appear to be real institutional timetables"; apparent full teacher names in at least one file and name-shaped entries in others | Doc `security-privacy.md:47`, `functional-inventory.md:87` |
| Scale held in them (activities, groups, teachers, rooms, restrictions, slots) | **Unknown.** It is derivable only by an approved private analysis that outputs aggregate counts (decision DEC-2, §7) | — |
| Demo scenarios | `AncoraDemo_Autom1 *` (7 files: too many activities and lack of resources, restricted brigade, early Friday exit, denied access, student assistants, distance between rooms, enrolment and capacity), `d2`–`d4`, `AncoraDemo_Primaria` | File names; Doc `functional-inventory.md:86` ("privacy spot-check needed") |
| A random dataset generator | present in the VB6 code ("2026-restored") | Doc `functional-inventory.md:56`. Its parameters are **unknown** here |
| Repository visibility | the GitHub API reports `rafageist/ancora-vb6` as **public** (2026-11-01) | GitHub API. See §1.4 |

### 1.4 Privacy implications (no content reproduced)

| Item | Implication |
|---|---|
| `dev/backup/ancoragoh.{sql,custom,tar}` | One user row with an unsalted MD5 password hash and teacher names, in the current tree and the history since `b42aa03`. Already recorded; the history decision is open (`security-privacy.md:37-38,83,111`). Not suitable as a fixture, and must not be ingested by CI (`security-privacy.md:37,52`). |
| `rafageist/ancora-vb6`, faculty `.anc` files | Recorded as "keep private" and "do not use or redistribute until reviewed and anonymized" (`security-privacy.md:47,52`), while the repository is public. This assessment did not open them. A maintainer decision is needed on their exposure (DEC-7). They do not block a synthetic ladder. |
| `dev/sql/datos/datos_por_defecto.sql` | Seed rows for roles, one permission and one administrator account; no historical person. Not a scale source. |
| `dev/sql/generador/generador.log` | Generated ids and two subject names only (spot-checked by count; `functional-inventory.md:85`). |

### 1.5 What the history does and does not support

- **Supported:** the algorithm, its data model and its options existed in 2010 and ran end to end on small developer data. One timed run is recorded: 212 s for a 7-template scope, on unknown hardware.
- **Not supported by an artefact:** any number of activities, groups, teachers, rooms or periods of the university deployment, any run time on such data, and any outcome (placement rate, manual fixes) of a real semester. These remain maintainer statements until a private aggregate extraction is approved (DEC-2) or the maintainer supplies the figures.
- **Current evidence is small by design.** The SQL fixtures place 2–8 demands per case (`sql-characterization.md` "Data"); the E2E and parity scenarios place 8 (digest `4199818b55d4f1c44f2cba24f0367e29`, `dev/e2e/expected-placements.md5`); the largest closure world has 154 input rows (`single-db-workspace.md` §5.2). Generation time on realistic data is unknown (`sql-characterization.md` "Not covered", `e2e-harness.md` "Not covered").

---

## 2. What the engine enforces today

"The engine" is the unchanged `generar()` of `dev/sql/generador/generar.sql`, run in a full legacy database or in a job workspace (byte-identical functions, `single-db-workspace.md` §1). One run covers one scope: (specialty, level, period).

### 2.1 Hard constraints (a placement is never made against them)

| Constraint | Mechanism | Evidence |
|---|---|---|
| A teacher, a room and every brigade of the group are busy at most once per (period, day, slot) | occupancy rows written by the RULE on `actividad` (`ancoragoh.sql:126-129`, `restringe_segun_actividad`); `UNIQUE (objeto, periodo, dia, turno)` on `restriccion_de_{profesor,lugar,brigada}` refuses a collision with 23505 | Verified 012, 060, 149 |
| An other resource is booked at most once per (period, day, slot) | RULE on `otros_recursos_usados` (`ancoragoh.sql:131-134`), same unique key; a double booking fails with 23505 (D-05, #24) | Verified 083 |
| Manual restrictions of the period, activity type, subject, brigade, teacher and room | `posible_inicio` refuses a start whose slots are restricted for the period, the classification, the subject or a brigade; teacher and room restrictions remove them for the cell (`posible_inicio.sql:70-180`) | Verified 139, harness s08 |
| The teacher is eligible for the template and assigned to the group | teacher choice from `profesor_para_grupo` in priority order, least load (`generar.sql:845-950`); no assignment → nothing placed | Verified 060; `sql-characterization.md` "New findings" |
| The room is eligible, free for the whole duration, and its capacity is 0 or ≥ the sum of the group's enrolment | `generar.sql:1183-1335`, `quita_segun_capacidad.sql` | Verified 122, 141 |
| Fixed room first, when free | `generar.sql:1228-1234` (first `lugar_fijo` row only) | Verified 141 |
| Daily load limits (hygiene) of teachers and brigades | a day is skipped when (1 + activities that day) × the candidate's cost exceeds the limit (6.21); limit 0 places nothing | Verified 140, harness s09 |
| Breakdown day ranges under `respetar_orden`, `min_dias`, `max_dias`, `mismo_dia` | `rango.sql`, `generar.sql:585,1132-1140` | Verified 135, harness s06 (with quirks Q-04, Q-22, Q-24) |
| Each (template, group) placed at most once; earlier placements kept | `generar.sql:1004-1010` (continuation) | Verified 061, 128 |

All of these are **per period**. The occupancy key contains `periodo`, and every restriction function reads the period's own rows.

### 2.2 Soft preferences and priorities (they order the search; nothing measures them)

| Preference | Effect | Evidence |
|---|---|---|
| Classification priority and `forma` | outer loop order (classifications, or activity levels) | `generar.sql:262-268`; Verified 143 |
| Prioriza ordering of templates | 8 criteria `{t,f,t,f,f,f,t,f}`, unweighted sum of levels; sort defective (6.4); place restrictions counted with the teacher counter (6.12) | Verified 040–044; Doc `database-assessment.md` §5.5 |
| Teacher choice | least total load in the period (using the current template's duration, 6.14), ties by eligibility priority | `generar.sql:845-950` (PAR-053) |
| Room strategy `buscalugar` | FALSE: most used by the group, then priority tiers; TRUE: least travel cost to neighbouring rooms (legacy default TRUE, A-2/Q-21) | Verified 141, harness s10 |
| Room ties | first row in heap order (no `ORDER BY`) | Verified 131 (D18) |
| `compactar` | shifts the latest allowed day under `respetar_orden` | Verified 143 |
| Priority zones `zpriori` | change the cells only with `parche1 = TRUE`, which no legacy screen sends (Q-19) | Verified 140, harness s09 run F |
| Other resources | chosen by resource priority, then use, then travel cost, from every group but the last, `necesarios` of the first group (Q-31) | Verified 142 |
| Scan direction, day choice (`dia_minimo_impacto`), stages "same day / enough possibilities / any day" | heuristics of the search | `generar.sql:975-1462` (Doc `database-assessment.md` §5.2) |

The engine computes no objective value. There is no quality score to compare runs with, and no completeness guarantee: an unplaced demand may have a feasible cell that the greedy order never reached.

### 2.3 Read-only warnings, never refusals

| Warning | What it covers | What it does not | Evidence |
|---|---|---|---|
| Cross-plan conflicts (#133) | teachers and rooms of one owner across the owner's dated plans; real dates from Drupal data; shown on the plan timetable, the explorer and the move grid | no move or run is refused; other owners, cohorts and other resources are not compared; placements of a running job are not seen | `temporal-semantics.md` §6; `CrossPlanConflictDetector`; Kernel `testPerformanceBound` (600 placements, 6000 overlaps, 1.4 s) |
| Hygiene and breakdown range on a manual move | advisory: `generar()` applies them, the database does not | — | `timetable-editing.md` §4 |
| Collisions and overflow of a workspace run | shown as metrics on the job page | not refused; see §2.6 | `placement-promotion.md:80`; `SchedulingController.php:89-94` |

### 2.4 Unknown, not conflict-free

| Situation | Status | Evidence |
|---|---|---|
| A plan without `starts_on`, a period without `plan_days`, a period with slot length 0, or an activity type without duration | calendar **unknown**: the UI reads "Dates unknown / cross-plan conflicts not evaluated", never "no conflicts". The demo's synthetic fixture is undated | `temporal-semantics.md` §3, §6 |
| A teacher or room shared by two periods (of one plan or of two plans) on the same real dates | invisible to `generar()`, to the move check and to the unique keys | Verified 150; harness s03 |
| The legacy real-date branch (Q-01) | inert: the lookup finds nothing (6.11). It would over-block if it fired (6.10) | Verified 129 |
| Collisions with other owners' resources, other sites, cohorts across plans | not evaluated anywhere | `temporal-semantics.md` §6 "Not detected" |

### 2.5 Known defects and quirks that affect correctness at scale

| Id | Effect | Consequence for a representative workload | Evidence |
|---|---|---|---|
| 6.17 | `posible_inicio` accepts a start that overflows the day; a duration above the day's slots is placed | placements past the last slot are committed and, in workspace mode, published | case 025 (xfail), 138 (HISTORICAL) |
| 6.2 | 0-based loops over 1-based arrays: an only teacher or only room that is restricted does not block the start | the MPI over-approximates; the later teacher and room selection still filters, so the effect is wasted search, or a cell that then fails | case 021, 022 (xfail); 020, 030 |
| 6.3 / 6.13 | `k` carried over: essential resource groups ignored in `posible_inicio` | the essential-resource rule does not hold | case 024 (xfail) |
| Q-31 / Q-32 | resources come from every group but the last; the choice has no availability check, so a chosen resource that is busy or restricted at the cell fails the **whole run** with 23505 | with shared equipment under contention, a run can be lost completely. Expected from the characterized semantics; not yet exercised at scale | case 142, 083 |
| Q-05 | a template without a teacher or room requirement can never be placed and fails the whole run (23502) | one such template loses the run of the scope | case 123 |
| Q-27 | duration 0 loops until the timeout (availability exception; Drupal keeps `duration ≥ 1`) | excluded by input validation | case 137 (xfail 57014) |
| Q-28, Q-29 | HRT and conditional restrictions fail every run of the scope (42804, 42703); not offered in Drupal | these historical constraint kinds are **unavailable** | cases 070–072, 100 |
| Q-30 | forbidden rooms are never applied | a forbidden room, even every room, is used | case 141 |
| Q-02 | `dame_grupos` ignores the plan: a run schedules matching groups of other plans into this period | with several plans of the same specialty and level, a run places other plans' groups into this period | case 121; harness s03 |
| Q-01, Q-20 | real-date rows: object filter missing; hours built from the day count | inert today (case 129); the read-only warnings compute dates from Drupal data instead | case 129, 146, 151 |
| Specialty restrictions | stored, never read | no effect | case 139 |
| 6.4, 6.12, 6.14, 6.21 | ordering and load heuristics differ from the documented intent | quality, not hard correctness | cases 042/043; `database-assessment.md` §5.6 |
| 6.20 | an unplaced demand only raises a NOTICE (`generar.sql:1488-1492`, "TODO: registrar"); two reasons exist as NOTICE text (`:1436` no room, `:1455` no teacher) | a partial result has counts, not reasons | case 063 |
| D18 / B8 | heap order decides room ties; `dame_grupos` order depends on the plan shape | results depend on load order; fixtures must fix it | case 131; `engine-dependency-closure.md` §6 |

### 2.6 Can a result be called publishable?

**Not without qualification.** What a successful run guarantees today:

1. **Within its period:** no teacher, room, brigade or other-resource double booking, and every manual restriction of the six supported kinds honoured. The period, activity-type, subject and brigade restrictions are filtered by `posible_inicio`; for teachers, rooms, brigades and other resources the unique keys are the last guard, and a violating insert fails the whole run instead of being kept.
2. **Not guaranteed:**
   - **No overflow.** Overflow is committed (6.17). In the two-database mode the worker rolls back a run with collisions or overflow (`EngineClient.php:226-228`). In the **workspace mode** it does not (`WorkspaceClient.php:116-124`, `PlacementPublisher.php:44-46`, proposal P-3 in `placement-promotion.md` §11), and the job state follows only `unplaced = 0` (`Worker.php:286-294`). A workspace job can therefore be `succeeded`, read as the **final** timetable, with overflow > 0. The class comment of `Worker.php:26-27` says that success requires no collision and no overflow; that holds for the two-database mode only.
   - **Cross-period and cross-plan freedom from conflict.** Warning only, for dated plans; unknown for undated plans (§2.3, §2.4).
   - Forbidden rooms (Q-30), essential resources (6.3/6.13), inherited, conditional, specialty and manual other-resource restrictions (Q-28, Q-29, Q-32).
   - Any quality level: there is no objective (§2.2).
3. **Manual moves** are checked against the period's restrictions and occupancy, bounds and overflow (case 150, `timetable-editing.md` §9), but not hygiene, breakdown ranges or other plans.

A defensible label today is: *"valid within its period as the scheduler sees it; cross-plan conflicts: {none found among dated plans | N warnings | not evaluated}"*, and only when `unplaced = 0`, `collisions = 0` and `overflow = 0` are all shown. A product rule for "publishable" is decision DEC-3.

---

## 3. Scale dimensions and a synthetic workload ladder

### 3.1 Dimensions that drive work and difficulty

From the generator's structure (`database-assessment.md` §5.2, `generar.sql`):

| Dimension | Symbol | Where it acts |
|---|---|---|
| Days × slots of a period | D × T | `and_mpi` evaluates D × T cells per (template, group, brigade); each cell calls `posible_inicio`, which queries restrictions per slot of the duration |
| Templates in the scope | A (`cantActEnCurso`) | the whole pass repeats A times (`generar.sql:276,1539`); placed demands are skipped on later passes |
| Groups per template, brigades per group | G, B | MPI per group is the AND over its brigades |
| Demands | N = Σ templates × groups | placement attempts per pass |
| Teachers and rooms eligible per template | e_t, e_r | candidate lists, load and usage queries |
| Restriction density | ρ (share of cells restricted per object) | prunes the MPI; raises difficulty |
| Duration in slots | d | slots checked per cell; overflow risk at the day's end |
| Tightness | τ = demanded slot-units of a resource / its free slot-units in the period | the main difficulty lever; τ > 1 for any resource makes the instance unsatisfiable by counting |
| Sharing | teachers, rooms, resources shared between scopes, periods and plans | occupancy from other scopes (same period) is respected; across periods it is not (§2.4) |
| Other resources and groups | R, `necesarios`, essential | Q-31/Q-32 failure risk |
| Existing placements | P0 | continuation anchors ranges and occupancy |
| Cancellation checks | 51 call sites of `generador.fue_cancelado` (`generar.sql`) | per-cell overhead, several inside inner loops |

The cost is expected to grow at least with A × N × D × T × (queries per cell). This is a structural expectation from the code, **not a measured law**. The ladder exists to measure it.

### 3.2 Generation principles (all rungs)

- **Synthetic only**, from a seeded generator committed to the repository. Names `Teacher 0001`, `Room 0001`, `Cohort 0001`, `Subject 0001`; addresses `example.org`. No value is copied or transformed from the 2010 dump or an `.anc` file (`security-privacy.md` §3).
- **Two outputs per instance:**
  - a SQL fixture for the legacy database;
  - an `ancora.parity.scenario.v1` scenario (or a Drupal fixture for `ancora-academic:load-fixture`) for the Drupal and workspace path.

  Both are loaded in one fixed entry order (D18).
- **Planted structure:** every instance is built around a known feasible assignment (a planted timetable) and then "unplanted": the placements are removed and only the inputs are kept. The planted assignment proves feasibility for the "feasible" variants, and it gives the exact designed count of unsatisfiable demands for the "unsatisfiable" variants. The engine is greedy and incomplete, so the planted solution is a feasibility witness, not an expected output.
- **Only characterized semantics are exercised.** HRT and conditional restrictions (Q-28, Q-29), manual other-resource restrictions (Q-32), duration 0 (Q-27) and `parche`/`parche1` (Q-19; `parche1` only in a dedicated variant, as in s09) are left out of the normal variants. Their known failure modes appear only in the "hazard" variants.
- **Options:** the legacy window's defaults (`forma 1`, `parche false`, `compactar 1`, `parche1 false`, `buscalugar true`, `engine-dependency-closure.md` §5), plus the Drupal API defaults as a second option set.

### 3.3 The rungs

The sizes of L1 and L2 are engineering choices that bracket the current evidence. **L3 is not a historical figure.** Its ranges are provisional and must be recalibrated from approved aggregate counts (DEC-2) before L3 is called "historically representative".

| Parameter | L0 smoke (exists) | L1 small | L2 medium | L3 complex (provisional) |
|---|---|---|---|---|
| Plans / periods | 1 / 1 | 1 / 1 | 1 / 2 | 2–3 / 2 per plan, dated, overlapping repetitions |
| D × T | 2 × 4 (E2E) | 5 × 6 | 5 × 6 | 5–6 × 6–8 |
| Scopes (specialty × level) per period | 1 | 1 | 3–4 | 8–12 |
| Subjects per scope; templates per subject | 2; 2 | 4–6; 2–4 | 6–8; 3–5 | 8–10; 3–6 |
| Classifications (durations) | 1 (1 slot) | 2 (1 slot) | 3 (1–2 slots) | 3–4 (1–3 slots) |
| Brigades per scope; groups per template | 2; 2 | 2–4; 1–4 | 4–6; 1–6 (lecture = all brigades, practical = one) | 4–8; 1–8 |
| Demands N per period | 8 | 20–60 | 300–700 | 2,000–5,000 |
| Teachers; eligible per template | 2; 1 | 4–8; 1–2 | 20–40; 1–3, shared by 2+ scopes | 60–150; 1–3, shared across scopes and plans |
| Rooms; capacity vs enrolment | 1; none | 4–6; none | 10–20; capacities and enrolments set | 30–60; plus fixed rooms and travel costs |
| Other resources (groups, `necesarios`) | 0 | 0 | 3–6 (1 group, 1 needed) | 10–20 (1–2 groups) |
| Restriction density ρ | 0 | ≤ 5 % of cells (teacher, period) | 5–15 % (all six kinds) | 10–25 % (all six kinds) |
| Hygiene limits | none | none | teacher and brigade limits | teacher and brigade limits |
| Breakdown options | defaults | defaults | defaults plus `respetar_orden = false` on some subjects | all characterized combinations |
| Tightness τ (feasible variant) | low | ≤ 0.5 | 0.5–0.75 | 0.6–0.85 |
| Existing placements P0 | 0 | 0 | 0, and a continuation variant | 0, continuation and manual-move variants |
| Exercises | collisions, eligibility | hard constraints of §2.1 within one scope | cross-scope sharing in a period, rooms, resources, hygiene, breakdown | cross-plan warnings, scale, time limits, check latency |

### 3.4 Variants of every rung

| Variant | Construction | Expected (traceable invariant) |
|---|---|---|
| **F** feasible | planted timetable, τ as in §3.3 | I1–I9 of §3.5 hold; placed / N reported. Full placement is **not** required: the engine is incomplete |
| **T** tight | raise τ to 0.9–1.0 on a chosen resource class (teachers, rooms or one brigade) | I1–I9 hold; the shortfall is reported per resource class |
| **U1** unsatisfiable by restriction | one brigade restricted on every cell (pattern of case 063) | exactly the planted k demands of that brigade's groups unplaced; the rest as in F |
| **U2** unsatisfiable by counting | a teacher's eligible demands need more slot-units than the teacher's free cells (τ > 1) | at least (demand − free) of them unplaced (pigeonhole lower bound) |
| **U3** unsatisfiable by capacity | every eligible room of a template has 0 < capacity < the group's enrolment | all those demands unplaced |
| **U4** unsatisfiable by load | a hygiene limit of 0 for one teacher | all demands that only that teacher can teach unplaced (case 140 semantics) |
| **U5** unsatisfiable by breakdown | `min_dias` such that the later templates of a subject cannot fit in D days | the planted count unplaced (case 135 semantics, with Q-24) |
| **H** hazard (known whole-run failures) | one template without a teacher requirement (Q-05); a shared resource under contention (Q-31/Q-32); a duration above T (6.17) | the documented failure: 23502 or 23505 with nothing kept, or overflow > 0. **Recorded, not "fixed"**; each is a correctness risk of §5 |
| **C** continuation | run F partially (soft cancel), then run again | the second run keeps the first run's placements and ids (cases 128, 132) |
| **X** cross-plan | two dated plans sharing teachers and rooms, and one undated plan | warnings counted by `CrossPlanConflictDetector`; the undated plan reported "not evaluated", never "no conflicts" |

### 3.5 Invariants checked after every run

| Id | Invariant | How (existing or proposed) |
|---|---|---|
| I1 | no teacher, room or brigade overlap in a period | `tst.collision` (`dev/sql/tests/fixtures/helpers.sql:34-42`), `EngineClient` metrics |
| I2 | no other-resource overlap in a period | **missing**: `tst.collision` compares teachers, rooms and brigades only (`helpers.sql:41`). Proposed: extend the benchmark's own metric view, without touching the existing helper |
| I3 | overflow = 0, except in the H variant built for it | `tst.metrics` |
| I4 | every placement in a cell that the six restriction kinds allow, with its own occupancy excluded | the case 150 check (`valor_restriccion_*`), run on the final state |
| I5 | eligibility: teacher eligible for the template and assigned to the group; room eligible, capacity rule, fixed room preferred | SQL joins on the final state |
| I6 | hygiene limits per teacher and brigade per day, with the legacy cost semantics (6.21) | SQL on the final state |
| I7 | `placed + unplaced = N` for the scope. Today's `placed` counts every (template, group) placed in the **period** (`helpers.sql:49`), other scopes included, so it can exceed `demands` (§4.6.1: `demands=3 placed=5`); Q-02 adds other plans' groups. Count by scope and by the group's plan | `tst.demand` joined with the placements of the scope and the group's plan |
| I8 | determinism: the same placement digest in 3 repeated runs, and in the legacy database and the workspace | digest of `measure.sql` / `tst.cl_outcome()` style normalization |
| I9 | the designed unplaced count of the U variants is met exactly (U1, U3, U4, U5) or as a lower bound (U2) | the instance's sidecar file (§6.2) |

### 3.6 How partial results are explained

- **Today:** a count only. The engine raises a NOTICE (6.20). The worker records `unplaced`, and Drupal shows "Partial: draft" (`engine-worker.md` §3, `placement-promotion.md` §5). No list and no reason reaches the user.
- **In the benchmark (no engine change):** the list of unplaced demands is computed after the run (`tst.demand` minus `actividad`). The reason comes from the instance's design: the planted cause of U1–U5. For F and T, the benchmark reports which resource classes were saturated at the end, from occupancy counts. This is diagnosis by the test, not by the engine.
- **In the product:** a reason per unplaced demand needs either an engine change (record the two NOTICE reasons of `generar.sql:1436,1455` and the default case) or a separate post-run diagnostic. Either needs approval (DEC-5).

---

## 4. Measurement rubric

### 4.1 Feasibility and integrity (pass/fail per run)

| Measure | Rule |
|---|---|
| Run outcome | succeeded, partial, cancelled, timed_out or failed, with the SQLSTATE of a failure |
| I1–I9 | all must hold. Any violation is a FAIL, except the H variant's documented outcome |
| Across resource types | report I1/I2 per type: teacher, room, brigade, other resource |
| Across periods and plans | report the cross-plan warnings (X variant) as counts. They are never a pass/fail of the engine |

### 4.2 Quality criteria (descriptive, no target set)

The engine optimizes none of these. They are recorded to compare runs of the same instance (legacy database vs workspace, option sets), never to tune the solver.

| Criterion | Definition |
|---|---|
| Placement rate | placed / N, overall and per classification priority (were higher-priority classifications placed first?) |
| Teacher and brigade gaps | empty slots between a teacher's (brigade's) first and last activity of a day, summed |
| Daily load balance | variance of activities per teacher per day; days used per teacher |
| Breakdown spread | days between consecutive templates of a subject, against `min_dias`/`max_dias` |
| Room stability and travel | room changes per brigade per day; Σ travel cost between consecutive rooms (when costs exist) |
| Capacity slack | room capacity − enrolment, per placement with capacity > 0 |
| Zone adherence | share of placements in zone 1 of `zpriori` (meaningful only with `parche1`) |

### 4.3 Performance

| Metric | How | Note |
|---|---|---|
| `generar()` elapsed | `clock_timestamp()` around the call inside the session (`wsproof.run()` pattern), and the worker's `duration_ms` | server-side |
| Job end to end | queued → finished from `ancora_engine_job`, split into snapshot, workspace creation, load, closure and prune, `ANALYZE`, run and publication (phases of `single-db-workspace.md` §8) | workspace mode |
| Backend CPU | `utime + stime` of the backend from `/proc/<pid>/stat` before and after (pid from `pg_backend_pid()`), on the disposable cluster | GNU `time` around the client does **not** see the server's CPU (§4.6) |
| Backend memory | `VmHWM` of the backend from `/proc/<pid>/status`; `temp_bytes` from `pg_stat_database` | |
| Function call counts | `track_functions = all`; calls and self time of `posible_inicio`, `and_mpi`, `valor_restriccion_*` and `generador.fue_cancelado` from `pg_stat_user_functions` | explains growth; overhead to be stated |
| Workspace size | relation sizes of the job schema after the run | |
| Cancellation latency | soft cancel request → return | the existing pattern of `workspace-proof.sh` §5 |
| Timeouts | runs exceeding `time_limit` (default 120 s, maximum 120 s: `EngineSettings.php:63-64`) | a timeout keeps nothing |
| Check latency for moves | click → grid, split into request, queue wait, check run and detection (`dev/browser/tests/latency.spec.js`); report p50, p95, max over ≥ 20 checks at idle sleep 5 s and 1 s | today 6.8 s p50, about 9 s p95 at the default (`docs/ux/planning-workspace.md` §8.3), dominated by the idle sleep. The check loads the whole site, so it grows with L2/L3 |

Each timing is the median of at least 5 runs after one warm-up run, with the minimum and maximum. Cold-cache runs are reported separately.

### 4.4 Environment, commands and immutability

- **Environment record** with every result: CPU model and cores available to the process, RAM, OS and kernel, PostgreSQL version and the non-default settings (the disposable cluster runs with `fsync=off`, `dev/sql/tools/with-ephemeral-pg.sh`), PHP version, the git commit, the fixture generator version, the seed and the fixture's SHA-256.
- **Safety:** every measurement runs through `dev/sql/tools/with-ephemeral-pg.sh` on a socket-only throwaway cluster; never on a persistent server.
- **Proposed commands** (not implemented):

  ```text
  dev/sql/tools/with-ephemeral-pg.sh dev/bench/run.sh --rung L2 --variant F --seed 1 --repeat 5 --mode legacy
  dev/sql/tools/with-ephemeral-pg.sh dev/bench/run.sh --rung L2 --variant F --seed 1 --repeat 5 --mode workspace
  ```

  Each run writes one JSON result with the environment record, the I1–I9 results, the quality criteria and the timings.
- **Immutable fixtures:** generated from (generator version, rung, variant, seed). A committed manifest lists the SHA-256 of each generated fixture and its sidecar. CI regenerates and compares. Expected invariants live in the sidecar, each with the design rule it comes from (§3.4), so every expected number is traceable.

### 4.5 Legacy database vs workspace mode

- **Meaningful:** the same input, options and entry order in a full legacy database (`dev/sql/install.sh`) and in a job workspace. They must give the same placements, normalized inserted rows, DML counters and metrics (the oracle method of `single-db-workspace.md` §5). Timings are compared phase by phase.
- **Not meaningful as-is:** the two-database Drupal mode on a non-empty scope. It deletes the scope's placements first (D7) and rolls back invariant violations (D12), so continuation variants differ by design. Compare it on empty scopes only.
- **Historical comparison:** none is possible as a number. The 212 s trace has no input, no hardware record and no outcome count (§1.2).

### 4.6 Exploratory measurements made for this assessment (not a benchmark)

One machine: AMD Ryzen 3 3200G, 2 cores and about 3 GB RAM available to the session, Ubuntu 24.04, kernel 6.8, PostgreSQL 16.15, disposable cluster with `fsync=off`, commit `f299f10`.

| Command | Result |
|---|---|
| `dev/sql/tools/with-ephemeral-pg.sh dev/sql/tests/run.sh` | 74 cases: **63 PASS, 11 XFAIL, 0 FAIL/XPASS/XWRONG**. Wall clock 2 min 0 s; the sum of the per-case times is 100 s. The `generar()` cases on 2–8 demands take 0.1–1.6 s each (060–066); the closure cases take longest (127: 19.3 s, 126: 13.4 s), mostly fixture setup. GNU `time` reports 8.8 s user and 6.7 s system CPU for the **client side only**: the server runs under `pg_ctl`, outside the measured process tree |
| `dev/sql/tools/with-ephemeral-pg.sh modern/scripts/workspace-proof.sh` | see the TIMING lines quoted in §4.6.1 |

These numbers confirm that today's fixtures run in seconds. They say nothing about L2 or L3.

#### 4.6.1 Workspace proof timings

Second command of §4.6: **91 PASS, 0 FAIL**, wall clock 58 s. Selected lines (times in ms):

| Step | Workspace | Full legacy database |
|---|---|---|
| Engine install | 660 into the tenant, once; about 330 per workspace afterwards | 506 (one engine database) |
| E2E (8 demands): load / `ANALYZE` / `generar()` | 110 / 82 / 1,341 | `generar()` 2,474 (oracle) |
| #92 world jobs 10–14 (91–154 input rows, 17–63 kept by the closure): create / input / closure and prune / `ANALYZE` / run | 271–347 / 190–248 / 207–301 / 56–67 / 158–485 | — |
| Partial run then continuation, jobs 20–21 (84 input rows) | run 1,232 and 1,304 | — |
| Two concurrent jobs, with the proof's test-only slow-placement trigger (0.3 s per placement) | 5,001 in total | — |
| Soft cancel latency | 246 (3 placements kept) | — |
| TEMP alternative in one session (DDL, input, run) | 2,787 | — |
| Sizes | empty workspace tables 0.95 MiB; 124 relations per workspace; tenant database with 12 workspaces 47 MiB | empty engine database 9.8 MiB |

The world jobs report `demands=3 placed=5` with equal oracles: `placed` counts the placements of the whole period, including those of other scopes (I7, §3.5). On these sizes the fixed costs of a job (creation, input, closure, `ANALYZE`: about 0.8–0.9 s) are of the same order as the run itself.

---

## 5. Product-blocking gaps and correctness risks (prioritized)

"Approval" means that the change alters legacy behaviour or adds a product rule. Under #66/#70 it needs its own issue and explicit maintainer approval. Nothing below is fixed here.

| # | Gap or risk | Why it blocks representative use | Evidence | Needs an approved fix? |
|---|---|---|---|---|
| G1 | **No measured capacity at any representative size.** The only historical timing is 212 s for a 7-template scope on unknown hardware; today's fixtures hold 2–8 demands; the job time limit defaults to and is capped at 120 s | a representative run may time out and keep nothing; no size can be claimed | §1.2, §4.6; `EngineSettings.php:63-64`; case 066 | No (measurement first). Raising the limits is a configuration decision (DEC-4) |
| G2 | **A workspace job can be "succeeded" (final) with overflow > 0** (6.17), since the workspace mode does not apply the invariant rollback and the state follows `unplaced` only | a final timetable may contain activities past the last slot; the label "final" is then wrong | §2.6; `Worker.php:26-27` vs `:286-294`; `WorkspaceClient.php:116-124`; `PlacementPublisher.php:44-46`; P-3 | Yes: a product rule (P-3 or "final requires overflow = 0"), and/or the engine fix of 6.17 (DEC-3, DEC-5) |
| G3 | **Whole-run failure modes** (Q-05 template without a teacher/room requirement; Q-31/Q-32 resource choice without an availability check) | at scale one bad template or one contended resource loses the whole scope's run (all or nothing, nothing explained) | cases 123, 142, 083 | Yes: engine fixes, or input guards that refuse such data before the run (DEC-5). An input guard is a new rule too |
| G4 | **Partial results are not explained** (6.20) | a planner cannot act on "N unplaced" for hundreds of demands | `generar.sql:1436,1455,1488-1492`; case 063 | Yes: engine recording, or a separate read-only diagnostic (DEC-5) |
| G5 | **Cross-period and cross-plan double booking is invisible to the engine and only warned about** for dated plans; undated plans are unknown | representative institutions share teachers and rooms across periods and plans; a "no collisions" metric is per period only | §2.3–§2.4; case 150; s03; `helpers.sql:34-42` | Enforcement would be a solver change (not proposed). Labelling is DEC-3 |
| G6 | **Q-02: runs pull other plans' groups** of the same specialty and level | with several plans (years, semesters), runs place groups of the wrong plan into a period | case 121; harness s03 | Yes: engine fix (DEC-5); until then fixtures must state it |
| G7 | **Constraint kinds a historical user may expect are unavailable or inert:** forbidden rooms (Q-30), essential resources (6.3/6.13), HRT and conditional restrictions (Q-28, Q-29), specialty restrictions, manual other-resource restrictions (Q-32) | data imported from a richer source would be silently ignored or would fail runs | §2.5 | Yes, each separately (DEC-5) |
| G8 | **Integrity metrics are incomplete:** no other-resource overlap in `tst.collision`; no quality metrics; no server-side CPU or memory measurement | a benchmark built on today's helpers would report "0 collisions" without checking other resources | `helpers.sql:34-42`; §4.6 | No: benchmark code only (part of the §6 proposal) |
| G9 | **Check latency grows with the site:** each move check loads the whole site into a workspace, without the closure prune | interactive editing on L3-sized sites may become slow; unknown today | `timetable-editing.md` §4, §9; `planning-workspace.md` §8.3 | No (measure first); any threshold is a maintainer decision |
| G10 | **Results depend on entry and heap order** (D18, B8) | two imports of the same data in a different order give different timetables | case 131; `engine-dependency-closure.md` §6 | No: fixtures and importers must fix the order (D2/#144 contract) |
| G11 | **Quality heuristics deviate from the documented intent** (6.4, 6.12, 6.14, 6.21) | quality, not validity | cases 042/043; `database-assessment.md` §5.5–§5.6 | Yes, if ever changed; not a blocker |
| G12 | **Historical datasets are not usable as fixtures and are exposed** (2010 dump in history; VB6 faculty files in a public repository) | blocks any "historical replay" and is a privacy exposure independent of #143 | §1.4 | Maintainer decision (DEC-7), not a code fix |

Ordering rationale: G1 decides whether representative use is possible at all; G2 and G3 decide whether a result that looks complete can be trusted; G4–G7 decide whether a planner can act on it; G8–G12 are measurement, interaction and governance prerequisites.

---

## 6. Proposed fixture and benchmark design (requires approval; not implemented)

### 6.1 Scope of a follow-up implementation issue

1. A seeded synthetic instance generator, `dev/bench/generate.php` (PHP 8.4 CLI, no dependencies), that writes for (rung, variant, seed):
   - a SQL fixture;
   - a parity scenario (or Drupal fixture);
   - a sidecar JSON of expected invariants, with the design rule of each number.
2. A runner, `dev/bench/run.sh`, under `with-ephemeral-pg.sh` only, with the modes `legacy` (full database) and `workspace` (job workspace, `measure.sql`). It writes one JSON result per run with the §4 metrics.
3. A benchmark metric view in its own schema (other-resource overlaps, quality criteria). The existing `tst` helpers and the characterization cases stay unchanged.
4. CI: L1 (all variants) on every change to `dev/sql/**`, `dev/workspace/**` or `dev/bench/**`, with invariants only and no timing thresholds. L2 and L3 run on manual dispatch and publish their JSON as artifacts.
5. Documentation: `docs/evidence/benchmark.md` with the reference environment and the first results, labelled by commit.

Out of scope: any solver, ordering, MPI or Prioriza change; any engine schema change; any import of historical data; any public deployment.

### 6.2 Acceptance criteria for that implementation

- The same (generator version, rung, variant, seed) gives a byte-identical fixture and sidecar on two machines; the manifest SHA-256 values match in CI.
- L1-F and L1-U1..U5 meet every invariant of their sidecar in the legacy and workspace modes, with identical placement digests between modes and across 3 repeated runs.
- The H variants reproduce exactly the documented outcomes (23502/23505 with nothing kept; overflow > 0) and are reported as **known risks**, not failures of the benchmark.
- Each result JSON holds the full environment record and the server-side CPU and memory figures of §4.3.
- L2 and L3 report time, placement rate and integrity for at least 3 seeds each. No threshold is enforced until the maintainer sets one.
- No file under `dev/bench/` or its outputs contains a name, address or identifier that is not generated by the pattern of §3.2. The secret-hygiene check passes, and a name-shape check over the generated fixtures finds only generated patterns.
- The characterization suite (74 cases), the E2E digest `4199818b…`, the parity scenarios and the workspace proof are unchanged.

### 6.3 Privacy plan

1. **Synthetic by construction.** No input row is derived from the 2010 dump, the `.anc` files or any live system. Generated names follow fixed patterns; addresses use `example.org`.
2. **Optional calibration of L3, only if approved (DEC-2).** The maintainer, or an agent under explicit authorization, reads the private historical files **offline** and extracts **aggregate counts only**: entities per type, D × T, durations, restriction density bands, sharing degrees. The output is a small table, reviewed by the maintainer before it is committed. No names, ids, free text or per-person rows leave the private environment. Counts below a minimum cell size (for example 5) are coarsened into bands. Rights to use the historical datasets for this purpose are confirmed first.
3. **No de-identified historical sample** is proposed. If one is ever wanted, it needs its own issue with a rights review, a minimization review and re-identification checks.
4. **CI never reads** `dev/backup/**` or `.anc` files (existing rule, `security-privacy.md` §2–§3).

---

## 7. Decisions needed from the maintainer

| Id | Decision | Options |
|---|---|---|
| DEC-1 | Approve the ladder (§3.3–§3.5) and the rubric (§4) as the basis of the implementation issue | approve / amend ranges / drop variants |
| DEC-2 | Calibrate L3 against history | (a) private aggregate extraction from the VB6 faculty files under §6.3; (b) figures supplied by the maintainer; (c) keep L3 synthetic and label it "provisional, not historical" |
| DEC-3 | What "publishable" or "final" means | (a) keep today's states and add the qualified label of §2.6; (b) also require `overflow = 0` and `collisions = 0` in workspace mode (P-3); (c) also require cross-plan evaluation for dated plans |
| DEC-4 | Time limits for benchmark runs and for production | benchmarks with a raised limit (for example 30 min) to measure, and production defaults decided after the L2/L3 results |
| DEC-5 | Which correctness fixes become their own issues, and in which order | candidates: 6.17 overflow; Q-05 and Q-31/Q-32 whole-run failures (or input guards); 6.20 unplaced reasons (or a read-only diagnostic); Q-02 other plans' groups; Q-30 forbidden rooms; 6.3/6.13 essential resources |
| DEC-6 | Reference environment | a fixed reference workstation profile, GitHub runners, or both, labelled separately |
| DEC-7 | Historical data exposure | the open history decision of `security-privacy.md` §4, and the public visibility of the VB6 faculty files |

---

## LBR impact

Documentation only. **0 bytes of LBR retirement are claimed.**
