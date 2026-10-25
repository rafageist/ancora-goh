# Functional inventory: wiki design vs PHP/PostgreSQL vs VB6

Evidence labels: see [assessment.md](assessment.md) §1. "PHP" means the PHP/PostgreSQL implementation in this repository; "VB6" means `rafageist/ancora-vb6` at `6c7038d` (read-only source review; main engine in `cls/ancora_goh_traditional_TAncora.cls`, abbreviated `TAncora`). Line numbers for VB6 are approximate and refer to that commit.

Status vocabulary:

| Status | Meaning |
| --- | --- |
| **Implemented** | Wired end-to-end in the PHP UI and backed by schema/functions; not yet verified in a running web app |
| **Verified (DB)** | Database behavior reproduced in the experiment DB ([database-assessment.md](database-assessment.md) §6) |
| **Partial** | Some layers exist (schema/functions/UI) but the workflow is incomplete or defective |
| **Schema only** | Tables (and maybe functions) exist; no reachable UI |
| **Missing** | Not found in PHP |
| **Broken** | Exists but fails on any runtime tested or has a verified blocking defect |

## 1. Feature table

| # | Feature | Wiki design / requirement | PHP implementation | VB6 reference | Evidence | Status | Tests needed |
| --- | --- | --- | --- | --- | --- | --- | --- |
| F01 | Web delivery, desktop-like UI | FR-01; *Architecture* | `www/index.php`, `view/custom/app.tpl`, ribbon + windows, jQuery 1.3.2/UI 1.7.1 | Desktop app (`frmKernelMain`) | architecture.md §2, §7 | Implemented (legacy stack) | Browser smoke test after runtime upgrade |
| F02 | Authentication | FR-02; *Use-Cases: Authenticate* | `Application::VerifyAuth` (MD5, SQL injection) | None (no users) | `control/Application.php:29-48` | Partial / insecure | Valid/invalid login, injection attempt |
| F03 | Host-restricted login | *Use-Cases*; *Security-and-Operations* | `seguridad.host`, `seguridad.check_host` — never called | None | grep | Schema only | Decide keep/replace before testing |
| F04 | Roles, permissions, user accounts | *Use-Cases* (administration) | Tables + default data; `getPermissionsOfCurrentUser`; event map in `bin/security.conf` never applied; user/role/permission handlers unreferenced; CLI tools broken | None | architecture.md §4 | Partial / broken enforcement | Per-event authorization tests |
| F05 | Audit traces | *Use-Cases: Manage audit traces* | `seguridad.traza` table; no writer, no UI | None | grep | Schema only | Event logging tests once designed |
| F06 | System configuration | *Use-Cases: Configure system* | Only `bin/*.conf` files | `frmKernelSystemConfig` (not in vbp) | — | Missing | — |
| F07 | Planning projects (overlapping) | *Time-and-Periodicity*, *Domain-Model* | `planificacion` CRUD (`get_form_new/edit_Planificacion`, `get_browser_Planificaciones`), Gantt view (`get_diagram_gantt_planificaciones`) | No project entity; one `.anc` file = one plan | `control/custom/*Planificacion*` | Implemented | Overlap and shared-resource collision across plans (not handled by generator: restrictions are per period) |
| F08 | Periods, slots, breaks, periodicity (odd/even weeks) | *Time-and-Periodicity* | `periodo` (days, slots, start hour, slot minutes), `periodicidad` (`custom_set_Periodicidades`), `receso` (schema; no UI wiring found) | Periods with `template` inheritance; odd/even via separate periods | `get_form_edit_Periodo*` | Implemented (breaks: schema only) | Two periods sharing resources via periodicity; break handling |
| F09 | Specialties, levels | *Activities-and-Student-Groups* | CRUD + organization browser | `Especialidad()`, `addNivel` | `control/custom/get_browser_Organizacion.php` | Implemented | CRUD round-trip |
| F10 | Brigades and dynamic groups per classification | *Activities-and-Student-Groups* (core generality) | `brigada`, `grupo`, `brigada_de_grupo`; tree UI, move/remove brigade between groups (`custom_MoverBrigadaDeGrupo`, `custom_EliminaBrigadaDeGrupo`, `organizacion.mueve_o_crea_brigada_de_grupo`) | `TGxClasif`, `BrigadasDeGrupo` | DB fixture used groups G1/G2 | Implemented; Verified (DB) for generation per group | Combined vs split groups (wiki V&V scenarios 2–3) |
| F11 | Subjects, breakdowns, activity templates | *Activities-and-Student-Groups* | `asignatura`, auto-created `desglose` per period (triggers), `plantilla_de_actividad` ordering (`custom_SubePlantilla`, `custom_BajaPlantilla`, triggers) | `TDesglose` (≤5 activities), no separate template entity | `dev/sql/disparadores/*` | Implemented; ordering defect if `orden` NULL (§6.6) | NOT NULL `orden`; reorder tests |
| F12 | Activity classifications (duration, effort, priority) | *Activities-and-Student-Groups*, *Educational-Workload* | CRUD + priority up/down (`set_ClasifAct_subeprioridad/bajaprioridad`) | `TClasif` (`ct`, `zpriori`) | — | Implemented | Global vs per-plan scope of classifications |
| F13 | Teachers, places, other resources | *Resources-and-Assignments* | CRUD; capacity on places; resources | Yes | `get_form_*_profesor/lugar/otrorecurso` | Implemented | CRUD |
| F14 | Eligible teachers/places per template with priority | *Resources-and-Assignments* | `profesor_para_actividad`, `profesor_para_grupo`, `lugar_para_actividad` with priority editors (`custom_getui_EditarPrioridad*`, `set_prioridad_pxact`), auto-leveling triggers | `ProfeXAct`, `LugXact.priori` | `control/custom/*ParaActividad*` | Implemented | Priority tiers honored by generator |
| F15 | Other-resource groups (necessary count, essential) | *Resources-and-Assignments* | `otro_grupo_de_recursos_para_actividad` + `set_GrupoRecursos_{necesarios,imprescindibles}`; availability check defective (§6.2–6.3); occupancy not recorded (§6.19) | `TGOH_GroupRest`, `getFreeRecursos` | DB experiments | **Broken** | Essential bundle thresholds; double-booking |
| F16 | Capacity, prohibited places, fixed rooms | *Resources-and-Assignments* | `quita_segun_capacidad`, `quita_segun_prohibidos`, `lugar_fijo` used by generator; `acceso_prohibido`/`lugar_fijo` handlers unreferenced in UI | `QuitaSegunCapacidad`, `AccesosProhibidos`, `AulasFijas` | `generar.sql:1176-1213` | Partial (generator yes, UI no) | Capacity boundary (below/equal/above) |
| F17 | Travel cost (asymmetric) | *Resources-and-Assignments* | `costo_de_traslado` (origin→destination), editor `custom_set_costotraslado`, used when `buscalugar = true` and for resources | `Distancias`, `SelectLugarOptimo` | `generar.sql:1316-1322` | Implemented, untested | Asymmetric cost changes chosen room |
| F18 | Nominal time restrictions (period, specialty, subject, classification, brigade, teacher, place, resource) | FR-04; *Constraints-and-Inheritance* | Tables + `valor_restriccion_*`; UI for period restrictions (`custom_set_Restricciones_Periodo`) and generic `form_restricciones.tpl`; specialty restrictions not checked by `posible_inicio` | `getRestriccion` (N/H/T/S/E/P) | §6.1–6.2 | **Broken on PG ≥ 9** (§6.1); Verified (DB, compat mode) for brigade | Every slot of a multi-slot activity checked; each object type |
| F19 | Real (date/time) restrictions | *Constraints-and-Inheritance* | `restriccion_real_*` maintained by RULEs; evaluator lacks object filter (§6.10); no UI | **Not in VB6** | static | Partial (hypothesis of over-blocking) | Holiday on one teacher must not block others |
| F20 | HRT (directional, non-transitive, exceptions) | *Constraints-and-Inheritance* | `herencia_de_restricciones`, `excepcion_de_hrt`, `valor_restriccion_heredada`; only other-resource HRT storable (§6.8); fails on PG 16 (§6.9); handler unreferenced | `TGOH_HRT`, `estaRestringidoPorHerencia` (forward + reverse) | DB experiments | **Broken** | Directionality, non-transitivity, child activities block parent, exceptions |
| F21 | Conditional time restrictions (RCT) | *Constraints-and-Inheritance* | 60-column table, `ocurre_causa_rest_cond` (492 lines), evaluator with undefined variables (§6.18); no UI | **Not in VB6** | static | Schema only / broken | Placement-triggered restriction scenario (V&V 8) |
| F22 | Zone priorities (ZPriori) | *Glossary*, *Generation-Algorithm* step 6 | `zpriori` + editor (`custom_set_zpriori`); generator uses zones only with `parche1` (§6.15) | Zone 1 first; 0 = never | static | Partial | Zone preference changes placement |
| F23 | Educational workload limits | *Educational-Workload* | Tables + `es_higienico_*` used by generator; cost summation defect (§6.21); no UI | **Not in VB6** | static | Partial | Teacher/brigade daily max (V&V 9) |
| F24 | Breakdown rules (min/max days, order, same day) | *Activities-and-Student-Groups* | `desglose` fields; `rango()`, `respetar_orden`, `mismo_dia` in generator; empty range hangs (§6.5) | `RangoV2`, `BuscaActDia` | DB experiment | **Broken** (non-termination) | Spacing min/max; order preserved |
| F25 | Timetable generation (MPI + Prioriza) | FR-03; *Generation-Algorithm*, *MPI-and-Placement*, *Prioriza-in-Timetabling* | `generar` and helpers; queue window with strategy options | `Generar`/`ColocaAct` (greedy, no backtracking) | database-assessment.md §5–6 | Verified (DB, compat mode, small data) with defects | Full V&V scenario list; historical oracle |
| F26 | Two traversal orders | *Generation-Algorithm* | `forma = 1` (classifications × levels) else (levels × classifications) | Same two orders | `generar.sql:262-268` | Implemented | Same input, different `forma` |
| F27 | Directional vs priority-based placement | *MPI-and-Placement* | Directional alternation (`sentido`); priority-like pre-pass only with `parche1` | `sentido`; `parche1` | `generar.sql:1002-1012, 641-775` | Partial | Gap counts with/without `parche1` |
| F28 | Teacher day concentration | *Generation-Algorithm* step 8 | Least-loaded teacher, busiest days first | `setMiDiaSegunProfe` | `generar.sql:814-931` | Implemented with load defect (§6.14) | Teacher working-day count |
| F29 | Stop / continue generation ("anchor") | *Generation-Algorithm* | `generador.cancelar` + `fue_cancelado`; skip already placed | `Detener` flag + `DoEvents` | Verified (DB) | Verified (DB); UI cancel likely blocked (architecture.md §6) | HTTP-level cancel; resume after cancel |
| F30 | Report unplaced activities / reasons | FR-03; *MPI-and-Placement* diagnostics | Only RAISE NOTICE (`-- TODO: registrar`) | `Imposibles` with counts (restriction/teacher/place) and MAXPI; rejected-activities tab | `generar.sql:1465-1468` | Missing | Count of unplaced work (V&V 13) |
| F31 | Inspect timetables by brigade/teacher/place/resource | *Analytics-and-Reporting* | Explorer (`view/wins/explorador_de_horarios.php`, `get_minibrowser_horarios`) — start cell only, overwrites collisions; resource view depends on broken `dame_lista_id_otros_recursos_usados` | Grid explorer, zoom, cell content options, mini-timetable | architecture.md §7 | Partial | Multi-slot rendering; resource view |
| F32 | Manual edit: add/move/swap/fix/remove activities, close gaps | *Use-Cases: Manage timetables* | `add/set/del_Actividad` generated handlers exist but are unreferenced; no "fixed" attribute in `actividad` | Activity dialog (`goKernelDatosActividad`), `permuta`, `QuitaHuecos`, `degenerar` (undo keeping fixed), `fija` flag | grep; VB6 review | Missing | Invariants enforced on manual edits; fixed activity preserved by regeneration (V&V 12) |
| F33 | Undo/clear generated timetable | — (VB6) | None in UI | `degenerar`, `clearAsignacionesOfPeriod` | VB6 | Missing | — |
| F34 | Analyses: extreme slots, occupied time, restriction %, common free slots, quality | FR-05; *Analytics-and-Reporting* | None | `AnalisisPrecentRestricc`, `getANDRestrictions`/`DameHuecosComunes`, `goTurnosExtremos`, `AnalizaOptimo`, `getAnalisisRecursos`, comparative charts (OWC10) | VB6 review | Missing | Expected values on reproducible dataset |
| F35 | Publication / guest view | FR-06; *Use-Cases: Consult* | `access=public` browser (`get_browser_publico`, `get_minibrowser_publico`, `get_public_*`); lists all plans (no `activa` filter); same bypass as F02 | Static HTML site (`CreateHTMLSchedulePublish`) | static | Partial / insecure | Guest cannot see drafts or admin data (V&V 15) |
| F36 | Print / export (HTML, Excel, PDF) | *Analytics-and-Reporting* | None | RTF print, HTML publish, Excel/XML via OWC10 | VB6 review | Missing | Format contracts (to be designed) |
| F37 | Import | — | None | Only `.anc` open; random dataset generator (2026-restored) | VB6 review | Missing | — |
| F38 | Help / onboarding | *Deployment-and-Errors* (help) | Welcome template only | HTML Help project, 15-step wizard | VB6 review | Missing | — |
| F39 | Error handling (JS off, DB down, missing components) | *Deployment-and-Errors* Table 5 | Screen exists in `index.php` but `$dberror` never set and `<nosccript>` typo | — | `www/index.php` | Broken | DB-down behavior |
| F40 | Data repair / integrity tools | — (VB6) | DB FKs (139) and CHECKs (40) | `SuperDatarepara`, `modDataRepair` | — | Partial (by constraints) | — |

