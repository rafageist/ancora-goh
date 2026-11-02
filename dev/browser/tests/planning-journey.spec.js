// The complete academic planning journey (#105, functional completion
// package item 4), one test per step, in a real browser with the real
// worker. Run ONLY by modern/scripts/planning-journey.sh, which builds the
// disposable site, the account "planner" and the drush wrapper; skipped in
// every other run (browser-journey.sh).
//
// Each step drives the UI where the application has one, and calls the
// synthetic fixture (modern/scripts/lib/planning-journey/fixture.php) for
// the rest of the dataset; journey.json records the route of every step
// (ui, admin form, fixture), its page loads and its notes. A step that needs
// an open PR is skipped with "needs #N" when the code is not there; its part
// of the work is then done by the route that exists on master, in the step
// before it.
//
// Two scenarios share the site:
// - plan J (steps 1-9): the journey itself. Its run outcomes are a
//   CHARACTERIZATION of historical behaviour: with this ordinary dataset the
//   unchanged engine leaves one lab overflowing the day in CS/Y1 (6.17, still
//   labelled "Final") and fails MA/Y2 with 2202E. Both are pinned in step 6
//   and must not be read as a working timetable.
// - plan P (step 10): the POSITIVE PATH, a second dataset in which the
//   unchanged engine completes every requested scope (every run succeeded,
//   every demand placed, no collision, no overflow). Two of its
//   configuration choices exist only to stay clear of known defects (a LAB
//   restriction at the last slot, against 6.17 and the 2202E; two resource
//   groups on the template that needs a resource, against case 142); see
//   the fixture's "positive" command and docs/ux/planning-journey.md.
// Step 11 then proves that the planner can create and edit, through the
// Drupal UI, every record type that the fixture enters by the Entity API.
//
// The tests share the site, run in file order (one worker) and keep their
// state in files, so a failed step does not stop the next ones; a step whose
// input is missing is skipped as "blocked".
const fs = require('fs');
const path = require('path');
const { execFileSync } = require('child_process');
const { test } = require('@playwright/test');

const enabled = !!process.env.ANCORA_JOURNEY;
test.skip(!enabled, 'run by modern/scripts/planning-journey.sh');

const { login, shot, shown, out, expect } = require('./support');

// Fail a step quickly instead of waiting for the test timeout.
test.use({ actionTimeout: 20000, navigationTimeout: 30000 });

const features = JSON.parse(process.env.ANCORA_JOURNEY_FEATURES || '{}');
const NEEDS = { 148: 'skipped: needs #148', 153: 'skipped: needs #153', 154: 'skipped: needs #154' };
const TEACHERS = { ANA: 'Ana Example', BEN: 'Ben Example', CAR: 'Carla Example', DIA: 'Diego Example', EVA: 'Eva Example' };
// CHARACTERIZATION of historical behaviour, not a target: the outcome of
// each scope's run with plan J's dataset, on the unchanged engine (scope:
// period/specialty/level). CS/Y1 succeeds with one overflowing lab (6.17);
// MA/Y2 fails (2202E). The complete outcome is the positive path (step 10).
const EXPECTED_RUNS = {
  'JW/CS/Y1': { state: 'succeeded', why: 'all 8 demands placed' },
  // A 2-slot lab placed at the last slot overflows the day (6.17); its
  // occupancy row (turno 7) is out of the period's range, and
  // dia_minimo_impacto() fails on it later in the same run (2202E).
  // Verified: the same data with slot 6 restricted succeeds.
  'JW/MA/Y2': { state: 'failed', why: 'legacy 6.17 overflow then 2202E in dia_minimo_impacto' },
  'KW/INT/Y1': { state: 'succeeded', why: 'the one activity of plan K placed' },
};
const TERMINAL = /Succeeded|Partial|Cancelled|Timed out|Failed/;
const statePath = path.join(out, 'state.json');
const journeyPath = path.join(out, 'journey.json');

// ---------------------------------------------------------------- helpers

/** Runs drush on the site; returns the JSON of its last output line. */
function drush(...args) {
  const stdout = execFileSync(process.env.ANCORA_DRUSH, args, { encoding: 'utf8', timeout: 120000, stdio: ['ignore', 'pipe', 'pipe'] });
  const lines = stdout.trim().split('\n');
  return JSON.parse(lines[lines.length - 1]);
}
/** A command of the fixture script. */
const fixture = (...args) => drush('php:script', process.env.ANCORA_JOURNEY_FIXTURE, '--', ...args);
const ids = () => fixture('ids');

const readJson = (file, fallback) => {
  try {
    return JSON.parse(fs.readFileSync(file, 'utf8'));
  }
  catch (e) {
    return fallback;
  }
};
const state = () => readJson(statePath, {});
const save = (values) => fs.writeFileSync(statePath, JSON.stringify({ ...state(), ...values }, null, 2));
const writeOut = (name, data) => fs.writeFileSync(path.join(out, name), JSON.stringify(data, null, 2));

// The record of the running step, written by afterEach.
let step = null;
function begin(name, route) {
  step = { step: name, route, page_loads: 0, notes: [], data: {} };
  return step;
}
const note = (text) => step && step.notes.push(text);

/** Counts the full document loads of a page into the step record. */
function countLoads(page) {
  page.on('load', () => {
    if (step) {
      step.page_loads++;
    }
  });
}

/** Skips the test when an earlier step did not leave what it needs. */
function requires(...keys) {
  const s = state();
  const missing = keys.filter((k) => !s[k]);
  test.skip(missing.length > 0, `blocked: an earlier step did not complete (${missing.join(', ')})`);
  return s;
}

test.beforeAll(() => {
  if (enabled && !fs.existsSync(journeyPath)) {
    fs.writeFileSync(journeyPath, JSON.stringify({ features, steps: [] }, null, 2));
  }
});

test.afterEach(async ({}, testInfo) => {
  if (!enabled) {
    return;
  }
  const skip = testInfo.annotations.find((a) => a.type === 'skip');
  const record = step || { step: testInfo.title, route: null, notes: [] };
  record.outcome = testInfo.status === 'skipped' ? (skip?.description || 'skipped') : testInfo.status;
  if (testInfo.status !== 'passed' && testInfo.status !== 'skipped' && testInfo.error) {
    record.notes = [...(record.notes || []), `error: ${String(testInfo.error.message).split('\n')[0].slice(0, 300)}`];
  }
  record.seconds = Math.round(testInfo.duration / 100) / 10;
  const journey = readJson(journeyPath, { features, steps: [] });
  journey.steps.push(record);
  fs.writeFileSync(journeyPath, JSON.stringify(journey, null, 2));
  step = null;
});

/** Clicks a link by its accessible name (the first one). */
async function link(page, name) {
  await page.getByRole('link', { name, exact: true }).first().click();
}

/** Submits a form by its button and waits for the next document. */
async function submit(page, name) {
  await Promise.all([page.waitForNavigation(), page.getByRole('button', { name, exact: true }).click()]);
}

/** The status message area: no error after a save. */
async function noError(page) {
  await expect(page.locator('[role="alert"], .messages--error')).toHaveCount(0);
}

