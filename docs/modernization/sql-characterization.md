# Characterization suite: MPI, Prioriza, constraints and known defects (CHAR-01/02)

Issue: #10 (phase #6). This builds on #9 (PR #18), which builds on #8 (PR #16), and is stacked on those branches.

The suite records what the original database logic **does today**. It covers the documented defects as well as the correct behavior, so that future fixes are visible, reviewed changes rather than silent ones. **No generator or SQL logic was changed.** The suite only adds tests, fixtures, a runner, CI and the following tooling exclusions:

- `tests/` is excluded in `compilasql.php`;
- `tests/` is excluded in `tools/check-sources.sh`.

## Running it

```sh
dev/sql/tools/with-ephemeral-pg.sh dev/sql/tests/run.sh          # whole suite, throwaway server
dev/sql/tools/with-ephemeral-pg.sh dev/sql/tests/run.sh 02 060   # cases whose name contains "02" or "060"
```

The runner works in four steps:

1. Installs the schema from sources once, using `dev/sql/install.sh`, into a template database.
2. Adds `tests/fixtures/helpers.sql` to the template.
3. Runs every `tests/cases/*.sql` in a fresh copy of that template (`CREATE DATABASE … TEMPLATE`), so each case is isolated and the suite is idempotent.
4. Applies limits to each case: `statement_timeout` from the case header (default 20 s), plus a wall-clock guard of 3× that value. **No case can hang CI.**

Results:

| Result | Meaning | Exit status |
| --- | --- | --- |
| PASS | `expect: pass` and the case succeeded | ok |
| XFAIL | `expect: xfail` and the case failed with the documented SQLSTATE | ok |
| FAIL | `expect: pass` and the case failed (regression) | **non-zero** |
| XPASS | `expect: xfail` but the case now succeeds (behavior changed) | **non-zero** |
| XWRONG | `expect: xfail` but it failed for a different reason | **non-zero** |

Generator cases print a metrics line, for example `METRIC demands=4 placed=4 unplaced=0 collisions=0 overflow=0`:

- **demands**: template × group pairs for the specialty, level and period.
- **collisions**: overlapping placements that share a teacher, a place or a brigade.
- **overflow**: placements that run past the last slot of the day.

The helpers live in `fixtures/helpers.sql` (`tst.demand`, `tst.placed`, `tst.collision`, `tst.metrics`, `tst.check`).

CI runs the suite in `.github/workflows/db-characterization.yml` on PostgreSQL 16, inside a socket-only cluster that the job creates.

### Default settings only

Since #22, every case runs with the server's default settings. The runner no longer sets `plpgsql.variable_conflict`: the three functions that need it carry their own `#variable_conflict use_variable` directive (`sql-variable-conflict.md`). The runner refuses two things: a `PGOPTIONS` that overrides the option, and a case that `SET`s it. Case 010 (formerly an expected failure with SQLSTATE 42702) now passes. Case 013 checks the default setting, the exact set of functions with the directive, and the affected code paths.

### Data

Only synthetic fixtures are used (`tests/fixtures/`), with invented names (Teacher A, Room 1, Cohort 1, …) and `example.org` addresses. Nothing comes from the 2010 dump or the VB6 `.anc` files (`security-privacy.md`).

## Verified results (PostgreSQL 16.15, local)

The suite was run twice in a fresh throwaway cluster. Both runs produced identical output: **16 PASS, 17 XFAIL, 0 FAIL / XPASS / XWRONG**, about 30 s per run.

After #22 (default settings, 34 cases): **18 PASS, 16 XFAIL, 0 FAIL / XPASS / XWRONG**. Case 010 now passes and case 013 was added. Every other case has the same result and the same METRIC lines as before.

After #23 (35 cases): **23 PASS, 12 XFAIL, 0 FAIL / XPASS / XWRONG**. Cases 023, 041, 064 and 065 now pass, case 044 was added and case 066 was reworked (`sql-mpi-termination.md`). Every other case is unchanged.

After #24 (36 cases): **26 PASS, 10 XFAIL, 0 FAIL / XPASS / XWRONG**. Cases 081 and 082 now pass, case 083 was added and case 080 records the repaired booking (`sql-other-resource-occupancy.md`). Every other result and every METRIC line is unchanged.

A negative run confirmed that the runner exits non-zero. It used a temporary copy of the suite with three cases modified to give one FAIL, one XPASS and one XWRONG.

### Coverage against the issue

| Required coverage | Cases |
| --- | --- |
| `generar` | 060 basic (4/4, exact cells), 061 continuation, 062 cancellation, 063 partial / over-constrained, 064 and 065 non-termination, 066 timeout rollback |
| `posible_inicio` | 020 baseline, 021–025 |
| `and_mpi` (and `get_max_colocation`) | 030, 031 |
| `valor_restriccion_*` | 010, 011, 012, 070–072 (HRT), 080/081 (other resources), 100 (conditional) |
| Occupancy RULEs | 012 (activity → brigade, teacher, place; cascade delete), 080/081 (other resources) |
| `prioriza.nivelar` / `nivelar_matrix` | 040, 041 |
| Prioriza ordering inside `generar` | 042, 043. The loop is extracted from the **installed** `generar()` source at run time (`fixtures/prioriza_order.sql`), not copied, and the case fails loudly if its markers move. |
| Cancellation and continuation | 061, 062 (plus 066: a timeout rolls everything back) |

