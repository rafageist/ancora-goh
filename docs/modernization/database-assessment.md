# PostgreSQL assessment: schema, installation and generator trace

Evidence labels: see [assessment.md](assessment.md) §1. "Experiment DB" means a disposable PostgreSQL 16.15 cluster created for this assessment (§4); nothing was run against any persistent system. Paths are relative to `dev/sql/` unless stated.

## 1. Sources of database truth

| Source | What it is | Installable? |
| --- | --- | --- |
| `dev/sql/<package>/*.sql` (≈236 files) | Hand-written object sources, one object per file, organized by package | Not directly; needs ordering (`deploy.priority`) |
| `dev/sql/deploy.priority` | 283-line manifest `type: package.file @ description @ deps` read by the bundlers | Defines a partial install (see §3) |
| `dev/sql/compilasql.php` → `result.sql` | Bundler that concatenates every manifest entry after its dependencies | Committed `result.sql` is **stale** and fails (§4) |
| `dev/sql/compilatablas.php` → `tabla.sql` | Same bundler restricted to `table:` entries | Fails for the same DDL reasons |
| `dev/sql/ancoragoh.sql` | 26 RULEs (real-restriction maintenance and per-activity occupancy) | **Not in the manifest**; must be applied manually |
| `dev/sql/generador/esquema_generador.sql` | `generador` schema, `generador.cancelar`, `generador.fue_cancelado()` | **Not in the manifest** |
| `dev/sql/trash/*` | Superseded objects | No |
| `model/AncoraGOH.creator.sql` / `.dropper.sql` | Code-generator view of tables using pseudo-types `string`, `replica` | No (not SQL DDL) |
| `dev/backup/ancoragoh.sql` (+ `.custom`, `.tar`) | `pg_dump` of the developer database, started 2010-10-06 | **Yes** — restores on PostgreSQL 16 (§4.4) |
| `dev/sql/generador/generador.log` | NOTICE trace of one historical `generar` run (7 activities, 212,466 ms) | Evidence only |

## 2. Inventory

### 2.1 Schemas and objects

| Schema | Tables | Functions (dump) | Notes |
| --- | --- | --- | --- |
| `public` | 54 (+2 views: `asignaturas_cant_act`, `uso_de_otros_recursos`) | 161 | Domain model, restrictions, generator, helpers |
| `seguridad` | 6 (`rol`, `usuario`, `rol_de_usuario`, `permiso`, `traza`, `host`) | 2 (`tiene_permiso`, `check_host`) | |
| `generador` | 1 (`cancelar`) | 1 (`fue_cancelado`) | Only the cancel mechanism; generator functions live in `public` |
| `matrix` | 0 | 20 | Array helpers (lists, matrices, cubes, min/max, echo) |
| `prioriza` | 0 | 2 | `nivelar`, `nivelar_matrix` |
| `tiempo` | 0 (+2 composite types `ancora_date`, `ancora_hour`) | 14 | Date/time numbering helpers |
| `portal` | source only (`portal/portal.sql`) | 0 | Not in manifest, not in dump |

Totals (dump): **61 tables, 2 views, 200 functions (the dump also declares 18 `pldbg_*` debugger functions that cannot be restored without the `pldbgapi` library), 139 foreign keys, 40 CHECK constraints, 12 triggers, 26 rules, composite types `trango`, `tiempo.ancora_date`, `tiempo.ancora_hour`**. Sources: 195 distinct function names.

### 2.2 Tables by domain area (observed in sources/dump)

| Area | Tables |
| --- | --- |
| Planning & time | `planificacion` (`fecha_inicial`, `cant_dias`, `activa`), `periodo` (`cant_dias`, `cant_turnos`, `hora_inicial`, `duracion_del_turno`), `periodicidad` (period occurrence day within the plan), `receso` (break after slot) |
| Organization | `especialidad`, `nivel`, `brigada` (`matricula`), `grupo` (per classification × specialty × level × plan), `brigada_de_grupo` |
| Activities | `clasificacion_de_actividad` (`duracion`, `prioridad`, `gasto`), `asignatura`, `desglose` (per subject × period: `min_dias`, `max_dias`, `respetar_orden`, `mismo_dia`), `plantilla_de_actividad` (`orden`, `exige_profesor`, `exige_lugar`), `actividad` (placement: template, group, teacher, place, day, slot), `otros_recursos_usados` |
| Resources & eligibility | `profesor`, `lugar` (`capacidad`), `otro_recurso`, `profesor_para_actividad` (+`prioridad`), `profesor_para_grupo`, `lugar_para_actividad` (+`prioridad`), `otro_grupo_de_recursos_para_actividad` (`necesarios`, `imprescindibles`, `prioridad`), `otro_recurso_para_actividad`, `acceso_prohibido` (group × place), `lugar_fijo`, `costo_de_traslado` (directed origin→destination) |
| Nominal restrictions | `restriccion_de_{periodo,especialidad,brigada,asignatura,clasif_act,profesor,lugar,otro_recurso}` (period/day/slot/valor; resource ones carry `por_actividad`, `actividad`) and `restriccion_por_actividad[_de_*]` link tables |
| Real (date/time) restrictions | `restriccion_real` and `restriccion_real_de_*` (fecha, hora_inicial, hora_final), maintained by RULEs calling `actualiza_restriccion_real` |
| HRT | `herencia_de_restricciones` (one child and one parent column per object type, CHECK `check_hrt(...)`), `excepcion_de_hrt` |
| Conditional | `restriccion_condicional` (≈60 columns: cause filters, relative day/slot windows, consequences `consec_*`) |
| Optimization | `zpriori` (classification × period × day × slot → zone value), `higiene_para_profesor`, `higiene_para_brigada` (`gasto_maximo_diario`) |
| Security | `seguridad.*` |