/** The planner's plan page of plan J. */
const planPage = (s) => `/ancora/planning/${s.plan}`;

/** Waits until the workspace is not loading a region. */
async function settled(page) {
  await page.waitForFunction(() => !document.querySelector('[data-ancora-workspace][aria-busy]'));
}

/** Waits on a run page until the run has ended; reloads by hand if asked. */
async function waitForRun(page, byHand) {
  const table = page.locator('table.ancora-scheduling-job');
  if (!byHand) {
    await expect(table).toContainText(TERMINAL, { timeout: 170000 });
    return 0;
  }
  let reloads = 0;
  for (; reloads < 60; reloads++) {
    if (TERMINAL.test(await table.textContent())) {
      return reloads;
    }
    await page.waitForTimeout(3000);
    // A page that reloads itself (#154) may be loading already.
    await page.reload().catch(() => page.waitForLoadState('load'));
  }
  throw new Error('the run did not end within 60 reloads');
}

/** The final or draft label of every scope on the plan timetable. */
async function scopeLabels(page) {
  return page.locator('[data-timetable]').evaluateAll((nodes) => nodes.map((n) => ({
    scope: n.querySelector('h2')?.textContent.trim(),
    timetable: n.dataset.timetable,
  })));
}

// ------------------------------------------------------------------ steps

test('1 plan, period and break', async ({ page }) => {
  begin('1 plan, period and break', 'ui: Overview › Create a plan (admin form) › plan page › Add period › Breaks');
  countLoads(page);
  await login(page, 'planner');
  await page.goto('/ancora');
  await link(page, 'Create a plan');
  await page.fill('#edit-label-0-value', 'Journey spring 2027');
  await page.fill('#edit-code-0-value', 'J');
  await page.fill('#edit-starts-on-0-value-date', '2027-02-01');
  await page.fill('#edit-ends-on-0-value-date', '2027-02-26');
  await submit(page, 'Save');
  await noError(page);
  // The plan from the overview.
  await page.goto('/ancora');
  await link(page, 'Journey spring 2027');
  await expect(page).toHaveURL(/\/ancora\/planning\/\d+$/);
  const plan = Number(page.url().match(/\/ancora\/planning\/(\d+)$/)[1]);
  await link(page, 'Add period');
  await expect(page.locator('#edit-plan')).toHaveValue(String(plan));
  await page.fill('#edit-label-0-value', 'Week 1');
  await page.fill('#edit-code-0-value', 'JW');
  await page.fill('#edit-days-0-value', '5');
  await page.fill('#edit-slots-per-day-0-value', '6');
  await page.fill('#edit-start-hour-0-value', '8');
  await page.fill('#edit-slot-duration-0-value', '1');
  await page.fill('#edit-plan-days-0-value', '1');
  await submit(page, 'Save');
  await noError(page);
  await expect(page).toHaveURL(new RegExp(`/ancora/planning/${plan}$`));
  const period = ids().period.JW;
  await page.click(`a[href$="/ancora/planning/breaks?period=${period}"]`);
  await page.getByLabel('Break after slot 3, in hours').fill('0.5');
  await submit(page, 'Save');
  await noError(page);
  await shot(page, 'j01-break');

  const server = fixture('inspect', 'J');
  expect(server.starts_on).toBe('2027-02-01');
  expect(server.ends_on).toBe('2027-02-26');
  expect(server.periods[String(period)]).toMatchObject({ code: 'JW', days: 5, slots: 6, breaks_after: [3] });
  save({ plan, period });
});

test('2 catalogues', async ({ page }) => {
  const s = requires('plan');
  begin('2 catalogues', 'ui: plan page "Add …" links (admin forms) for LAB, EVA, LABB, PRJ; fixture: the rest of the catalogue');
  countLoads(page);
  await login(page, 'planner');
  const add = async (label, fields) => {
    await page.goto(planPage(s));
    await link(page, label);
    for (const [id, value] of Object.entries(fields)) {
      await page.fill(id, value);
    }
    await submit(page, 'Save');
    await noError(page);
    await expect(page).toHaveURL(new RegExp(`/ancora/planning/${s.plan}$`));
  };
  await add('Add activity type', { '#edit-label-0-value': 'Laboratory', '#edit-code-0-value': 'LAB', '#edit-duration-slots-0-value': '2', '#edit-cost-0-value': '1' });
  await add('Add teacher', { '#edit-label-0-value': 'Eva Example', '#edit-code-0-value': 'EVA' });
  await add('Add room', { '#edit-label-0-value': 'Lab B', '#edit-code-0-value': 'LABB', '#edit-capacity-0-value': '30' });
  await add('Add other resource', { '#edit-label-0-value': 'Projector cart', '#edit-code-0-value': 'PRJ' });
  fixture('catalogue');
  const r = ids();
  expect(Object.keys(r.type).sort()).toEqual(['LAB', 'LEC']);
  expect(Object.keys(r.teacher).sort()).toEqual(['ANA', 'BEN', 'CAR', 'DIA', 'EVA']);
  expect(Object.keys(r.room).sort()).toEqual(['AUD', 'LABA', 'LABB', 'R101']);
  expect(Object.keys(r.cohort)).toHaveLength(4);
  expect(Object.keys(r.subject)).toHaveLength(5);
  step.data.catalogue = { types: 2, specialties: 2, levels: 2, cohorts: 4, subjects: 5, teachers: 5, rooms: 4, other_resources: 1 };
  save({ catalogue: true });
});

/** Adds PRG's three templates (LEC, LAB, LEC), then moves the LAB last. */
async function programmingTemplates(page, s, inPlace) {
  const r = ids();
  const breakdowns = `/admin/ancora/breakdowns?period=${s.period}`;
  for (const type of ['LEC', 'LAB', 'LEC']) {
    if (inPlace) {
      await page.goto(breakdowns);
      await link(page, 'Add activity template to Programming (Week 1)');
      // Prefilled from the breakdown and locked.
      await expect(page.locator('#edit-subject')).toHaveValue(String(r.subject.PRG));
      await expect(page.locator('#edit-period')).toHaveValue(String(s.period));
      await expect(page.locator('#edit-subject')).toBeDisabled();
    }
    else {
      await page.goto(planPage(s));
      await link(page, 'Add activity template');
      await page.selectOption('#edit-subject', String(r.subject.PRG));
      await page.selectOption('#edit-period', String(s.period));
    }
    await page.selectOption('#edit-activity-type', String(r.type[type]));
    await submit(page, 'Save');
    await noError(page);
  }
  // Order: the lab (second) moves down, after the second lecture.
  if (inPlace) {
    await page.goto(breakdowns);
    await Promise.all([page.waitForNavigation(), link(page, 'Move down Programming #2 (JW)')]);
    await expect(page).toHaveURL(/\/admin\/ancora\/breakdowns\?period=/);
  }
  else {
    await page.goto(`/admin/ancora/templates?plan=${s.plan}`);
    const row = page.locator('tr', { has: page.locator('a:text-is("Programming #2 (JW)")') });
    const move = row.locator('a:text-is("Move down")');
    // An operation of the row's drop button: open it first.
    if (!(await move.isVisible())) {
      await row.locator('.dropbutton-toggle button').click();
    }
    await Promise.all([page.waitForNavigation(), move.click()]);
  }
  await noError(page);
  const t = ids().template;
  for (const label of ['Programming #1 (JW)', 'Programming #2 (JW)', 'Programming #3 (JW)']) {
    expect(t[label], label).toBeTruthy();
  }
  return t;
}

