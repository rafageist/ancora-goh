# When a placement happens: the temporal semantics (#133, phase 1)

Status: phase 1 (characterization, §1–§5) accepted; phase 2 (the warnings) implemented, §6.

Evidence:
- the legacy sources (cited by file and line);
- characterization case `dev/sql/tests/cases/151_real_date_semantics.sql`;
- Kernel `ancora_academic/tests/src/Kernel/TemporalSemanticsTest.php`, with the same numbers as case 151.

The legacy real-date rows (`restriccion_real_de_*`) are used **as evidence of the formula only, never as a source**:
- their real-date branch is dead (Q-01);
- their hours are built from the number of days (Q-20);
- a missing date becomes 1999-12-31 (case 151, part 4).

## 1. The model

| Item | Legacy | Drupal (canonical) | Evidence |
|---|---|---|---|
| Plan first day | `planificacion.fecha_inicial`, a day number: days since 1999-12-31 (`tiempo.date_to_number`; the PHP form gives one day less before 2000, Q-35) | `ancora_plan.starts_on` (date only); exported as `LegacyRows::legacyDayNumber()`, or **0 when empty** | case 151 part 1 (every day of 2000–2031); Kernel `testDayNumbersAndPlanLength`, `testExportOfTheCalendar` |
| Plan length | `cant_dias` | `duration_days`, derived from `starts_on`/`ends_on` (last − first + 1). Without dates it is only the default 30 | Kernel `testDayNumbersAndPlanLength` |
| Periodicity | `periodicidad (periodo, dia)`: each row **starts one repetition** of the period on plan day `dia`. The period then covers `cant_dias` consecutive plan days | `ancora_period.plan_days` (exported one row per value) | `actualiza_restriccion_real.sql:86` (`fecha = dia_inicial + periodicidad.dia − 1 + pdia − 1`); `form_periodo_periodicidad.tpl:31-49` (the UI marks `cant_dias` days from each start); case 151 part 3 |
| Day numbering | period day `d` of the repetition starting at plan day `p` is plan day `p + d − 1`, and the date is first day + `p − 1` + `d − 1` | same | case 151 part 3 (period day 2 of the repetitions on days 1, 8, 27 → 2026-09-08, 2026-09-15, 2026-10-04) |
| Weekdays | **none**: legacy has no weekday logic. A weekday follows from the first day's weekday plus the offset (plan day 1 is a Monday in the fixture, so period day 2 of the weekly repetitions is a Tuesday) | same | case 151 (`isodow`) |
| Gaps | the days between repetitions belong to no repetition of that period; repetitions are listed explicitly | same | case 151 |
| Past the plan's end | **not clipped**: a repetition that starts near the end runs past `cant_dias` (period day 5 of the repetition from day 27 of a 28-day plan is dated day 31) | same | case 151 part 3; Kernel `testOccurrencesAndOverlaps` |
| Slot start | `dame_hora_inicial_turno` = `hora_inicial + (turno − 1) · duracion_del_turno + Σ receso.duracion (despues_del_turno < turno)` | `PeriodInputs::slotStart()`, the same formula over `start_hour`, `slot_duration` and `ancora_break` (hours) | case 151 part 2 (480, 600, 690 minutes); Kernel `testSlotTimes` (8.00, 10.00, 11.50 h) |
| Slot end | `hora_final = hora_inicial + duracion_del_turno` (each slot its own row) | slot start + `slot_duration` | `actualiza_restriccion_real.sql:73` |
| Duration | `clasificacion_de_actividad.duracion` slots; one row per slot (`restringe_segun_actividad`) | `ancora_activity_type.duration_slots` | case 151 part 3: a 2-slot activity is **two** intervals, and a break between its slots is not booked |
| Units | `hora_inicial` and `duracion_del_turno` in minutes as the UI stores them, **except** that the client posts the number of days as `hora_inicial` (Q-20). Real hours are therefore wrong | `start_hour`, `slot_duration` and break length are decimal hours with 2 places, the true values the user chose | case 151 part 5; Kernel `testExportOfTheCalendar` |
| Time zone | none: day numbers and hour numbers | `starts_on` is a date-only value, and hours are wall-clock decimals. **Floating local time of the site** (one organization per site): no conversion, and daylight saving is irrelevant when comparing within a site | field definitions |
| Periods of different calendars | each period has its own start hour, slot length, breaks and repetitions; comparison is on (date, wall-clock interval) | same | Kernel `testOccurrencesAndOverlaps` (1.5 h slots with a break vs 1 h slots from 08:30) |

## 2. A placement as half-open intervals

For a placement on period day `d`, first slot `s`, with an activity type of `n` slots, in a period with repetitions `P` of a plan with first day `F`:

```text
for each p in P, for each t in s .. s + n − 1:
  date  = F + (p − 1) + (d − 1)
  start = slotStart(period, t)                 (breaks included)
  end   = start + slot_duration
  occurrence (date, [start, end))
```

