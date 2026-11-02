# The planning journey: a reproducible functional demonstration (#105)

Issues: #105 (UI integration), with #70 and #74. Authorization: the functional completion package of 2026-11-01, item 4 ("a reproducible synthetic end-to-end planning journey on disposable sites only"). The functional completeness matrix (FC rows) is in PR #152, `docs/modernization/functional-completeness.md`.

This is a **functional application test, not a benchmark**. It drives one planner through the whole academic planning journey in a real browser, on a disposable Drupal site, with the real engine worker, and checks the result on the server. The dataset is synthetic and small enough for the default 120 s run limit.

## 1. Running it

```sh
dev/sql/tools/with-ephemeral-pg.sh modern/scripts/planning-journey.sh
```

It needs `modern/vendor` (composer install), `jq`, and `dev/browser/node_modules` (npm ci) with a Playwright Chromium. It takes about two minutes. Everything runs on a throwaway PostgreSQL cluster (Unix socket only); nothing leaves it.

| Piece | Role |
|---|---|
| `modern/scripts/planning-journey.sh` | Builds a disposable workspace-mode site (one database: Drupal and the unchanged engine), the account `planner`, the PHP server and the real worker (`drush ancora-engine:work`); detects the open PRs; runs the spec; prints one line per step. |
| `dev/browser/tests/planning-journey.spec.js` | One Playwright test per step. Skipped in every other run (`browser-journey.sh`). |
| `modern/scripts/lib/planning-journey/fixture.php` | The rest of the synthetic dataset (Entity API, validated, as `planner`), and the server-side reads (`inspect`, `ids`). It refuses to run when a record that a UI step should have created is missing. |
| `modern/scripts/lib/workspace-journey/enqueue.php` (existing) | Queues plan K's run, as the request form does. |