test('3 groups and templates', async ({ page }) => {
  const s = requires('plan', 'catalogue');
  begin('3 groups and templates', features[153]
    ? 'ui: plan page › Add student group (admin form); templates in step 3b'
    : 'ui: plan page › Add student group, Add activity template (admin forms), templates list › Move down');
  countLoads(page);
  await login(page, 'planner');
  const r = ids();
  await page.goto(planPage(s));
  await link(page, 'Add student group');
  await expect(page.locator('#edit-plan')).toHaveValue(String(s.plan));
  await page.fill('#edit-label-0-value', 'CS lecture group');
  await page.fill('#edit-code-0-value', 'GCL');
  await page.selectOption('#edit-specialty', String(r.specialty.CS));
  await page.selectOption('#edit-level', String(r.level.Y1));
  await page.selectOption('#edit-activity-type', String(r.type.LEC));
  await page.check(`#edit-cohorts-${r.cohort.C1A}`);
  await page.check(`#edit-cohorts-${r.cohort.C1B}`);
  await submit(page, 'Save');
  await noError(page);
  expect(ids().group.GCL).toBeTruthy();
  if (!features[153]) {
    await programmingTemplates(page, s, false);
    note('templates of PRG through the plain template form (subject and period chosen again each time) and the templates list (#153 absent)');
    save({ templates: true });
  }
  save({ group: true });
});

test('3b templates in place from the breakdown (#153)', async ({ page }) => {
  test.skip(!features[153], NEEDS[153]);
  const s = requires('plan', 'catalogue', 'group');
  begin('3b templates in place (#153)', 'ui: Subject breakdowns › Add activity template to Programming (Week 1) ×3 › Move down');
  countLoads(page);
  await login(page, 'planner');
  await programmingTemplates(page, s, true);
  await page.goto(`/admin/ancora/breakdowns?period=${s.period}`);
  await shot(page, 'j03b-breakdown');
  save({ templates: true });
});

/** Opens the assignment editor of a template by the best route there is. */
async function openEditor(page, s, template, label) {
  if (features[153]) {
    await page.goto(`/admin/ancora/breakdowns?period=${s.period}`);
    await link(page, `Teachers, groups and rooms of ${label}`);
  }
  else {
    await page.goto(`/admin/ancora/templates/${template}`);
    const link = page.locator(`a[href*="assignment-editor?template=${template}"]`).first();
    if (await link.count()) {
      await link.click();
    }
    else {
      await page.goto(`/ancora/planning/assignment-editor?template=${template}`);
      note(`no link from the template page to its assignment editor; opened by URL (${label})`);
    }
  }
  await expect(page).toHaveURL(new RegExp(`assignment-editor\\?template=${template}`));
}

/** Presses a button of the assignment editor (a full form post). */
async function press(page, name) {
  await Promise.all([page.waitForNavigation(), page.getByRole('button', { name, exact: true }).click()]);
  await noError(page);
}

/** Assigns a teacher with a priority and groups in the assignment editor. */
async function assignTeacher(page, label, priority, groups) {
  await press(page, `Assign ${label}`);
  for (const group of groups) {
    await page.getByLabel(`Group to add to ${label}`, { exact: true }).selectOption({ label: group });
    await press(page, `Add the chosen group to ${label}`);
  }
  if (priority !== 1) {
    await page.getByLabel(`Priority of ${label}`, { exact: true }).selectOption(String(priority));
    await press(page, `Set the priority of ${label}`);
  }
}

/** Assigns a room with a priority in the assignment editor. */
async function assignRoom(page, label, priority) {
  await press(page, `Assign ${label}`);
  if (priority !== 1) {
    await page.getByLabel(`Priority of ${label}`, { exact: true }).selectOption(String(priority));
    await press(page, `Set the priority of ${label}`);
  }
}

test('4 teachers, rooms, resources, restrictions and limits', async ({ page }) => {
  test.setTimeout(240000);
  const s = requires('plan', 'catalogue', 'group', 'templates');
  begin('4 assignments', `fixture: the other groups and templates; ui: assignment editor (${features[153] ? 'from the breakdown list' : 'from the template page'}) for PRG, template resources, restriction grid, daily load limits; fixture: the other subjects' assignments`);
  countLoads(page);
  fixture('structure');
  await login(page, 'planner');
  const t = ids().template;
  const plan = [
    ['Programming #1 (JW)', [['Ana Example', 1, ['CS lecture group']], ['Ben Example', 2, ['CS lecture group']]], [['Auditorium', 1], ['Room 101', 2]]],
    ['Programming #2 (JW)', [['Ana Example', 1, ['CS lecture group']], ['Ben Example', 2, ['CS lecture group']]], [['Auditorium', 1], ['Room 101', 2]]],
    ['Programming #3 (JW)', [['Ben Example', 1, ['Lab group C1A', 'Lab group C1B']], ['Carla Example', 2, ['Lab group C1A', 'Lab group C1B']]], [['Lab A', 1], ['Lab B', 2]]],
  ];
  const loadsBefore = step.page_loads;
  for (const [label, teachers, rooms] of plan) {
    await openEditor(page, s, t[label], label);
    for (const [teacher, priority, groups] of teachers) {
      await assignTeacher(page, teacher, priority, groups);
    }
    for (const [room, priority] of rooms) {
      await assignRoom(page, room, priority);
    }
  }
  step.data.assignment_editor_page_loads = step.page_loads - loadsBefore;
  await shot(page, 'j04-editor');

  // The projector for PRG's first lecture: the template resources editor.
  if (features[153]) {
    await page.goto(`/admin/ancora/breakdowns?period=${s.period}`);
    await link(page, 'Other resources of Programming #1 (JW)');
  }
  else {
    await openEditor(page, s, t['Programming #1 (JW)'], 'Programming #1 (JW)');
    await link(page, 'Other resources');
  }
  await press(page, 'Add a group');
  await page.locator('details', { hasText: 'Group 1' }).getByLabel('Projector cart').check();
  await press(page, 'Save group 1');

  // DIA is not available on day 1, slots 1 and 2: the restriction grid.
  const r = ids();
  await page.goto(planPage(s));
  await page.click(`a[href*="/ancora/planning/restrictions?kind=period&period=${s.period}"]`);
  // The object chooser is folded once a period is chosen.
  if (!(await page.locator('#edit-kind').isVisible())) {
    await page.locator('details:has(#edit-kind) > summary').click();
  }
  await page.selectOption('#edit-kind', 'teacher');
  await press(page, 'Show');
  if (!(await page.locator('#edit-target').isVisible())) {
    await page.locator('details:has(#edit-target) > summary').click();
  }
  await page.selectOption('#edit-target', String(r.teacher.DIA));
  await press(page, 'Show');
  await page.getByLabel('Day 1, slot 1 restricted').check();
  await page.getByLabel('Day 1, slot 2 restricted').check();
  await press(page, 'Save restrictions');

  // BEN teaches at most 3 per day: the daily load limits.
  await page.goto(planPage(s));
  await page.click(`a[href$="/ancora/planning/load-limits?period=${s.period}"]`);
  await page.getByLabel('Maximum daily load of Ben Example').fill('3');
  await press(page, 'Save');

  fixture('assignments');
  const server = fixture('inspect', 'J');
  expect(server.demands).toEqual({ 'JW/CS/Y1': 8, 'JW/MA/Y2': 14 });
  // What the UI entered, as the server stored it.
  const lecture = { teachers: ['ANA:1:GCL', 'BEN:2:GCL'], rooms: ['AUD:1', 'R101:2'] };
  expect(server.eligibility['Programming #1 (JW)']).toEqual(lecture);
  expect(server.eligibility['Programming #2 (JW)']).toEqual(lecture);
  expect(server.eligibility['Programming #3 (JW)']).toEqual({ teachers: ['BEN:1:G1A+G1B', 'CAR:2:G1A+G1B'], rooms: ['LABA:1', 'LABB:2'] });
  expect(server.resource_groups).toContainEqual({ template: 'Programming #1 (JW)', needed: 1, essential: true, members: ['PRJ'] });
  expect(server.restricted_cells.filter((r) => r.who === 'teacher:DIA').map((r) => r.cell).sort()).toEqual([`${s.period}.1.1`, `${s.period}.1.2`]);
  expect(server.loads).toContainEqual(expect.objectContaining({ kind: 'teacher', object: 'BEN', max_daily_load: 3 }));
  step.data.demands = server.demands;
  save({ assignments: true });
});