### 2.3 Function groups

| Group | Main functions (sources) |
| --- | --- |
| Generator | `generar`, `and_mpi`, `posible_inicio`, `get_max_colocation`, `rango`, `dia_minimo_impacto`, `agrega_actividad`, place selection (`extraer_lugares_que_no_permanecen`, `lugar_permanece_en`, `quita_segun_capacidad`, `quita_segun_prohibidos`, `busca_lugar_mas_utilizado_por`, `busca_lugar_menos_utilizado_por`, `busca_lugar_menos_costoso`, `dame_lugares_usados_alrededor_de`, `dame_lugares_utilizados_en_el_dia`, `dame_gasto_lugares_en_el_dia`), `dame_costo_trasladar_recurso` |
| Restriction evaluation | `valor_restriccion_{periodo,especialidad,brigada,asignatura,clasif_act,profesor,lugar,otro_recurso}` → `valor_restriccion_generica`, `valor_restriccion_heredada`, `valor_restriccion_condicional`; `valor_rest_entre_config_distintas`; counters `dame_cant_rest_*` |
| Occupancy maintenance | `restringe_segun_actividad`, `restringe_por_uso_de_otro_recurso` (via RULEs on `actividad`, `otros_recursos_usados`) |
| Real-time restrictions | `actualiza_restriccion_real`, `dame_dia_real_planificacion`, `turnos_se_solapan`, `dias_se_solapan` |
| Workload | `es_higienico_profesor`, `es_higienico_brigada`, `dame_gasto_de_plantilla` |
| Eligibility lookups | `dame_lista_id_*`, `dame_cant_*`, `existe_grupo_de_profesor`, … |
| Ordering/priority maintenance | `actualiza_orden_plantillas`, `sube/baja_orden_de_plantilla`, `sube/baja_prioridad_clasif`, `arregla_prioridades_clasif`, `nivela_prioridad_*` triggers |
| Prioriza | `prioriza.nivelar(numeric[], boolean)`, `prioriza.nivelar_matrix(numeric[], boolean[])` |
| Utilities | `iif`, `crear_cadena_aleatoria`, `crear_id_de_replica`, `bool_to_*`, `matrix.*`, `tiempo.*` |

### 2.4 Triggers and rules (dump)

- Triggers: `nuevo_desglose_si_nueva_asignatura` (asignatura), `nuevo_desglose_si_nuevo_periodo` (periodo) — create one `desglose` per subject × period; five `trigger_actualiza_orden_act_*` on `plantilla_de_actividad`; `trigger_actualiza_profesor_para_grupo`; `trigger_arregla_prioridades_clasif_after_delete`; `trigger_nivela_prioridad_lxact_after_{delete,update}`; `trigger_nivela_prioridad_rxact_after_delete`.
- Rules (`dev/sql/ancoragoh.sql`): INSERT/UPDATE on each nominal restriction table → `actualiza_restriccion_real(...)`; INSERT/UPDATE on `restriccion_por_actividad_de_*` → flag `por_actividad`; **INSERT on `actividad` → `restringe_segun_actividad(...)`** (marks brigade/teacher/place as occupied); **INSERT on `otros_recursos_usados` → `restringe_por_uso_de_otro_recurso(...)`**.
- Removing a placement relies on `ON DELETE CASCADE` from the occupancy rows to `actividad` (**verified**: deleting activities left no orphan per-activity restrictions).
- Design note: occupancy is stored **as restrictions** (`por_actividad = true`), so "restriction" and "allocation" share tables and evaluators; this matches VB6 (occupancy bits on the same matrices) and the wiki's HRT rule that child *activities* restrict the parent (`valor_restriccion_heredada` second loop uses `'ACTIVIDAD'`).