### Known defects encoded as expected failures

Each xfail case asserts the **intended** behavior. Where it matters, a PASS baseline case also asserts today's behavior, marked `HISTORICAL:`.

| Defect (assessment §6) | Source | Baseline (PASS) | Intended (XFAIL, SQLSTATE) |
| --- | --- | --- | --- |
| 6.1 `id` ambiguity without the compatibility setting | `valor_restriccion_generica.sql:26` | 011 | 010: **fixed by #22**, now PASS; see also 013 |
| 6.2 0-based loops over 1-based arrays (only teacher, only place, both teachers) | `posible_inicio.sql:117-183` | 020, 030 | 021, 022 (P0001) |
| 6.3 `k` not initialized: non-termination | `posible_inicio.sql:200-254` | — | 023: **fixed by #23**, now PASS |
| 6.3 / 6.13 `k` carried over: essential resources ignored | same | 020 | 024 (P0001) |
| 6.4 Prioriza ordering detaches totals (VB6 sorts correctly) | `generar.sql:467-494` | 042 | 043 (P0001) |
| 6.5 empty day range: day loop never ends | `generar.sql:1041-1047, 1439` | — | 064: **fixed by #23**, now PASS |
| 6.6 template with `orden IS NULL` hangs | `dame_id_plantilla.sql:11`, `and_mpi` | — | 065: **fixed by #23** (rejected with 23502), now PASS |
| 6.7 / D-01 random ids shorter than requested (historical quirk kept) | `crear_cadena_aleatoria.sql` | 090 | 091 (P0001) |
| 6.8 HRT `iif` overwrite: only other-resource rows accepted | `check_hrt.sql:29-43` | 070 | 071 (23514) |
| 6.9 HRT evaluation: polymorphic `iif` | `dame_tipo_hijo_hrt.sql:8` | — | 072 (42804) |
| 6.17 start overflowing the day accepted | `posible_inicio.sql` | 020 | 025 (P0001) |
| 6.18 conditional restriction evaluator uses undefined names | `valor_restriccion_condicional.sql:13-34` | — | 100 (42703) |
| 6.19 / D-05 other-resource occupancy cannot be recorded | `restringe_por_uso_de_otro_recurso.sql`, `agrega_actividad.sql:22` | 080 | 081, 082: **fixed by #24**, now PASS; see also 083 |
| 6.20 unplaced work only reported as NOTICE | `generar.sql:1465-1468` | 063 | — (nothing to assert yet) |
| 6.22 `set_matrix_in_cube` returns NULL | `matrix/set_matrix_in_cube.sql:7` | 050 | — |
| 6.23 `extract_boolean_column` returns one extra element | `matrix/extract_boolean_column.sql` | 050 | 051 (P0001) |

### New findings while writing the suite (verified)

- **Prioriza levelling never terminated on non-integer values (case 041; fixed by #23).** In `prioriza/nivelar.sql`, `optimo` is an `INTEGER`, so a value like `1.5` never equals the stored optimum. `generar()` passes integer counts today, so this is latent. It matters if Prioriza criteria ever become fractional, which is relevant to the independent Prioriza research line.
- **Other resources cannot be booked by the generator.** `agrega_actividad()` inserts into `otros_recursos_usados` without the `NOT NULL` `creador` column (case 082), so this fails before the D-05 rule function is even reached. Generation with other resources therefore fails in two independent places.
- **The fixture needs `profesor_para_grupo` rows.** Without them `generar()` places nothing (`cantActualProfe = 0`). This is an input requirement, not a defect, but the PHP UI and any future importer must create these rows.

### Maintainer decisions affecting the suite (2026-10-25)

- **D-05 and 082** stay expected failures. They are approved known defects of the current baseline. Their repair, along option B of `sql-reconciliation.md` §5, comes with a separate behavioral-fix issue. Case 081 or 082 will then XPASS and must be flipped to `expect: pass` in that same PR. The occupancy RULE stays installed: no case may disable it.
- **D-10**: the source-only functions are kept. Their paths are exercised by the essential-resource and overlap cases (020, 024, 030).

## When behavior changes

A fix that changes behavior must be approved separately. When it lands:

1. The xfail case turns into **XPASS** and CI fails. Change it to `expect: pass`.
2. If there is a `HISTORICAL:` assertion in a baseline case, it now **FAILs**. Remove or update it in the same PR.

This keeps every behavior change explicit in review. Exact placement cells (060) are a historical baseline. Changes to ordering, MPI or Prioriza are expected to move them.

## Not covered (stated limits)

- Real-date restrictions (6.10, 6.11). These are hypotheses that need the date/slot mapping of `restriccion_real_*` to be characterized first.
- `zpriori` zones, `parche` / `parche1` / `compactar` / `buscalugar` options (6.15, 6.16), workload hygiene (6.21) and the teacher-load heuristic (6.14).
- Performance and scale. The fixtures are tiny on purpose. Generation time on realistic datasets is unknown (historically 7 activities took 212 s).
- Concurrency between two sessions generating at the same time (assessment §7).
- PostgreSQL 17 and later. There is also no PHP or HTTP path.