/** Requests one scope through the request form; returns the run page URL. */
async function requestScope(page, s, specialty, level, prefilled) {
  const r = ids();
  await page.goto(planPage(s));
  if (prefilled) {
    await page.click(`a[href*="plan=${s.plan}&period=${s.period}"]:text-is("Generate")`);
    await expect(page.locator('select[name="period"]')).toHaveValue(String(s.period));
  }
  else {
    await link(page, 'Request a timetable');
    await page.selectOption('select[name="period"]', String(s.period));
  }
  await expect(page.locator('select[name="plan"]')).toHaveValue(String(s.plan));
  await page.selectOption('select[name="specialty"]', String(r.specialty[specialty]));
  await page.selectOption('select[name="level"]', String(r.level[level]));
  await Promise.all([page.waitForURL(/\/ancora\/scheduling\/jobs\/\d+$/), page.click('input[value="Request the timetable"]')]);
  return page.url();
}

test('5 generate through the worker', async ({ page }) => {
  test.setTimeout(420000);
  const s = requires('plan', 'assignments');
  const scopes = features[154] ? [['CS', 'Y1']] : [['CS', 'Y1'], ['MA', 'Y2']];
  begin('5 generate', `ui: plan page › Request a timetable (period, specialty, level chosen) › run page reloaded by hand, for ${scopes.map((x) => x.join('/')).join(' and ')}${features[154] ? '; MA/Y2 in step 5b' : ''}`);
  // The second dated plan K (fixture), queued first: cross-plan warnings.
  fixture('second-plan');
  const k = execFileSync(process.env.ANCORA_DRUSH, ['php:script', process.env.ANCORA_ENQUEUE, '--', 'planner', 'K', 'KW', 'INT', 'Y1'], { encoding: 'utf8' }).trim().split('\n').pop();
  note(`plan K (fixture) queued by drush as job ${k}`);
  countLoads(page);
  await login(page, 'planner');
  const runs = [];
  for (const [specialty, level] of scopes) {
    const before = step.page_loads;
    const url = await requestScope(page, s, specialty, level, false);
    const reloads = await waitForRun(page, true);
    const outcome = (await page.locator('table.ancora-scheduling-job').textContent()).match(TERMINAL)[0];
    runs.push({ scope: `${specialty}/${level}`, job: Number(url.match(/(\d+)$/)[1]), outcome, page_loads: step.page_loads - before, manual_reloads: reloads });
  }
  await shot(page, 'j05-run');
  step.data.runs = runs;
  save({ generated: true, jobK: Number(k) });
});

test('5b generate from the context and follow the run (#154)', async ({ page }) => {
  test.setTimeout(300000);
  test.skip(!features[154], NEEDS[154]);
  const s = requires('plan', 'assignments', 'generated');
  begin('5b generate from the context (#154)', 'ui: plan page period row › Generate (plan and period prefilled) › run page reloads itself › Open in the workspace');
  countLoads(page);
  await login(page, 'planner');
  const before = step.page_loads;
  const url = await requestScope(page, s, 'MA', 'Y2', true);
  const atRun = step.page_loads;
  await waitForRun(page, false);
  const outcome = (await page.locator('table.ancora-scheduling-job').textContent()).match(TERMINAL)[0];
  const selfReloads = step.page_loads - atRun;
  await link(page, 'Open in the workspace');
  await page.waitForURL(new RegExp(`/ancora/planning/${s.plan}/workspace\\?period=${s.period}$`));
  await settled(page);
  await shot(page, 'j05b-workspace');
  step.data.run = { scope: 'MA/Y2', job: Number(url.match(/(\d+)$/)[1]), outcome, page_loads: step.page_loads - before, self_reloads: selfReloads };
});