## 3. Deployment order and dependency risks (observed)

`compilasql.php` loads `deploy.priority`, then recursively appends each entry after its declared dependencies (`anexa`, lines 64-110). Problems:

1. **Silent dependency mismatches.** Dependency names are matched fuzzily (`packsiguales`, lines 46-62: exact, `*suffix`, `prefix*`, or "ends with"). Unmatched names are ignored without error. The manifest contains typos that therefore do nothing: `seguridad.usurio` (line 69), `activida.asignatura` (line 127), `restriccion_de_obrigada` (line 157), `miscelanea.crea_id_de_replica` (line 158), `@tiempo.is_leap_year` (line 55).
2. **Wrong order.** `organizacion.grupo` (line 82) declares only `clasificacion_de_actividad` and `crear_id_de_replica`, but its DDL references `especialidad`, `planificacion` and `nivel`, listed after it. On a fresh database `grupo` cannot be created, and ~45 dependent objects cascade-fail.
3. **Omitted objects.** 30 source functions are not in the manifest (list in §4.2), including `valor_restriccion_heredada` and `valor_restriccion_otro_recurso` (called on every restriction check), `dame_cant_rest_lugar`, the `generador` schema file and the RULE file `ancoragoh.sql`.
4. **Builder defects.** `packsiguales` line 55 computes `substr(...) - 1` on a string — PHP 8 `TypeError` (verified). `compilatablas.php:216` calls `strpos("DROP", $s)` with arguments reversed, so the intended DROP filter never matches.
5. **Encoding.** Many source files start with a UTF-8 BOM; concatenation places BOMs mid-file, producing `syntax error at or near "﻿CREATE"` (verified).
6. **Destructive scripts.** Most table files start with `DROP TABLE IF EXISTS … CASCADE`; the bundle is not an upgrade path, only an empty-database installer. `dev/backup/drop_database.php` drops and recreates the database with a hard-coded superuser password.

## 4. Installation reproducibility experiments (verified)

Environment: `initdb` of PostgreSQL 16.15 in the session scratch directory, Unix-socket only, port 55432; databases created and dropped freely. No repository file was modified; repairs were applied to scratch copies only.

### 4.1 Committed bundle

`psql -f dev/sql/result.sql` on an empty database → **68 errors**. Root causes in order:

| Line (result.sql) | Error | Root cause | Version-specific? |
| --- | --- | --- | --- |
| 4-5 | `syntax error at or near "'plpgsql'"` | `DROP/CREATE LANGUAGE 'plpgsql'` with quoted name; plpgsql is built in since 9.0 | Modern PG |
| 380 | `trailing junk after numeric literal at or near "0THEN"` | `matrix/create_string_list.sql:25` `filas = 0THEN` | PG ≥ 15 |
| 1692 | `syntax error at or near "clasificacion"` | `organizacion/grupo.sql:4` missing comma | **Any version** |
| 1762 | `syntax error at or near "NOT"` | `actividad/plantilla_de_actividad.sql:10` `actualizacion_interna NOT NULL BOOLEAN` | **Any version** |
| 2701 | `EXIT cannot be used outside a loop` | `tiempo/restriccion/nominal/restringe_por_uso_de_otro_recurso.sql:29` (no enclosing LOOP; also calls non-existent `crea_id_de_replica` with double-quoted identifiers at line 21) | Error in modern PL/pgSQL; older behavior unverified |
| others | `relation ... does not exist` | cascades from the above | — |

### 4.2 Regenerated bundle from current sources

The committed `result.sql` differs from what the sources produce (e.g. `matrix.get_length_of` NULL guard, `get_max_colocation` body). After a one-character scratch fix to `compilasql.php:55`, regeneration produced a 7,689-line bundle; installing it → **88 errors** (same root causes plus BOMs and the `grupo` ordering).

### 4.3 Minimal scratch repair

Applying only: BOM removal; dropping `CREATE/DROP LANGUAGE`; `0THEN`→`0 THEN`; the two DDL syntax fixes; moving the `organizacion.grupo` block after `organizacion.nivel`; prepending `generador/esquema_generador.sql`; appending `ancoragoh.sql` → **2 errors**: `restringe_por_uso_de_otro_recurso` (does not compile) and the RULE that calls it. Loading the 30 omitted functions from sources added two more compile failures: `actividad/dame_lista_id_otros_recursos_usados.sql:8` (`WHERE actividad = idactividad = idgrupo`) and the same `restringe_por_uso_de_otro_recurso`.

Additional runtime blocker found while loading data: `miscelanea/crear_cadena_aleatoria.sql:33` returns undeclared `r`, so every DB-side default ID (`crear_id_de_replica`) fails — e.g. inserting a subject fails inside the `nuevo_desglose_cuando_nueva_asignatura` trigger (**verified**). The dump's version returns `cadena`.

