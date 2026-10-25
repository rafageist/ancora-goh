# PL/pgSQL variable/column name collisions (issue #22, DB-05)

Issue: #22 (phase #6). It is stacked on #26 (PR #27) → #10 (PR #19) → #9 (PR #18) → #8 (PR #16).

## Problem

Up to PostgreSQL 8.4, which the 2010 system ran on, PL/pgSQL always substituted a variable where a name in an SQL statement matched both a variable and a column. From 9.0 on, the default `plpgsql.variable_conflict = error` raises `42702 column reference "…" is ambiguous` instead. Until now the characterization suite set `plpgsql.variable_conflict = use_variable` for each session to reproduce the 2010 behaviour. The application never set it, so restriction checks and `generar()` failed when called from PHP.

**Approved approach (maintainer, 2026-10-25):** a per-function `#variable_conflict use_variable` directive in each affected function. No global, database, role, session or PHP connection setting. No identifier renaming (that belongs to #21). The characterized behaviour is preserved.

## Which functions are affected

Two independent methods, both on a fresh source install under PostgreSQL 16.15 with default settings:

1. **Static analysis with `plpgsql_check` 2.7.2.** Every one of the 136 PL/pgSQL functions was analysed; trigger functions were analysed with their table. Only three functions have ambiguous references (`42702`):

   | Function | Names | Statements |
   | --- | --- | --- |
   | `valor_restriccion_generica` | `id` (parameter: the restricted object) | 9 (the 8 nominal lookups and the period lookup) |
   | `dame_costo_trasladar_recurso` | `id`, `costo` (variables) | 3 |
   | `dame_clasif_de_asignaturas` | `periodo`, `asignatura` (parameters) | 1, the loop query |

   The extension is not a project dependency. It was used from a private, relocated copy of the PostgreSQL 16 binaries, loaded only into the disposable cluster.
2. **Name-overlap scan as a cross-check.** It lists every function whose parameter or declared variable has the name of some column: 17 functions. For the 14 not listed above, `plpgsql_check` analysed all statements and found no ambiguous reference (their SQL qualifies the columns). Two exceptions, `dame_lugares_utilizados_en_el_dia` and `dame_gasto_lugares_en_el_dia`, could not be fully analysed because they are broken for other reasons (a non-existent table `actividades`, a `SELECT` without `INTO`). Their `periodo` is not a column of the queried table `actividad`, so no directive is needed.
3. **Run-time evidence: the suite without the session setting, before the change.** 17 of 33 cases failed with `42702`. `id` came from `valor_restriccion_generica`, through every `valor_restriccion_*`, `posible_inicio`, `and_mpi` and the HRT and conditional paths. `periodo` came from `dame_clasif_de_asignaturas`, which `generar()` calls at `generar.sql:230` before anything is placed, so **every generation run failed**.

After the change, `plpgsql_check` reports no `42702` anywhere and no other finding in the three functions.

## Change

Each of the three files gets this at the start of the function body:

```sql
#variable_conflict use_variable
-- #22: a name that is both a variable and a column resolves to the variable,
-- as PostgreSQL 8.4 (2010) always did; PostgreSQL >= 9.0 raises 42702 instead.
-- Here: …
```

The directive changes only how the ambiguous names are resolved, and it resolves them exactly as the session setting did during characterization. Nothing else in the bodies changed.

The test runner no longer sets the option. It refuses a `PGOPTIONS` that overrides it and any case that `SET`s it.

## Verification (PostgreSQL 16.15, disposable clusters only)

| Check | Result |
| --- | --- |
| Characterization, default settings | **18 PASS, 16 XFAIL, 0 FAIL / XPASS / XWRONG**, two identical runs |
| Compared with the previous baseline (session setting, before #22) | the only change in result is 010, XFAIL (42702) → PASS. Every other case has the same result, and every METRIC line (demands, placed, unplaced, collisions, overflow), including 060's exact cells, is identical |
| Before the change, default settings | 8 PASS, 8 XFAIL, 8 FAIL, 9 XWRONG (all failures `42702`). This is the negative control |
| New case 013 | the server default (`error`) is in effect; exactly these three functions carry the directive; all 8 object types of the generic evaluator evaluate; `dame_clasif_de_asignaturas` returns the subject's classification; `dame_costo_trasladar_recurso` evaluates |
| `check-sources`, strict install, smoke-check, installer and guard tests, bundler self-test | unchanged: 268 loaded, 0 failed, 1 KNOWN (D-05); 0 errors; OK |

`generar()` runs under default settings in cases 060–066: 060 places 4/4 in the historical cells, and 061, 062, 063 and 066 pass.

## Findings, not changed here (behaviour)

The directive reproduces the 2010 resolution exactly, including two places where the variable is clearly *not* what the author meant. Fixing them changes behaviour and needs a separate decision.

1. **`valor_restriccion_generica`, period lookup for real-date restrictions** (`SELECT * INTO rperiodo FROM periodo WHERE id = idperiodo`). `id` is the restricted object's id, so the lookup finds a period only if the object id equals the period id. The real-date part of restriction evaluation (assessment 6.10 and 6.11, not characterized yet) therefore runs with an empty period record. The evident intent is `periodo.id = idperiodo`.
2. **`dame_costo_trasladar_recurso`** (used by `generar()` for other-resource transfer costs, `generar.sql:1358`). The period lookup compares the still-NULL variable `id`, so the cost is always 0. In `SELECT costo INTO r FROM costo_de_traslado …`, `costo` is the variable, not the column. Case 013 records the current result as `HISTORICAL`.

Both behaved like this in 2010 as well. The 8.4 server resolved them the same way.