test('6 runs and placements on the server', async ({ page }) => {
  test.setTimeout(240000);
  const s = requires('plan', 'generated');
  begin('6 outcome', 'server: fixture inspect (placements, runs); ui: plan timetable (final or draft)');
  // Every run of J and K has ended (bounded wait).
  let server;
  for (let i = 0; i < 90; i++) {
    server = fixture('inspect', 'J');
    const k = fixture('inspect', 'K');
    const open = [...server.jobs, ...k.jobs].filter((j) => /queued|running/.test(j.state));
    if (open.length === 0 && server.jobs.length >= 2) {
      step.data.plan_k = { jobs: k.jobs.map((j) => ({ id: j.id, state: j.state, error: j.error, ...j.metrics })), placements: k.count };
      break;
    }
    await new Promise((resolve) => setTimeout(resolve, 2000));
  }
  writeOut('inspect-after-generation.json', server);
  step.data.jobs = server.jobs.map((j) => ({ id: j.id, scope: `${j.period}/${j.specialty}/${j.level}`, state: j.state, queued_s: j.queued_s, run_s: j.run_s, ...j.metrics, input_hash: undefined }));
  step.data.placements = server.count;
  step.data.demands = server.demands;
  step.data.collisions = server.collisions;
  step.data.overflow = server.overflow;
  step.data.break_crossing = server.break_crossing;
  step.data.restricted_cells = server.restricted_cells;
  step.data.loads = server.loads;
  const placed = server.jobs.reduce((n, j) => n + (j.metrics.placed || 0), 0);
  // Reported, not judged (legacy behaviour, assessment 6.17 for overflows).
  note(`${server.count} placements; ${server.overflow.length} overflow the day (6.17, reported); ${server.break_crossing.length} span the break`);
  for (const r of server.restricted_cells.filter((x) => x.occupied_by.length)) {
    note(`restricted cell ${r.kind} ${r.who || ''} ${r.cell} occupied by ${r.occupied_by.join(',')}`);
  }
  for (const l of server.loads) {
    const over = Object.entries(l.activities_per_day).filter(([, n]) => n > l.max_daily_load);
    if (over.length) {
      note(`${l.kind} ${l.object}: more activities than the daily load limit ${l.max_daily_load} on day(s) ${over.map(([d]) => d).join(',')} (the limit counts costs, see case 140)`);
    }
  }
  // The integrity of what was stored: hard assertions.
  expect(server.count, 'stored placements = placed by the runs').toBe(placed);
  expect(server.collisions, 'no teacher, room, group, cohort or resource in two places at once').toEqual([]);
  save({ placements: server.count });

  // The outcome of every run, pinned to what the unchanged engine does with
  // this dataset (legacy is the specification; nothing is tuned to avoid a
  // legacy defect). A change of outcome fails this step on purpose.
  const outcomes = Object.fromEntries([...server.jobs, ...(step.data.plan_k?.jobs || []).map((j) => ({ ...j, period: 'KW', specialty: 'INT', level: 'Y1' }))]
    .map((j) => [`${j.period}/${j.specialty}/${j.level}`, j.state]));
  step.data.outcomes = outcomes;
  for (const [scope, expected] of Object.entries(EXPECTED_RUNS)) {
    expect.soft(outcomes[scope], `run of ${scope}: ${expected.why}`).toBe(expected.state);
    if (outcomes[scope] === 'failed') {
      const job = server.jobs.find((j) => `${j.period}/${j.specialty}/${j.level}` === scope);
      note(`run of ${scope} failed: ${job?.error} (${expected.why})`);
    }
  }

  // Final or draft, as the plan timetable says it, against the run states.
  countLoads(page);
  await login(page, 'planner');
  await page.goto(`/ancora/planning/${s.plan}/timetable?period=${s.period}`);
  const labels = await scopeLabels(page);
  step.data.timetable_labels = labels;
  const finals = server.jobs.filter((j) => j.state === 'succeeded').length;
  const drafts = server.jobs.filter((j) => j.state === 'partial').length;
  expect(labels.filter((l) => l.timetable === 'final').length, 'scopes labelled final = succeeded runs').toBe(finals);
  expect(labels.filter((l) => l.timetable === 'draft').length, 'scopes labelled draft = partial runs').toBe(drafts);
  if (server.overflow.length && finals) {
    note('a run that left an activity overflowing the day is labelled "Final timetable" (workspace mode keeps 6.17 overflow, placement-promotion.md)');
  }
  await shot(page, 'j06-timetable');
});

test('7 timetable perspectives', async ({ page }) => {
  const s = requires('plan', 'placements');
  begin('7 perspectives', 'ui: workspace lenses (teacher, group, room) in place; plan timetable › explorer (cohort, other resource) by form');
  const server = fixture('inspect', 'J');
  const count = (f) => server.placements.filter(f).length;
  countLoads(page);
  await login(page, 'planner');
  await page.goto(`/ancora/planning/${s.plan}/workspace?period=${s.period}`);
  await settled(page);
  const loadsAtWorkspace = step.page_loads;
  const cross = page.locator('.ancora-wsbar__cross');
  if (await cross.count()) {
    step.data.cross_plan = (await cross.textContent()).trim();
  }
  const lens = async (name, record, expected) => {
    await page.click(`.ancora-wsnav__lenses a:text("${name}")`);
    await settled(page);
    await page.click(`.ancora-wsnav__list a:has-text("${record}")`);
    await settled(page);
    await expect(page.locator('#ancora-canvas-title')).toHaveText(new RegExp(`^${record}: ${expected} activit`));
  };
  await lens('Teachers', 'Ben Example', count((p) => p.teacher === 'BEN'));
  await lens('Groups', 'CS lecture group', count((p) => p.group === 'GCL'));
  await lens('Rooms', 'Auditorium', count((p) => p.room === 'AUD'));
  await shot(page, 'j07-workspace-room');
  step.data.workspace_full_loads = step.page_loads - loadsAtWorkspace;

  const r = ids();
  const before = step.page_loads;
  await explore(page, s, 'brigade', r.cohort.C1A, 'Cohort C1A', count((p) => p.cohorts.includes('C1A')));
  await explore(page, s, 'resource', r.resource.PRJ, 'Projector cart', count((p) => p.resources.includes('PRJ')));
  await shot(page, 'j07-explorer-resource');
  step.data.explorer_loads = step.page_loads - before;
  note('cohort and other-resource timetables only in the explorer (no workspace lens; FC-36/G5)');
  if (!server.placements.some((p) => p.resources.length)) {
    note('no placement uses an other resource: with one resource group per template, generar() takes resources from every group but the last (SharedResources, case 142), so the other-resource view is empty');
  }
});

/** Opens the explorer from the plan timetable; checks one object's count. */
async function explore(page, s, selector, id, label, expected) {
  // From the plan timetable's link, which carries the period.
  await page.goto(`/ancora/planning/${s.plan}/timetable?period=${s.period}`);
  await link(page, 'Explore by cohort, teacher, room or other resource');
  await expect(page.locator('#ancora-explorer-period')).toHaveValue(String(s.period));
  await page.selectOption(`#ancora-explorer-${selector}`, String(id));
  await Promise.all([page.waitForNavigation(), page.locator('.ancora-explorer__form button[type="submit"], .ancora-explorer__form input[type="submit"]').first().click()]);
  await expect(page.locator('caption').first()).toHaveText(new RegExp(`^${label}: ${expected} activit`));
}

/** The placements as "id: day.slot teacher room", for the move checks. */
const layout = (server) => Object.fromEntries(server.placements.map((p) => [p.id, `${p.day}.${p.slot} ${p.teacher} ${p.room} ${p.group}`]));

/** A one-slot lecture of PRG: the activity the journey moves. */
const moved = (server) => server.placements.find((p) => p.subject === 'PRG' && p.type === 'LEC');

/** Asserts that only the placement changed (day and slot) since before. */
function onlyMoved(before, after, id) {
  const changed = Object.keys(after).filter((k) => after[k] !== before[k]);
  expect(changed, 'only the moved placement changed').toEqual([String(id)]);
  expect(after[id].split(' ').slice(1), 'teacher, room and group unchanged').toEqual(before[id].split(' ').slice(1));
}