### 4.4 Historical dump

`psql -f dev/backup/ancoragoh.sql` on PostgreSQL 16 → loads. All 301 errors are `role "rafa" does not exist` (ownership) or the missing `$libdir/pldbgapi` debugger library. Contents: 61 tables, 2 views, 200 functions, 26 rules, 12 triggers and data (39 activities, 61 template×group demands, 9 teachers, 11 places, 6 brigades, 6 subjects, 5 periods, 2 plans, 1 user). The function bodies are compared with sources in §4.5. **Privacy:** the dump contains one user row (name, info, e-mail-like field, unsalted MD5 password hash) and teacher/place/subject names; it must be reviewed before being used as a public fixture.

### 4.5 Dump vs sources (normalized function bodies)

- 187 functions identical.
- Only in dump: `actualiza_orden_actividades`, `concat`, `first` (×2), `greatest` (×2), `if` (×2), `nivela_prioridad_lxact_after_delete`, `rand`, and the dump versions of `dame_lista_id_otros_recursos_usados` and `restringe_por_uso_de_otro_recurso` (present in dump because `check_function_bodies = false`; their sources do not compile).
- Only in sources: `dame_cant_profes_asignados_a_plantilla`, `dame_cant_rest_lugar`, `dame_hora_inicial_periodo`, `esta_turno_en_rango`, **`valor_restriccion_otro_recurso`**.
- Different: `nivela_prioridad_rxact_after_delete`; plus `crear_cadena_aleatoria` (dump correct, source broken).

Implication: in the 2010 database, `valor_restriccion_otro_recurso` did not exist, so any path of `posible_inicio` reaching the essential-resource check would raise an error there (Hypothesis about historical runtime; reachable only for templates with ≥ 2 resource groups because of the indexing defect in §6.2).

### 4.6 Modern PostgreSQL compatibility checklist

| Issue | Example | Effect on PG 16 | Status |
| --- | --- | --- | --- |
| Parameter/column name collisions in PL/pgSQL (pre-9.0 code relied on variable substitution) | `valor_restriccion_generica(… id …)` → `where periodo = id` | `column reference "id" is ambiguous` on **every** restriction check | Verified |
| Polymorphic `iif(anyelement)` with two untyped literals | `dame_tipo_hijo_hrt.sql:8` `iif(cond,'','BRIGADA')` | `could not determine polymorphic type` as soon as an active HRT row exists | Verified |
| Numeric literal followed by keyword | `0THEN` | Syntax error (PG ≥ 15) | Verified |
| `CREATE LANGUAGE 'plpgsql'` | `language/plpgsql.sql` | Error (language exists) | Verified |
| `LANGUAGE 'plpgsql'` quoted in function definitions | many files | Accepted, deprecated | Observed |
| `standard_conforming_strings = off`, `escape_string_warning` in dump | dump header | Accepted | Observed |
| RULE-based side effects (`DO ALSO SELECT f(NEW.*)`) | `ancoragoh.sql` | Work, but rules re-evaluate volatile defaults for `NEW.*`; prefer triggers | Observed (risk) |
| OUT parameter misuse | `matrix.set_matrix_in_cube(out pcube anyarray, …)` | Always starts from NULL → returns NULL; `gruposEnCurso` in `generar` is lost | Verified (returns empty) |
| `EXIT` outside loop | `restringe_por_uso_de_otro_recurso.sql:29` | Compile error | Verified |

**Workaround used for behavioral experiments:** `ALTER DATABASE … SET plpgsql.variable_conflict = 'use_variable'` reproduces pre-9.0 name resolution without editing code. It is a valid characterization tool, not a production fix.

## 5. Generator trace (observed in source; behavior verified where stated)

### 5.1 Entry points

```
UI:   view/custom/win_generador.tpl → ancoragoh.client.wins.generador
JS:   www/js/app/server/ancoragoh_server_generador.js  generar(idesp, idnivel, idperiodo, forma, parche, compactar, parche1, buscalugar)
PHP:  control/custom/generar.php → model/data/PublicFunctions.php:1554 Generar()
SQL:  SELECT public.generar('<esp>','<nivel>','<periodo>', forma, parche, compactar, parche1, buscalugar)   -- string-interpolated
Cancel: control/custom/cancelar_generacion.php → new CancelarGenerador(esp,nivel,periodo) → INSERT generador.cancelar
```

Actual signatures (source):

