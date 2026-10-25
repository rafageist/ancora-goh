# SQL source ↔ 2010 dump reconciliation (DB-01)

Issue: #8 (phase #6). Baseline: assessment PR #5, `database-assessment.md` §3–§4 and roadmap GOV-01/DB-01.

## Approved direction (from #6/#8)

- **`dev/backup/ancoragoh.sql`** (with `.custom` and `.tar`) is the 2010 historical oracle. It is kept unchanged and used only for local comparison. It is never an installer or a test fixture, and it contains private data (see `security-privacy.md`, PR for #11).
- **`dev/sql/`** is the canonical, maintainable source for a clean PostgreSQL 16 database.

This document records how the two were compared, every object that differs, and the decision taken for each, including the maintainer approvals. Evidence labels follow the assessment: **verified** means reproduced on PostgreSQL 16.15 in a disposable socket-only cluster; **hypothesis** means not reproducible here (for example, PostgreSQL 8.4 behavior).

## 1. Method and exact invocation

Three new helper scripts live in `dev/sql/tools/`:

| Script | Purpose |
| --- | --- |
| `with-ephemeral-pg.sh <cmd>` | `initdb` a throwaway cluster in a temporary directory and start it with `listen_addresses=''` (Unix socket only). Export `PGHOST`/`PGPORT`/`PGUSER`, run `<cmd>`, then stop and delete the cluster. It never touches an existing server. |
| `check-sources.sh [--keep] [--report F]` | Create a fresh database and load **every** maintained source file. Each file runs in its own transaction with `check_function_bodies = on` and `ON_ERROR_STOP`. Files that fail are retried in later passes until no pass makes progress (fixpoint). Whatever is left is classified by SQLSTATE as **SYNTAX**, **DEPENDENCY** or **OTHER**. The exit status is non-zero if any file is left. |
| `compare-catalog.sh [--all] <ref-db> <cand-db>` + `catalog-inventory.sql` | Read-only, object-by-object comparison of two databases. Covers schemas, tables, columns (type, NOT NULL, default), PK/FK/UNIQUE/CHECK constraints, views, composite types, functions (normalized `pg_get_functiondef`), triggers and rules. |

**Fixpoint loading deliberately ignores `deploy.priority`.** It answers "does each object compile once its dependencies exist?", separately from "is the manifest order right?". The manifest, the bundler and the strict ordered installer are #9.

Excluded from the check, with reasons:

- `trash/`: superseded objects.
- `generador/trash.sql`: an ad-hoc scratch script with 2010 entity ids, not an object.
- `result.sql`, `tabla.sql`: stale generated bundles; #9 decides whether to keep them.
- `datos/`: the data seed, which is an installer concern.

Reproduce the compile check:

```sh
dev/sql/tools/with-ephemeral-pg.sh dev/sql/tools/check-sources.sh
```

Reproduce the comparison. This needs a local copy of the private dump, so it is a maintainer-only step and is not run in CI:

```sh
dev/sql/tools/with-ephemeral-pg.sh bash -c '
  createdb hist && psql -X -q -d hist -f dev/backup/ancoragoh.sql >/dev/null 2>&1
  dev/sql/tools/check-sources.sh --keep | tail -1           # prints the kept database name
  dev/sql/tools/compare-catalog.sh hist agoh_srccheck_<pid>'
```

Restoring the dump on PostgreSQL 16 reports 301 errors, all expected: ownership by a role that does not exist locally (260), the missing `pldbgapi` debugger library and its `pldbg_*` functions (40), and `plpgsql` already existing (1). This matches assessment §4.4.

## 2. Compile results (verified, PostgreSQL 16.15)

| Tree | Files | Loaded | Failed | Failure classes |
| --- | --- | --- | --- | --- |
| `master` @ `2881353` | 268 | 192 (7 passes) | 76 | 6 SYNTAX roots, 70 DEPENDENCY cascades (everything that needs `grupo`, `plantilla_de_actividad` and so on) |
| this branch | 268 | 268 (7 passes; 1 KNOWN, see D-05) | **0** | — |

The 6 syntax roots on `master` were:

| File | Error | Version-specific? |
| --- | --- | --- |
| `language/plpgsql.sql` | `DROP/CREATE LANGUAGE 'plpgsql'` | PG ≥ 9.0 |
| `matrix/create_string_list.sql:25` | trailing junk after numeric literal (`0THEN`) | PG ≥ 15 |
| `organizacion/grupo.sql:4` | missing comma | any version |
| `actividad/plantilla_de_actividad.sql:10` | `NOT NULL BOOLEAN` | any version |
| `actividad/dame_lista_id_otros_recursos_usados.sql:8` | `a = b = c` (`=` is non-associative since PG 9.5) | PG ≥ 9.5 |
| `tiempo/restriccion/nominal/restringe_por_uso_de_otro_recurso.sql:24` | `EXIT` outside a loop | identical in the dump; see D-05 |

**No remaining install-order failure is a syntax failure.** After the fixes, every DEPENDENCY failure on `master` disappears in fixpoint loading. The manifest-specific problems (`grupo` order, dependency typos, 30 omitted functions, `esquema_generador.sql` and `ancoragoh.sql` not listed) are unchanged and belong to #9.

## 3. Object reconciliation (verified)

Full per-object table: [`sql-reconciliation-objects.tsv`](sql-reconciliation-objects.tsv). It has 923 rows of `kind / identity / status`, object names only, with no data. Five dump-only debugger composite types (`breakpoint`, `frame`, `proxyinfo`, `targetinfo`, `var`) are left out (D-12).

| Kind | Identical | Different | Only in dump | Only in sources |
| --- | --- | --- | --- | --- |
| schema | 6 | 0 | 0 | 1 (`portal`) |
| table | 60 | 1 | 0 | 0 |
| column | 358 | 1 | 0 | 0 |
| constraint (PK / FK / UNIQUE / CHECK) | 60 / 139 / 15 / 35 | 0 | 0 | 0 |
| view | 2 | 0 | 0 | 0 |
| composite type | 3 | 0 | 0 | 0 |
| function | 189 | 2 | 8 | 5 |
| trigger | 12 | 0 | 0 | 0 |
| rule | 26 | 0 | 0 | 0 |

Every non-identical object, with the decision applied:

| Object | Status | Decision |
| --- | --- | --- |
| `plantilla_de_actividad.actualizacion_interna` (and its table) | different: sources `NOT NULL`, dump nullable | D-02 |
| `matrix.create_string_list(bigint)` | different: whitespace token only | D-07 |
| `dame_lista_id_otros_recursos_usados(varchar)` | different | D-04 |
| `actualiza_orden_actividades()`, `concat(text,text)`, `first(anyelement,anyelement)`, `greatest(numeric,numeric)`, `greatest(numeric,numeric,numeric)`, `if(boolean,integer,integer)`, `if(boolean,text,text)`, `rand()` | only in dump | D-09 |
| `valor_restriccion_otro_recurso(...)`, `dame_hora_inicial_periodo(varchar)`, `dame_cant_rest_lugar(varchar,varchar)`, `dame_cant_profes_asignados_a_plantilla(varchar)`, `esta_turno_en_rango(...)` | only in sources | D-10 |
| schema `portal` | only in sources | D-11 |

Objects that **became identical** because of this change: `crear_cadena_aleatoria` (D-01), `nivela_prioridad_lxact_after_delete` and its trigger (D-03), and `restringe_por_uso_de_otro_recurso` (now loaded, byte-equal to the dump; D-05). `nivela_prioridad_rxact_after_delete` is also now unambiguous (D-03).

**Relation to the assessment's "18 function discrepancies"** (12 only in dump, 5 only in sources, 1 changed, plus `crear_cadena_aleatoria`). The assessment loaded sources through the broken manifest, so some objects were missing for install reasons rather than real differences. With fixpoint loading they now resolve as follows:

- 3 dump-only entries were artifacts of the old load: `dame_lista_id_otros_recursos_usados` and `restringe_por_uso_de_otro_recurso` (they did not compile from source), and `nivela_prioridad_lxact_after_delete`, whose source had been overwritten (D-03).
- 8 remain dump-only (D-09). The assessment listed `first` twice: the second is the `first(anyelement)` **aggregate**, which the function inventory does not cover. It is also dump-only and handled in D-09.
- The 5 source-only functions are unchanged (D-10).
- The changed function `nivela_prioridad_rxact_after_delete` was a duplicate-file conflict (D-03).
- `crear_cadena_aleatoria` was a source defect (D-01).

## 4. Decision log

Status values:

- **Applied**: done in this PR on evidence.
- **Approved**: proposed in this PR and confirmed by the maintainer (2026-10-25, recorded on #8). D-05 is approved as a documented known defect, with the alternatives in §5.

| ID | Object / file | Decision | Evidence | Behavior impact | Status |
| --- | --- | --- | --- | --- | --- |
| D-01 | `miscelanea/crear_cadena_aleatoria.sql:33` | `RETURN r` → `RETURN cadena`, matching the dump | The dump body returns `cadena`, and `r` is undeclared (verified error `column "r" does not exist` on every DB-side id). Now identical to the dump. | Restores 2010 behavior. The historical quirk is **kept**: `round(1 + random()*n)` can index past the end, so ids can be shorter than requested (observed 19 vs 20). That belongs to #10. | Applied |
| D-02 | `actividad/plantilla_de_actividad.sql:10` | `NOT NULL BOOLEAN default FALSE` → `BOOLEAN NOT NULL default FALSE` | The source states NOT NULL intent, and so does `trash/actualiza_orden_actividades.sql:19` (`SET NOT NULL`). The dump column is nullable with default `false`, and its 19 rows have 0 NULLs. The ordering triggers test `= FALSE`, where NULL would silently skip renumbering. | Only rejects an explicit NULL write. No source writes NULL. | **Approved**: keep `BOOLEAN NOT NULL DEFAULT FALSE` |
| D-03 | `disparadores/nivela_prioridad_lxact.sql` | File restored from the dump's `nivela_prioridad_lxact_after_delete()` body plus trigger `trigger_nivela_prioridad_lxact_after_delete` on `lugar_para_actividad` | The file name says *lxact*, but it held a stale copy of `nivela_prioridad_rxact_after_delete` (with `id = OLD.id`), which conflicted with `disparadores/nivela_prioridad_rxact_after_delete.sql` (`plantilla = OLD.plantilla`, identical to the dump). The dump has both triggers. | Restores 2010 behavior: re-levels place priorities after a delete. Removes a load-order-dependent conflict. | Applied |
| D-04 | `actividad/dame_lista_id_otros_recursos_usados.sql:8` | `WHERE actividad = idactividad = idgrupo` → `WHERE actividad = idactividad` | `idgrupo` does not exist. The function name and parameter state the intent. The dump body is the same broken text: it compiled on 8.4 and would fail at run time (hypothesis, no 8.4 available). It is called by `control/custom/get_minibrowser_{horarios,publico}.php:58`. | Changes "always errors" into the evident intended result. Not generator code. Listed in roadmap DB-01. | **Approved**: keep the corrected predicate |
| D-05 | `tiempo/restriccion/nominal/restringe_por_uso_de_otro_recurso.sql` | **Not changed.** Listed in `dev/sql/tools/known-broken.txt` and loaded with `check_function_bodies = off`, exactly as the 2010 `pg_dump` loaded it | The body is byte-equal in source and dump. It has `EXIT` outside a loop, uses an undefined `idplantilla`, calls a non-existent `crea_id_de_replica` with double-quoted identifiers, reads `rplantilla.duracion` (no such column) and `ractividad.periodo` (no such column), and inserts a link row whose id is never used for the restriction. Calling it fails in both databases (verified). It is called by the RULE `ancoragoh.sql:132` on every `INSERT INTO otros_recursos_usados`, which `generador/agrega_actividad.sql:22` performs for templates that use other resources. | A repair would change generator occupancy semantics: other-resource bookings would start blocking. | **Approved as a documented known defect** of the current baseline (option A, §5). Do not disable the occupancy RULE. **Repaired by #24** (option B, `sql-other-resource-occupancy.md`). |
| D-06 | `language/plpgsql.sql` | `DROP/CREATE LANGUAGE 'plpgsql'` → `CREATE EXTENSION IF NOT EXISTS plpgsql` | PL/pgSQL is built in and extension-owned since 9.0. The old statements error on PostgreSQL 16 (verified). | None | Applied |
| D-07 | `matrix/create_string_list.sql:25` | `0THEN` → `0 THEN` | Lexer change in PostgreSQL 15 (verified). Same tokens as the dump. | None | Applied |
| D-08 | `organizacion/grupo.sql:4` | Add the missing comma | Fails on any version. | None. Column **order** stays the source order (`id, descripcion, clasificacion, …`). The dump order is `id, clasificacion, descripcion, …`, which matters only for positional `INSERT … VALUES`. | Applied |
| D-09 | Dump-only helpers: `concat`, `first` (function and aggregate), `greatest` ×2, `if` ×2, `rand`, `actualiza_orden_actividades` | **Not imported** into sources | They are MySQL-style shims (`SELECT $1 \|\| $2`, `COALESCE`, `CASE`, `random()`) plus a superseded trigger function (`trash/actualiza_orden_actividades.sql`; no dump trigger uses it). The only references are generated wrappers in `model/data/PublicFunctions.php` (`Concat`, `First`, `First_v2`, `Greatest`, `Greatest_v2`, `Rand`, …), with no call sites in `control/`, `view/` or `www/` (verified by grep). `concat` and `greatest` collide with modern built-ins and SQL syntax. | None for reachable code. Calling the generated wrappers would fail. | **Approved**: do not import them |
| D-10 | Source-only functions: `valor_restriccion_otro_recurso`, `dame_hora_inicial_periodo`, `dame_cant_rest_lugar`, `dame_cant_profes_asignados_a_plantilla`, `esta_turno_en_rango` | **Kept** as canonical sources | `posible_inicio` (sources **and** dump) calls `valor_restriccion_otro_recurso`. `turnos_se_solapan` (sources and dump) calls `dame_hora_inicial_periodo`. So in the 2010 database those paths would have raised "function does not exist" (hypothesis about 2010 runtime). The other three have no callers. | The fresh install gains working essential-resource checks and the overlap check where 2010 errored. #10 must characterize these paths. | **Approved**: keep them and characterize their behavior (#10) |
| D-11 | `portal/portal.sql` (empty `portal` schema) | Kept in sources | Not in the dump or manifest, and no objects depend on it. | None | Applied. #9 decides whether to install it. |
| D-12 | Debugger objects in the dump (`pldbg_*` functions, 5 composite types, `plpgsql_oid_debug`) | Excluded from the canonical set | They belong to the `pldbgapi` developer extension, not to Áncora. | None | Applied |
| D-13 | UTF-8 BOM at the start of 39 source files | **Not stripped** here | psql ignores a BOM at the start of a file, and all 39 compile when loaded per file (verified). BOMs only break concatenation. | None | Deferred to the #9 bundler |
| D-14 | PL/pgSQL parameter/column name collisions (`id` ambiguity) | **No code change** | `valor_restriccion_profesor('x','p1',1,1)` → `column reference "id" is ambiguous` on PostgreSQL 16, and returns `0` with `plpgsql.variable_conflict = use_variable` (verified on the source-built database). | — | **Superseded by #22**: a per-function directive in the 3 affected functions (§6) |
| D-15 | `result.sql`, `tabla.sql` | Not used or edited | Stale bundles (assessment §4.1–4.2). | — | Deferred to #9 |

## 5. Decision D-05: `restringe_por_uso_de_otro_recurso`

**Maintainer decision (2026-10-25): option A is approved for the current
baseline.** The function stays as a documented known defect
(`tools/known-broken.txt`), and the occupancy RULE (`ancoragoh.sql:131-134`)
stays installed, so option C is rejected. The intended next step is a
repair along option B, together with the other-resource insertion defect
(`generador/agrega_actividad.sql:22` omits the `NOT NULL` column `creador`;
characterization cases 081/082 in #10). It will be done under a separate
behavioral-fix issue, with regression tests.

| Option | Change | Pros | Cons |
| --- | --- | --- | --- |
| **A. Keep the historical state (current)** | None. The installer loads it with body checks off, listed in `known-broken.txt`. | Byte-equal to 2010, and no behavior change before characterization. | Any generation that books an *other resource* fails at the RULE (as in 2010), and other-resource double-booking (assessment 6.19) stays unprotected. |
| B. Repair by analogy with `restringe_segun_actividad` | Use `ractividad.plantilla`. Take the duration from `clasificacion_de_actividad`. Get the period via `dame_periodo_plantilla_de_actividad`. Call `crear_id_de_replica('ancoragoh', …)` with string literals and insert `id = pid`. Remove the stray `EXIT`. | Other-resource occupancy is recorded like teacher/place/brigade occupancy, which is the evident design intent. | Changes generator outcomes. Needs #10 characterization first and a separately approved behavior fix. |
| C. Drop the RULE until B is approved | Remove `ancoragoh.sql:131-134` from the install. | Generation with other resources no longer aborts. | It is also a behavior change: it silently allows double-booking. |

The original recommendation was "A now, B after #10 records the current behavior", and that is what was approved.

## 6. Variable/column name collisions (`id` ambiguity)

**Since #22 this section is historical.** The three affected functions carry a per-function `#variable_conflict use_variable` directive, and no session, role, database or server setting is needed or used. The analysis is in `sql-variable-conflict.md`. The text below describes the interim test-only setting that #22 replaced.

Pre-9.0 PL/pgSQL substituted variables before resolving column names. Many functions (`valor_restriccion_generica` and every `valor_restriccion_*`, among others) have a parameter named like a column (`id`), so on PostgreSQL ≥ 9.0 they fail with `column reference "id" is ambiguous`.

Until a separate, behavior-preserving renaming task with tests exists:

- **Tests and characterization only:** set it **per session**, either with `SET plpgsql.variable_conflict = use_variable;` or with the libpq environment `PGOPTIONS='-c plpgsql.variable_conflict=use_variable'`. This reproduces the pre-9.0 resolution without editing code.
- **Do not** use `ALTER DATABASE … SET`, `ALTER ROLE … SET`, `ALTER SYSTEM` or `postgresql.conf` for this. It is not a fix and must not leak into installations.
- The fresh-install compile check above does not need the setting, because PL/pgSQL resolves names only at run time.

## 7. What this does not establish

- **Behavior.** Compiling is not running. Run-time defects documented in the assessment (§6: 0-based arrays, `k` loop, Prioriza ordering, HRT `iif`, conditional restrictions and others) are untouched and belong to #10.
- **Install order.** `deploy.priority`, the bundler and an ordered, strict, `ON_ERROR_STOP` installer are #9.
- **PostgreSQL 8.4 behavior.** Statements about how 2010 behaved at run time are hypotheses unless the dump itself proves them.
- **Data seed.** `datos/datos_por_defecto.sql` was not loaded here.