test('8 move an activity and undo (move page)', async ({ page }) => {
  test.setTimeout(240000);
  const s = requires('plan', 'placements');
  begin('8 move and undo (move page)', 'ui: plan timetable › Move (classic move page, full page per step)');
  const server = fixture('inspect', 'J');
  const target = moved(server);
  const before = layout(server);
  countLoads(page);
  await login(page, 'planner');
  await page.goto(`/ancora/planning/${s.plan}/timetable?period=${s.period}`);
  const loads0 = step.page_loads;
  await page.click(`a[href$="/ancora/timetables/placements/${target.id}/move"] >> nth=0`);
  await Promise.all([page.waitForURL(/check=\d+/), page.click('input[value="Find the valid destinations"]')]);
  await page.waitForSelector('#ancora-move-grid', { timeout: 90000 });
  await page.locator('#ancora-move-grid input.ancora-move__cell:not([disabled])').first().click();
  await page.waitForSelector('#ancora-move-preview');
  await submit(page, 'Move here');
  await expect(page.locator('#ancora-move-feedback')).toContainText('Moved it to day');
  step.data.move_page_loads = step.page_loads - loads0;
  const afterMove = layout(fixture('inspect', 'J'));
  onlyMoved(before, afterMove, target.id);
  await shot(page, 'j08-moved');

  const loads1 = step.page_loads;
  await Promise.all([page.waitForURL(/check=\d+/), page.click('input[value="Undo the last move"]')]);
  await page.waitForSelector('#ancora-move-grid', { timeout: 90000 });
  await page.locator('#ancora-move-grid input.ancora-move__cell:not([disabled])').first().click();
  await page.waitForSelector('#ancora-move-preview');
  await submit(page, 'Move it back here');
  await expect(page.locator('#ancora-move-feedback')).toContainText('Moved it back to day');
  step.data.undo_page_loads = step.page_loads - loads1;
  const end = fixture('inspect', 'J');
  expect(layout(end), 'the undo restores every placement').toEqual(before);
  const changes = end.changes.filter((c) => c.placement === target.id);
  expect(changes.map((c) => c.reason)).toEqual(['move', 'undo']);
  expect(changes[1].reverts).toBe(changes[0].id);
  expect(changes[1].from).toBe(changes[0].to);
  expect(changes[1].to).toBe(changes[0].from);
  step.data.placement = target.id;
  step.data.audit = changes;
  writeOut('inspect-after-undo.json', end);
});

test('8b move and undo in place in the workspace (#148)', async ({ page }) => {
  test.setTimeout(240000);
  test.skip(!features[148], NEEDS[148]);
  const s = requires('plan', 'placements');
  begin('8b move and undo in place (#148)', 'ui: workspace inspector › Find the valid destinations › cell › Move here › Undo the last move');
  const server = fixture('inspect', 'J');
  const target = moved(server);
  const before = layout(server);
  const changesBefore = server.changes.length;
  const r = ids();
  countLoads(page);
  await login(page, 'planner');
  const loads0 = step.page_loads;
  await page.goto(`/ancora/planning/${s.plan}/workspace?period=${s.period}&lens=teacher&obj=${r.teacher[target.teacher]}&sel=${target.id}`);
  await settled(page);
  const find = async (button) => {
    await page.locator(`#ancora-move-form input[type="submit"][value="${button}"]`).click();
    await settled(page);
    await page.waitForSelector('#ancora-canvas-moving', { timeout: 90000 });
    await settled(page);
  };
  await find('Find the valid destinations');
  await page.locator('.ancora-dest.is-valid').first().click();
  await settled(page);
  await page.locator('#ancora-move-preview .button--primary').click();
  await settled(page);
  await expect(page.locator('#ancora-move-feedback')).toContainText('Moved it to day');
  onlyMoved(before, layout(fixture('inspect', 'J')), target.id);
  await shot(page, 'j08b-moved');
  await find('Undo the last move');
  await page.locator('.ancora-dest.is-valid').first().click();
  await settled(page);
  await expect(page.locator('#ancora-move-preview .button--primary')).toHaveValue('Move it back here');
  await page.locator('#ancora-move-preview .button--primary').click();
  await settled(page);
  await expect(page.locator('#ancora-move-feedback')).toContainText('Moved it back to day');
  step.data.page_loads_move_and_undo = step.page_loads - loads0;
  const end = fixture('inspect', 'J');
  expect(layout(end), 'the undo restores every placement').toEqual(before);
  const changes = end.changes.slice(changesBefore);
  expect(changes.map((c) => [c.placement, c.reason])).toEqual([[target.id, 'move'], [target.id, 'undo']]);
  expect(changes[1].reverts).toBe(changes[0].id);
  step.data.audit = changes;
});

test('9 reload, back and forward keep the context', async ({ page }) => {
  const s = requires('plan', 'placements');
  begin('9 reload and history', 'ui: workspace in place, browser back/forward and reload');
  const server = fixture('inspect', 'J');
  const target = moved(server);
  countLoads(page);
  await login(page, 'planner');
  await page.goto(`/ancora/planning/${s.plan}/workspace?period=${s.period}`);
  await settled(page);
  const loads0 = step.page_loads;
  await page.click('.ancora-wsnav__lenses a:text("Teachers")');
  await settled(page);
  await page.click(`.ancora-wsnav__list a:has-text("${TEACHERS[target.teacher]}")`);
  await settled(page);
  await page.click(`[data-ancora-placement="${target.id}"]`);
  await settled(page);
  const selected = await shown(page);
  const selectedUrl = page.url();
  expect(selected.selected).toBe(String(target.id));
  await page.click('.ancora-wsnav__lenses a:text("Rooms")');
  await settled(page);
  const rooms = await shown(page);
  const roomsUrl = page.url();
  await page.goBack();
  await settled(page);
  expect(page.url()).toBe(selectedUrl);
  expect(await shown(page)).toEqual(selected);
  await page.goForward();
  await settled(page);
  expect(page.url()).toBe(roomsUrl);
  expect(await shown(page)).toEqual(rooms);
  await page.goBack();
  await settled(page);
  await page.reload();
  await settled(page);
  expect(page.url()).toBe(selectedUrl);
  expect(await shown(page)).toEqual(selected);
  step.data.full_loads = step.page_loads - loads0;
  step.data.context = { url: selectedUrl.replace(/^https?:\/\/[^/]+/, ''), shown: selected };
  // Resume on the overview returns to this workspace.
  await page.goto('/ancora');
  const resume = page.locator('a[data-ancora-resume]');
  if (await resume.count()) {
    step.data.resume = (await resume.textContent()).trim();
  }
});

// ------------------------------------------------------------ positive path

// Plan P's scopes and their demands (fixture command "positive").
const POSITIVE_SCOPES = { 'PW/PH/Y3': 8, 'PW/CH/Y4': 5 };