## 2. Coverage summary

| Domain (wiki *Functional-Requirements*) | PHP | VB6 |
| --- | --- | --- |
| Administration | Partial (insecure, unenforced) | None |
| Academic organization | Implemented | Implemented |
| Time organization | Implemented (breaks/real restrictions partial) | Implemented (no dates) |
| Activity organization | Implemented | Implemented |
| Resources & eligibility | Implemented; resource bundles broken | Implemented |
| Restrictions: nominal | Broken on modern PG, logic OK in compat mode for brigades | Implemented |
| Restrictions: HRT / conditional / real / workload | Broken / schema-only / partial / partial | HRT yes; others none |
| Generation | Works in compat mode with verified defects | Implemented (greedy) |
| Manual planning | Missing | Implemented |
| Analysis | Missing | Implemented |
| Publication/export | Partial guest view only | HTML/RTF/Excel |

The PHP version is **ahead of VB6 in model generality** (overlapping plans, real-date and conditional restriction design, workload, multi-user, DB-side engine) and **behind VB6 in operational features** (manual editing, diagnostics, analyses, publication). Recovery candidates from VB6 are listed in roadmap.md (FC tasks).

## 3. Datasets and fixtures

| Candidate | Location | Content | Suitability |
| --- | --- | --- | --- |
| Synthetic fixture of this assessment | database-assessment.md §6 | 1 plan, 1 period, 2 brigades/groups, 2 templates, 2 teachers, 2 rooms, resources, HRT rows | **Safe** (no personal data); first CI fixture |
| 2010 developer database | `dev/backup/ancoragoh.sql` | 61-table schema + small real dataset: 39 placements, 9 teachers, 11 places, 6 brigades, 1 user with MD5 hash | Excellent **historical oracle** after anonymization (names, user row) and maintainer approval; do not publish as-is |
| Historical generator trace | `dev/sql/generador/generador.log` | NOTICE trace, 7 activities, 212 s | Performance/behavior reference; contains generated IDs only (spot-check before reuse) |
| VB6 demo scenarios | `ancora-vb6/archivos_ejemplos/demo/AncoraDemo_*` | Small scenarios: denied access, enrolment vs capacity, travel distance, … | Good behavior scenarios; requires an `.anc` reader/converter; privacy spot-check needed |
| VB6 faculty datasets | `ancora-vb6/archivos_ejemplos/{electrica,informatica,arquitectura}/`, `citi.anc` | Real semesters 2006–2009 (up to ~29k lines); apparent full teacher names in `electrica_S2_0607.anc`, name-shaped entries in `citi.anc`, `INFORMATICA2007_S2.ANC` | **Do not publish**; usable only privately for scale benchmarks after anonymization |
| Code-generator models | `dev/src/*.json` | Table/function metadata | Schema documentation, not data |