Results go to `$ANCORA_JOURNEY_OUT` (default `dev/browser/journey-results`, ignored by git): `journey.json` (per step: outcome, route, page loads, notes, data), `inspect-after-generation.json` and `inspect-after-undo.json` (the server's view), `report.json` (Playwright) and `screenshots/`. Playwright writes into a short temporary directory first: a long output path broke trace files before.

CI: the Browser workflow runs it after the workspace suite, on the PR's code.

## 2. Feature detection

The journey uses three open PRs where their code is present. It detects them from the code, not from the page, so a present feature whose UI is broken fails instead of being skipped.

| PR | Marker | Step that needs it | Without it |
|---|---|---|---|
| #148 (S1b, move and undo in place) | `ancora_scheduling/src/Workspace/WorkspaceVarySubscriber.php` | 8b | "skipped: needs #148"; step 8 (the move page) still runs |
| #153 (slice B, templates in place) | `'breakdown'` in `AcademicEntityForm.php` | 3b | "skipped: needs #153"; step 3 adds the templates through the plain template form |
| #154 (slice A, generate from the context) | `ancora_scheduling/js/job-refresh.js` | 5b | "skipped: needs #154"; step 5 requests both scopes through the plain request form |

**On master alone, three steps are skipped.** The journey does not pass as a whole without those PRs; it passes the steps that master supports.

## 3. The dataset

All names are invented. Owner: `planner`.

| What | Records | Entered by |
|---|---|---|
| Plan J "Journey spring 2027", 2027-02-01 to 2027-02-26 | 1 | UI |
| Period JW "Week 1": 5 days × 6 one-hour slots from 08:00, plan day 1 | 1 | UI |
| Break after slot 3, 0.5 h | 1 | UI |
| Activity types LEC (1 slot), LAB (2 slots) | 2 | LAB: UI; LEC: fixture |
| Specialties CS, MA; levels Y1, Y2 | 4 | fixture |
| Cohorts C1A, C1B (CS, Y1, 25 students); M2A, M2B (MA, Y2, 20) | 4 | fixture |
| Groups of plan J: GCL (CS lecture, C1A + C1B), G1A, G1B (CS labs), GML (MA lecture), G2A, G2B (MA labs) | 6 | GCL: UI; others: fixture |
| Subjects PRG, DSC (CS, Y1); ALG, CAL, STA (MA, Y2) | 5 | fixture |
| Templates: PRG LEC, LEC, LAB; DSC LEC, LEC, LAB; ALG LEC×3, LAB; CAL LEC×3; STA LEC×2, LAB×2 | 17 | PRG: UI; others: fixture |
| Teachers ANA, BEN, CAR, DIA, EVA; two per template, priorities 1 and 2 | 5 | EVA: UI; others: fixture |
| Rooms AUD (60), R101 (60), LABA (30), LABB (30); two per template, priorities 1 and 2 | 4 | LABB: UI; others: fixture |
| Other resource PRJ "Projector cart", needed by PRG #1 and STA's first lab | 1 | PRJ and PRG #1's group: UI; STA's: fixture |
| Time restrictions: DIA day 1 slots 1–2 (UI); period day 5 slot 6; AUD day 3 slot 6 | 4 cells | |
| Daily load limits: BEN 3 (UI); cohort M2A 4 | 2 | |
| Plan K "Journey intensive K", the same dates, its own specialty INT, cohort I1A, subject WSH, one lecture taught by ANA in R101 | — | fixture; queued by drush |

Demands: **8** in scope (JW, CS, Y1) and **14** in scope (JW, MA, Y2), 22 activities; plus 1 in plan K.

Plan K has its own specialty on purpose. A first version reused CS/Y1, and its run failed with 2202E: the subject DSC had no template in K's period. That is the documented quirk "a scope subject without templates in the period fails the run" (`engine-dependency-closure.md`, quirk 2, case 127 F2); the breakdown triggers create such pairs for every period of the owner.

## 4. The steps

"Route" is what the step used; page loads are full document loads counted by the browser in the integration run.

| Step | What it does | Route | Server-side checks |
|---|---|---|---|
| 1 | Create plan J (dates), period JW, a break | Overview › Create a plan (admin form) › plan page › Add period (prefilled) › Breaks | dates, period size, break after slot 3 |
| 2 | Catalogue | plan page "Add …" links to the admin forms for LAB, EVA, LABB, PRJ; fixture for the rest | record counts |
| 3 | Group GCL; PRG's templates (without #153) | plan page › Add student group (admin form); Add activity template (subject and period chosen each time); templates list › Move down | three templates in order |
| 3b (#153) | PRG's three templates in place, then reorder | Subject breakdowns › "Add activity template to Programming (Week 1)" (prefilled, locked) ×3 › "Move down Programming #2 (JW)" | three templates in order |
| 4 | Assignments | fixture: other groups and templates; assignment editor for PRG's three templates (6 teachers with priorities and groups, 6 rooms with priorities); template resources (PRJ); restriction grid (DIA); daily load limits (BEN); fixture: the other subjects | the teachers, priorities, groups and rooms of PRG's templates, the resource group, DIA's two cells and BEN's limit as stored; demands per scope (8 and 14) |
| 5 | Generate, by hand | plan page › Request a timetable › period, specialty, level › run page reloaded by hand. CS/Y1, and MA/Y2 when #154 is absent | — |
| 5b (#154) | Generate MA/Y2 from the context | plan page period row › Generate (plan and period prefilled) › the run page reloads itself › Open in the workspace | — |
| 6 | Outcome | `fixture.php inspect`; plan timetable | stored placements = placed; **no teacher, room, group, cohort or other resource in two places at once**; each run's outcome pinned (below); "Final" / "Draft" labels match the run states; overflow, break crossings, restricted cells and daily loads reported |
| 7 | Perspectives | workspace: Teachers › BEN, Groups › GCL, Rooms › AUD, in place; plan timetable › explorer: cohort C1A, other resource PRJ | each count equals the server's |
| 8 | Move one lecture and undo, on the move page | plan timetable › Move › Find the valid destinations › cell › Move here; Undo the last move › Move it back here | only that placement changed (day and slot); the undo restores every placement; audit: a "move" and an "undo" that reverts it, by `planner` |
| 8b (#148) | The same in place | workspace inspector › Find the valid destinations › cell › Move here; Undo the last move | the same |
| 9 | Context | workspace: teacher › select › Rooms; back, forward, reload; Overview "Resume" | URL and shown state equal after back, forward and reload |

### Pinned run outcomes

The engine is unchanged, and nothing in the dataset is tuned to avoid a legacy defect. The spec pins what the engine does with this dataset; a different outcome fails step 6 on purpose.

| Scope | Outcome | Why |
|---|---|---|
| JW/CS/Y1 | succeeded, 8/8, **overflow 1** | One DSC lab of group G1B is placed at day 1, slot 6: a 2-slot activity that overflows the 6-slot day (6.17). The workspace mode keeps legacy overflow (`placement-promotion.md`, "Collisions and overflow are kept"), so the scope is labelled **"Final timetable"** and the run page shows "Overflow 1". |
| JW/MA/Y2 | **failed**, `[2202E] array subscript out of range`, nothing kept | Server log: `dia_minimo_impacto(...) line 59`, called from `generar() line 987`. A 2-slot lab placed at the last slot leaves an occupancy row at slot 7; `dia_minimo_impacto()` then writes `zp[dia][turno]` for the brigade occupancy rows of the level, out of the period's range. Verified on the same data: restricting slot 6 of days 1–4 makes the run succeed (12/12, overflow 0). This combination of 6.17 with quirk 3 of `engine-dependency-closure.md` is not in the parity inventory yet. |
| KW/INT/Y1 | succeeded, 1/1 | |

## 5. Results

Integration: local branch of `master` (`f299f10`) + #148 (`134aad6`) + #153 (`812515f`) + #154 (`3a54d46`) + this branch, not pushed. Master alone: this branch.

| Step | Integration | Master alone | Page loads (integration / master) |
|---|---|---|---|
| 1 plan, period, break | pass | pass | 10 / 10 |
| 2 catalogues | pass | pass | 14 / 14 (4 records) |
| 3 group and templates | pass | pass | 5 / 16 (master includes the templates) |
| 3b templates in place | pass | skipped: needs #153 | 14 / – |
| 4 assignments | pass | pass | 45 / 46; 31 of them in the assignment editor |
| 5 generate by hand | pass | pass | 6 per scope, 2–3 manual reloads |
| 5b generate from the context | pass | skipped: needs #154 | 6, 2 self-reloads, then the workspace |
| 6 outcome | pass | pass | 3 |
| 7 perspectives | pass | pass | workspace: 0 after the first load; explorer: 3 per lookup |
| 8 move and undo, move page | pass | pass | 13 / 15 (the page reloads itself while the check runs) |
| 8b move and undo in place | pass | skipped: needs #148 | 1 |
| 9 reload and history | pass | pass | 1 (the reload) |

Generation (integration run): CS/Y1 8 demands, 8 placed, overflow 1, 4.5 s in the engine (queued 2 s, 6 s run); MA/Y2 failed after 1.7 s; plan K 1/1 in 0.2 s. Integrity: 0 occupancy collisions (teacher, room, group, cohort, other resource), 1 overflow, 1 activity across the break (a lab over slots 3–4, which legacy allows), no restricted cell occupied, BEN at most 2 activities a day (limit 3). Cross-plan: plan K's lecture did not meet ANA's plan J activities ("No cross-plan overlap").

### Usability per step

| Step | Classification |
|---|---|
| 1 plan, period, break | Usable from the planner pages; the plan and period are **admin forms** reached from the Overview and the plan page, which return to the plan page. |
| 2 catalogues | **Admin forms**, one per record, reached from the plan page's "Add …" links. |
| 3 groups, templates | Group: admin form with cohort checkboxes. Templates: **in place with #153** (prefilled and locked, 0 re-entries); on master the plain form asks for the subject and the period again for each template, and ordering is in a drop button of the templates list. |
| 4 assignments | The assignment editor is the new UI and works by keyboard, but every change is a full form post: 31 page loads for 3 templates. Template resources, the restriction grid and the daily load limits are planning pages linked from the plan page; the restriction grid folds its object chooser once a period is chosen. |
| 5 generate | Master: request form with the scope chosen again, run page reloaded by hand. **With #154**: prefilled, self-refreshing, and it leads to the workspace. |
| 6 outcome | Run page and plan timetable say final or draft correctly. A failed run shows only the engine error (`[2202E] array subscript out of range`); there is no list of unplaced demands (G7). |
| 7 perspectives | Teacher, group and room in the workspace, in place. **Cohort and other resource only in the explorer**, one form submit each (G5). |
| 8 move and undo | Master: the move page, a full page per step. **With #148**: in place, 1 page load for move and undo. |
| 9 context | Usable: back, forward, reload and Resume keep the context. |

### Blockers and findings

1. **Generation of a realistic second scope fails** (MA/Y2, 2202E), from the legacy engine's 6.17 overflow followed by `dia_minimo_impacto()`. The engine is out of scope; this is reported, not fixed. It needs a maintainer decision (record it as a quirk; or an approved engine fix; or a documented planner practice such as restricting the last slot when 2-slot activities exist).
2. **An overflowing placement is published as "Final timetable"** in workspace mode (by design: legacy keeps 6.17 overflow). The run page shows "Overflow 1" next to "Succeeded: final timetable".
3. The F2 quirk makes any owner with several plans prone to failed runs: a new period gets a breakdown for every subject of the owner, and a scope subject without a template in the period fails the run.
4. **No placement uses the projector**, although PRG #1 and STA's first lab each have an essential group that needs it. With one resource group per template, `generar()` takes resources from every group but the last (`SharedResources`, case 142), so the explorer's other-resource view is empty. Legacy behaviour, reported.
5. Without #148, #153 and #154 the journey is completable on master, with the extra page loads and re-entries above.

## 6. Proposal: a more representative private demo (for review, nothing done)

The private demo shows the 8-activity E2E organization. This proposal is for the maintainer's review; it changes no server, data or user, and needs its own authorization.

1. **Dataset.** Use this journey's synthetic dataset (22 activities, two scopes, a second dated plan, restrictions, limits, an other resource) as the demo organization's content, instead of the 8-activity smoke set. It exercises every planner page this journey visits. No historical data.
2. **Loading.** A new disposable organization (the existing tenant provisioning), a demo planner account, and the fixture's phases (`catalogue`, `structure`, `assignments`, `second-plan`) run with drush, plus the UI parts of steps 1–4 entered once by hand or by a small drush step that creates the same records. The worker generates the scopes through the normal request path, so the demo shows real runs, not imported placements.
3. **The MA/Y2 failure.** Decide before loading: show it as it is (a failed run with its error, which is the legacy behaviour), or add a documented planner choice (for example, slot 6 restricted for the MA cohorts), stated on the demo's plan page. Either choice is a presentation decision for the maintainer, not an engine change.
4. **Reset.** Rebuild the demo organization from the script instead of editing it in place, so every demonstration starts from the same state.
5. **Prerequisites.** #148, #153 and #154 merged and deployed, so the demo shows the in-place journey; otherwise the demo shows the master routes of section 5.
6. **Risks.** Demo users can change data (a reset is needed after each session); a larger dataset is still far from a real faculty and must not be presented as a benchmark.