test('10 positive path: every scope of plan P complete', async ({ page }) => {
  test.setTimeout(420000);
  begin('10 positive path (plan P)', 'fixture: plan P dataset (Entity API); ui: plan page › Request a timetable for PH/Y3 and CH/Y4 › run page; server: fixture inspect P; ui: plan timetable, explorer (other resource)');
  // Independent of plan J: own catalogue, dates and scopes.
  const p = fixture('positive');
  const before = fixture('inspect', 'P');
  expect(before.demands, 'demands per scope of plan P').toEqual(POSITIVE_SCOPES);
  expect(before.jobs).toEqual([]);
  expect(before.resource_groups.map((g) => g.members), 'two resource groups on the analytical chemistry lab (case 142)').toEqual([['SPC'], ['FUM']]);
  note('configuration chosen to stay clear of known defects: LAB restricted at slot 6 of every day (6.17 overflow, then 2202E); two resource groups on the template that needs a resource (case 142: the last group is never used)');
  countLoads(page);
  await login(page, 'planner');
  const runs = [];
  for (const [specialty, level] of [['PH', 'Y3'], ['CH', 'Y4']]) {
    const loads = step.page_loads;
    const url = await requestScope(page, p, specialty, level, false);
    await waitForRun(page, !features[154]);
    const outcome = (await page.locator('table.ancora-scheduling-job').textContent()).match(TERMINAL)[0];
    runs.push({ scope: `${specialty}/${level}`, job: Number(url.match(/(\d+)$/)[1]), outcome, page_loads: step.page_loads - loads });
  }
  step.data.runs = runs;
  await shot(page, 'j10-run');

  // Every run of P has ended (bounded wait).
  let server;
  for (let i = 0; i < 60; i++) {
    server = fixture('inspect', 'P');
    if (server.jobs.length === 2 && !server.jobs.some((j) => /queued|running/.test(j.state))) {
      break;
    }
    await new Promise((resolve) => setTimeout(resolve, 2000));
  }
  writeOut('inspect-positive.json', server);
  step.data.jobs = server.jobs.map((j) => ({ id: j.id, scope: `${j.period}/${j.specialty}/${j.level}`, state: j.state, queued_s: j.queued_s, run_s: j.run_s, ...j.metrics, input_hash: undefined }));
  step.data.placements = server.count;
  step.data.collisions = server.collisions;
  step.data.overflow = server.overflow;
  step.data.break_crossing = server.break_crossing;
  step.data.restricted_cells = server.restricted_cells;
  const scopes = Object.fromEntries(server.jobs.map((j) => [`${j.period}/${j.specialty}/${j.level}`, j]));
  expect(Object.keys(scopes).sort(), 'one run per requested scope').toEqual(Object.keys(POSITIVE_SCOPES).sort());
  for (const [scope, demands] of Object.entries(POSITIVE_SCOPES)) {
    const j = scopes[scope];
    expect(j.state, `run of ${scope}: succeeded (${j.error || 'no error'})`).toBe('succeeded');
    expect(j.metrics.demands, `${scope}: demands`).toBe(demands);
    expect(j.metrics.placed, `${scope}: every demand placed`).toBe(demands);
    expect(j.metrics.unplaced || 0, `${scope}: nothing unplaced`).toBe(0);
    expect(j.metrics.collisions || 0, `${scope}: no collision reported by the run`).toBe(0);
    expect(j.metrics.overflow || 0, `${scope}: no overflow reported by the run`).toBe(0);
  }
  // The stored result, derived on the server: hard assertions.
  const placed = server.jobs.reduce((n, j) => n + (j.metrics.placed || 0), 0);
  expect(server.count, 'stored placements = placed by the runs').toBe(placed);
  expect(server.count, 'every demand of plan P placed').toBe(Object.values(POSITIVE_SCOPES).reduce((a, b) => a + b, 0));
  expect(server.collisions, 'no teacher, room, group, cohort or other resource in two places at once').toEqual([]);
  expect(server.overflow, 'no activity overflows the day').toEqual([]);
  expect(server.restricted_cells.filter((r) => r.kind === 'activity_type').length, 'LAB restricted at slot 6 of five days').toBe(5);
  expect(server.restricted_cells.filter((r) => r.occupied_by.length), 'no restricted cell occupied').toEqual([]);
  const withResource = server.placements.filter((x) => x.resources.length);
  expect(withResource.map((x) => `${x.subject}/${x.type}/${x.group}:${x.resources.join('+')}`), 'the spectrometer is placed with the analytical chemistry lab; the last group (fume hood) never is').toEqual(['ANC/LAB/GH4A:SPC']);
  save({ positive: { plan: p.plan, period: p.period } });

  // The plan timetable: both scopes final, none draft.
  await page.goto(`/ancora/planning/${p.plan}/timetable?period=${p.period}`);
  const labels = await scopeLabels(page);
  step.data.timetable_labels = labels;
  expect(labels.map((l) => l.timetable).sort(), 'both scopes labelled final').toEqual(['final', 'final']);
  await shot(page, 'j10-timetable');
  // The other resource's timetable, from the explorer.
  await explore(page, p, 'resource', ids().resource.SPC, 'Spectrometer', 1);
  await shot(page, 'j10-explorer-resource');
});

// ------------------------------------------------- UI coverage of the fixture

/** Opens a record's edit form, fills fields, saves, and checks no error. */
async function editRecord(page, segment, id, fields) {
  await page.goto(`/admin/ancora/${segment}/${id}/edit`);
  for (const [selector, value] of Object.entries(fields)) {
    if (typeof value === 'boolean') {
      await page.locator(selector).setChecked(value);
    }
    else {
      await page.fill(selector, value);
    }
  }
  await submit(page, 'Save');
  await noError(page);
  await expect(page.locator('body')).toContainText('Updated the');
}