```sql
generar(idesp VARCHAR, idnivel VARCHAR, idperiodo VARCHAR, forma BIGINT, parche BOOLEAN,
        compactar BIGINT, parche1 BOOLEAN, buscalugar BOOLEAN) RETURNS void          -- generador/generar.sql:9
and_mpi(listaidbrg VARCHAR[], cd INTEGER, ct INTEGER, idplantilla VARCHAR,
        zona BIGINT /*unused*/, excepto BOOLEAN) RETURNS BOOLEAN[]                    -- generador/and_mpi.sql
posible_inicio(idplantilla VARCHAR, dia INTEGER, turno INTEGER, idbrigada VARCHAR,
        plugares VARCHAR[], pprofesores VARCHAR[]) RETURNS BOOLEAN                     -- generador/posible_inicio.sql:9
agrega_actividad(idplantilla, idprofesor, idlugar VARCHAR, pdia, pturno INTEGER, idgrupo VARCHAR,
        idrecursos VARCHAR[], momento DOUBLE PRECISION, pcreador VARCHAR) RETURNS void -- generador/agrega_actividad.sql
```

The README/`docs/03-algoritmo/mpi-sql.md` signatures for `and_mpi`/`generar` are illustrative and do not match.

### 5.2 `generar` — faithful pseudocode (line numbers in `generador/generar.sql`)

```
asig  := subjects of (esp, nivel) ordered by activity count in idperiodo DESC          -- 191 (dame_asig_segun_esp_anno_per)
CD,CT := periodo.cant_dias, periodo.cant_turnos                                         -- 201-206
matrixAct[subject][k] := classification of k-th activity template (by orden)            -- 220-238
clasifs := all classifications ORDER BY prioridad (global, not per plan)                -- 259
(cycle1, cycle2) := forma=1 ? (classifications, activity levels) : (levels, classifications) -- 262-268
LOOP voyact = 0 .. cantActEnCurso-1                     -- whole pass repeated N times     -- 281, 1513-1515
  FOR cycle1, cycle2 → (nivel_actual, clasif_actual)
    cand := templates (subject i, order nivel_actual) whose classification = clasif_actual -- 310-322
    TablaPrioriza[i] := [ #teachers, Σ teacher restrictions, #places,
                          Σ place restrictions (computed with dame_cant_rest_profe! line 369),
                          #activities of subject, #essential resource groups,
                          #assigned other resources, #subject restrictions, total ]          -- 335-412
    niveles := prioriza.nivelar_matrix(TablaPrioriza, {t,f,t,f,f,f,t,f})                  -- 425-426
    total_i := Σ_j niveles[i][j]   (unweighted)                                           -- 432-445
    order := sort by total ASC   (defective double swap, §6.4)                           -- 456-491
    idClasifSgte := next classification present at this level                            -- 501-519
    zp := dame_zpriori(clasif, periodo); ztodo := max zone value                          -- 529-542
    FOR each template in order:                                                          -- 549
      maxDia := CD - ((cantActs[i] - nivel_actual) - compactar) * (respetar_orden?1:0)   -- 566 (uses i, not the subject index)
      grupos := dame_grupos(esp, nivel, classification)                                   -- 571
      per group: MPI := and_mpi(brigades of group, CD, CT, template, 0, …); idMaxPIGrupo  -- 581-631
      [parche1: reserve best zone-1 start per group per day]                              -- 641-775
      FOR each group j:
        skip if already placed (continuation / "anchor")                                 -- 985-991
        rango := rango(template, group, min_dias, max_dias)                               -- 812
        if exige_profesor: teachers assigned to group; pick least-loaded; order days by
           that teacher's existing load DESC; mask MPI cells where teacher restricted    -- 814-972
        dia_anterior := dia_minimo_impacto(next classification …) when j = 1              -- 975-980
        alternate scan direction (sentido) per group                                     -- 1002-1012
        FOR zona 1..ztodo, etapa 1..3 (same day | days with enough possibilities | any): -- 1028-1462
          FOR day kk in [CicloIni..CicloFin] (EXIT WHEN kk = CicloFin)                    -- 1051-1440
            teacher workload check (es_higienico_profesor); brigade workload checks       -- 1061-1097
            breakdown rule (no same-day repeat if !respetar_orden && !mismo_dia)          -- 1108-1116
            FOR slot t in direction: if MPI[k][t]:
               places := eligible − not-free − capacity − prohibited; fixed room first,
                         then by priority tier: most-used-by-group / least-used / first,
                         or (buscalugar) least travel cost to neighbours                 -- 1159-1331
               resources := per resource group ORDER BY prioridad, uso, costo LIMIT necesarios -- 1343-1370
               agrega_actividad(...)  → INSERT actividad (+RULE marks occupancy)          -- 1406
        if not placed: NOTICE 'no se pudo colocar' (no persistent record: "TODO: registrar") -- 1465-1468
```

### 5.3 `and_mpi` and `posible_inicio`