- **Arithmetic** is done in exact integers of hundredths of an hour. The decimals have 2 places, so 0.33-hour slots touch exactly (`testSlotTimes`).
- **Overlap:** two occurrences overlap if and only if they have the same date and `a.start < b.end` and `b.start < a.end`. **Adjacency** (`a.end = b.start`) is not an overlap. Partial overlaps of different slot lengths are reported as their common part (`2026-09-15 10:00–10:30` in `testOccurrencesAndOverlaps`).
- **Duplicates:** the same pair of placements can overlap on several dates and slots. A warning lists the pair once, with its dates.
- **Identity:**
  - A teacher or room is one Drupal entity, owned by a user and shared by all of that owner's plans (D1, owner catalogues). The same entity id in two placements is the same person or room.
  - Different owners never share an entity. Sites never share a database.
  - Only the owner's own plans are compared.

## 3. Unknown, never "no conflict"

The calendar of a placement is **unknown**, and its conflicts are not evaluated, when:
1. its plan has no `starts_on`. The demo's synthetic fixture has none. Legacy would date it 1999-12-31 (case 151), and Drupal must not;
2. its period has no `plan_days`. Legacy then writes no real row (case 151);
3. its period's `slot_duration` is 0. The legacy form allows 0:00; the intervals would be empty;
4. its activity type has no duration.

A plan whose calendar is unknown gets the note "Dates unknown / cross-plan conflicts not evaluated", never "no conflicts". A plan that has dates but no `ends_on` is still dated: the repetitions are explicit, and `ends_on` only matters for the open question below.

## 4. Open questions for the maintainer

1. **Occurrences past the plan's last day.** Legacy dates them; nothing clips. Should they count?
   - Proposal: count them, but mark them "after the plan's last day".
2. **Overlapping repetitions.** Two periodicity days closer than the period's length make a date belong to two repetitions. The formula dates both; that is consistent, but probably a data-entry slip.
   - Proposal: show both.
3. **Which placements:**
   - Proposal: the ones the timetable shows (final or draft, never those of a running job), as the read model already does.

## 5. Verdict

The model is **reliable for plans with a first day and periods with repetitions**:
- every element is canonical Drupal data with an exact formula;
- the formula is confirmed by the legacy real-date formula, the legacy period editor and `dame_hora_inicial_turno`;
- unknowns are explicit.

The phase 2 detector can implement §2 and §3 as they stand. The open questions in §4 need a maintainer decision, or the proposed defaults.

## 6. Implemented: cross-plan conflict warnings (#133, phase 2)

The model of §1–§3 is implemented with the maintainer's decisions on §4. Occurrences after the plan's last day count and are marked. Overlapping repetitions give both occurrences. The placements compared are those the timetables show.

**Detector:** `CrossPlanConflictDetector` (`ancora_scheduling.cross_plan_conflicts`). It is read-only and uses Drupal data only: no worker, no engine SQL, no legacy change.
- `forPlan(plan, account, ?period)` returns:
  - `evaluated`: FALSE when no period of the plan has a known calendar;
  - `unknown`: the owner's undated periods, named only where visible;
  - `conflicts`: one entry per pair of placements and resource, with the resource, both placements, and the common parts by date with `after_end` flags.
- `forCandidates(placement, cells, account)` assesses each prospective destination of a move against the owner's other periods. Nothing is committed.
- `cacheability()` returns the list tags of placements, plans, periods, breaks, activity types, templates, teachers and rooms, plus the run tag.
- **Scope:** only the plans of one owner (owner catalogues, D1), and only for a user who may view that owner's plan. Another plan is named only when the user may view it. Other owners' teachers and rooms are other entities. The viewer shows nothing of it.
- **Source:** the placements are read through `TimetableReader`, so both engine modes are covered: the final or draft placements of each scope, never those of a running job.

**Where it shows**
- The plan timetable and the explorer (not the viewer) have a "Cross-plan conflicts" section.
  - An undated plan reads "Dates unknown / cross-plan conflicts not evaluated", never "no conflicts".
  - A dated plan without overlap reads "No overlap found with the dated periods of your plans", and lists the undated periods that were not evaluated.
  - At most 50 overlaps are listed, followed by the count of the rest.
- The #130 move grid and preview:
  - a valid cell that would overlap another of the owner's dated periods says so ("Cross-plan warning: teacher … busy in plan …, period … on …");
  - **the cell stays selectable and the move stays allowed**;
  - the preview lists the overlaps;
  - an undated placement shows "Dates unknown / cross-plan conflicts not evaluated (reason)".

**Not detected**
- Other owners' resources (separate entities), other sites, and cohorts or other resources (only teachers and rooms are compared).
- Undated plans or periods: the plan has no first day, the period has no days of the plan, or the period has no slot length.
- Placements of a running job.
- Any blocking: no move or run is refused.

**Measured:** 600 placements of two overlapping plans, each with 2 repetitions and sharing 10 teachers and 10 rooms, give 6000 overlaps in 1.4 s (Kernel `testPerformanceBound`, bound 15 s). It is computed once per request and cached by the tags above.