test('11 the fixture\'s record types are created and edited through the UI', async ({ page }) => {
  test.setTimeout(300000);
  const s = requires('plan', 'assignments', 'generated');
  begin('11 UI coverage of the fixture\'s record types', 'ui: catalogue section "Add …" links, edit forms, assignment editor, template resources, restriction grid, daily load limits (as planner); server: fixture show and inspect');
  countLoads(page);
  await login(page, 'planner');
  const r = ids();
  const show = (type, id) => fixture('show', type, String(id));
  const covered = {};

  // Created through the UI: the catalogue types that only the fixture
  // creates in the journey (specialty, level, cohort, subject).
  const addFromCatalogues = async (label, fields, selects = {}) => {
    await page.goto('/ancora/planning/catalogues');
    await link(page, label);
    for (const [id, value] of Object.entries(fields)) {
      await page.fill(id, value);
    }
    for (const [id, value] of Object.entries(selects)) {
      await page.selectOption(id, String(value));
    }
    await submit(page, 'Save');
    await noError(page);
  };
  await addFromCatalogues('Add specialty', { '#edit-label-0-value': 'UI specialty', '#edit-code-0-value': 'UXS' });
  await addFromCatalogues('Add level', { '#edit-label-0-value': 'UI level', '#edit-code-0-value': 'UXL' });
  const uxs = fixture('find', 'ancora_specialty', 'UXS').id;
  const uxl = fixture('find', 'ancora_level', 'UXL').id;
  await addFromCatalogues('Add cohort', { '#edit-label-0-value': 'UI cohort', '#edit-code-0-value': 'UXC', '#edit-enrollment-0-value': '15' }, { '#edit-specialty': uxs, '#edit-level': uxl });
  await addFromCatalogues('Add subject', { '#edit-label-0-value': 'UI subject', '#edit-code-0-value': 'UXJ' }, { '#edit-specialty': uxs, '#edit-level': uxl });
  expect(show('ancora_specialty', uxs)).toMatchObject({ code: 'UXS', label: 'UI specialty' });
  expect(show('ancora_level', uxl)).toMatchObject({ code: 'UXL', label: 'UI level' });
  expect(show('ancora_cohort', fixture('find', 'ancora_cohort', 'UXC').id)).toMatchObject({ code: 'UXC', specialty: uxs, level: uxl, enrollment: 15 });
  expect(show('ancora_subject', fixture('find', 'ancora_subject', 'UXJ').id)).toMatchObject({ code: 'UXJ', specialty: uxs, level: uxl });
  Object.assign(covered, { specialty: 'created', level: 'created', cohort: 'created', subject: 'created' });

  const t = r.template;
  // Teacher and room assignments of a fixture-created template: the
  // assignment editor (priority of ANA 2 -> 1, of Room 101 2 -> 1; the
  // editor offers 1..max + 1, or 1..max when the priorities are 1..n).
  await openEditor(page, s, t['Calculus II #1 (JW)'], 'Calculus II #1 (JW)');
  for (const label of ['Ana Example', 'Room 101']) {
    await page.getByLabel(`Priority of ${label}`, { exact: true }).selectOption('1');
    await press(page, `Set the priority of ${label}`);
  }
  const calEligibility = fixture('inspect', 'J').eligibility['Calculus II #1 (JW)'];
  expect(calEligibility.teachers).toContain('ANA:1:GML');
  expect(calEligibility.rooms).toContain('R101:1');
  covered.teacher_assignment = 'edited';
  covered.room_assignment = 'edited';

  // The resource group of STA's first lab (fixture): not essential any
  // more, and a second member added, through the template resources page.
  expect(t['Statistics #3 (JW)'], 'STA\'s first lab').toBeTruthy();
  await page.goto(`/ancora/planning/template-resources?template=${t['Statistics #3 (JW)']}`);
  const group1 = page.locator('details', { hasText: 'Group 1' });
  await group1.getByLabel('Essential').uncheck();
  await group1.getByLabel('Spectrometer').check();
  await press(page, 'Save group 1');
  const staGroup = fixture('inspect', 'J').resource_groups.find((g) => g.template === 'Statistics #3 (JW)');
  expect(staGroup).toMatchObject({ essential: false });
  expect(staGroup.members.sort()).toEqual(['PRJ', 'SPC']);
  covered.resource_group = 'edited';
  covered.resource_member = 'created';

  // Edited through the edit forms: one fixture-created record of each type.
  const calTemplate = t['Calculus II #3 (JW)'];
  expect(calTemplate, 'a fixture-created template without placements (MA/Y2 failed)').toBeTruthy();
  const edits = [
    ['ancora_activity_type', 'activity-types', r.type.LEC, { '#edit-label-0-value': 'Lecture (edited)' }, { label: 'Lecture (edited)' }],
    ['ancora_specialty', 'specialties', r.specialty.MA, { '#edit-label-0-value': 'Mathematics (edited)' }, { label: 'Mathematics (edited)' }],
    ['ancora_level', 'levels', r.level.Y2, { '#edit-label-0-value': 'Year 2 (edited)' }, { label: 'Year 2 (edited)' }],
    ['ancora_cohort', 'cohorts', r.cohort.M2B, { '#edit-enrollment-0-value': '22' }, { enrollment: 22 }],
    ['ancora_teacher', 'teachers', r.teacher.CAR, { '#edit-label-0-value': 'Carla Example (edited)' }, { label: 'Carla Example (edited)' }],
    ['ancora_room', 'rooms', r.room.LABA, { '#edit-capacity-0-value': '32' }, { capacity: 32 }],
    ['ancora_other_resource', 'other-resources', r.resource.FUM, { '#edit-label-0-value': 'Fume hood (edited)' }, { label: 'Fume hood (edited)' }],
    ['ancora_student_group', 'groups', r.group.G2B, { '#edit-label-0-value': 'Lab group M2B (edited)' }, { label: 'Lab group M2B (edited)' }],
    ['ancora_activity_template', 'templates', calTemplate, { '#edit-requires-room-value': false }, { requires_room: 0 }],
    ['ancora_activity_template', 'templates', calTemplate, { '#edit-requires-room-value': true }, { requires_room: 1 }],
    ['ancora_plan', 'plans', r.plan.K, { '#edit-label-0-value': 'Journey intensive K (edited)' }, { label: 'Journey intensive K (edited)' }],
    ['ancora_period', 'periods', r.period.KW, { '#edit-label-0-value': 'Intensive week (edited)' }, { label: 'Intensive week (edited)' }],
    ['ancora_subject', 'subjects', r.subject.CAL, { '#edit-label-0-value': 'Calculus II (edited)' }, { label: 'Calculus II (edited)' }],
  ];
  for (const [type, segment, id, fields, stored] of edits) {
    await editRecord(page, segment, id, fields);
    expect(show(type, id), `${type} ${id} as stored after the edit`).toMatchObject(stored);
    covered[type.replace('ancora_', '')] = covered[type.replace('ancora_', '')] || 'edited';
  }

  // Time restrictions (the grid): fixture-created period and room cells of
  // JW removed; period, activity-type and room cells created in plan K.
  const grid = async (period, kind, target, cells) => {
    await page.goto(`/ancora/planning/restrictions?period=${period}&kind=${kind}${target ? `&target=${target}` : ''}`);
    for (const [cell, on] of cells) {
      await page.getByLabel(`Day ${cell[0]}, slot ${cell[1]} restricted`, { exact: true }).setChecked(on);
    }
    await press(page, 'Save restrictions');
  };
  await grid(s.period, 'period', null, [[[5, 6], false]]);
  await grid(s.period, 'room', r.room.AUD, [[[3, 6], false]]);
  const kw = r.period.KW;
  await grid(kw, 'period', null, [[[5, 6], true]]);
  await grid(kw, 'activity_type', r.type.LAB, [[[1, 6], true]]);
  await grid(kw, 'room', r.room.R101, [[[4, 1], true]]);
  const jCells = fixture('inspect', 'J').restricted_cells;
  expect(jCells.filter((c) => c.kind === 'period' || c.kind === 'room'), 'the fixture\'s period and room cells of JW removed').toEqual([]);
  const kCells = fixture('inspect', 'K').restricted_cells.map((c) => `${c.kind}:${c.cell}`).sort();
  expect(kCells).toEqual([`activity_type:${kw}.1.6`, `period:${kw}.5.6`, `room:${kw}.4.1`].sort());
  covered.time_restriction = 'created and edited';

  // Daily load limits: the fixture's cohort limit of M2A 4 -> 5; a new
  // cohort limit for I1A in plan K.
  await page.goto(`/ancora/planning/load-limits?period=${s.period}`);
  await page.getByLabel('Maximum daily load of Cohort M2A', { exact: true }).fill('5');
  await press(page, 'Save');
  await page.goto(`/ancora/planning/load-limits?period=${kw}`);
  await page.getByLabel('Maximum daily load of Cohort I1A', { exact: true }).fill('4');
  await press(page, 'Save');
  expect(fixture('inspect', 'J').loads).toContainEqual(expect.objectContaining({ kind: 'cohort', object: 'M2A', max_daily_load: 5 }));
  expect(fixture('inspect', 'K').loads).toContainEqual(expect.objectContaining({ kind: 'cohort', object: 'I1A', max_daily_load: 4 }));
  covered.load_limit = 'created and edited';
  step.data.covered = covered;
  await shot(page, 'j11-load-limits');
});
