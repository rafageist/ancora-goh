# Other-resource occupancy booking (issue #24, GEN-06)

Issue: #24 (phase #6). It is stacked on #23 (PR #29) → #22 (PR #28) → #26 (PR #27) → #10 → #9 → #8. This is the approved repair of decision D-05 (option B), together with the other-resource insertion defect. The occupancy RULE is kept.

## Before

Recording that an activity uses an *other resource* (projector, lab equipment, …) always failed, in two independent places:

1. `agrega_actividad()` inserted into `otros_recursos_usados` without the `NOT NULL` column `creador`, giving 23502 (case 082).
2. With `creador`, the RULE `actualiza_restricciones_por_uso_de_otro_recurso` (`ancoragoh.sql:131-134`) called `restringe_por_uso_de_otro_recurso()`. Its 2010 body never compiled (42601, case 081):
   - `EXIT` outside a loop;
   - an undefined `idplantilla`;
   - a non-existent `crea_id_de_replica` called with double-quoted identifiers;
   - the non-existent columns `ractividad.periodo` and `rplantilla.duracion`;
   - a link row whose id was never assigned.

So other-resource double-booking (assessment 6.19) was not prevented, and it was the last KNOWN source in `tools/known-broken.txt`.

## Change

| File | Change |
| --- | --- |
| `generador/agrega_actividad.sql` | Inserts `creador = pcreador`, the same user that creates the activity in the line above. `generar()` passes the subject's creator, as it does for the activity. |
| `tiempo/restriccion/nominal/restringe_por_uso_de_otro_recurso.sql` | Rewritten along `restringe_segun_actividad`, which records brigades, teacher and place. The period comes from `dame_periodo_plantilla_de_actividad(plantilla)` and the duration from the template's `clasificacion_de_actividad`. For each slot `turno … turno + duracion − 1` it inserts one per-activity nominal restriction (`valor 1, por_actividad TRUE, actividad`) with an id from `crear_id_de_replica`, linked in `restriccion_por_actividad_de_otro_recurso`. Same signature and the same RULE call. The header comment is in English. |
| `tools/known-broken.txt` | D-05 removed. The list is empty, so every source now compiles with `check_function_bodies = on`. |

**How conflicts are prevented.** `restriccion_de_otro_recurso` has a unique key on `(otro_recurso, periodo, dia, turno)`, the same as teachers and places. Booking a resource at a slot where it is already booked, or manually restricted, therefore fails with **23505 `unique_violation`**. Because the RULE runs inside the inserting statement, nothing of a rejected booking is kept. A call to `agrega_actividad()` that books a busy resource also loses its activity row.

**Removal.** Deleting the activity removes the usage rows, the restrictions and the links (`ON DELETE CASCADE`). The client never deletes `otros_recursos_usados` rows directly: `del_OtrosRecursosUsados` is a generated handler that no client script posts (`permission-matrix-proposal.md` §1.2). No DELETE RULE was added.

## Tests (PostgreSQL 16.15, disposable clusters, default settings)

- **080:** a usage without `creador` is still rejected (23502). With it, the RULE records the occupancy and the link. Until now this case asserted the 42601 failure as `HISTORICAL`.
- **081:** the occupancy is visible to `valor_restriccion_otro_recurso`. It flips from XFAIL to PASS.
- **082:** `agrega_actividad()` with a resource records the usage owned by the activity's creator, occupied exactly at the placed slot. It flips from XFAIL to PASS.
- **New case 083:**
  - a 2-slot activity books both slots, and only those;
  - a competing, overlapping booking through `agrega_actividad` is rejected with 23505 and keeps nothing, not even its activity;
  - an adjacent booking is accepted;
  - a repeated booking is rejected;
  - a booking over a manual restriction is rejected, and the restriction is untouched;
  - a rolled-back booking leaves nothing;
  - deleting the activity frees the resource and keeps the other booking.
- The essential and non-essential groups of the fixture show that booking does not depend on the flag. The flag matters only to the eligibility check in `posible_inicio`.

| Check | Result |
| --- | --- |
| Suite | **26 PASS, 10 XFAIL, 0 FAIL / XPASS / XWRONG**, two identical runs |
| Compared with #23 | only 081 and 082 changed (XFAIL → PASS), plus the new 083. All other results and **every METRIC line, including 060's exact placements**, are identical. The historical placement baseline does not change, because `generar()` does not book other resources today (finding below) |
| Negative control: cases 080–083 on #23's SQL | all four FAIL (42601, 23502) |
| `check-sources` | 268 loaded, 0 failed, **0 KNOWN** |
| Strict install, smoke-check, installer test | OK |
| `plpgsql_check` | the 42601 compile error is gone. The function now gets the same report, "there is not single query", as `restringe_segun_actividad` and `agrega_actividad`. That message is how `plpgsql_check` reports INSERTs into tables with RULEs (one statement rewritten into several); the cases above execute these statements |

## Finding: `generar()` does not select other resources (decision needed)

The selection loop in `generar.sql` (about lines 1371-1394) never passes a resource to `agrega_actividad()`:

- it starts at `gr := 0` on a 1-based array, so the first iteration reads `gruporecursos[0]` = NULL;
- it exits at the top on `gr = cantGrupoRecursos`, so the last group is never read (with one group, none is);
- it reads the group record with the activity index `i` instead of `gr`;
- it never checks whether the chosen resource is free at the chosen slot.

The transfer cost it orders by is always 0 (#22 finding 2).

Fixing the indexing alone would start booking resources without an availability check. A busy resource would then abort the whole generation with 23505, which is worse than today. A correct fix needs a behaviour decision:

| Option | What happens when a group cannot get enough free resources at the chosen slot |
| --- | --- |
| A. Essential groups are already enforced by `posible_inicio`; for non-essential groups book only free resources and place the activity with fewer | the activity is placed and the shortfall is reported (needs the unplaced/shortfall reporting of GEN-07) |
| B. Treat every group as required at booking time | the slot is rejected and the search continues |
| C. Keep today's behaviour (no other-resource booking by the generator) until GEN-02/GEN-07 | nothing changes; only manual and direct bookings are recorded |

This PR implements none of them. It also leaves the carry-over of `k` in the essential-resource check unchanged (6.13, case 024). That defect means `posible_inicio` does not yet reliably reject a slot whose essential resource is busy.