- `and_mpi` initializes a `CD×CT` boolean matrix to TRUE and, for every brigade of the group and every (day, slot), ANDs `posible_inicio(template, day, slot, brigade, places, teachers)`; with `excepto = TRUE` a cell already used by this template is forced TRUE. Inside the cell loop it re-filters places by capacity and prohibitions (repeated, idempotent work) and sets the cell FALSE when no place remains.
- `posible_inicio` checks, for each of the `duracion` slots: period, classification, subject and brigade restrictions; then "at least one eligible teacher free for all slots", "at least one eligible place free for all slots", and for each **essential** resource group "at least `necesarios` resources free". It does **not** check that `turno + duracion − 1 ≤ cant_turnos` (verified: a 2-slot activity at the last slot returns TRUE), nor specialty restrictions or workload.

### 5.4 Occupancy (`restringe_segun_actividad`)

Placing an activity inserts per-slot `restriccion_de_{brigada,profesor,lugar}` rows with `por_actividad = TRUE` (verified: 1 row each for a 1-slot activity) through the RULE on `actividad`. Other-resource occupancy depends on `restringe_por_uso_de_otro_recurso`, which does not compile in sources (§4.1): **other resources are never marked as used** in a source-based install.

### 5.5 Prioriza integration

- `prioriza.nivelar(values, true)` = "smaller value → higher priority (level 1)", `false` = "larger first"; equal values share a level (**verified**: `{20,30,20,40,30}` → `{1,2,1,3,2}`, the wiki example).
- The criterion vector `{t,f,t,f,f,f,t,f}` matches the wiki's Table 4 senses exactly (fewer teachers, more teacher restrictions, fewer places, more place restrictions, more subject activities, more essential resources, fewer assigned resources, more subject restrictions).
- Weights: none (sum of levels). The final ranking sorts raw totals instead of leveling them again (the wiki's `e_i = Fn(s_i)` step is equivalent only for ordering).
- `nivelar_matrix` levels the 9th (total) column with a NULL criterion (harmless, overwritten).
- Teacher choice inside placement is *not* Prioriza: least total load, ties by eligibility priority order.

### 5.6 Answers to wiki verification questions (Educational-Workload page)

- *Cost once or per slot?* Once per activity (`dame_gasto_de_plantilla` = classification `gasto`), not per slot.
- *But*: `es_higienico_profesor/brigada.sql:14` adds the **candidate template's** cost for each existing activity of the day instead of each existing activity's own cost (Observed; hypothesis of wrong totals when classifications have different costs).
- *Teacher and brigade scales*: separate tables (`higiene_para_profesor`, `higiene_para_brigada`) per period; same classification cost.
- *Hard constraint?* Yes — a placement day is skipped when the limit would be exceeded; no exceptions mechanism.
- *HRT interaction*: none; workload does not consider inherited objects.

## 6. Defects and risks found (with reproduction)

All "Verified" items were reproduced in the experiment DB built from the §4.3 repaired bundle, the 30 omitted source functions, a fixed `crear_cadena_aleatoria`, and `plpgsql.variable_conflict = use_variable`, using a synthetic fixture (1 plan; 1 period of 2 days × 3 slots; 1 classification; 1 specialty/level; brigades B1, B2 in groups G1, G2; subject A; templates T1/T2; teachers TA/TB; rooms L1/L2). Minimal fixture SQL:

```sql
insert into seguridad.usuario(id,nombre,info,correo,nick,contrasena) values ('u1','Planner','-','-','planner','x');
insert into planificacion(id,descripcion,fecha_inicial,cant_dias,creador) values ('P','Plan',0,30,'u1');
insert into periodo(id,descripcion,cant_dias,cant_turnos,hora_inicial,duracion_del_turno,planificacion) values ('PER','Week',2,3,8,1,'P');
insert into periodicidad(id,periodo,dia) values ('PD1','PER',1);
insert into clasificacion_de_actividad(id,descripcion,duracion,creador,prioridad,gasto) values ('C1','Lecture',1,'u1',1,1);
insert into especialidad values ('E','Program','u1');  insert into nivel values ('N','Year 1','u1');
insert into brigada values ('B1','Cohort 1','E','N',20,'u1'),('B2','Cohort 2','E','N',20,'u1');
insert into grupo values ('G1','G1','C1','E','P','N'),('G2','G2','C1','E','P','N');
insert into brigada_de_grupo values ('B1','G1'),('B2','G2');
insert into asignatura(id,descripcion,especialidad,nivel,creador) values ('A','Subject A','E','N','u1'); -- trigger creates desglose
insert into plantilla_de_actividad(id,desglose,orden,clasificacion) select 'T1',id,1,'C1' from desglose;
insert into profesor values ('TA','Teacher A','u1');
insert into profesor_para_actividad values ('PPA1','T1','TA',1);
insert into lugar values ('L1','Room 1',50,'u1');  insert into lugar_para_actividad values ('T1','L1',1);
```

| # | Defect | Location | Reproduction | Result | Status |
| --- | --- | --- | --- | --- | --- |
| 6.1 | Restriction evaluation fails on PG ≥ 9.0 | `tiempo/restriccion/valor_restriccion_generica.sql:26` (and every branch) | `select valor_restriccion_profesor('x','p1',1,1)` without compatibility setting | `ERROR: column reference "id" is ambiguous` | **Verified** |
| 6.2 | 0-based iteration over 1-based arrays skips the last eligible teacher/place/resource group and evaluates index 0 (NULL) | `generador/posible_inicio.sql:117-145, 155-183, 200-254`; also `generar.sql:348-353, 366-371, 1350-1370` | `insert into restriccion_de_profesor values ('RP1','TA','PER',1,1,1,false,null); select posible_inicio('T1',1,1,'B1',null,null)` | `true` (expected `false`); same for the only room; with two teachers both restricted still `true` | **Verified** |
| 6.3 | `k` not reset in essential-resource loop | `generador/posible_inicio.sql:225-235` | template with `exige_profesor = exige_lugar = false` and two essential resource groups, both restricted; `statement_timeout = 5s` | **infinite loop** (timeout). With `exige_profesor = true` (k = 1 carried over): returns `true` although both resources are restricted | **Verified** |
| 6.4 | Prioriza order sort detaches totals from activities | `generador/generar.sql:468-491` | extracted loop with totals `{5,3,4,1}` | order `{4,3,1,2}`, expected `{4,2,3,1}`. The VB6 original (`TAncora.cls` ≈l.6214-6225) swaps totals by position and sorts correctly, so this is a **porting defect** | **Verified** (on extracted code) |
| 6.5 | Day loop never terminates when the range is empty | `generador/generar.sql:1051-1053, 1439` (`EXIT WHEN kk = CicloFin`) | 2 templates, teacher TA restricted on all slots of day 1; `generar('E','N','PER',1,false,0,false,false)` | `kk` grows past 30,000 with `CicloFin = 0` until timeout | **Verified** |
| 6.6 | Template with `orden IS NULL` hangs the generator | `actividad/dame_id_plantilla.sql:11`, `posible_inicio` loop `EXIT WHEN i = duracion` with NULL | templates inserted without `orden` (the before-insert trigger only renumbers on collision) | `generar` stuck in `and_mpi`; `posible_inicio(NULL,…)` loops | **Verified** (input-validation gap; PHP UI sets `orden`, but nothing enforces it) |
| 6.7 | DB-side IDs broken in sources | `miscelanea/crear_cadena_aleatoria.sql:33` | `insert into asignatura(...)` without id | `ERROR: column "r" does not exist` (inside desglose trigger) | **Verified** |
| 6.8 | HRT `iif` overwrite: only the last type survives | `tiempo/restriccion/herencia/check_hrt.sql:29-43`, `dame_tipo_hijo_hrt.sql:8-11`, `dame_tipo_padre_hrt.sql`, `valor_restriccion_heredada.sql:25-28, 49-52` | `insert into herencia_de_restricciones(id,planificacion,activa,hijo_profesor,padre_profesor) values ('H1','P',true,'TC','TD')` | rejected by `herencia_de_restricciones_check`; child gets no inherited restriction | **Verified** |
| 6.9 | HRT evaluation fails on modern PG | `dame_tipo_hijo_hrt.sql:8` | other-resource HRT row + `valor_restriccion_otro_recurso('RC','PER',2,2)` | `could not determine polymorphic type because input has type unknown` | **Verified** |
| 6.10 | Real-date restrictions are not filtered by object | `valor_restriccion_generica.sql` real-restriction branch (e.g. `restriccion_real_de_profesor WHERE fecha = … AND NOT (…)` without `objeto = id`) | — | any object's real restriction would block all objects of that type on that date/time | Hypothesis (static, high confidence) |
| 6.11 | `periodo` lookup by ambiguous `id` | `valor_restriccion_generica.sql:69` | under PG 8.x semantics `WHERE id = idperiodo` compares parameter `id` (object id) with `idperiodo` | `rperiodo` NULL → real-restriction branch inert | Hypothesis |
| 6.12 | Place restrictions counted with the teacher counter | `generar.sql:369` (`dame_cant_rest_profe(lugaresAsignados[j], …)`) | — | Prioriza criterion 4 is always 0 for places | Observed |
| 6.13 | Resource group indexing | `generar.sql:1354` uses `gruporecursos[i]` (template index) instead of `[gr]`; loop starts at 0 | — | wrong `necesarios` limit, last group skipped | Observed |
| 6.14 | Teacher load uses the current template duration for every existing activity | `generar.sql:851` | — | "busiest day" heuristic distorted when durations differ | Observed |
| 6.15 | Zone priority only constrains placement with `parche1 = TRUE` | `generar.sql:1134-1146` | — | `zona` loop repeats identical searches otherwise | Observed |
| 6.16 | `parche1` path assigns a 3-D array with 2 subscripts | `generar.sql:714` `gruposEnCurso[g][t+tt-1] = FALSE` | — | runtime error expected when `parche1 = TRUE` | Hypothesis |
| 6.17 | `posible_inicio` does not reject starts that overflow the day | `posible_inicio.sql` | `posible_inicio('T1',1,3,'B1',null,null)` with duration 2, 3 slots | `true` | **Verified** (whether `get_max_colocation`/MPI masks it downstream is not established) |
| 6.18 | Conditional restriction evaluator does not compile at runtime | `tiempo/restriccion/condicional/valor_restriccion_condicional.sql:17,34` (`rcdia_ini`, `act`, `rc` undefined) | — | error once any active conditional restriction exists | Observed |
| 6.19 | Other-resource occupancy not recorded | `restringe_por_uso_de_otro_recurso.sql` | install | function missing → resources can be double-booked | **Verified** (compile failure) |
| 6.20 | Unplaced work is not recorded | `generar.sql:1465-1468` (`-- TODO: registrar`) | — | only a NOTICE; UI cannot report unplaced activities (wiki FR-03) | Observed |
| 6.21 | Workload sum uses the candidate's cost | `actividad/higiene/es_higienico_{profesor,brigada}.sql:14` | — | see §5.6 | Observed |
| 6.22 | `matrix.set_matrix_in_cube` returns NULL (OUT param) | `matrix/set_matrix_in_cube.sql:7` | `select matrix.set_matrix_in_cube('{{t}}'::boolean[],1)` | empty | **Verified** |
| 6.23 | `matrix.extract_boolean_column` returns one extra element | — | 2×3 matrix, column 1 | `{t,t,NULL}` | **Verified** |

Positive verifications (same environment):

- `generar('E','N','PER',1,false,0,false,false)` with two templates, two groups, eligible teachers/rooms placed **4/4** activities in 0.4 s with no collisions; groups alternated slots (1 and 3), illustrating the documented gap side-effect of directional scanning.
- Re-running `generar` placed nothing new (**continuation is idempotent**).
- A pre-inserted `generador.cancelar` row made `generar` return without placing and consumed the row (**cancellation works at SQL level**).
- Brigade restrictions are honored by `posible_inicio`; occupancy rows are created on placement and cascade-deleted with the activity.
- The 2010 historical timetable in the dump has **0** teacher, place or brigade overlaps (interval overlap per period/day) and no activity past the last slot; coverage 39 / 61 template×group demands.

## 7. Concurrency, transactions and cancellation

| Topic | Observation | Risk | Test proposal |
| --- | --- | --- | --- |
| Transaction scope | Whole generation is one statement/transaction | Long locks on `actividad` and restriction tables; nothing visible until commit; statement timeout loses all work | Run generation with `statement_timeout`, assert rollback; run read queries concurrently |
| Cancellation | Polling of `generador.fue_cancelado` (SELECT + DELETE) from 51 call sites in `generar.sql`, several inside the innermost loops | Works at SQL level (verified); expensive; UI path likely blocked by PHP session lock | HTTP-level test: start generation, send cancel from same session, measure latency |
| Concurrent generations | No advisory locks; occupancy rows uncommitted in other transaction | Double booking across specialties sharing teachers/rooms | Two sessions generating different specialties sharing one teacher; assert no overlap |
| Manual edits during generation | None implemented in PHP | — | n/a until manual editing exists |
| Partial results | Committed on cancel (anchor semantics) | Expected per wiki; must be documented | Characterization test |
| Performance | Historical: 7 activities in 212 s; pass repeated `cantActEnCurso` times; per-cell function calls with repeated queries | Unusable at institutional scale without profiling | Benchmark on reproducible datasets of increasing size |

## 8. Recommended test scaffolding

1. A reproducible installer (`dev/sql` → ordered bundle) validated on an empty database in CI (roadmap DB-01..DB-04).
2. A **characterization suite** in SQL (pgTAP or plain `DO` assertions) that records *current* behavior of `posible_inicio`, `and_mpi`, `valor_restriccion_*`, `prioriza.nivelar`, `generar` on synthetic fixtures — run first under `plpgsql.variable_conflict = use_variable`, then after the name-collision fix to prove equivalence.
3. Defect tests from §6, each initially marked "expected failure", flipped when the maintainer approves the corresponding fix.
4. A **historical oracle**: restore the (privacy-reviewed, anonymized) 2010 dump, delete placements, regenerate, compare coverage and collision-freedom (not exact equality, since defects and ordering affect placement).