## 4. Wiki-to-code discrepancies and verification questions (for #4)

| Wiki page | Claim | Code reality | Type |
| --- | --- | --- | --- |
| Architecture | Classes `PartModel`, `EntityModel`, `Entity`, `EntityCollection`, `Service`, `Views`, `Functions`, `Generator`; controller `AncoraApplication`; JS `AncoraServer` | `PartModel` ✓; per-table `<X>Model`, `<X>`, `<X>Collection`; `PersonsService`; `PublicDatabaseViews`; `PublicFunctions`/`MatrixFunctions`/`PriorizaFunctions`/`SeguridadFunctions`/`GeneradorFunctions`; controller `Application`; JS `ancoragoh.server.*` | Naming discrepancy |
| Architecture | "Model provides data access and calls to PostgreSQL routines" | ✓, plus heavy side effects in RULEs/triggers | Omission |
| Data-Model | Recommends recovering a schema manifest | Done here: database-assessment.md §2 | Resolved by this task |
| Time-and-Periodicity | Period: days, slots, start time, slot minutes | ✓ plus `receso` (breaks) not mentioned | Omission |
| Time-and-Periodicity | Multi-slot placement must fit day bounds | `posible_inicio` does not enforce it (§6.17) | Verification gap |
| Activities-and-Student-Groups | Breakdown rules min/max days, order, same day | ✓ (`desglose`), `rango()`; non-termination defect | Defect |
| Resources-and-Assignments | Resource groups with minimum quantity, essential flag | ✓ schema; check and occupancy broken | Defect |
| Constraints-and-Inheritance | HRT applies to "planning objects" | Code: only brigade/teacher/place/other-resource pairs of the same type are representable; storage limited to other-resource by `check_hrt` defect | Scope + defect |
| Constraints-and-Inheritance | Conditional restriction timing open question | Code evaluates conditional restrictions inside `valor_restriccion_*` at candidate evaluation time (design); evaluator incomplete | Answers wiki question (design intent) |
| Constraints-and-Inheritance | Hard vs soft restriction | Code: `valor > 0` = hard block everywhere; zone priorities are the only soft preference; `valor` magnitudes unused | Clarification |
| Educational-Workload | Open questions | Answered in database-assessment.md §5.6 | Answers |
| Generation-Algorithm | Prioriza weights not specified | PHP unweighted 8 criteria; VB6 9 criteria with teacher/place ×2 | Divergence PHP vs VB6 |
| Generation-Algorithm | "Report placements and unplaced work" | Unplaced work not recorded | Gap |
| Generation-Algorithm | Scope = specialty, level, period | ✓ signature | Confirmed |
| MPI-and-Placement | OR-combination for alternative resources | Not implemented; `and_mpi` has unused `zona` parameter | Historical-only description |
| MPI-and-Placement | Rejection diagnostics | Not implemented (VB6 has reason counts) | Gap (proposed improvement per wiki) |
| Prioriza-in-Timetabling | Leveling example | Matches `prioriza.nivelar` exactly (verified) | Confirmed |
| Prioriza-in-Timetabling | `e_i = Fn(s_i)` final leveling | Code sorts raw sums (defective sort) | Divergence + defect |
| Analytics-and-Reporting | VB6 had HTML/Excel outputs | Confirmed in VB6 source (OWC10, HTML frameset, RTF) | Confirmed |
| Security-and-Operations | Host restriction, audit | Schema only | Gap |
| Deployment-and-Errors | Error categories handled | Not implemented in PHP | Gap |
| Verification-and-Validation | `.anc` datasets "for primary and higher education" | VB6 samples contain likely personal data | Privacy caveat to add |
| Home / Historical-Research | PHP repository status | Add a link to this assessment once merged | Doc maintenance |
