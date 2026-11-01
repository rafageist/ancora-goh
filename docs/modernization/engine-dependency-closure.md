# Engine dependency closure and side effects of `generar()` (#92)

Issue: #92, under #91 (one PostgreSQL database per tenant, scoped SQL workspace) and #66. This is an audit with characterization tests. It changes no Drupal code, no engine code and no legacy SQL. It renames nothing. **0 bytes of LBR retirement are claimed.**

**Rule of this document.** The legacy code is the functional specification (#70, #66). Everything below records what the SQL does, with `file:line` evidence under `dev/sql/`. Oddities are recorded, not fixed. The ids PAR-nnn, Q-nn, A-n and D1–D18 are those of [legacy-parity-inventory.md](legacy-parity-inventory.md) and [legacy-parity-harness.md](legacy-parity-harness.md).

Evidence levels used below:

- **Verified (case NNN)**: a case of `dev/sql/tests/cases/` asserts it (all `expect: pass`).
- **Source**: read in the SQL; the line is given. Not exercised by a case.

Paths without a directory prefix are under `dev/sql/`. `vrg` abbreviates `tiempo/restriccion/valor_restriccion_generica.sql`.

---

## 1. Answers in short

| Question | Answer |
| --- | --- |
| (a) Which legacy tables can be workspace **input** | The 17 tables Drupal already syncs (`LegacyTableSync::TABLES`), plus the engine-only inputs that legacy reads and Drupal does not provide yet (D9): `receso`, `zpriori`, the nominal restriction tables, `acceso_prohibido`, `lugar_fijo`, `costo_de_traslado`, `higiene_para_*`, `otro_recurso`, `otro_grupo_de_recursos_para_actividad`, `otro_recurso_para_actividad`, `herencia_de_restricciones`, `excepcion_de_hrt`, `restriccion_condicional`. Existing placements (`actividad`, `otros_recursos_usados`, and their occupancy rows) are input too: legacy continues from them (section 5). Section 3 classifies every table. |
| (b) Which tables `generar()` modifies | INSERT: `actividad`, `otros_recursos_usados`; through RULEs: `restriccion_de_{brigada,profesor,lugar,otro_recurso}` (INSERT, then UPDATE of `por_actividad`), `restriccion_por_actividad_de_{brigada,profesor,lugar,otro_recurso}` (INSERT), `restriccion_real_de_{brigada,profesor,lugar,otro_recurso}` (INSERT, then UPDATE). DELETE: `generador.cancelar` (the scope's cancellation row). Nothing else, verified by the DML counters of case 125. |
| (c) Data outside the selected specialty/level/period that must be included | Groups of **other plans** with a brigade of the specialty and level (Q-02); every subject of the specialty and level, with or without templates; in the **same period**: occupancy, load and hygiene of the shared teachers, rooms, brigades and other resources (placements of other specialties, other creators); in **every period**: the room usage of the candidate rooms when `buscalugar = false`, the other-resource usage counts, and the brigade restrictions of the specialty and level (only their bounds matter); conditional restrictions aimed at the period from **any plan**; inheritance rows of the scope's objects; plus the foreign-key parents of all these. Section 4, verified by cases 125–127. |
| (d) Existing placements | Legacy keeps every existing placement, inside and outside the scope, and places only the missing (template, group) demands around them. Partial and soft-cancelled runs leave committed placements that the next run continues. The Drupal worker deletes the scope's placements first (D7). Section 5, verified by cases 061, 062, 063, 128. |
| (e) Single-database execution without behaviour change | **Possible**, with a workspace that reproduces the physical tables, views, RULEs, triggers, inheritance and foreign keys, placed first in the `search_path`, loaded in legacy entry order. Blockers to solve (section 6): two views bound to `public` tables, 26 RULEs and 12 triggers bound to `public` tables, the schema-qualified shared table `generador.cancelar`, foreign keys to `seguridad.usuario`, physical (heap) order and plan-shape dependence, no `SET search_path` on any function. Recommendation for #93: a job-scoped physical schema, with TEMP as the measured alternative. |
| (f) Compatibility and English naming changes | None is made here. Section 8 drafts the Spanish → English mapping, with the names whose translation could change meaning (`actividad` = a placement, not an activity type; `periodo`, `turno`, `brigada`, `grupo`, `fue_cancelado` consumes the request, …). Section 6 lists the compatibility work #93/#94 need. |

Genuine source conflicts that need the maintainer: **none**. Section 10 lists the corrections this audit makes to the #70 inventory.

---

## 2. Objects of the engine database

Installed from the sources by `install.sh` (268 bundle entries) on PostgreSQL 16.15. Catalog query of this audit:

| Schema | Tables | Views | Functions | Role in a run |
| --- | --- | --- | --- | --- |
| `public` | 54 | 2 (`asignaturas_cant_act`, `uso_de_otros_recursos`) | 157 | academic data, restrictions, placements, the generator and its helpers |
| `seguridad` | 6 | — | 2 | `usuario` is the FK parent of every `creador`; the other 5 tables are not used by a run |
| `generador` | 1 (`cancelar`) | — | 1 (`fue_cancelado`) | soft cancellation |
| `matrix` | — | — | 20 | array helpers, no table access |
| `prioriza` | — | — | 2 | Prioriza levelling, no table access |
| `tiempo` | — | — | 14 (+2 types) | date/hour arithmetic, no table access |

The task statement names five schemas; there are six: `tiempo` holds pure functions and types and has no table.

Properties that matter for a workspace:

- **No function has `SET search_path`** (`pg_proc.proconfig` is NULL everywhere) and **none is `SECURITY DEFINER`**. Every unqualified table name in a function body is resolved with the caller's `search_path` at execution time.
- **Schema-qualified references** in the run path: `generador.fue_cancelado` (51 calls in `generador/generar.sql`), `generador.cancelar` (`generador/esquema_generador.sql:14,17`), `matrix.*`, `prioriza.*`, `tiempo.*` (pure functions). `seguridad.usuario` is referenced only by foreign keys, never by a function of the run.
- **No sequence.** Ids of rows the engine creates come from `crear_id_de_replica` (`miscelanea/crear_id_de_replica.sql:31-43`): `ancoragoh-<CURRENT_TIMESTAMP>-<20 random characters>-<table>`, with a uniqueness probe `EXECUTE 'SELECT * FROM ' || tabla …` (line 36) on the unqualified table name.
- **26 RULEs** (`ancoragoh.sql:1-134`): `DO ALSO` on INSERT/UPDATE of the 8 nominal restriction tables (write the real-date rows), on INSERT/UPDATE of the 4 `restriccion_por_actividad_de_*` tables (set `por_actividad`), on INSERT into `actividad` (`restringe_segun_actividad`) and into `otros_recursos_usados` (`restringe_por_uso_de_otro_recurso`).
- **12 triggers** on `periodo`, `asignatura`, `clasificacion_de_actividad`, `plantilla_de_actividad` (5), `lugar_para_actividad` (2), `otro_grupo_de_recursos_para_actividad`, `profesor_para_grupo`. **None fires during a run** (the run writes none of these tables); they fire while the input is loaded.
- **Inheritance**: `restriccion_real` is the parent of the 8 `restriccion_real_de_*` tables, `restriccion_por_actividad` of the 4 `restriccion_por_actividad_de_*` tables. The parents hold no rows of their own; `DELETE FROM restriccion_real` reaches every child.
- **Foreign keys**: 139, all `ON DELETE CASCADE ON UPDATE CASCADE`. `planificacion`, nine catalogue tables (`clasificacion_de_actividad`, `especialidad`, `nivel`, `brigada`, `asignatura`, `profesor`, `lugar`, `otro_recurso`, `otro_grupo_de_recursos_para_actividad`), `actividad` and `otros_recursos_usados` reference `seguridad.usuario` through `creador`.
- `actividad` has two CHECK constraints that call `plantilla_exige_profesor/lugar` (`actividad/actividad.sql`), functions that read `plantilla_de_actividad` by unqualified name.

---

## 3. Relation access map

Access: **R** read by the run, **I/U/D** insert, update, delete by the run, **FK** only as a foreign-key parent of a row the run writes or keeps, **—** not used by a run. "RULE" names a statement fired by a RULE.

Class (for the workspace of #91):

- **CI** canonical Drupal input (an entity or field holds it, or will under D9/A-4);
- **TI** input transformed for the run (derived by triggers or by the sync, not edited as such);
- **WS** mutable work state: input that the run also extends;
- **PO** provisional output of the run;
- **JM** durable job metadata or control state;
- **N** not part of a run.

### 3.1 `public`: configuration, organization, subjects

| Relation | Access by the run (evidence) | Class |
| --- | --- | --- |
| `planificacion` | R `fecha_inicial` for the real-date rows the run writes (`tiempo/restriccion/real/actualiza_restriccion_real.sql:67,71`); FK parent of `periodo`, `grupo` | CI |
| `periodo` | R `cant_dias`, `cant_turnos` (`generador/generar.sql:223-225`; `generador/rango.sql:19`; `generador/dia_minimo_impacto.sql:25`), hours (`tiempo/configuracion/dame_hora_inicial_turno.sql:15`); vrg:73 looks it up **by the object id** (section 4.4) | CI |
| `periodicidad` | R of the period (`actualiza_restriccion_real.sql:80`: one real-date row per periodicity); R of **every other period** in vrg:79 (inert, section 4.4) | CI |
| `receso` | R of the period (`dame_hora_inicial_turno.sql:18`): only the hours of the real-date rows the run writes | CI (engine-only, D9) |
| `clasificacion_de_actividad` | R **every row, every creator**, `ORDER BY prioridad` (`actividad/dame_lista_id_clasif.sql:14`, Q-03); R by id (`generar.sql:548`; `restringe_segun_actividad.sql:24`; `dame_duracion_clasif_de_actividad.sql:3`; `actividad/higiene/dame_gasto_de_plantilla.sql:3`) | CI (priority maintained by triggers: TI) |
| `especialidad`, `nivel` | FK only: the scope ids are parameters; no function of the run reads these tables | CI |
| `brigada` | R (`organizacion/dame_especialidad_de_brigada.sql:3`, `dame_nivel_de_brigada.sql:3`, `dame_matricula.sql:3`, `dia_minimo_impacto.sql:30`) | CI |
| `grupo` | R `clasificacion` (`organizacion/dame_clasificacion_de_grupo.sql:3`); FK parent of `actividad` | CI |
| `brigada_de_grupo` | R (`organizacion/dame_grupos.sql:6`, no plan filter, Q-02; `dame_lista_id_brigadas_de_grupo.sql:6`; `dame_grupo.sql:3`) | CI |
| `asignatura` | R by specialty and level only (`actividad/dame_asig_segun_esp_anno_per.sql:15`), by id (`generar.sql:582`), through the view `asignaturas_cant_act` (`actividad/max_cant_act.sql:10-12`, called at `generar.sql:227`) | CI |
| `desglose` | R (`generar.sql:200,581`; `actividad/dame_cant_act.sql:12`; `dame_clasif_de_asignaturas.sql:21`; `dame_id_plantilla.sql:11`; `dame_periodo_desglose.sql:3`; `rango.sql:18`) | TI (created by the triggers per subject × period of the creator, PAR-031) |
| `plantilla_de_actividad` | R (`generar.sql:199,580`; `rango.sql:17,33`; `agrega_actividad.sql:12`; CHECKs of `actividad`; many `dame_*`) | CI (`orden` maintained by triggers: TI) |
| `zpriori` | R (`generar.sql:549`; `tiempo/zpriori/dame_zpriori.sql:33`; `dia_minimo_impacto.sql:21`) | CI (engine-only, D9) |

### 3.2 `public`: resources and assignments

| Relation | Access by the run (evidence) | Class |
| --- | --- | --- |
| `profesor` | FK only (parent of `actividad.profesor`, of `restriccion_de_profesor`) | CI |
| `profesor_para_actividad` | R (`recurso/asignacion/dame_lista_id_profes_asignados.sql:14`, `_al_grupo.sql:15` `ORDER BY prioridad`, `dame_cant_profes_asignados.sql:10`) | CI |
| `profesor_para_grupo` | R (`dame_lista_id_profes_asignados_al_grupo.sql:16`) | CI |
| `lugar` | R `capacidad` (`recurso/dame_capacidad.sql:3`) | CI |
| `lugar_para_actividad` | R (`generar.sql:1248,1261`; `dame_lista_id_lugares_asignados.sql:13`, **no ORDER BY**: heap order decides ties, case 131; `dame_cant_lugares_asignados.sql:3`) | CI |
| `acceso_prohibido` | R (`recurso/asignacion/es_lugar_prohibido.sql:5`) | CI (engine-only, D9) |
| `lugar_fijo` | R first row of the group (`generar.sql:1228-1229`) | CI (engine-only, D9) |
| `costo_de_traslado` | R with `buscalugar = true` (`generador/busca_lugar_menos_costoso.sql:48`); R in `dame_costo_trasladar_recurso.sql:29,47`, which always returns 0 (`sql-variable-conflict.md`, finding 2) | CI (engine-only, D9) |
| `otro_recurso` | FK only | CI |
| `otro_grupo_de_recursos_para_actividad` | R (`generar.sql:1378`; `generador/posible_inicio.sql:211`; `dame_lista_id_grupos_de_recursos_asignados.sql:14`; `dame_cant_otros_recursos_imprescindibles.sql:5`; `dame_total_otros_recursos_asignados.sql:5`) | CI (D9) |
| `otro_recurso_para_actividad` | R (`generar.sql:1383` `ORDER BY prioridad, uso, costo`; `dame_lista_id_otros_recursos_asignados.sql:15`; `dame_cant_otros_recursos_asignados.sql:3`) | CI (D9) |
| `higiene_para_profesor`, `higiene_para_brigada` | R first row of (period, object) (`actividad/higiene/es_higienico_profesor.sql:17`, `es_higienico_brigada.sql:17`) | CI (engine-only, D9) |

### 3.3 `public`: placements, restrictions, occupancy

| Relation | Access by the run (evidence) | Class |
| --- | --- | --- |
| `actividad` | R (`generar.sql:869` teacher load in the period; `:1004` already placed; `:1134` breakdown per group; `and_mpi.sql:74`; `rango.sql:32`; `busca_lugar_mas_utilizado_por.sql:19` and `busca_lugar_menos_utilizado_por.sql:20` **no period filter**; `dame_lugares_usados_alrededor_de.sql:22,37`; `es_higienico_*.sql:13`; `restringe_por_uso_de_otro_recurso.sql:27`; uniqueness probe of `crear_id_de_replica`). **I** `agrega_actividad.sql:15` (called at `generar.sql:1430`); RULE `actualiza_restricciones_por_actividad` (`ancoragoh.sql:126-129`) | WS (existing placements) + PO (new rows) |
| `otros_recursos_usados` | R usage count **in every period** (`recurso/dame_uso_de_otro_recurso.sql:9`); R in `dame_costo_trasladar_recurso.sql:25,43`. **I** `agrega_actividad.sql:24`; RULE `ancoragoh.sql:131-134` | WS + PO |
| `restriccion_de_periodo` | R (vrg:30; `dia_minimo_impacto.sql:65`) | CI |
| `restriccion_de_especialidad` | **not read** by a run (`valor_restriccion_especialidad` has no caller) | CI (stored only) |
| `restriccion_de_asignatura` | R (vrg:34) | CI (D9) |
| `restriccion_de_clasif_act` | R (vrg:42; `dia_minimo_impacto.sql:51`) | CI (D9) |
| `restriccion_de_brigada` | R (vrg:38; `dia_minimo_impacto.sql:58`: **every period**). **I** `restringe_segun_actividad.sql:39`; **U** RULE `ancoragoh.sql:74-77` | CI (manual rows, `por_actividad = false`) + WS/PO (occupancy rows) |
| `restriccion_de_profesor` | R (vrg:58). **I** `restringe_segun_actividad.sql:56`; **U** RULE `ancoragoh.sql:89-91` | CI + WS/PO |
| `restriccion_de_lugar` | R (vrg:50). **I** `restringe_segun_actividad.sql:60`; **U** RULE `ancoragoh.sql:102-104` | CI + WS/PO |
| `restriccion_de_otro_recurso` | R (vrg:54). **I** `restringe_por_uso_de_otro_recurso.sql:38`; **U** RULE `ancoragoh.sql:115-117` | CI + WS/PO |
| `restriccion_por_actividad_de_{brigada,profesor,lugar,otro_recurso}` | **I** `restringe_segun_actividad.sql:40,57,61`, `restringe_por_uso_de_otro_recurso.sql:40` (their RULEs update the nominal row) | PO |
| `restriccion_real_de_{brigada,profesor,lugar,otro_recurso}` | **I/U** by `actualiza_restriccion_real` (dynamic SQL, `actualiza_restriccion_real.sql:83,90,95`) through the RULEs `ancoragoh.sql:17-59`; R in vrg:104-160 (no effect, section 4.4) | PO (derived) |
| `restriccion_real_de_{periodo,asignatura,especialidad,clasif_act}` | R in vrg:86-130 (no effect); written only when their nominal rows are entered, never by a run | TI (derived from CI) |
| `restriccion_real`, `restriccion_por_actividad` (parents) | `restriccion_real` R only by `ocurre_causa_rest_cond` (conditional path, which fails, PAR-074) | — |
| `herencia_de_restricciones`, `excepcion_de_hrt` | R (`tiempo/restriccion/herencia/valor_restriccion_heredada.sql:16,34,48,66`; `dame_tipo_hijo_hrt.sql:6`, `dame_tipo_padre_hrt.sql:6`) | CI (engine-only; HRT fails with 42804, PAR-073) |
| `restriccion_condicional` | R (`tiempo/restriccion/condicional/valor_restriccion_condicional.sql:12`; `dame_tipo_consec_rest_cond.sql:8`) | CI (must stay empty: any matching row fails the run, PAR-074) |

### 3.4 `seguridad`, `generador`, views

| Relation | Access by the run (evidence) | Class |
| --- | --- | --- |
| `seguridad.usuario` | FK parent of `actividad.creador` (= the subject's creator, `generar.sql:1430`, PAR-008) and `otros_recursos_usados.creador` | TI (Drupal user → engine user `u<uid>`, `LegacyTableSync::ensureCreator`) |
| `seguridad.{rol, rol_de_usuario, permiso, traza, host}` | — | N |
| `generador.cancelar` | R then **D** of the scope's row at each of the 51 checks (`generador/esquema_generador.sql:14,17`) | JM (control state) |
| view `asignaturas_cant_act` | R through `max_cant_act` (`actividad/max_cant_act.sql:11`) | derived (its definition is bound to `public.asignatura` and `public.desglose` by OID, section 6) |
| view `uso_de_otros_recursos` | — (no caller) | N |

Runtime cross-check (case 125, `METRIC scanned=`): the tables with a sequential or index scan during the run are exactly those marked R or FK above that hold data in the fixture. The `restriccion_real_de_{asignatura,clasif_act,periodo,especialidad}` tables are not even scanned: with the NULL date and hours of vrg the planner short-circuits the query.

---

## 4. Minimal complete input closure of one request

A request is (specialty `E`, level `N`, period `P`, options). The plan is the period's plan; the creator is not a parameter. The closure below is implemented as `tst.cl_keep()` in `dev/sql/tests/fixtures/closure.sql` and proven by cases 125–127.

Notation: `S` = subjects with `especialidad = E AND nivel = N` (any creator, any plan); `D` = their breakdowns of `P`; `T` = the templates of `D`; `K` = the classes of `T`; `G` = groups of **any plan** whose class is in `K` and that hold a brigade of (`E`, `N`) (`dame_grupos.sql:6`); `B` = all brigades of `G` plus every brigade of (`E`, `N`); `TE`, `RO`, `OR` = the teachers, rooms and other resources assigned to `T`.

### 4.1 Inside the scope

| Rows | Why (evidence) |
| --- | --- |
| `periodo P`, its `planificacion`, its `periodicidad`, its `receso` | sizes (`generar.sql:223-225`); the real-date rows the run writes (fecha, hours, one per periodicity: `actualiza_restriccion_real.sql:67-98`). With a NULL `hora_inicial` and at least one periodicity row the run fails with 22004 (verified, case 130) |
| `S` with all their `desglose` of `P` and the templates `T` | `dame_asig_segun_esp_anno_per.sql:15` takes **every** subject of (E, N). A subject without a template in `P` makes the run fail with 2202E (the activity-matrix loop at `generar.sql:250-254`; verified, case 127 F2) |
| `K` | durations, cost, priority order (`generar.sql:278,548`) |
| `zpriori` of `K` in `P` | `generar.sql:549,557`; `dia_minimo_impacto.sql:19-23` |
| `G` (every plan), all `brigada_de_grupo` rows of `G`, the brigades `B` | demands (Q-02, verified 121, 126 b); MPI per brigade (`and_mpi.sql:71`); enrolment (`quita_segun_capacidad.sql:10`) |
| `profesor_para_actividad` and `profesor_para_grupo` of `T`; `lugar_para_actividad`, `otro_grupo_de_recursos_para_actividad`, `otro_recurso_para_actividad` of `T` | candidates (`generar.sql:847,1193,1371`) |
| `acceso_prohibido`, `lugar_fijo` of `G` | room filters (`generar.sql:1216,1228`) |
| `higiene_para_profesor` (P, `TE`), `higiene_para_brigada` (P, `B`) | `es_higienico_*.sql:17` |
| nominal restrictions of `P`: `restriccion_de_periodo`; `_clasif_act` of `K`; `_asignatura` of `S`; `_brigada` of `B`; `_profesor` of `TE`; `_lugar` of `RO`; `_otro_recurso` of `OR` (manual rows **and** occupancy rows) | vrg:30-58 through `posible_inicio`, `lugar_permanece_en`, `generar.sql:972` |
| `costo_de_traslado` with `origen` in `RO`, when `buscalugar = true` | `busca_lugar_menos_costoso.sql:48` |
| the `generador.cancelar` row of (E, N, P) | the 51 checks |
| `actividad` of `T` (any group) | already placed (`generar.sql:1004`), order range (`rango.sql:32`), `mismo_dia` (`generar.sql:1134`), `and_mpi.sql:74` |

### 4.2 Outside the scope, same period

| Rows | Why (evidence) | Proof |
| --- | --- | --- |
| placements in `P` of a teacher of `TE` by **another specialty or creator**, with their occupancy rows | occupancy (vrg:58), teacher load (`generar.sql:869`), teacher hygiene (`es_higienico_profesor.sql:13`) | case 127 R1: removing the other specialty's placement of shared teacher TA moves T1/G1 from slot 2 to slot 1 |
| occupancy in `P` of a room of `RO`, of a brigade of `B`, of a resource of `OR`, from any scope | vrg:38-58 | included by 4.1 (nominal rows of `P`) |
| placements in `P` of groups holding a brigade of `B` | brigade hygiene (`es_higienico_brigada.sql:13`), rooms around (`dame_lugares_usados_alrededor_de.sql:22,37`) | source |
| `restriccion_condicional` rows with `activa` and `consec_periodo = P`, **from any plan** | the evaluator references undefined names and fails with 42703 (PAR-074); `consec_periodo` is NOT NULL, and a row aimed at another period is filtered out before the failing function runs | case 127 F3 (fails), N8 (another period: no effect) |

### 4.3 Outside the period, the plan or the creator

| Rows | Why (evidence) | Proof |
| --- | --- | --- |
| groups of other plans (in `G`) and their plans | `dame_grupos.sql:6` has no plan filter (Q-02) | 121, 126 b |
| with `buscalugar = false`: **every** placement (any period, plan, creator) in a room of `RO` | `busca_lugar_mas_utilizado_por.sql:19` and `busca_lugar_menos_utilizado_por.sql:20` have no period filter. Only existence counts: after a non-STRICT `SELECT INTO`, `GET DIAGNOSTICS … ROW_COUNT` is at most 1 (probe) | case 127 R2: removing group G1's placement in room L2 **in plan P2** moves G1 from L2 to L1. Not read with `buscalugar = true` (127 R2, legacy options) |
| `restriccion_de_brigada` of every brigade of (E, N) in **every period** | `dia_minimo_impacto.sql:58-60` (called at `generar.sql:996` when another class follows at the same level). The value it computes is never used (section 4.4), but a row whose `dia`/`turno` is outside `P`'s size fails the run with 2202E | case 127 F1 (fails), N5 (in-range rows: no effect) |
| `otros_recursos_usados` of every resource of `OR`, every period | usage order `ORDER BY prioridad, uso, costo` (`generar.sql:1381-1384`, `dame_uso_de_otro_recurso.sql:9`) | source (other-resource assignment needs two or more resource groups to run at all, Q-09/6.13) |
| `herencia_de_restricciones` rows whose child or parent is in `TE`, `RO`, `B` or `OR`, any plan; their `excepcion_de_hrt` of `P` | `valor_restriccion_heredada.sql:16-74`. The function call `dame_tipo_hijo_hrt(id)` is evaluated only on rows that pass the cheaper conditions (planner cost order on PostgreSQL 16): a row of an unrelated object is not evaluated; a row of a scope object fails with 42804 (PAR-073) | 072; case 127 N7 (a row of two unrelated resources: no effect) |
| the classes of other creators | read (`dame_lista_id_clasif.sql:14`, Q-03) but they have no template in the scope, so `cantPriorizando = 0` skips them. They could only matter if two classes of the scope have the **same** priority: the order of the tie then depends on the sort of the whole list | case 127 N1 (no effect with distinct priorities) |

### 4.4 Read but without effect on the outcome

| Rows | Why |
| --- | --- |
| every `restriccion_real_de_*` row, every other period's `periodicidad` | The real-date branch of vrg looks up the period with `WHERE id = idperiodo`, and under `#variable_conflict use_variable` (vrg:8, #22, the 2010 resolution) `id` is the **object** parameter. `rperiodo` is NULL, so dates and hours are NULL and no row matches (vrg:73-160). Q-01 therefore does not happen. Verified, case 129 (another teacher is free; the same date in another plan is free), case 127 N3/N4. The branch runs only for an object whose id equals the period id (case 129). |
| the shared teacher's placements and occupancy in **other** periods and plans | every teacher check is per period (vrg:58, `generar.sql:869`, `es_higienico_profesor.sql:13`); the real-date branch is inert. Verified, case 127 N2 |
| the value of `dia_minimo_impacto` | `dia_anterior` restricts stage 1 to one day, but `posibXDia[k]` is set only for `k = 1` (`generar.sql:640-642`: the list starts as zeros, `matrix.create_numeric_list`, and `posibXDia[k] > maxColoca` is never true for a zero), so stages 1 and 2 accept only day 1 and the chosen day is never tried. Only the call itself matters: it fails on out-of-range rows (F1), and when it runs `dia_anterior` is no longer 0, which stops the `xasig` marking of stage 2 (`generar.sql:1172-1174`). Source; consistent with 127 N5 |
| `costo_de_traslado` in `dame_costo_trasladar_recurso` | always 0 (`sql-variable-conflict.md`, finding 2; case 013) |
| `restriccion_de_especialidad` | never read (no caller of `valor_restriccion_especialidad`). Verified, 127 N6 |

### 4.5 Not read

Everything of another creator that is not a shared object above (127 N1 removes Bob's user and everything that cascades); other periods' `receso`, `zpriori`, hygiene; teachers, rooms and resources not assigned to `T`; `seguridad.{rol, rol_de_usuario, permiso, traza, host}`; the view `uso_de_otros_recursos`.

### 4.6 Referential closure

The workspace keeps the physical foreign keys (#91). The closure therefore also contains every row referenced by a kept row, to a fixed point: for example the template, breakdown, subject, group and plan of another plan's placement kept for room usage, and the `seguridad.usuario` rows of every `creador`. `tst.cl_keep()` computes it from `pg_constraint`; `tst.cl_prune()` then verifies that no foreign key is dangling. In case 125 the closure of the scope is **64 of 156 rows** of the database (Drupal options; the legacy-option closure is not smaller here because the brigade rows of 4.3 pull the other plan's placements in as FK parents).

### 4.7 Two interacting requests

A request run after another reads what the first wrote. The closure of a sequence is the **union** of the closures, computed before the first run: the first run's output is produced inside the workspace. Verified for three pairs (case 126), each giving the same outcome on the full database and on the union:

| Pair | Interaction | Union closure (rows) |
| --- | --- | --- |
| two creators: Bob (EB, NB, QB), then Alice (E, N, PER) | none: Alice's closure holds no row of Bob (`seguridad.usuario` u2 absent after pruning) | 60 (Bob alone 18) |
| one creator, two plans: (E, N, QER) of plan P2, then (E, N, PER) of plan P | Q-02: the plan 2 group is scheduled in both plans; room usage in QER decides the PER rooms (`buscalugar = false`) | 56 |
| two specialties sharing teacher TA in one period: (F, N, PER), then (E, N, PER) | occupancy and load of TA in PER: T1/G1 moves from slot 1 (alone) to slot 2 | 67 |

### 4.8 The closure depends on the options

`buscalugar` selects what is read for the room choice (`generar.sql:1309`): `false` reads room usage in every period; `true` reads `costo_de_traslado` and the rooms used around the slot in the same period (`dame_lugares_usados_alrededor_de.sql`). `forma`, `compactar`, `parche` and `parche1` change the order and the day range, not the set of relations read (`parche1 = true` is expected to fail, 6.16). A workspace extract must be computed for the options of the job.

---

## 5. Existing placements, partial and cancelled runs, option defaults

Characterized, not decided.

**Continuation (PAR-085, D7).** `generar()` never deletes a placement. For each (template, group) it checks `SELECT count(id) … FROM actividad WHERE plantilla = … AND grupo = …` (`generar.sql:1004-1010`) and skips placed demands. Existing placements, in and outside the scope, occupy their cells through their occupancy rows, count in the teacher load and hygiene, anchor the day range of the later templates of their breakdown (`rango.sql:32-97`), and decide rooms through usage. `control/custom/generar.php` deletes nothing. Verified: 061 (a second run adds nothing), 128 (a partial run's rows are kept with the same ids, cells and placement moment, and the second run adds only the missing demands; a placement that exists before the run is kept, and with `respetar_orden` it leaves the later template of its breakdown unplaced). The Drupal worker deletes the scope's placements first (`modern/web/modules/custom/ancora_engine/src/Engine/EngineClient.php:190`); on case 128's data that gives 4 placements instead of 2.

**Partial runs (PAR-087, D8).** An unplaced demand only raises a NOTICE (`generar.sql:1489-1492`). What was placed is committed with the statement (legacy runs `select public.generar(...)` in autocommit, `model/data/PublicFunctions.php:1415-1417`). The next run continues from it. Verified 063, 128.

**Cancellation (PAR-086, D12).** The legacy window posts `cancelar_generacion.php`, which **inserts** the row (`model/generator/CancelarGeneradorModel.php:33`; a second insert while a row is pending fails on the primary key). Each of the 51 checks reads and deletes the scope's row and returns normally (`generador/esquema_generador.sql:14-18`): the placements made so far are committed and are continued by the next run. Legacy never clears a row that arrives after the run: it stops the **next** run of that scope at its first check (after the NULL-order check of #23, before anything is placed). Verified 062 and `tests/concurrency/cancel_midflight.sh` (scenarios soft-N, late-row). The Drupal worker deletes a pending row before each run and after it (`EngineClient.php:167,223`), and its hard-cancel escalation and its invariant rollback (`EngineClient.php:225-227`) have no legacy counterpart. That first point is a deviation not listed under D12 so far (section 10).

**Errors and timeouts (PAR-088).** One statement: any error rolls back everything of the run, including placements made before the error (verified 066, 123, 127 F1–F3, 130). Legacy sets `set_time_limit(0)` and no `statement_timeout`.

**Option defaults the legacy window posts (D11, A-2).** `www/js/app/client/wins/ancoragoh_client_wins_generador.js:5-11,312-316`: `forma = 1`, `parche = 'false'`, `compactar = 1`, `parche1 = 'false'`, `buscalugar = 'true'` (the default "lugar_cercano: 1" posts `true`, which SQL treats as least travel cost, `generar.sql:1309`). Drupal sends `compactar = 0`, `buscalugar = false` (`modern/web/modules/custom/ancora_engine/src/Job/JobOptions.php:24-28,55`). On the closure fixture the two option sets give different rooms (case 125). The comment at `JobOptions.php:17` ("TRUE = most used place, FALSE = nearest place") is the reverse of the SQL.

---

## 6. Single-database execution: blockers and recommendation

The original SQL can run against a private workspace in the tenant database without changing its behaviour if the workspace reproduces the shapes the code binds to. The blockers, with what each needs:

| # | Blocker | Evidence | What the workspace needs |
| --- | --- | --- | --- |
| B1 | ~157 functions use **unqualified** table names, none has `SET search_path` | catalog (section 2) | the workspace first in the `search_path` for relations (implicit for `pg_temp`; an explicit `SET search_path = <job schema>, <code schema>` otherwise), set by the worker, never by a role with CREATE on another schema in the path |
| B2 | **Views are bound by OID**, not by name: `max_cant_act` reads `asignaturas_cant_act`, which keeps reading `public.asignatura`/`public.desglose` even when same-named workspace tables shadow them, while `dame_cant_act` inside it resolves by name | `actividad/max_cant_act.sql:11`, `actividad/asignaturas_cant_act.sql`; probe: a TEMP `asignatura` shadows unqualified reads but not the view | recreate both views in the workspace |
| B3 | **26 RULEs and 12 triggers** are attached to the `public` tables; a workspace table has none | `ancoragoh.sql`, `disparadores/` | recreate them on the workspace tables. The RULEs carry the occupancy and real-date writes (without them a run would not see its own placements as occupied) |
| B4 | **`generador.cancelar` is schema-qualified** shared state keyed by (specialty, level, period), consumed by whoever checks first | `esquema_generador.sql:2-18`; 51 calls | cannot be shadowed. Two concurrent jobs of the same scope share the row (one consumes the other's cancellation); jobs of different scopes do not interfere. Serialize jobs per scope, or give #94 a compatibility layer keyed by job |
| B5 | Foreign keys to **`seguridad.usuario`** (schema-qualified in DDL) from `actividad`, `otros_recursos_usados`, `planificacion` and nine catalogue tables | catalog | a TEMP table cannot reference a permanent one: the workspace needs its own user table. No function reads `seguridad.usuario`, so only the DDL changes |
| B6 | **Inheritance** `restriccion_real` → 8 children, `restriccion_por_actividad` → 4 children | catalog | recreate the hierarchy (a TEMP child of a TEMP parent is allowed) |
| B7 | **Dynamic SQL** with unqualified names: `actualiza_restriccion_real.sql:83-95`, `crear_id_de_replica.sql:36` | source | works with B1; the strings are built by concatenation (a NULL hour fails with 22004, case 130) |
| B8 | **Physical order and plan shape**: several lists have no `ORDER BY` (`dame_lista_id_lugares_asignados.sql:13`, `dame_lista_id_profes_asignados.sql:14`, `dame_lista_id_brigadas_de_grupo.sql:6`, `lugar_fijo` first row, `higiene_*` first row, `SELECT … INTO` first rows). Room ties are decided by heap order (verified, case 131). `dame_grupos` relies on `GROUP BY grupo`, which PostgreSQL 16 runs as Sort + Group (id order) on these tables, but a HashAggregate plan returns hash order (probe on a TEMP copy: `{GX95, GX54, …}`) | 131; harness "id order" finding; D18 | load each table in legacy entry order (= id order of legacy replica ids); run `ANALYZE` on the loaded workspace (TEMP tables get no autovacuum); compare the plan shape of `dame_grupos` and the digest against the legacy oracle in #93 |
| B9 | **Ids**: no sequence; generated ids embed `CURRENT_TIMESTAMP` and random characters | `crear_id_de_replica.sql:33` | nothing to isolate; digests and comparisons must normalize generated ids (as `tst.cl_outcome()` does). Engine input ids must keep entry order (D15) and must not make an object id equal to a period id (case 129: that activates the real-date branch; Drupal's `%010d:code` ids are unique per entity type only, so a teacher and a period with the same entity id and code would collide) |
| B10 | **plpgsql plan cache**: plans are cached per session and bound to relation OIDs | PostgreSQL behaviour | create the workspace before the session first runs the engine, or use a fresh session per job (the worker already connects per run) |
| B11 | `CHECK` constraints and column `DEFAULT`s call functions by OID whose bodies resolve tables by name | `actividad.sql` CHECKs; `DEFAULT crear_id_de_replica(…)` | works with B1 |
| B12 | `matrix`, `prioriza`, `tiempo` are schema-qualified but pure | catalog | can stay shared code |

TEMP tables compared with a job-scoped schema, from this evidence (the prototype is #93):

| Aspect | TEMP (`pg_temp`) | Job-scoped schema |
| --- | --- | --- |
| Isolation between concurrent jobs | automatic per session | by name; the worker must own it |
| Name resolution | relations of `pg_temp` come first implicitly | explicit `search_path` per session |
| Views, RULEs, triggers, inheritance, FKs | all must be recreated as TEMP; FKs only to TEMP tables (B5) | all must be recreated; FKs may point to tenant tables, but the workspace should be self-contained |
| `generador.cancelar` (B4) | shared, keyed by scope; reachable by a canceller in another session | the same |
| Visibility to other sessions | none: the Drupal process that promotes the output (Entity API, its own connection) cannot read it; output must be read by the worker session before it closes | readable by the promotion step, by diagnosis and by a later transaction |
| Multiple short transactions, retention, recovery between computing and promotion (#91) | only within one session; a crash loses the workspace (the run is then redone from the input snapshot) | possible; cleanup and retention must be explicit |
| Statistics (B8) | no autovacuum: an explicit `ANALYZE` is required | the same right after loading |
| Catalog churn | ~60 tables, 26 RULEs, 12 triggers created per job | the same, plus explicit DROP |

**Recommendation for #93.** Build the prototype as a **job-scoped physical schema** created from one versioned DDL (the legacy table, view, RULE, trigger, inheritance and FK shapes), loaded in legacy entry order with the closure of section 4, analyzed, and run with `search_path = <job schema>, <engine code schema>`, keeping `generador.cancelar` shared and jobs serialized per scope. The reasons are the promotion through Drupal's own connection, retention for diagnosis, and recovery between computing and promotion, which TEMP cannot give across sessions. Measure TEMP with the same DDL as the alternative: it passes the same digest only if the worker reads the output in the same session. Both must reproduce, before any rename, the digests and DML counters of cases 125–127 and the E2E digest.

---

## 7. Characterization cases added

All `expect: pass`. Helpers: `dev/sql/tests/fixtures/closure_world.sql` (two creators, two plans of one creator, two specialties sharing a teacher, rooms of equal priority, a zone row) and `dev/sql/tests/fixtures/closure.sql` (`tst.cl_keep`, `tst.cl_prune`, `tst.cl_run`, `tst.cl_outcome`). An outcome is `placements=[…] inserted=<md5 of the normalized inserted rows> rows=<n> cancel_consumed=<n> dml=[per-table +inserted ~updated -deleted]`, so two equal outcomes have the same placements and the same DML side effects.

| Case | Asserts |
| --- | --- |
| 125_closure_one_scope | POSITIVE: same outcome on the full database (156 rows) and on the closure (64 rows) of (E, N, PER), with Drupal options and with the legacy window's default options; the closure excludes the other creator, all real-date rows and the shared teacher's occupancy in the other plan, and includes room usage in the other plan and the other specialty's placement of the shared teacher; prints the tables the run scans and its DML counters |
| 126_closure_interacting_scopes | POSITIVE: same outcomes of two runs in sequence on the full database and on the union of their closures, for two creators, one creator with two plans (Q-02) and two specialties sharing a teacher; the interaction is real (T1/G1 slot 1 alone, slot 2 after the other specialty) |
| 127_closure_negative | NEGATIVE: removing a required row changes the outcome (R1 the shared teacher's placement of another specialty in the period; R2 the group's room usage in another plan, only with `buscalugar = false`); three required rows fail the run identically on the closure and succeed once removed (F1 out-of-range brigade restriction of another period, 2202E; F2 subject without template, 2202E; F3 conditional restriction of another plan aimed at the period, 42703); removing rows outside the closure changes nothing (N1 the other creator; N2 the shared teacher's occupancy in another plan at the same dates and hours; N3 every real-date row; N4 other periodicities; N5 in-range brigade rows of another plan; N6 specialty restrictions; N7 an inheritance row of unrelated resources; N8 a conditional restriction aimed at another period) |
| 128_generar_continuation_builds_on_placements | continuation: a partial run's rows are kept unchanged and continued; an existing placement is kept and anchors its breakdown's order range; delete-then-run (D7) differs |
| 129_real_restrictions_inert | the real-date branch has no effect (Q-01 does not happen), except for an object whose id equals the period id |
| 130_generar_null_hour_with_periodicity | NULL start hour plus a periodicity row fails the run with 22004; without periodicity it succeeds |
| 131_room_tie_heap_order | equal-priority rooms: the first row in heap order wins; re-entering the rows in the other order changes the room (D18) |

Result: `dev/sql/tools/with-ephemeral-pg.sh dev/sql/tests/run.sh` on PostgreSQL 16.15 (local ephemeral cluster): **43 PASS, 10 XFAIL, 0 FAIL / XPASS / XWRONG** (53 cases). No existing case, fixture or helper was changed; the 46 earlier cases give the same results and METRIC lines as before.

---

## 8. Draft Spanish → English terminology (mapping only)

Nothing is renamed. The English column is a proposal for #94. **Bold** marks names where a literal translation could change the meaning.

### 8.1 Schemas

| Legacy | Proposed | Note |
| --- | --- | --- |
| `public` (academic tables, generator) | `scheduler` (code) + workspace schema (data) | #91 separates code from job data |
| `generador` | `scheduler_control` | holds only the cancellation table |
| `matrix`, `prioriza`, `tiempo`, `seguridad` | `matrix`, `prioritize`, `time`, `security` | `time` needs quoting as an identifier in some contexts; prefer `timeutil` |

### 8.2 Tables and key columns

| Legacy | Proposed | Note |
| --- | --- | --- |
| `planificacion` (`fecha_inicial`, `cant_dias`, `activa`, `creador`) | `plan` (`start_day_number`, `day_count`, `active`, `owner`) | **`fecha_inicial` is a day number counted from 2000** (`date_to_number`), not a DATE |
| `periodo` (`cant_dias`, `cant_turnos`, `hora_inicial`, `duracion_del_turno`) | `period` (`day_count`, `slot_count`, `start_hour`, `slot_length`) | **a period is a window of days × slots inside a plan, repeated by its periodicity**, not a calendar term. The unit of `hora_inicial` is open (A-1) |
| `periodicidad` (`dia`) | `period_occurrence` (`plan_day`) | **the plan day on which the period starts**, not a frequency |
| `receso` (`despues_del_turno`, `duracion`) | `break` (`after_slot`, `length`) | |
| `turno` (column everywhere) | `slot` | **not "shift"** |
| `dia` (column everywhere) | `day` | 1-based index inside the period, except `periodicidad.dia` (plan day) |
| `clasificacion_de_actividad` (`duracion`, `prioridad`, `gasto`) | `activity_type` (`duration_slots`, `priority`, `load_cost`) | |
| `especialidad`, `nivel` | `program`, `level` | Drupal calls them specialty and level; pick one vocabulary |
| `brigada` (`matricula`) | `cohort` (`enrolment`) | **a student cohort/section**, not a team |
| `grupo` | `teaching_group` | **a set of cohorts taught together for one activity type**; not the cohort |
| `brigada_de_grupo` | `teaching_group_cohort` | |
| `asignatura` | `subject` | |
| `desglose` (`min_dias`, `max_dias`, `respetar_orden`, `mismo_dia`) | `subject_period_breakdown` (`min_gap_days`, `max_gap_days`, `keep_order`, `same_day`) | **`mismo_dia = false` means one template per group in total when `respetar_orden = false`** (A-3, Q-22); keep the semantics, not the label |
| `plantilla_de_actividad` (`orden`, `exige_profesor`, `exige_lugar`) | `activity_template` (`sequence`, `requires_teacher`, `requires_room`) | |
| **`actividad`** (`momento_de_colocacion`) | `placement` (`placed_at`) | **an `actividad` is a placed occurrence (template × group × day × slot)**; "activity" would be confused with the template and the type |
| `profesor`, `profesor_para_actividad`, `profesor_para_grupo` | `teacher`, `template_teacher`, `template_teacher_group` | |
| `lugar` (`capacidad`), `lugar_para_actividad` (`actualizacion_interna`) | `room` (`capacity`, 0 = unlimited), `template_room` (`internal_update`) | `lugar` is any place; "room" is the Drupal term |
| `acceso_prohibido`, `lugar_fijo` | `forbidden_room`, `fixed_room` | |
| `costo_de_traslado` (`origen`, `destino`) | `room_travel_cost` (`from_room`, `to_room`) | directional |
| `otro_recurso`, `otro_grupo_de_recursos_para_actividad` (`necesarios`, `imprescindibles`), `otro_recurso_para_actividad`, `otros_recursos_usados` | `resource`, `template_resource_group` (`required_count`, `essential`), `template_resource`, `placement_resource` | |
| `higiene_para_profesor`, `higiene_para_brigada` (`gasto_maximo_diario`) | `teacher_daily_load_limit`, `cohort_daily_load_limit` (`max_daily_load`) | **"higiene" means workload limits**, not hygiene |
| `zpriori` (`valor`) | `priority_zone` (`zone`) | values ≠ 1 are stored only |
| `restriccion_de_<obj>` (`valor`, `por_actividad`, `actividad`) | `<obj>_unavailability` (`weight`, `is_occupancy`, `placement`) | **a row means "unavailable", whatever `valor`**; occupancy rows and manual restrictions share the table |
| `restriccion_real_de_<obj>` (`fecha`, `hora_inicial`, `hora_final`) | `<obj>_dated_unavailability` (`day_number`, `start_hour`, `end_hour`) | derived rows |
| `restriccion_por_actividad_de_<obj>` | `<obj>_occupancy_link` | |
| `herencia_de_restricciones`, `excepcion_de_hrt` | `unavailability_inheritance`, `unavailability_inheritance_exception` | |
| `restriccion_condicional` (`consec_*`) | `conditional_unavailability` (`effect_*`) | `consec` = consequence |
| `generador.cancelar` | `scheduler_control.cancel_request` | |
| `seguridad.usuario` | `security.app_user` | `user` is reserved |

### 8.3 Key functions

| Legacy | Proposed | Note |
| --- | --- | --- |
| `generar(idesp, idnivel, idperiodo, forma, parche, compactar, parche1, buscalugar)` | `generate(program, level, period, prioritization, group_by_type, compaction, maximize_combination, nearest_room)` | **`buscalugar = true` is "least travel cost"** in SQL, though the legacy label says "most used" (A-2). Name the parameter after the SQL meaning, never after the label |
| **`generador.fue_cancelado`** | `consume_cancel_request` | **it deletes the row it finds**: "was_cancelled" hides the side effect |
| `posible_inicio`, `and_mpi` | `can_start_at`, `possible_start_matrix` | MPI = *matriz de posibles inicios* |
| `get_max_colocation` | `max_placements_in_day` | |
| `rango` / `TRango` | `day_range` / `day_range_t` | |
| `dia_minimo_impacto` | `least_impact_day` | |
| `agrega_actividad` | `add_placement` | |
| `restringe_segun_actividad`, `restringe_por_uso_de_otro_recurso` | `record_placement_occupancy`, `record_resource_occupancy` | |
| `actualiza_restriccion_real` | `refresh_dated_unavailability` | |
| `valor_restriccion_<obj>` / `_generica` / `_heredada` / `_condicional` | `<obj>_unavailability_value` / `unavailability_value` / `inherited_…` / `conditional_…` | |
| `dame_<x>` (about 90 functions) | `get_<x>` | |
| `quita_segun_capacidad`, `quita_segun_prohibidos`, `extraer_lugares_que_no_permanecen` | `filter_rooms_by_capacity`, `filter_forbidden_rooms`, `filter_rooms_free_for_duration` | |
| `busca_lugar_mas_utilizado_por` / `_menos_utilizado_por` / `_menos_costoso` | `most_used_room` / `least_used_room` / `cheapest_room` | the "used" checks are existence checks in every period (section 4.3) |
| `es_higienico_profesor` / `_brigada` | `within_teacher_daily_load` / `within_cohort_daily_load` | |
| `prioriza.nivelar`, `nivelar_matrix` | `prioritize.level_column`, `level_matrix` | |
| `crear_id_de_replica`, `crear_cadena_aleatoria` | `new_replica_id`, `random_string` | |

---

## 9. D1–D18: source behaviour, tests, uncovered inputs

| D | Concrete source behaviour that the parity test must exercise | Tests | Domain inputs not covered yet |
| --- | --- | --- | --- |
| D1 catalogues per creator | `dame_asig_segun_esp_anno_per.sql:15` (subjects by specialty and level only); `dame_grupos.sql:6` (groups of every plan); breakdown triggers per creator (PAR-031) | 110, 121, 126 b; harness s03 | a subject of (E, N) without templates in one of the creator's periods fails that period's run (127 F2): a creator with two plans whose subjects differ per plan |
| D2 visibility | none in the engine (UI lists only) | harness s02 | — |
| D3 owner | `actividad.creador` = subject creator (`generar.sql:1430`); `otros_recursos_usados.creador` (#24); FKs to `seguridad.usuario` | 082, 125 (`creador` is part of the inserted-row digest) | — |
| D4 breakdown options | `generar.sql:585` (`maxDia`), `:831` (`rango`), `:1132-1140` (`mismo_dia`); `rango.sql` | 064, 120, 128 | `min_dias`/`max_dias` other than 1/5 with three templates; `respetar_orden` with existing placements of other runs (128 covers one) |
| D5 delete cascades | FK cascades; the next run re-places the freed demands (continuation) | 114, 128 | — |
| D6 invented validations | NULL `hora_inicial` + periodicity fails (130); out-of-range rows fail (127 F1); zero templates fail (127 F2) | 110, 127, 130 | which of these the Drupal validations prevent and which they reject that legacy accepts |
| D7 continuation | `generar.sql:1004-1010`; `EngineClient.php:190` deletes first | 061, 128 | continuation across a soft cancel through the Drupal worker |
| D8 unplaced / partial | `generar.sql:1489-1492` (NOTICE only); committed partial output | 063, 128 | — |
| D9 engine-only inputs | sections 3 and 4: restrictions, zones, hygiene, fixed and forbidden rooms, travel costs, other resources, inheritance, conditional, receso | 012, 020–025, 080–083, 100, 122, 124 | travel costs with `buscalugar = true` and neighbours (`busca_lugar_menos_costoso`, `dame_lugares_usados_alrededor_de`); `receso` (affects only the real-date rows written); other-resource choice with two or more groups and usage in other periods; brigade hygiene; HRT of other resources (fails, 072) |
| D10 membership | `dame_grupos.sql:6`, `dame_lista_id_brigadas_de_grupo.sql:6` | 113 | a brigade in groups of two specialties |
| D11 options | `generar.sql:281-288` (`forma`), `:585` (`compactar`), `:1309` (`buscalugar`); legacy defaults posted (section 5) | 125 (both option sets), 127 R2 | `forma = 2`; `compactar` > 1; `parche`/`parche1 = true` |
| D12 cancel and rollback | `fue_cancelado` consumes and returns normally; legacy never clears late rows (section 5); one-statement rollback | 062, 066, `cancel_midflight.sh` | the late-row semantics through the Drupal worker (it clears the row) |
| D13 order and priority | `dame_lista_id_clasif.sql:14` (`ORDER BY prioridad` over all creators, ties undefined); `dame_clasif_de_asignaturas.sql:23` and `dame_id_plantilla.sql:11` (by `orden`) | 111, 112 | two classes of one scope with the same priority |
| D14 presentation | none in the engine | — | — |
| D15 ids | entry order (`dame_grupos` sorts by id); object id = period id activates the real-date branch (129) | 129, harness s04 | an engine id collision between entity types |
| D16 admin visibility | none in the engine | — | — |
| D17 user deletion | `creador … ON DELETE CASCADE` | 114 | — |
| D18 physical order | heap order of `lugar_para_actividad` decides room ties (131); other lists without `ORDER BY` (B8) | 131 | teacher list order with equal priorities; brigade order inside a group |

---

## 10. Corrections and additions to the #70 inventory

These refine rows of [legacy-parity-inventory.md](legacy-parity-inventory.md). They are characterization results, not decisions.

1. **Q-01 / PAR-072 do not happen in the characterized code.** The real-date branch is inert (section 4.4, case 129). Real-date rows are still written (side effects) and still fail the run when the start hour is NULL and the period has a periodicity (case 130). The inventory's "Needed" scenario for PAR-072 is answered: teacher TA occupied in one period does not block TA or another teacher in another period on the same date and hour.
2. **New quirk: a subject of the scope without templates in the period fails the run** (2202E, `generar.sql:250-254`; case 127 F2). The breakdown triggers create such pairs whenever a creator's subjects are not used in every period of every plan.
3. **New quirk: `dia_minimo_impacto` reads brigade restrictions of every period** and fails with 2202E on a row outside the period's size (case 127 F1); its computed day is never used (`posibXDia`, section 4.4).
4. **New quirk: NULL start hour plus periodicity fails the run** with 22004 (case 130). Relevant to A-1/PAR-011: the legacy UI posts a non-NULL `hora_inicial` (the day count), so it is reached only with the column default.
5. **D18 now has a scenario**: equal-priority rooms are chosen by heap order (case 131).
6. **D12 addition**: the Drupal worker deletes a pending cancellation row before and after each run (`EngineClient.php:167,223`); legacy leaves a late row in place, and it stops the next run of the scope.
7. **D11 addition**: the comment on `buscalugar` in `JobOptions.php:17` is the reverse of the SQL (`generar.sql:1309`).
8. **PAR-056 addition**: with `buscalugar = false` the room choice reads placements of the group and of other groups in **every** period, plan and creator (case 127 R2).
9. Static observation, not characterized: `dame_grupos_de_brigadas.sql:17` appends a group only when it is already in the list (`<> -1`), so `and_mpi` filters forbidden rooms with an empty group list; the forbidden-room filter of `generar.sql:1216` still applies at placement (case 122).

---

## 11. LBR impact

**0 bytes retired.** The cases and helpers are test code. This document plans no retirement; renames or moves would not earn any (#67, #77).
