# Integrated planning workspace: audit, blueprint and frontend decision (#139)

**Status: design only.** Nothing here is authorized for implementation.
- The blueprint was merged in PR #140.
- The maintainer decided amendments A1–A4 on 2026-10-30/31. They are recorded in §4.6, and the text below follows them.
- Each slice in §5 still needs the maintainer's approval first. The refined first slice is §7 and the pilot issue #141, which is **not authorized**.

**Parents and related issues**
- Parents: #74 (Drupal UI), #66 (architecture), and #142 (product vision and GOH scope, D3 "rich web experience").
- Related: #105 (planning journey), #122 (assignments), #123 / #130 (move), #133 (cross-plan warnings), and #136 / #138 (swap; #138 is **not authorized**).
- **Context, not prerequisites of S1:**
  - #143: workload evidence. It supplies the representative fixtures that turn the measured criteria of §5.3 into thresholds.
  - #144: interoperability. The workspace only reads Drupal's read model and calls existing commands, so it adds no contract that #144 would have to absorb.

**The rule this design keeps.** The legacy code is the specification for business rules. This document changes how the planner reaches those rules, not the rules themselves.
- No academic rule, generator behaviour or validation is redefined.
- Every write in this design goes through a command that already exists and is tested.

## Contents

1. [Method and safety of the evidence](#1-method-and-safety-of-the-evidence)
2. [Evidence audit](#2-evidence-audit)
3. [Target experience: IA and interaction blueprint](#3-target-experience-ia-and-interaction-blueprint)
4. [Frontend decision](#4-frontend-decision)
5. [Roadmap: vertical slices, dependencies, risks, acceptance](#5-roadmap)
6. [Open questions for the maintainer](#6-open-questions-for-the-maintainer)
7. [S1 refined: one complete scheduling workflow (#141)](#7-s1-refined-one-complete-scheduling-workflow-141)

---

## 1. Method and safety of the evidence

| Source | How it was read |
|---|---|
| VB6 desktop (`rafageist/ancora-vb6`) | Source read. Forms and the ribbon dispatcher were traced, and each fragment was classified as wired or unwired. |
| Thesis PHP application (this repo: `view/custom/*.tpl`, `view/wins/*`, `www/js/app/*`, `bin/security.conf`) | Source read. Templates, window registries, client calls and server event handlers were traced. |
| **Private Drupal demo** (`timetables.divengine.com/o/demo`, plan 1, period 1, workspace mode) | A real Chromium (Playwright) session, **read-only**. Details below. |
| Interactive states (move grid, preview, undo, warnings) | **Disposable** local sites with the #130 / #133 test fixtures. These captures are labelled *DISPOSABLE*. |

**How the demo session was kept read-only**
- The session logged in with a one-time admin link. On this site the link logs in on the GET itself.
- The browser context **aborted every non-GET request**. The result: 0 attempted and 0 blocked writes, and 0 POSTs, including the login.
- No form was submitted and no action button was pressed. "Find the valid destinations" was not pressed, and nothing was dragged.
- 18 pages returned 200, plus one section capture and one narrow-screen capture. The session ended by logging out.

**Recording**
- Each page's links, controls, words and height were recorded.
- Click distances are breadth-first shortest paths over the **captured link graph**, starting at `/ancora`.
- Gate credentials and login links are not recorded anywhere in this repository.

**Limit of the evidence: the demo is small.** It has one plan, one period, two groups, two teachers, two rooms and 8 placements.
- Density findings are therefore lower bounds.
- A real faculty period (6 days × 8–12 slots, dozens of groups) makes every issue in §2.4 worse, and none of them better.

---

## 2. Evidence audit

### 2.1 VB6 desktop: what was really wired

All references below are in `rafageist/ancora-vb6`, `frmKernelMain.frm` unless stated otherwise.

| Element | Evidence | Status |
|---|---|---|
| One main window (SDI) with a custom ribbon, `ACPRibbon`: 8 tabs and 46 buttons | `creaRibbon` :1196–1294; dispatcher `mainRibbon_ButtonClick` :897–1145 | Wired |
| **Left explorer tree** (`tvwScheduleExplorer`, :230) of plan, period, groups, teachers, rooms and resources | Click shows that object's timetable (:1463–1471). Double-click opens its editor (:1433–1452). Right-click opens a context menu (:692–723). | Wired: **the core interaction** |
| **Right panel: one timetable grid** (`frameCasillero`, :338) with brigade, teacher, room and resource perspectives | `mostrarHorario` :1931ff | Wired |
| Multi-slot activities drawn as spans | :2001, :2500–2511 | Wired |
| Subject colours | Random per subject (:2256–2266) | Wired. Colours are not stable between sessions. |
| Zoom (±20 px) | :854–868 | Wired |
| Restriction toggles by cell click | `goMPIIn` :2346–2410 | Wired |
| Activity popup on cell click (`frmDatosActividad`) | :2350–2362 | Wired |
| Hover tooltip on activities | grid handlers | Wired |
| **Swap by two right-clicks** | :824–833, :1302–1370 | Wired (characterized in #136) |
| Assignment window `frmDatosAsignacionRxAct` | Tree, lists, `>` `<` `^` `v`, *Clonar* (:312–419); no drag handler | Wired |
| Context | The selected tree node key (`keyfrmExplorerSchedule`, `modDataGlobals.bas`:62). `PeriodoActual` is private to the form (:814). | Wired, but **implicit** |
| Forms in the project | 55 `.frm` files: 41 in the `.vbp`, 14 unreferenced, about 28 reachable from the ribbon or tree. `frmExplorerAct` is referenced but missing. | Partly unwired |

**What VB6 got right**
- **One screen.** The tree on the left selects; the grid on the right shows. Every selection changes the grid **in place**.
- The planner never re-enters the period or the object.
- Details are popups over the grid, not page changes.

**What it got wrong**
- The context is invisible: there is no breadcrumb of plan or period.
- Colours are random.
- Restriction toggling and swapping are hidden in mouse gestures (right-clicks) with no keyboard path.
- Almost no accessibility.

### 2.2 Thesis PHP application: what was really wired

| Element | Evidence | Status |
|---|---|---|
| `view/custom/ribbon.tpl` (20 ribbon links) | Never included by any template or script (`git grep ribbon` finds only the file itself) | **Dead** |
| Real navigation: a **start-menu "desktop"** with 6 items filtered by permission, plus a taskbar | `view/custom/app.tpl`:142–195 (`start_menu` at :169) | Wired |
| **41 draggable modal windows**: 7 "browsers" (`view/wins/*.php`) and 34 forms | Registered in `app.tpl`:10–72; contents loaded by AJAX `app_event_*` handlers | Wired |
| Context | Per-browser JavaScript flags. It is not shared between windows. | Wired, **fragmented** |
| Known client defect | `refrescaDesgloseDetalle` (`www/js/app/client/browsers/ancoragoh_client_browsers_desgloses.js`) | Bug |
| Server events never called by the client | 272 of 417 `app_event_*` handlers are not posted by any client script | **Unwired** |
| Event permissions | Recovered `bin/security.conf` (deny by default) lists only 2 `event_permissions`, so about 147 events run only for `todos` holders | Restricts the UI to super-users |

**Patterns worth keeping**
- The **inline 1..N priority picker**, already reproduced in #122.
- **Inline delete confirmations** inside the panel, not a new page.
- **Up/down template order.**
- **Sliding detail panels** next to a list.

**What the PHP application got wrong**
- It recreated a Windows desktop in the browser: overlapping modal windows, no URLs, and no back button.
- Context was lost between windows.
- #66 rules this out explicitly ("no jQuery desktop"). That is correct and stays.

### 2.3 The current Drupal demo

The pages are server-rendered by Drupal (`ancora_theme`, Twig), with a horizontal main menu of six sections: Overview, Plans & periods, Academic catalogues, Assignments & templates, Generate and Timetables. Only two pages carry JavaScript:
- the assignment editor (`assignment-editor.js`, 183 lines);
- the move page (`placement-move.js`, 78 lines).

Both scripts are progressive enhancement over buttons (the #128 conventions). There is no Drupal AJAX (`#ajax`, `AjaxResponse`) and no JSON endpoint in the custom modules.

| Plan page | Plan timetable |
|---|---|
| ![Plan page of the demo](img/demo-03-plan.jpg) | ![Timetable of plan 1, period 1](img/demo-14-timetable.jpg) |
| The plan is a dashboard of record counts with "List" and "Add" links into entity admin pages. | One grid per specialty and level. Filters are a separate form. Each placement has a "Move" link to another page. The cross-plan section sits below the grid. |

| Explorer (by teacher) | Viewer (by teacher) |
|---|---|
| ![Explorer by teacher](img/demo-16-explorer-teacher.jpg) | ![Viewer by teacher](img/demo-17-viewer-teacher.jpg) |
| The legacy explorer, reproduced. The period and the object are picked again in a form. | The same data, read-only. It is a separate route, reached by a tab that drops the current selection. |

| Move page (opened only) | Assignment editor (#122) |
|---|---|
| ![Move page](img/demo-18-move-page.jpg) | ![Assignment editor](img/demo-10-assignment-editor.jpg) |
| A full page for one activity. The grid of destinations appears only after "Find the valid destinations" and a background check. | The most "application-like" screen today: three columns, with drag as a convenience over buttons. Its links lead to entity admin lists, not back to the plan or the timetable. |

| Interactive states (**DISPOSABLE** site, #130 fixtures) | | |
|---|---|---|
| ![DISPOSABLE: move destinations grid](img/disposable-move-destinations.jpg) | ![DISPOSABLE: move preview with cross-plan warning](img/disposable-move-preview-warning.jpg) | ![DISPOSABLE: after undo](img/disposable-move-undone.jpg) |
| The destination grid, with disabled cells that state their reasons. | The preview, with a #133 cross-plan warning. | The page after an undo. |

Other captures are in [`img/`](img/):
- [the request page](img/demo-11-request.jpg);
- [the run status](img/demo-13-job.jpg);
- [the explorer before a selection](img/demo-15-explorer.jpg);
- [the narrow screen (390 px)](img/demo-20-mobile-timetable.jpg).

**Measured pages (demo, 1440 px wide)**

| Page | Shortest clicks from `/ancora` | Path | Words | Links | Controls | Height (px) |
|---|---|---|---|---|---|---|
| Landing `/ancora` | 0 | — | 143 | 39 | 2 | 1210 |
| Planning overview | 1 | Planning | 232 | 64 | 0 | 2357 |
| Plan page | 1 | Open | 227 | 54 | 2 | 1702 |
| Period record `/admin/ancora/periods/1` | **not reachable by links**¹ | — | 40 | 9 | 1 | 900 |
| Catalogues section | 1 | Academic catalogues | 154 | 40 | 0 | 1607 |
| Assignment editor (one template) | 3 | Assignments & templates › Assignment editor › template | 243 | 24 | 15 | 1466 |
| Request a timetable | 1 | Generate | 210 | 38 | 10 | 1360 |
| Run status | 1 | Run 19 | 61 | 16 | 0 | 997 |
| Plan timetable | 2 | Open › Timetable | 243 | 34 | 5 | 1423 |
| Explorer (period only) | 3 | Open › Timetable › Explore… | 111 | 16 | 6 | 900 |
| Explorer / viewer for one teacher | **form selection only**² | — | 234 / 136 | 20 / 16 | 7 / 7 | 1419 / 1086 |
| Move page | 3 | Open › Timetable › Move | 201 | 14 | 2 | 960 |

¹ The plan page links each period's timetable, edit, restrictions, breaks, limits and breakdowns, but not the period record itself.
² The object is only reachable through a form submit (GET), never through a link that carries it.

### 2.4 Journey map: the planner's real tasks

Each **page load** is a full document request.

**T1. "Check teacher Alpha's week and move one of their activities, then undo."**

Counted from the captured links and from the move flow documented in `timetable-editing.md` §9. The demo move itself was not pressed.

| # | Today (demo) | Page loads | Context the user must re-enter |
|---|---|---|---|
| 1 | Landing › **Open** plan | 1 | — |
| 2 | **Timetable** | 1 | period (implicit `?period=1`) |
| 3 | Pick teacher in the filter › **Filter** | 1 | **teacher** |
| 4 | **Move** on one activity | 1 | — (teacher filter now gone from the URL) |
| 5 | **Find the valid destinations** (POST) | 1 | — |
| 6 | Wait for the check: automatic reloads every 2 s (≥ 1) | ≥ 1 | — |
| 7 | Pick a cell (preview) | 1 | — |
| 8 | **Move here** | 1 | — |
| 9 | **Undo the last move**: a new check, its reloads, then confirm | ≥ 3 | — |
| 10 | **Back to the timetable**: returns to `?period=1`, the teacher filter is lost | 1 | — |
| 11 | Pick the teacher again › **Filter** | 1 | **teacher, again** |
| | **Total** | **≥ 13 page loads, 2 re-entries** | The selected activity is never highlighted when the user comes back. |

**T2. "See the same activity from the room's and the group's point of view."**
- Today there are two routes. The plan timetable filter (four selects) or the explorer and viewer each need 1 form submit per perspective.
- The explorer link from the timetable carries only `period`, so any filter is dropped.
- The "Timetable explorer" ↔ "Timetable viewer" tabs carry **no** query, so period and object are both dropped (`/ancora/timetables/viewer` with no query).
- Result: **≥ 2 page loads and ≥ 2 re-entries per perspective switch.**

**T3. "Fix why an activity has no room, then regenerate."**
- Plan › Assignments & templates › Assignment editor › template: 3 page loads.
- Set a priority or assign a room: 1 POST page load each.
- **Generate**: 1 page load. That page shows the plan selector again, and the scope must be picked again: specialty, level and period.
- Submit, then the run page. It does not refresh itself: the user reloads it by hand until the run is done.
- Run › "Timetable of this plan" (1). Re-filter (1).
- The assignment editor has **no link back to the plan, the period's timetable or the request**. Its links lead to five entity admin lists.
- Result: **≥ 9 page loads and 3 context re-entries** (plan, scope, filter).

**T4. "Are there cross-plan conflicts?"**
- The #133 section is at the bottom of every timetable page, below the grid, with the same height whether it has 0 or n warnings.
- When dates are unknown it says so (correct: unknown never means "no conflict"). The warning does not link to the activity it concerns.

**Summary of what is lost**

| State | Where it is lost |
|---|---|
| Selected object (teacher, room, group) | The timetable's Move link, the move page's "Back to the timetable", the explorer ↔ viewer tabs, the timetable's "Explore…" link |
| Selected activity | Every return to a grid: no highlight and no scroll position |
| Plan and period | Assignment editor, request page and entity admin lists: the shell has **no persistent context bar** |
| Generation scope | Request page: picked again on every run |

**Density and other confusing interactions**

*Page layout*
- The grid shows 4–5 text lines per placement: subject, group, teacher, room and "Move", with no colour by subject or activity type.
- The demo's 8 placements already need 1423 px.
- The cross-plan section and the filters compete with the grid for the first screen.

*Navigation*
- Three entry points show the same timetable data with different controls: the plan timetable, the explorer and the viewer.
- The request page shows 38 links, 22 of them Drupal administration links (entity lists, Structure, Appearance, Extend, Configuration, People, Reports).
- The Drupal "Tools / Administration" block sits under every page.

*Narrow screens*
- At 390 px the timetable becomes a day-by-day list with no horizontal overflow (good, measured overflow 0).
- The list has no day switcher and no "now" anchor, so the user scrolls through every day.

*Move and undo*
- The move flow is correct and accessible, but **it is a separate page per activity**.
- The destination check is a background job: the wait shows as automatic page reloads.
- Undo is offered only on that activity's move page, not where the user saw the result.

### 2.5 Gap matrix: historic vs current

| Capability | VB6 (2010) | PHP thesis | Drupal demo today | Gap to close |
|---|---|---|---|---|
| Persistent plan/period context | Implicit (tree selection) | Per window, lost | **None** (only in some URLs) | A visible, persistent context bar, stored in the URL |
| Explore objects without leaving the grid | **Yes** (tree → grid in place) | Separate windows | No (forms, separate routes) | An object list or lens switch that updates the grid in place |
| Perspectives (group / teacher / room / resource) | Yes, one grid | Explorer window | Yes, but across 3 routes | One canvas, a lens switch, and the URL kept |
| Multi-slot spans | Yes (drawn across slots) | Yes | Partly: the card is repeated in every slot it occupies (with "Move" only in the first); there is no visual span | Draw one card across the slots it occupies |
| Activity detail without navigation | Popup | Modal window | No (Move page only) | Inspector drawer |
| Move with validation | Partial (manual edit) | Not wired | **Yes, server-validated (#130)**, separate page | The same command, in place |
| Undo | No | No | **Yes (#130)**, on the move page | Undo in the toast and the inspector |
| Swap | Two right-clicks | No | Designed (#136); #138 on hold | In the inspector, once authorized |
| Cross-plan warnings | No | No | **Yes (#133)**, at the bottom | Badges on cells plus an inspector panel |
| Assignment priorities | Window with ^ v | Inline picker | **Yes (#122)**, separate page | Inspector tab for the template |
| Restriction toggles | Click on cell (hidden gesture) | Forms | Grid form (separate page) | Later slice: a "restrictions" lens on the same canvas |
| Generation status | Modal | Modal | Run page, reloaded by hand | A run drawer with a status that updates by itself |
| Keyboard and screen reader | Poor | Poor | **Good** (buttons first) | Keep as a hard requirement |
| Mobile | n/a | n/a | Usable list | A day agenda with a day switcher |
| Permissions and security | Weak | Deny by default, 2 rules | **Strong** (access checks, CSRF, owner checks) | **Must not regress** |

---

## 3. Target experience: IA and interaction blueprint

### 3.1 Principles

1. **The server stays the authority.**
   - Every validity question is answered by an existing domain service: `PlacementModification`, `CrossPlanConflictDetector`, the case-150 SQL functions, and the assignment services of #122.
   - JavaScript **never** decides validity. It only shows what the server returned.
2. **The URL is the state.** Plan, period, lens, object and selected activity live in the query, so reload, back, bookmarks and shared links restore the screen exactly.
3. **Buttons first, enhancement second (#128 conventions).**
   - Every action is a real link or a form button that works without JavaScript, with a full page load.
   - With JavaScript the same request returns a partial and the page is updated in place.
   - Drag remains a shortcut that presses the same button.
4. **One canvas.** The plan timetable, the explorer and the viewer become **perspectives** ("lenses") of one workspace.
   - Their existing URLs keep working.
   - Read-only access and the viewer's least privilege are preserved: a viewer gets the read-only perspectives and never an edit action.
5. **Context is visible and survives everything.** A context bar is always on screen. Plan, period, selected resource and selected activity are preserved through navigation, writes, reload and browser history.
6. **Shared resources stay visible.** The workspace is plan-centric, but teachers and rooms are shared across plans.
   - The #133 cross-plan information is shown in the canvas, the inspector and the move overlay.
   - When dates are unknown, that is shown too, so a plan never looks conflict-free when it was simply not evaluated.
7. **Durations are drawn truthfully.**
   - A multi-slot activity is one block across the slots it occupies.
   - A break between two of its slots is shown inside the block.
   - Every perspective reflects the real occupancy of the teacher, room, group and resources over the whole duration.

### 3.2 Information architecture

```
Áncora (shell)
├─ Overview  /ancora                           ← stays the entry point; obvious "Resume <last plan>"
├─ Plans (list, create)
└─ Plan workspace  /ancora/planning/{plan}/workspace?period=&lens=&obj=&sel=
   ├─ Context bar: Plan ▾ · Period ▾ · Scope (specialty/level) ▾ · state badge · Run ▸
   ├─ Left rail (collapsible): Groups · Teachers · Rooms · Resources · Templates (search)
   ├─ Canvas: timetable grid (lens = group | teacher | room | resource | all)
   ├─ Inspector drawer (right; off-canvas on narrow): Activity · Object · Template · Run
   └─ Secondary pages (unchanged routes, opened from the workspace):
      catalogues CRUD, period inputs (breaks, limits, restrictions), entity admin
```

**Query parameters**

| Parameter | Meaning |
|---|---|
| `period` | the period |
| `lens` | `group`, `teacher`, `room`, `resource` or `all` |
| `obj` | the object's id in that lens |
| `sel` | the placement id |
| `panel` | `activity`, `object`, `template` or `run` |

Each parameter is validated server-side against the user's access, exactly as the explorer validates its query today. An invalid value falls back to the default, never to another tenant's data.

### 3.3 Wireframes

**W1. Desktop workspace, teacher lens, activity selected (≥ 1200 px)**

```
┌──────────────────────────────────────────────────────────────────────────────────────────┐
│ Áncora   Plans   Catalogues   Runs                                   admin ▾   Log out    │ ① shell
├──────────────────────────────────────────────────────────────────────────────────────────┤
│ Plan: Plan P ▾ │ Period: Week ▾ │ Program · Year 1 ▾ │ ● Final timetable (run 19) │ ▶ Generate │ ② context bar
├───────────────┬─────────────────────────────────────────────────────┬────────────────────┤
│ Lens          │ Teacher Alpha · 4 activities          ⚠ 0  ◷ 1 moved │ Activity           │
│ (•)Teacher    │ ┌──────┬──────────┬──────────┬───────┬───────┬──────┐ │ Subject Alpha #1   │ ⑤ inspector
│ ( )Group      │ │      │ Day 1    │ Day 2    │ Day 3 │ Day 4 │ Day 5│ │ Group 1 · Room 1   │
│ ( )Room       │ ├──────┼──────────┼──────────┼───────┼───────┼──────┤ │ Teacher Alpha      │
│ ( )Resource   │ │08:00 │▌Alpha #1 │▌Alpha #2 │       │       │      │ │ Day 1, slot 1      │
│ [search…   ]  │ │      │ G1 · R1  │ G1 · R1  │       │       │      │ │ ✎ moved by hand    │
│ Teachers      │ │      │ ✎        │          │       │       │      │ │────────────────────│
│ ▸ Teacher Alpha│ ├──────┼──────────┼──────────┼───────┼───────┼──────┤ │ [Move…] [Swap…]⑦   │
│   Teacher Beta│ │09:00 │          │          │       │       │      │ │ [Undo last move]   │
│               │ │ ...  │          │          │       │       │      │ │ History (4) ▸      │
│ ③ rail        │ │11:00 │▌Alpha #1 │▌Alpha #2 │       │       │      │ │ Cross-plan: dates  │
│               │ │      │ G2 · R1  │ G2 · R1  │       │       │      │ │ unknown ⓘ          │
│               │ └──────┴──────────┴──────────┴───────┴───────┴──────┘ │ Template ▸ (#122)   │
│               │ ④ canvas: cells are buttons; ▌= subject colour token    │                    │
├───────────────┴─────────────────────────────────────────────────────┴────────────────────┤
│ ⑥ status region (role=status): "Moved Subject Alpha #1 to day 3, slot 1.  [Undo]"         │
└──────────────────────────────────────────────────────────────────────────────────────────┘
```

Annotations:
- ① **Shell.**
  - The Overview stays the entry point (decision of 2026-10-30), with an obvious **Resume <last plan>** action.
  - Plans, Catalogues and Runs replace the six admin-style sections. Catalogues stays a section because its records serve all plans.
  - Drupal admin remains for configuration and catalogues (A2).
- ② **Context bar.**
  - It is sticky.
  - Changing the plan or period replaces the canvas in place and pushes the URL.
  - The state badge shows the publication state from the existing read model: final, partial, failed, or "stale since input changed".
  - "Generate" opens the run drawer (W4) prefilled with the current scope.
- ③ **Rail.**
  - It lists the user's objects for the lens, using the explorer's existing access rules.
  - Picking an object is a link (`?lens=teacher&obj=1`), enhanced to replace the canvas only.
  - Search filters the list client-side; it is only a display filter.
- ④ **Canvas.**
  - It is the existing timetable grid, rendered by `TimetableReader` and `TimetableExplorer`, as an SDC component.
  - Each placement is a button (`aria-pressed` for the selection).
  - A multi-slot activity is **one block** across the slots it occupies (`rowspan`), instead of a card repeated in every slot.
    - A break between two of its slots is drawn inside the block.
    - In every perspective the block occupies the same slots, so the occupancy of the teacher, room, group and resources is visible.
  - A colour token per subject is stable across sessions, derived from the subject id, with a legend.
  - Markers: ✎ moved by hand; ⚠ cross-plan warning (n); ◌ cross-plan not evaluated (dates unknown). The selected block is outlined and scrolled into view.
- ⑤ **Inspector.**
  - It is a server-rendered aside (§7.3). Drupal core's off-canvas dialog is a reasonable first option, not a mandatory one (A4).
  - It shows the selected activity, its history (the existing `ancora_placement_change` list) and its warnings.
  - It holds the actions the user may take.
- ⑥ **Status region.** Every server write reports here (`role=status`, or `role=alert` for refusals), with Undo where the domain allows it (only the last change, §9 of `timetable-editing.md`).
- ⑦ **Swap** is shown only when #138 is authorized and implemented. Until then it is absent, not disabled.

**W2. Move in place (the #130 flow without leaving the workspace)**

```
 Inspector: Activity › Move                       Canvas (same grid, "move mode")
┌──────────────────────────────┐   ┌──────┬───────────┬───────────┬───────────┐
│ Move Subject Alpha #1        │   │      │ Day 1     │ Day 2     │ Day 3     │
│ Only day and slot change.    │   │08:00 │ ▣ current │ ✕ teacher │ ◯ valid   │
│ What is checked ⓘ            │   │      │           │   busy    │           │
│ ◷ Checking destinations…     │   │09:00 │ ◯ valid   │ ✕ group   │ ◯ valid   │
│   (aria-busy; Cancel)        │   │      │           │   busy    │ ⚠ 1 cross │
│ ── when done ──              │   └──────┴───────────┴───────────┴───────────┘
│ 9 valid cells. Pick one on   │   ◯ = button "Move to day 3, slot 1"
│ the grid or in this list:    │   ✕ = disabled, reason in aria-describedby
│  • Day 1 slot 2              │   ⚠ = valid with cross-plan warning (#133)
│  • Day 3 slot 1  ⚠ 1        │
│ [Preview] → "Move here"      │   Drag ▣ onto ◯ = press that button
└──────────────────────────────┘
```

1. "Move…" requests a check (`requestCheck`, unchanged).
2. The inspector shows the busy state. Its AJAX poll **replaces only the inspector and canvas overlay**, not the page: same interval, same 30-try limit.
3. When the check is done, the server renders the same destination grid that `PlacementMoveForm` renders today, as an overlay of the canvas, plus the list alternative.
4. Preview and commit post the **same form** (`PlacementMoveForm`, same form token, same `commit()` refusals).
5. On success the canvas and inspector are re-rendered, the moved cell is selected, and the status region offers Undo.
6. Without JavaScript the "Move…" link opens today's move page, unchanged.

**W3. Cross-plan warnings in place (#133)**

```
 Context bar: … │ ⚠ 2 cross-plan │   ← count, links to ?panel=conflicts
 Cell:  ▌Alpha #2  ⚠              ← badge on each placement that has a warning
 Inspector › Conflicts:
   Room L2 · Day 2 slot 2 · 2026-09-15 10:00–10:30
   also used by Mathematics #1, Group B1 — plan Intensive B, period Week B   [Open there ↗]
   "Warning only: the scheduler checks each period on its own."
 When dates are unknown: one neutral notice in the bar ("Cross-plan: not evaluated — set dates ↗"),
 never a green "no conflicts".
```

**W4. Run drawer (generation status without leaving)**

```
 ▶ Generate → Inspector › Run
   Scope: Program · Year 1 · Week (from the context bar; editable)   [Request a timetable]
   Run 20 · queued → running → ✓ final timetable (8/8 placed, 0 collisions)
   [Show the new timetable]  ← replaces the canvas; the lens and object are kept
```

The request is the existing `RequestRunForm` with the same validation. The status is a partial of the existing run page, polled with a bounded interval.

**W5. Template assignments in the inspector (#122)**

```
 Inspector › Template "Subject Alpha #1 (PER)"
   Teachers   P1: Teacher Alpha [Groups: G1 ×  G2 ×] [Priority ▾] [Remove]
   Rooms      P1: Room 1   P2: Room 2
   Available  Teacher Beta [Assign]
   (the same AssignmentEditorForm, rendered narrow; drag stays a shortcut)
   ⓘ Changes apply to the next run; the state badge turns "stale".
```

**W6. Narrow screens (< 768 px)**

```
┌──────────────────────────────┐
│ ☰  Plan P · Week ▾      ⚠ 0  │ context collapses to one line; tap opens the bar as a sheet
│ Lens: Teacher Alpha ▾        │
│ ◀ Day 2 ▶   (Mon … Fri tabs) │ day switcher instead of a 5-column grid
├──────────────────────────────┤
│ 08:00 ▌Subject Alpha #2      │ one row per slot; spans show "08:00–10:00"
│       G1 · Room 1        ⋯   │ ⋯ opens the inspector as a bottom sheet
│ 09:00 — free —               │
│ 11:00 ▌Subject Alpha #2   ⚠  │
└──────────────────────────────┘
 Move on narrow screens: inspector › Move › list of valid cells (no grid, no drag).
```

### 3.4 States

| State | Treatment |
|---|---|
| Loading a partial | `aria-busy` on the region being replaced. The old content stays visible, dimmed, so nothing jumps. |
| Destination check queued or running | Inspector busy state with a Cancel link. Bounded polling. The time waited is shown. |
| Generation queued or running | Context-bar badge "Run 20 running". Moves are refused by the server (`job`), and the UI says why **before** the user starts. |
| Stale input | Badge "Inputs changed since run 19". Moves stay possible, as today. |
| Version conflict (another user moved it) | The server's `version` or `placement` refusal is shown in an alert. The canvas is re-rendered from the server. |
| Empty | No plan, no period, no run, or no placements. Each gets one sentence and the one next action. |
| Error, 403 or 404 on a partial | An alert in the region, plus a link to the full page fallback. The session is never left blank. |
| Offline or no JavaScript | Every action works with full page loads (today's routes). |

### 3.5 Keyboard and accessibility (hard requirements)

**The canvas**
- The canvas is a grid with **roving tabindex**: arrows move between cells, Enter selects, and `m` opens Move for the selected activity. Every shortcut is listed and optional.
- Every action is reachable by Tab through real buttons. No action needs a pointer, a right-click or a hover.

**The inspector**
- It is a labelled dialog region. Esc closes it and returns focus to the selected cell.

**Feedback and focus**
- All feedback goes to `role=status` or `role=alert`.
- After every in-place update, focus moves to a defined target: the moved cell, or the error.

**Visual rules**
- Colour is never the only signal. Markers have text and `aria-label`.
- Subject colour tokens meet a contrast ratio of at least 3:1 against the cell, and text keeps 4.5:1.

### 3.6 Before / after workflows

| Task | Today | Target (with JS) | Without JS (target) |
|---|---|---|---|
| T1 Check teacher, move, undo | ≥ 13 page loads, 2 re-entries | **1 page load** (workspace), then in-place partials. About 6 user actions: lens, teacher, cell, Move, destination, Undo. 0 re-entries. | Same as today, but the move page's "Back" returns to the workspace URL **with** lens, object and selection. |
| T2 Switch perspective | ≥ 2 page loads and re-entries per switch | 0 page loads, 1 action (lens and object). The selection is kept when it is visible in the new lens. | 1 page load, no re-entry |
| T3 Fix assignment, regenerate | ≥ 9 page loads, 3 re-entries | 1 page load. Inspector › Template, then Run drawer prefilled. 0 re-entries. | Links between editor, request and workspace carry context |
| T4 Cross-plan conflicts | Scroll to the bottom; no link to the activity | Badge in the bar, ⚠ on cells, and inspector detail with "Open there" | Section kept, with anchors to cells |

### 3.7 Component inventory

| Component | Kind | Built from | Notes |
|---|---|---|---|
| `app-shell` | Theme `page.html.twig` | Existing theme | Overview (with "Resume <last plan>"), Plans, Catalogues, Runs; admin links only for admins |
| `context-bar` | SDC + small behavior | Plan, period and scope pickers (links or GET form) | Sticky; GET fallback |
| `object-rail` | SDC | Explorer's object lists | Links; client-side text filter |
| `lens-switch` | SDC | Radios in a GET form | Enhanced to update in place |
| `timetable-canvas` | SDC (Twig) | `TimetableReader` / `TimetableExplorer` render arrays | Cells as buttons, spans, markers, legend |
| `placement-card` | SDC | Existing placement markup | Subject token, ✎ and ⚠ markers |
| `inspector` | Server-rendered aside (off-canvas is a first option, A4) | Its own partial endpoint (§7.3) | Tabs: Activity, Conflicts, Template, Run |
| `move-panel` | Existing form | `PlacementMoveForm` (unchanged contract) | AJAX-wrapped; full page fallback stays |
| `destination-overlay` | SDC | The move form's grid | Same buttons, rendered over the canvas |
| `history-list` | Existing render | `ancora_placement_change` | — |
| `conflict-list` | Existing render | `CrossPlanConflictView` | Adds per-cell anchors |
| `assignment-panel` | Existing form | `AssignmentEditorForm` (#122) | Narrow layout variant |
| `run-panel` | Existing form and page | `RequestRunForm` + run status | Bounded polling |
| `status-region` | Behavior | `Drupal.announce` + a visible toast | Undo button where allowed |
| `ancora-workspace` controller | One client module (§7.4) | Drupal behaviors and AJAX for writes, plus GET region fetches | URL and history sync, focus management, roving tabindex, progress feedback |

---

## 4. Frontend decision

### 4.1 What the UX actually needs from the client

1. Replace one region (canvas, inspector, bar) after a server response, without a page load.
2. Keep the URL in sync, so back and reload work.
3. Manage focus and keyboard in a grid.
4. Poll a bounded background check or run.
5. Drag as a shortcut that presses a button.

**None of these needs a client-side model of the timetable.** The server already renders the grid and is the only authority on validity. This is the deciding fact for the matrix below.

### 4.2 Options

| Criterion | A. Twig/SDC + behaviors (status quo) | **B. A + Drupal AJAX partials (HTMX-style), progressive** | C. B + one Web Component for the canvas | D. React/Vue/Preact island for the canvas | E. SPA |
|---|---|---|---|---|---|
| Fixes page loads and context loss | No (still full pages) | **Yes** | Yes | Yes | Yes |
| Accessibility | Good (buttons first) | **Good**: same markup; focus management needed | Good if light DOM; shadow DOM complicates labels and forms | Depends on the team; rebuilds accessible markup in JS | Same as D, everywhere |
| Drupal render, cache and permissions | Native | **Native**: partials are render arrays with the same access checks and cache contexts | Native (server HTML inside the element) | Needs JSON endpoints, plus duplicated cache and access reasoning | Needs a full API layer |
| CSRF and forms | Native form tokens | **Native**: the same `FormBase` forms over `#ajax`; tokens unchanged | Native | Custom token handling for every action | Custom everywhere |
| JSON/action API needed | No | **No** (HTML partials) | No | **Yes**: read model and commands | Yes, complete |
| Domain logic duplication risk | None | **None** | Low | Medium: validity tempted into JS | High |
| Bundle, build and deploy | None | **None** (core libraries) | None, or a small build if one is justified | npm build, lockfile, CI step, bundle on the droplet | Same, plus routing and auth |
| Tests | PHPUnit Functional | **Functional + FunctionalJavascript + Playwright E2E** | Same, plus element unit tests | Plus JS unit tests and API contract tests | Everything twice |
| Responsiveness on a 1 vCPU host | Full page per action | **Small HTML fragments** | Same | JSON plus client render | Same as D |
| Fits #66 / #74 (decisions of §4.6) | Yes | **Yes** (A1 approved) | Yes (A1 approved) | Allowed only with evidence (A3 provisional) | Not now (A3) |
| Effort for slice 1 | — | **Medium** | Medium+ | High | Very high |

**Notes on the options**
- **HTMX itself.** It would be a new third-party dependency doing what `core/drupal.ajax` already does: `use-ajax` links, `#ajax` on form elements, and `AjaxResponse` with `ReplaceCommand`, `HtmlCommand`, `InvokeCommand` and custom commands. B uses Drupal's own mechanism, so CSRF, the form cache, attachments (`#attached` libraries) and `drupalSettings` keep working.
- **What core AJAX lacks.** It does not update the URL. That gap is closed by one small custom AJAX command, `ancoraUrl`, that calls `history.replaceState` or `history.pushState` with a server-computed URL, plus a `popstate` handler that re-requests the region.

### 4.3 Recommendation

**Option B: Twig/SDC components, Drupal behaviors, and Drupal AJAX partials, with strict progressive enhancement.**
- C (a light-DOM Web Component) remains an **option inside B**, adopted where it is simpler.
- The S1-specific choice, with its deciding experiment, is in §7.4.
- Following A1, the choice is made from the interaction requirements, not from a size budget. A build step is allowed when a PR justifies it.

**Not chosen for now (A3, provisional)**
- D (a React, Vue or Preact island) and E (a SPA). They would require a JSON command layer and client-side state. None of the measured problems requires them, and they would invite validity logic into JS.
- They are **not permanently excluded**. The trigger below says what evidence would reopen them.

**Revisit trigger for D**, recorded here so the decision is falsifiable. Revisit only if one of these is measured on a real fixture:
- a lens switch partial is measured as too slow at representative size (#143 fixtures) and the cause is the HTML round-trip, not the read model;
- a needed interaction cannot be expressed as "server returns HTML for a region", such as continuous client-side what-if editing of many cells before a commit.

### 4.4 Reusable contracts (no domain rewrite)

| Contract | Exists today | Used by the workspace as |
|---|---|---|
| `TimetableReader` / `TimetableExplorer` read model | Yes | Canvas render for every lens |
| `PlacementModification::requestCheck / check / commit / undo` | Yes (#130) | Move and undo panel. **Same methods, same refusals.** |
| `PlacementMoveForm` (`FormBase`) | Yes | Wrapped with `#ajax`; its submit handlers do not change |
| `CrossPlanConflictDetector` / `CrossPlanConflictView` | Yes (#133) | Bar count, cell badges, inspector list |
| `AssignmentEditorForm` and its services | Yes (#122) | Inspector › Template |
| `RequestRunForm` + job status | Yes | Run drawer |
| Placement swap command | **Designed only (#136); #138 not authorized** | Inspector action, added only when #138 lands |
| New: workspace route + region partial routes | No | Thin controllers that **only compose** the above render arrays. Same access checks as the routes they wrap. |

**How each feature migrates**
- **#130 move/undo.**
  - No change to `PlacementModification`, the check runner, the worker or the tables.
  - The form gains an AJAX wrapper and a `destination` parameter so that "Back" and success return to the workspace URL. The check-pending reload becomes an AJAX poll of the inspector region.
  - Kernel and functional tests stay as they are. Browser tests (Playwright on disposable fixtures, §7.6) are added for the in-place path.
- **#133 warnings.** No change to the detector. Its output is rendered in three places instead of one: the canvas, the inspector and the move overlay. The "unknown" semantics stay ("not evaluated", never "none").
- **#122 assignments.** No change to the form's actions or services. A narrow template variant is used in the inspector, and the drag shortcut stays a button press.
- **#138 swap (when authorized).** Its command follows the #136 design. The workspace gets an inspector action and a second selection mode on the canvas. No other workspace change is needed. **Nothing in this document authorizes #138.**

### 4.5 Security, cache and permissions rules for the workspace

- **Writes**
  - Every write stays a Drupal form submit with its form token. There are no new write endpoints.
  - Partial endpoints are **GET, read-only**.
- **Access**
  - Partial routes declare the same `_permission`, `_custom_access` and entity access as the pages they compose.
  - A partial never returns data that the full page would not show to that user.
  - The `obj`, `sel` and `period` query values are validated against ownership, as the explorer does today. Forged ids get 403 or 404, which is tested.
- **Cache metadata**
  - Partials carry the cache metadata of their render arrays: `user`, `url.query_args:<name>`, and the placement, plan and period cache tags.
  - The workspace page varies by `url.query_args`.
- **Escaping.** No `innerHTML` with client-built strings. The client only inserts server-rendered HTML, through Drupal AJAX commands or the region loader chosen in §7.4.
- **Logging.** No academic data is written to the browser console or to `drupalSettings` beyond the ids already in the URL.

### 4.6 Decisions on the amendments to #66 / #74

The maintainer decided A1–A4 on 2026-10-30/31, within the product direction of #142. The proposals as written in PR #140 are kept in its history.

| # | Topic | Decision (2026-10-30/31) | What changes in this document |
|---|---|---|---|
| A1 | JavaScript scope (#66 §4, #74) | **Approved with modifications.** A coherent interactive frontend with partial page updates and shared workspace state is allowed. There is **no arbitrary size limit** (the proposed 25 KB is dropped), and **build tools are not prohibited**. Pick the simplest maintainable solution from the actual interaction requirements. | U9 becomes "dependencies and build justified in the PR; no unused framework". §7.4 chooses the S1 frontend from the S1 interactions. |
| A2 | Primary frame (#74) | **Approved.** The integrated workspace is the planner's primary interface. Drupal admin stays for configuration and catalogues. | §3.2. The Overview stays the entry point, with "Resume <last plan>". |
| A3 | Client framework | **Provisional.** Progressive enhancement without a SPA comes first. React, Vue or other component approaches are not permanently excluded if evidence justifies them. | §4.3 keeps the revisit trigger, now tied to measurements on representative fixtures (#143). |
| A4 | Inspector container | **Provisional.** Drupal's off-canvas dialog is a reasonable first option, not mandatory. | §3.3, §3.7 and §7.3: the inspector is a server-rendered region. Its container is chosen in S1. |

**Product decisions recorded with them (2026-10-30/31, #142)**
- **Entry point.** The Overview stays the entry point, with an obvious "Resume <last plan>".
- **Perspectives.** Timetable, explorer and viewer become workspace perspectives.
  - Their existing URLs keep working.
  - Read-only and viewer permissions are preserved, including the viewer's least privilege.
- **Shared resources.** Resources are shared across plans. The plan-centric workspace must not hide cross-plan availability or conflicts:
  - the #133 information, "unknown" included, is surfaced in the canvas, the inspector and the move overlay.
- **Multi-slot activities** are drawn as one block, with breaks between slots shown and real resource occupancy reflected.
- **Context.** Plan, period, selected resource and activity are preserved through navigation, reload and browser history.
- **Check latency.**
  - The worker's validation latency is **measured on representative fixtures before any threshold is set**.
  - Progress feedback is immediate.
  - Latency criteria are "measured and reported; threshold set afterwards".
- **Reuse.** Move/undo, assignment editing and cross-plan warnings are reused, with **no** validation logic duplicated in JavaScript.
- **Related work.** #143 (workload evidence) and #144 (interoperability) are cross-referenced. Neither is a prerequisite of S1.

---

## 5. Roadmap

### 5.1 Vertical slices (each one ships on its own)

| Slice | Content | Depends on | Ships value alone? |
|---|---|---|---|
| **S1: Pilot: one complete scheduling workflow** (refined in §7, issue #141) | From the Overview, resume the plan. In the workspace, use the teacher perspective. Multi-slot blocks and cross-plan state are visible. Inspector. Validated move in place with progress, the result and the cross-plan impact. Undo. Reload and back restore everything. Full page fallbacks. Latency measured. | #130, #133 (both deployed) | Yes: T1 end to end |
| S2: All perspectives; one entry per view | Group, room and resource perspectives. The explorer, viewer and plan timetable become workspace perspectives. Their URLs keep working (rendered as perspectives, or kept as pages), and the viewer's least privilege is preserved. The timetable's "Explore…" link goes away. | S1 | Yes |
| S3: Cross-plan in place, complete | Bar count, cell badges, "Open there" links into the other plan's workspace | S1 | Yes |
| S4: Template assignments in the inspector | #122 form in the inspector, with "stale" feedback in the bar | S1, #122 | Yes: T3 part 1 |
| S5: Run drawer | Request and status in the inspector, scope prefilled, "Show the new timetable" keeps the lens | S1 | Yes: T3 part 2 |
| S6: Narrow-screen agenda | Day switcher, bottom-sheet inspector, list-based move | S1 | Yes |
| S7: Period inputs on the canvas | Restrictions and breaks as a lens on the same grid, replacing the separate restriction grid page (the legacy `goMPIIn` affordance) | S1, S2 | Yes |
| S8: Swap in place | Inspector action and two-selection mode | **#138 authorized and merged**, S1 | Yes |
| S9: Visual system | Subject colour tokens, density modes (compact/comfortable), print and export view, SDC library documentation | S1 (can run alongside S2–S6) | Yes |

### 5.2 Dependency and risk matrix

| Risk | Slice | Likelihood | Impact | Mitigation |
|---|---|---|---|---|
| AJAX path diverges from the no-JS path (two behaviours) | S1+ | Medium | High | One form, one submit handler. Tests run both paths (Functional + FunctionalJavascript) for every write. |
| Cache leaks across users or tenants in partials | S1+ | Low | **Critical** | Cache contexts `user` and `url.query_args`, plus tags. A two-user test of the same partial URL. Run the multisite tenant E2E (#75). |
| URL state accepts forged ids | S1 | Medium | High | Server-side validation as in the explorer. 403/404 tests for every parameter. |
| Polling load on the 1 vCPU host | S1, S5 | Medium | Medium | The existing bounded poll (2 s, at most 30 tries). Only the small region is returned. Backoff on the run drawer. |
| Destination check latency feels broken in place | S1 | Medium | Medium | Immediate progress feedback: the queued or running state, the time waited, and Cancel. The latency is measured on representative fixtures (§7.7) **before** any threshold is set. |
| Focus loss after partial replacement (accessibility regression) | S1+ | Medium | High | A focus target is defined for every response. An axe-core scan and keyboard-only E2E in CI. |
| Scope creep into a framework | all | Medium | Medium | A1: every dependency and any build step is justified in its PR, and no framework goes unused. The revisit trigger in §4.3 is the path to D. |
| Real-size grids too dense | S1, S9 | High | Medium | A realistic-size fixture in E2E. A compact density mode (S9). Lens filtering by default (one object, not "all"). |
| #138 pressure | S8 | — | — | S8 stays blocked until the maintainer authorizes #138. The UI shows no disabled "Swap" before then. |

### 5.3 Measurable UX acceptance criteria

The criteria below are measured by a Playwright journey test on a **disposable fixture site**: the #130/#133 fixtures, plus a representative-size fixture when #143 provides one. Criteria marked *measured* are reported first; their threshold is set afterwards by the maintainer.

| # | Criterion | Target | Today (baseline) |
|---|---|---|---|
| U1 | Full page loads to complete the S1 workflow (§7.1) with JS, after the Overview | **1** (the workspace) | ≥ 13 |
| U2 | Context re-entries during T1, T2 and T3 (pick plan, period, object or scope again) | **0** | 2 / ≥ 2 / 3 |
| U3 | Reload or back at any step restores plan, period, lens, object and selection | **100 %** of steps | Partial (selection never restored) |
| U4 | T1 is completed with keyboard only (no pointer events in the test) | Pass | Pass (but page by page) |
| U5 | axe-core on workspace states (default, selected, move overlay, inspector open, alert) | 0 serious or critical | Not measured |
| U6 | Every write is also possible with JavaScript disabled (Functional tests on fallback routes) | 100 % of write actions | 100 % |
| U7 | Server time of a perspective-switch partial, and destination-check latency (queue wait plus run, §7.7) | **Measured and reported**; threshold set afterwards | n/a |
| U8 | Horizontal page overflow at 390 px | 0 px | 0 px |
| U9 | Frontend dependencies and build | **Each dependency and any build step justified in the PR; no unused framework** (A1) | 2 scripts, no dependencies, no build |
| U10 | Visual consistency: every workspace region is an SDC component (or the chosen inspector container); no inline styles | 100 % | n/a |
| U11 | Security parity: every access, refusal and CSRF test of #122, #130 and #133 still passes. New partial routes have 403/404 tests for viewer, anonymous and another owner. | Pass | Pass |
| U12 | Multi-slot truth: on a fixture with 2- and 3-slot activities, one of them crossing a break, each is one block in every perspective, the break is visible, and the occupied slots match the engine's occupancy | Pass | Fails (repeated cards, no break) |
| U13 | Cross-plan visibility: on the #133 fixture, the warning appears on the block, in the inspector and in the move overlay. On a period with unknown dates, "not evaluated" appears in all three. | Pass | Bottom section only |

---

## 6. Open questions for the maintainer

Answered on 2026-10-30/31 (§4.6):
- the amendments;
- the default landing (the Overview, with "Resume");
- the retiring of routes (they become perspectives, the URLs keep working, and the viewer keeps its least privilege);
- the check latency (measure first).

Still open:
1. **S1 frontend experiment.** Accept the experiment of §7.4 as the way to decide between core AJAX partials and a small client component.
2. **Browser test infrastructure.** Playwright on disposable fixtures in CI needs a new CI job, a fixture site and a worker. Is a new workflow acceptable?
3. **Latency thresholds.** Set them after the §7.7 report.
4. **"Resume <last plan>".** It needs a per-user "last plan" (user data or a cookie-free server record). Confirm that storing it is acceptable.
5. **#138.** Swap stays out of every slice until it is authorized.

---

## 7. S1 refined: one complete scheduling workflow (#141)

**Status:** planned and **not authorized** (#141 keeps the maintainer's hold).
- It reflects the decisions of §4.6 and the D3 gate of #142.
- #143 and #144 are context, not prerequisites.
- #138 (swap) is not part of it.

### 7.1 The workflow

S1 delivers one workflow end to end, and nothing it does not need. The **URL** column is what the address bar holds after each step.

| # | User step | What the server does | URL after the step |
|---|---|---|---|
| 1 | Overview › **Resume "Plan P"** | Renders the Overview with the user's last opened plan (owner-checked) | `/ancora` |
| 2 | The workspace opens (the only full page load) | Renders shell, context bar, rail, canvas (default perspective: all of the period), inspector (empty) and status region | `…/planning/1/workspace?period=1` |
| 3 | Pick **Teacher** perspective › **Teacher Alpha** | Returns the canvas and rail for that teacher | `…&lens=teacher&obj=1` (pushState) |
| 4 | **Spot** an activity: a 2-slot block crossing a break, with its cross-plan marker (⚠ n or ◌ not evaluated) | — (already rendered) | — |
| 5 | Select it › the **inspector** opens | Returns the inspector: detail, occupancy (slots, teacher, room, group, resources), history, the cross-plan list or "not evaluated" | `…&sel=5` (pushState) |
| 6 | **Move…** | `PlacementModification::requestCheck()` through `PlacementMoveForm`. The inspector shows **queued** at once, then **running**, with the time waited and Cancel. Cancel only leaves move mode: there is no cancel command, and the check row expires as it does today. | `…&panel=move` (replaceState) |
| 7 | The check is done: the destination overlay appears on the canvas | Returns the overlay: valid, refused (with reasons) and valid-with-cross-plan-warning cells, plus the list alternative | — |
| 8 | Pick a cell › **preview** › **Move here** | `commit()` through the same form, with the same token and refusals | `…&sel=5` (replaceState; `panel` cleared) |
| 9 | See the **result** | Re-renders the canvas (block at its new place, ✎), the inspector (history, **new cross-plan state**) and the status region ("Moved … [Undo]"). A new cross-plan warning is announced. | — |
| 10 | **Undo** | `undo()` through the form, with its own check: steps 6–8 again, prefilled with the origin cell | — |
| 11 | **Reload**, then **Back ×3**, then **Forward ×3** | Each URL renders the same state server-side; back and forward re-request only the regions that changed | Restored exactly at every step |

**Without JavaScript**
- Steps 3, 5 and 11 are links and GET forms that do full page loads.
- Steps 6–10 use today's move page. Its "Back" and its success redirect return to the workspace URL with `lens`, `obj` and `sel`.

### 7.2 Scope

**In S1**
- Overview "Resume".
- The workspace route.
- The context bar (plan, period, publication state).
- The **teacher** perspective, plus the default "all of the period" view.
- The object rail for teachers.
- The canvas with multi-slot blocks, breaks and cross-plan markers.
- The inspector (activity tab and conflicts tab).
- Move and undo in place, with progress feedback.
- The status region.
- URL and history state.
- No-JS fallbacks.
- The latency measurement (§7.7).

**Not in S1**
- Group, room and resource perspectives, and turning the explorer, viewer and timetable routes into perspectives (S2).
- Assignments in the inspector (S4) and the run drawer (S5).
- The narrow-screen agenda (S6). In S1, narrow screens get the current list layout plus the inspector below.
- Swap (#138).
- Any change to the domain, the engine, the worker or the tables.

### 7.3 Component boundaries

**Regions.** Each region is server-rendered, with one partial endpoint each. All of them are GET and read-only.

| Region | Source (existing) | Partial | Re-rendered after |
|---|---|---|---|
| Context bar | Plan and period entities, publication state from `TimetableReader` | `…/workspace/region/bar` | plan or period change; a commit (the state badge) |
| Rail | `TimetableExplorer` object lists, with the explorer's access rules | `…/workspace/region/rail` | perspective change |
| Canvas | `TimetableReader` / `TimetableExplorer` scope, plus `CrossPlanConflictView` per placement | `…/workspace/region/canvas` | perspective or object change; a commit or undo |
| Inspector | Placement, `ancora_placement_change` history, `CrossPlanConflictView` | `…/workspace/region/inspector` | selection; a commit or undo; check progress |
| Move overlay | `PlacementModification::check()` grid, as `PlacementMoveForm` renders it | part of the move form's response | check progress or completion |
| Status region | The form's messages | carried in each write response | every write |

**Endpoints and writes**
- All region endpoints share **one access callback**: the workspace's.
  - Each endpoint validates `period`, `lens`, `obj` and `sel` exactly as the explorer validates its query.
  - An invalid value falls back to the default view. A forged id of another owner gets 403 or 404.
- **Writes are not endpoints.** They are `PlacementMoveForm` submits, unchanged, rendered inside the inspector and submitted with AJAX.
- The form gains only:
  - a validated `destination` back to the workspace URL;
  - an AJAX callback that returns the affected regions.

**Inspector container (A4).** Two candidates:
- a fixed right-hand aside region in the workspace layout;
- core off-canvas.

The aside is the default proposal, because it stays in the document flow, keeps focus order simple and works without JavaScript. Off-canvas is kept for narrow screens if S6 prefers it.

**Client state model**

| State | Where it lives | Why |
|---|---|---|
| plan, period, lens, obj, sel, panel | **URL** (path and query) | Reload, back, bookmarks and shared links restore it. The server validates it on every request. |
| Last opened plan (for "Resume") | Server, per user (an open question, §6) | Survives devices and sessions. Never a client-side record. |
| Pending check id, poll timer, time waited | **Memory** (and the server's check row) | Transient. Losing it on reload is safe: the server still knows the check, and the inspector re-renders its state. |
| Focus target and scroll position | Memory and `history.state` | Restored on back and forward. Not shareable. |
| Text filter in the rail | Memory | A display filter only |
| Validity of any cell | **Never on the client** | Comes only from the server's check (#130) |

### 7.4 Frontend choice for S1

The S1 interactions are:
- (a) replace one or more regions together after a read or a write;
- (b) push and replace the URL, and restore on popstate;
- (c) a keyboard grid with a selection;
- (d) poll a check and show progress;
- (e) submit the existing form in place and show the refusals;
- (f) move focus after each update;
- (g) drag as a shortcut that presses a button.

| Interaction | **P: Drupal AJAX partials + behaviors** | **Q: Small component layer** (light-DOM custom elements; optionally a minimal library with a build) |
|---|---|---|
| (a) Several regions from one response | **Native**: one `AjaxResponse` with several `ReplaceCommand`s. `#attached` libraries and settings are attached automatically. | Custom: fetch HTML, split it, insert it, then call `Drupal.attachBehaviors` by hand |
| Read requests as GET (cacheable) | Supported for links: `data-ajax-http-method="GET"` (`core/misc/ajax.js`:330). The default is POST. | Native `fetch` GET |
| (b) URL, history and popstate | Needs a custom AJAX command plus a popstate handler | Natural in an element's own code |
| (c) Keyboard grid | A behavior with roving tabindex | A custom element is a natural home for it |
| (d) Progress polling | A timed AJAX request of the inspector region | A timed fetch |
| (e) Existing form in place, CSRF, form state, refusals | **Native** (`#ajax` on `PlacementMoveForm`; token and form cache unchanged) | Would have to post the Drupal form by hand (form build id, token) and parse the result: **re-implements Form API plumbing** |
| (f) Focus after update | Custom code in either option | Custom code in either option |
| (g) Drag shortcut | Exists (#130 behavior) | Exists |
| Dependencies and build | Core only (it pulls in jQuery through `core/drupal.ajax`) | None for plain custom elements. A library adds a build. |

**Recommendation for S1: P for every request and every write, plus one light-DOM custom element (`<ancora-timetable-canvas>`) for the keyboard grid and focus.**
- Writes must stay Drupal forms, which decides (e) for P.
- One response updating canvas, inspector and status together, with libraries attached, decides (a).
- The parts that P does not provide, (b), (c) and (f), are custom code in either option.
- A minimal library with a build adds nothing for S1, because every region is server HTML. Under A1 it is allowed, but it is not justified by these interactions.

**It is close on the read side**, between P's GET AJAX links with a custom history command and Q's plain `fetch`. The **deciding experiment** is the first step of S1, on a disposable site:
- implement the perspective switch, the selection and popstate restore both ways;
- run 20 scripted back and forward transitions (Playwright);
- for each way, count:
  - state mismatches,
  - focus-target misses,
  - axe violations,
  - first-party lines of code;
- check that the GET partial is served from Drupal's dynamic page cache on a repeat.

Keep the variant with zero mismatches and misses and fewer lines. On a tie, keep P, which has less code to own.

### 7.5 Accessibility

**Grid and actions**
- The canvas is a `grid`.
  - It uses a roving tabindex: arrows move, Home and End go to the day's edges, Enter selects, and Esc clears the selection.
  - A multi-slot block is one grid cell, `aria-rowspan`, labelled with its full time range, including the break.
- Every action is a real button, and keyboard paths equal pointer paths. Drag stays a shortcut.

**Inspector and focus**
- The inspector is a labelled region (`aside`, `aria-labelledby`). Selecting a block moves focus to its heading, and Esc returns focus to the block.

**Announcements**
- Progress: `role=status`, with "Checking destinations… 4 s".
- Results: `role=status`, with Undo as a button.
- Refusals: `role=alert`.
- A new cross-plan warning after a move is announced in words, not by colour.
- Busy regions carry `aria-busy`. Old content stays visible until it is replaced.

**Markers and tooling**
- Markers (✎ ⚠ ◌) have text equivalents.
- axe-core runs in the browser tests (§7.6).

### 7.6 Security, tests and regression

**Access by perspective and role**

| Who | Workspace and regions | Move and undo | Notes |
|---|---|---|---|
| Plan owner (editor) | Yes: own plans | Yes | As the plan timetable and move routes today |
| Another editor | 403/404 for this plan and its ids | 403 | Forged `sel`, `obj` or `period` tested |
| Administrator | Yes | Yes | As today |
| Viewer only (`view timetable viewer`) | **Not the editing workspace.** The viewer perspective (S2) stays read-only with the viewer's own data scope, and `view academic data` still does not imply it. | Never | S1 must not widen the viewer: `ViewerTest` and `TimetableExplorerTest::testViewer*` stay green |
| Anonymous | 403 | 403 | — |

**Security rules**
- **CSRF.** Writes are only `PlacementMoveForm` submits, with the Drupal form token. Region endpoints are GET and write nothing, including the "last plan" record. That record is written by the full workspace page, or by a token-protected route if it is ever needed elsewhere.
- **Cache contexts.**
  - Every region declares `user`, `url.query_args` (or the specific args) and the placement, plan and period cache tags.
  - A two-user test requests the same partial URL as two owners and asserts different, correct content.

**Browser tests: Playwright on a disposable fixture site**
- It is built like the #130/#133 fixture runs: an ephemeral PostgreSQL and a local PHP server with the worker.
- The fixture includes 2- and 3-slot activities, one crossing a break, and a dated cross-plan overlap plus an undated period.
- It covers the workflow of §7.1 with JavaScript, the same with JavaScript disabled, and keyboard only.
- It runs axe in every state and records the measurements for U1–U3 and U7.

**Regression.** Every existing test must stay green:
- `PlacementModificationTest`, `PlacementMoveUiTest`, `PlacementPublisherTest`;
- `CrossPlanConflictDetectorTest`, `CrossPlanConflictUiTest`;
- `TemplateAssignmentsTest`, `AssignmentEditorUiTest`;
- `TimetableExplorerTest`, `ExplorerTest`, `ViewerTest`, `WorkspacePagesTest`;
- `workspace-journey.sh` step 11 (real engine);
- the engine, characterization and e2e workflows.

### 7.7 Latency measurement plan (no threshold before the report)

**What is measured.** A destination check's latency, as the user sees it, has four parts:
1. **request**: the form submit, until the check row is created;
2. **queue wait**: until the worker picks up the check. Checks run at the start of every worker cycle, and the idle sleep is 5 s by default (`ancora-engine:work --idle-sleep`);
3. **run**: the workspace load of the whole site plus the evaluation of every cell (`PlacementCheckRunner`);
4. **detection**: until the client's poll sees "done" (2 s interval today).

**How it is measured**
- The check table records only `created` and `changed`, in whole seconds, so the harness measures from outside:
  - browser timestamps for the click, the first "running" and "done" rendered;
  - the worker's log lines per check.
- Nothing is added to the schema in S1. A `started` column would be a separate proposal.
- Fixtures:
  - the #130 fixture (small);
  - the #133 fixture (two plans);
  - a representative fixture from #143 when it exists. The report says which ones were used and never extrapolates.
- Each fixture gets 20 checks, with p50, p95 and max per part.
- The report records the worker settings (`--idle-sleep`, `--max-runtime`), the host and the PostgreSQL version.
- The same harness measures the server time of the perspective-switch partial (U7).

**What follows from it**
- The S1 PR reports the numbers in `timetable-editing.md` and in this document.
- The maintainer sets thresholds afterwards. A change to the worker cycle, if one is needed, is a separate decision.

### 7.8 S1 acceptance (for #141)

The workflow of §7.1 passes in Playwright on the disposable fixture, with JavaScript, without it, and keyboard only. In addition:

| # | Criterion | Target |
|---|---|---|
| U1 | Full page loads after the Overview | **1** |
| U2 | Context re-entries | 0 |
| U3 | Reload, back and forward restore every step | 100 % |
| U4 | Keyboard only | Pass |
| U5 | axe | 0 serious or critical |
| U6 | No-JS fallback for every write | Pass |
| U7 | Latency report (§7.7) | Delivered; no threshold |
| U9 | Dependencies and build | Justified in the PR; no unused framework |
| U10 | Visual consistency | Pass |
| U11 | Security parity and the access table of §7.6 | Pass |
| U12 | Multi-slot blocks and breaks | Pass |
| U13 | Cross-plan in canvas, inspector and overlay, including "not evaluated" | Pass |
| — | The §7.4 experiment result is recorded in the PR | Delivered |

### 7.9 Recommended first coding slice (smaller than S1)

**S1a: the read-only workspace, plus the frontend experiment and the latency harness.**
- Overview "Resume".
- The workspace route and its regions.
- The teacher perspective.
- Multi-slot blocks with breaks.
- Cross-plan markers and "not evaluated".
- The inspector (read-only: detail, occupancy, history, conflicts).
- URL and history state.
- The §7.4 experiment.
- The Playwright fixture and harness, including the latency measurement of today's move page.

**It writes nothing**, so it carries no CSRF or domain risk, and it settles the frontend question before any write is wrapped.

**S1b** then adds move and undo in place with progress feedback, completing §7.1.
