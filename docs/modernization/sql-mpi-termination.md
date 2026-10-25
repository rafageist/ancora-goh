# MPI termination guarantees (issue #23, GEN-01)

Issue: #23 (phase #6). It is stacked on #22 (PR #28) → #26 (PR #27) → #10 (PR #19) → #9 (PR #18) → #8 (PR #16). The baseline is the characterization suite under default settings (#22).

**Goal:** remove the non-termination paths confirmed by characterization, without changing any outcome for input that terminated before. These are termination fixes only. Scheduling, eligibility, priority and placement semantics are untouched. In particular the carry-over of `k` between the resource checks (assessment 6.13, case 024) and the Prioriza ordering (case 043) remain expected failures.

## Changes

| Path | Before | After | Why valid input is unaffected |
| --- | --- | --- | --- |
| `posible_inicio`: essential other resources (assessment 6.3, case 023) | `k` stayed NULL when the template requires neither a teacher nor a place, so `EXIT WHEN k = duracion` never held | `k INTEGER := 0` at declaration | Whenever a teacher or place is required, `k` is assigned 0 in those checks before the resource loop, exactly as before. The initial value only matters on the path that never returned. |
| `generar`: day loop (6.5, case 064) | the loop exits only at the end, on `kk = CicloFin`, so an empty range (`CicloIni > CicloFin`) or a NULL bound looped forever | `EXIT WHEN CicloFin IS NULL OR kk IS NULL OR kk > CicloFin` at the top of the loop | For `CicloIni <= CicloFin` the new exit never fires: the first iteration has `kk <= CicloFin`, and the old exit leaves at `kk = CicloFin` before `kk` can exceed it. |
| `generar`: template without `orden` (6.6, case 065) | `generar` never returned | Before anything is placed, `generar` raises **23502 `not_null_violation`**: `activity template <id> has no orden`, with a hint | The documented invariant is that every template has an `orden`: the PHP UI always sets it, and templates are looked up by subject, period and `orden` (`dame_id_plantilla`). Data that satisfies it takes the same path as before. |
| `prioriza.nivelar`: non-integer values (case 041) | the running optimum was an `INTEGER`, so `1.5` never equalled it and the loop never ended | the optimum is `NUMERIC` | Integer input compares exactly as before. Case 040 and the new `HISTORICAL` checks in 044 assert the same levels. |
| `prioriza.nivelar`: empty input, NULL input or a NULL element (found while writing case 044; both looped forever) | no return | empty or NULL array → `'{}'` (no levels); NULL element → **22004 `null_value_not_allowed`** | `generar()` passes non-empty arrays of counts, and nothing that terminated before reaches these branches. |

An explicit error was chosen over silently skipping the template, so the caller (the PHP generator screen) sees why nothing was generated. Comments are in English. No SQL identifiers were renamed.

## Tests

- **Cases 023, 041, 064 and 065** now pass. 064 asserts the observed result: T2 is placed for both groups and T1 stays unplaced, because its day search range is empty (see the findings). 065 asserts the error code and message, that nothing was placed, and that the same data generates 4/4 once `orden` is set.
- **New case 044** covers the invalid Prioriza inputs and the `HISTORICAL` integer levels.
- **Case 066** ("a timeout rolls everything back") used the empty day range to make `generar()` run into the timeout. That range now terminates, so the case uses a test-only trigger that slows every placement down. A sequence, which a rollback does not reset, proves that placement had started. The behaviour asserted is the same.
- **Every case keeps a statement timeout** of 3 s or 8 s, plus the runner's wall-clock guard of 3× that.

**Verification** (PostgreSQL 16.15, disposable clusters, default settings):

| Check | Result |
| --- | --- |
| Suite | **23 PASS, 12 XFAIL, 0 FAIL / XPASS / XWRONG**, two identical runs |
| Compared with #22 | only 023, 041, 064 and 065 changed (XFAIL → PASS), plus the new 044. Every other result and every METRIC line is identical, **including 060's exact placements** |
| Negative control: the new and updated cases on #22's SQL | 023, 041, 044, 064 and 065 FAIL with 57014 (timeout). 066 passes on both |
| `check-sources`, strict install, smoke-check, installer test | 268 loaded, 0 failed, 1 KNOWN (D-05); 0 errors; OK |
| `plpgsql_check` (as in #22) | no new findings |

## Findings, not changed (placement behaviour)

- **Case 064.** After the empty range ends, T1 stays unplaced, although day 2 has no restriction for its teacher. The range comes from `rango()` and `maxDia` (`respetar_orden`, `compactar`). Whether day 2 should be offered is a placement question, outside a termination fix.
- **The same loop shape elsewhere.** Many other loops use `EXIT WHEN i = n` after the body, with `n` a count that can be 0 or NULL (for example the activity matrix in `generar`, `dame_grupos_de_brigadas` and `and_mpi`). This change does not touch them. With the inputs characterized so far they are reached only with positive counts. Inputs that make such a count 0 (for example a subject without templates in the period) have not been characterized yet.
