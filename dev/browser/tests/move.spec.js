// S1b (#141): move and undo in place in the workspace, with the real worker.
// Every state is scanned by axe and photographed; the checks' latency in
// the workspace is recorded (move-latency.json), not asserted.
const { test } = require('@playwright/test');
const { ids, login, axe, shot, record, workspace, shown, expect } = require('./support');

const p = ids.planner;
const latency = [];

const settled = async (page) => {
  await page.waitForFunction(() => !document.querySelector('[data-ancora-workspace][aria-busy]'));
};

async function clean(page, label) {
  expect(await axe(page, label), `axe: ${label}`).toEqual([]);
}

/** Presses a step of the move form and waits for its regions. */
async function step(page, locator) {
  await locator.click();
  await settled(page);
}

/** Finds the destinations; returns when the overlay is drawn. */
async function findDestinations(page, label, button = 'Find the valid destinations') {
  const t0 = Date.now();
  await step(page, page.locator(`#ancora-move-form input[type="submit"][value="${button}"]`));
  // Progress first: queued or running, with the time waited.
  const progress = page.locator('[data-ancora-check-pending]');
  if (await progress.count()) {
    await expect(progress).toHaveAttribute('data-ancora-check-state', /queued|running/);
    await expect(progress.locator('[data-ancora-elapsed]')).toContainText(' s');
    await shot(page, `${label}-progress`);
    await clean(page, `${label}: progress`);
  }
  await page.waitForSelector('#ancora-canvas-moving', { timeout: 60000 }).catch(async () => {
    console.log('NO OVERLAY', label, page.url(), await page.locator('#ancora-move-form').getAttribute('action'));
  });
  await expect(page.locator('#ancora-canvas-moving')).toBeVisible({ timeout: 1000 });
  await settled(page);
  latency.push({ label, ms: Date.now() - t0 });
  await expect(page.locator('#ancora-move-grid')).toBeFocused();
}

test.afterAll(() => {
  record('move-latency', { note: 'Click on the check button to the destinations drawn, in the workspace (1 s poll).', samples: latency });
});

test('T1 in place: teacher, move with a cross-plan warning, undo; one full page load (U1)', async ({ page }) => {
  await login(page, 'planner');
  let loads = 0;
  page.on('load', () => {
    loads++;
  });
  await page.goto(workspace());
  await page.click('.ancora-wsnav__lenses a:text("Teachers")');
  await settled(page);
  await page.click('.ancora-wsnav__list a:has-text("Teacher TC")');
  await settled(page);
  await expect(page.locator('.ancora-wsbar__cross')).toContainText('One cross-plan overlap in this period.');
  await page.click(`[data-ancora-placement="${p.A4}"]`);
  await settled(page);
  await expect(page.locator('.ancora-inspector__move')).toBeVisible();
  await shot(page, 's1b-01-inspector');
  await clean(page, 'inspector with the move');

  await findDestinations(page, 's1b-02');
  // The overlay: the current cell, refused cells with reasons, and the
  // cross-plan warning where room L2 would meet B1 (day 2, slot 2).
  await expect(page.locator('.ancora-dest.is-here')).toHaveAttribute('data-ancora-dest', '4.1');
  await expect(page.locator('.ancora-dest.is-refused:not(.is-here)').first()).toContainText('Not a destination');
  const warned = page.locator('.ancora-dest[data-ancora-dest="2.2"]');
  await expect(warned).toHaveClass(/is-valid/);
  await expect(warned).toContainText('room Room L2 busy in plan Intensive B, period Week B on 2026-09-15 10:00–10:30');
  await shot(page, 's1b-03-overlay');
  await clean(page, 'overlay');

  await step(page, warned);
  await expect(page.locator('#ancora-move-preview')).toContainText('to day 2, slot 2');
  await expect(page.locator('#ancora-move-preview')).toContainText('Cross-plan warnings (the move is still allowed)');
  await expect(page.locator('#ancora-move-preview .button--primary')).toBeFocused();
  await expect(page.locator('.ancora-dest.is-preview')).toHaveAttribute('data-ancora-dest', '2.2');
  await shot(page, 's1b-04-preview');
  await clean(page, 'preview');

  await step(page, page.locator('#ancora-move-preview .button--primary'));
  await expect(page.locator('#ancora-move-feedback')).toContainText('Moved it to day 2, slot 2.');
  await expect(page.locator('#ancora-move-feedback')).toBeFocused();
  await expect(page.locator('[data-ancora-status]')).toContainText('Moved it to day 2, slot 2.');
  // The canvas, the inspector and the warnings, refreshed in place.
  await expect(page.locator(`[data-ancora-placement="${p.A4}"]`)).toContainText('10:00–11:30');
  await expect(page.locator(`[data-ancora-placement="${p.A4}"]`)).toContainText('1 cross-plan warning');
  await expect(page.locator('.ancora-wsbar__cross')).toContainText('2 cross-plan overlaps in this period.');
  await expect(page.locator('.ancora-inspector__conflicts')).toContainText('Room Room L2');
  await expect(page.locator('#ancora-canvas-moving')).toHaveCount(0);
  await shot(page, 's1b-05-moved');
  await clean(page, 'moved');

  // Undo: a new check, only the origin is offered, then back.
  await findDestinations(page, 's1b-06-undo', 'Undo the last move');
  await expect(page.locator('.ancora-dest.is-valid')).toHaveCount(1);
  await expect(page.locator('.ancora-dest.is-valid')).toHaveAttribute('data-ancora-dest', '4.1');
  await shot(page, 's1b-07-undo-overlay');
  await clean(page, 'undo overlay');
  await step(page, page.locator('.ancora-dest.is-valid'));
  await expect(page.locator('#ancora-move-preview .button--primary')).toHaveValue('Move it back here');
  await step(page, page.locator('#ancora-move-preview .button--primary'));
  await expect(page.locator('#ancora-move-feedback')).toContainText('Moved it back to day 4, slot 1.');
  await expect(page.locator('.ancora-wsbar__cross')).toContainText('One cross-plan overlap in this period.');
  await shot(page, 's1b-08-undone');
  await clean(page, 'undone');
  expect(loads, 'full page loads in T1 (U1)').toBe(1);

  // The URL keeps the context; a reload shows the same state.
  await expect(page).toHaveURL(new RegExp(`lens=teacher&obj=${p.TC}&sel=${p.A4}$`));
  const before = await shown(page);
  await page.reload();
  expect(await shown(page)).toEqual(before);
});

test('A 2-slot activity across the break: moved and undone as one block', async ({ page }) => {
  await login(page, 'planner');
  await page.goto(workspace({ lens: 'teacher', obj: p.TA, sel: p.A2 }));
  await findDestinations(page, 's1b-09-multislot');
  // Each destination is a start slot: day 5 slot 1 keeps the break inside.
  await step(page, page.locator('.ancora-dest[data-ancora-dest="5.1"]'));
  await step(page, page.locator('#ancora-move-preview .button--primary'));
  const a2 = page.locator(`[data-ancora-placement="${p.A2}"]`);
  await expect(a2).toHaveCount(1);
  await expect(a2).toContainText('08:00–11:30');
  await expect(a2).toContainText('break 09:30–10:00');
  await expect(a2).toHaveAttribute('style', /--r1: 2; --r2: 5;/);
  const day = await a2.evaluate((el) => el.closest('.ancora-canvas__day').querySelector('h3').textContent);
  expect(day).toBe('Day 5');
  await shot(page, 's1b-10-multislot-moved');
  await findDestinations(page, 's1b-11-multislot-undo', 'Undo the last move');
  await step(page, page.locator('.ancora-dest.is-valid'));
  await step(page, page.locator('#ancora-move-preview .button--primary'));
  await expect(page.locator('#ancora-move-feedback')).toContainText('Moved it back to day 1, slot 1.');
  expect(await a2.evaluate((el) => el.closest('.ancora-canvas__day').querySelector('h3').textContent)).toBe('Day 1');
});

test('Keyboard only: find, choose, preview and move, then undo', async ({ page }) => {
  await login(page, 'planner');
  await page.goto(workspace({ lens: 'teacher', obj: p.TB, sel: p.A3 }));
  const focusOn = async (selector, key = 'Tab') => {
    for (let i = 0; i < 80; i++) {
      if (await page.locator(selector).evaluate((el) => el === document.activeElement).catch(() => false)) {
        return;
      }
      await page.keyboard.press(key);
    }
    throw new Error(`cannot reach ${selector}`);
  };
  await focusOn('#ancora-move-form input[value="Find the valid destinations"]');
  await page.keyboard.press('Enter');
  await expect(page.locator('#ancora-canvas-moving')).toBeVisible({ timeout: 60000 });
  await settled(page);
  await expect(page.locator('#ancora-move-grid')).toBeFocused();
  // The list alternative in the inspector: the first valid cell's button.
  const first = await page.locator('#ancora-move-grid input.ancora-move__cell:not([disabled])').first().getAttribute('name');
  await focusOn(`#ancora-move-grid input[name="${first}"]`);
  await page.keyboard.press('Enter');
  await settled(page);
  await expect(page.locator('#ancora-move-preview .button--primary')).toBeFocused();
  await page.keyboard.press('Enter');
  await settled(page);
  await expect(page.locator('#ancora-move-feedback')).toBeFocused();
  await expect(page.locator('#ancora-move-feedback')).toContainText('Moved it to day');
  await focusOn('#ancora-move-form input[value="Undo the last move"]');
  await page.keyboard.press('Enter');
  await expect(page.locator('#ancora-canvas-moving')).toBeVisible({ timeout: 60000 });
  await settled(page);
  await focusOn('#ancora-move-grid input.ancora-move__cell:not([disabled])');
  await page.keyboard.press('Enter');
  await settled(page);
  await page.keyboard.press('Enter');
  await settled(page);
  await expect(page.locator('#ancora-move-feedback')).toContainText('Moved it back to day 3, slot 3.');
  await clean(page, 'keyboard undo');
});

test('Narrow screens: the drawer at 1024 px and the list at 390 px, no overflow', async ({ page }) => {
  await login(page, 'planner');
  await page.setViewportSize({ width: 1024, height: 768 });
  await page.goto(workspace({ lens: 'teacher', obj: p.TB, sel: p.A3 }));
  await findDestinations(page, 's1b-18-drawer');
  expect(await page.evaluate(() => getComputedStyle(document.querySelector('.ancora-inspector')).position)).toBe('fixed');
  await shot(page, 's1b-18-drawer-1024');
  await clean(page, 'drawer with destinations');
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto(page.url());
  await expect(page.locator('.ancora-dest.is-valid').first()).toBeVisible();
  await expect(page.locator('.ancora-dest.is-refused').first()).toBeHidden();
  expect(await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth)).toBeLessThanOrEqual(0);
  await shot(page, 's1b-19-list-390');
  await clean(page, 'list at 390 px');
});

test('Without JavaScript: the same steps as full pages, in the workspace', async ({ browser }) => {
  const context = await browser.newContext({ javaScriptEnabled: false });
  const page = await context.newPage();
  await login(page, 'planner');
  await page.goto(workspace({ lens: 'teacher', obj: p.TC, sel: p.A4 }));
  await page.click('#ancora-move-form input[value="Find the valid destinations"]');
  await expect(page).toHaveURL(/\/workspace\?.*sel=\d+&check=\d+/);
  for (let i = 0; i < 60 && (await page.locator('[data-ancora-check-pending]').count()); i++) {
    await page.waitForTimeout(1000);
    await page.click('[data-ancora-check-pending] a:text("Refresh")');
  }
  await expect(page.locator('#ancora-canvas-moving')).toBeVisible();
  await page.click('.ancora-dest[data-ancora-dest="5.1"]');
  await page.click('#ancora-move-preview .button--primary');
  await expect(page).toHaveURL(new RegExp(`/workspace\\?.*lens=teacher&obj=${p.TC}&sel=${p.A4}`));
  await expect(page.locator('#ancora-move-feedback')).toContainText('Moved it to day 5, slot 1.');
  await page.click('#ancora-move-form input[value="Undo the last move"]');
  for (let i = 0; i < 60 && (await page.locator('[data-ancora-check-pending]').count()); i++) {
    await page.waitForTimeout(1000);
    await page.click('[data-ancora-check-pending] a:text("Refresh")');
  }
  await page.click('.ancora-dest.is-valid');
  await page.click('#ancora-move-preview .button--primary');
  await expect(page.locator('#ancora-move-feedback')).toContainText('Moved it back to day 4, slot 1.');
  await shot(page, 's1b-12-no-javascript');
  await context.close();
});

test('A stale check: refused with a clear message, and a fresh check at once', async ({ browser }) => {
  const one = await browser.newContext();
  const page = await one.newPage();
  await login(page, 'planner');
  await page.goto(workspace({ lens: 'teacher', obj: p.TC, sel: p.A4 }));
  await findDestinations(page, 's1b-13-stale');
  await step(page, page.locator('.ancora-dest[data-ancora-dest="5.1"]'));
  // Meanwhile, in another tab, another activity is moved: the input of
  // every check changes.
  const two = await one.newPage();
  await two.goto(workspace({ lens: 'teacher', obj: p.TB, sel: p.A3 }));
  await findDestinations(two, 's1b-14-other-tab');
  await step(two, two.locator('.ancora-dest.is-valid').first());
  await step(two, two.locator('#ancora-move-preview .button--primary'));
  await expect(two.locator('#ancora-move-feedback')).toContainText('Moved it to day');
  // The first tab's move is refused, and a new check starts.
  await step(page, page.locator('#ancora-move-preview .button--primary'));
  await expect(page.locator('#ancora-move-feedback[role="alert"]')).toContainText('changed after the destinations were checked');
  await expect(page.locator('#ancora-move-feedback')).toContainText('A new check of the destinations has started.');
  await expect(page.locator('#ancora-move-feedback')).toBeFocused();
  await shot(page, 's1b-15-stale-refused');
  await clean(page, 'stale refusal');
  await expect(page.locator('#ancora-canvas-moving')).toBeVisible({ timeout: 60000 });
  await settled(page);
  // Put the other activity back.
  await findDestinations(two, 's1b-16-other-undo', 'Undo the last move');
  await step(two, two.locator('.ancora-dest.is-valid'));
  await step(two, two.locator('#ancora-move-preview .button--primary'));
  await expect(two.locator('#ancora-move-feedback')).toContainText('Moved it back');
  await one.close();
});

test('Two users: no access to the other planner\'s move flow, partials or checks', async ({ browser }) => {
  const planner = await browser.newContext();
  const page = await planner.newPage();
  await login(page, 'planner');
  await page.goto(workspace({ lens: 'teacher', obj: p.TC, sel: p.A4 }));
  await findDestinations(page, 's1b-17-isolation');
  const check = new URL(page.url()).searchParams.get('check');
  expect(check).toMatch(/^\d+$/);
  const url = new URL(page.url());
  const other = await browser.newContext();
  const intruder = await other.newPage();
  await login(intruder, 'other');
  // The planner's workspace, its regions and its move steps: refused.
  expect((await intruder.request.get(url.pathname + url.search)).status()).toBe(403);
  expect((await intruder.request.get(url.pathname.replace(/\/workspace$/, '/workspace/regions') + url.search)).status()).toBe(403);
  expect((await intruder.request.post(url.pathname + url.search, { form: { op_check: 'Find the valid destinations', form_id: 'ancora_scheduling_placement_move' } })).status()).toBe(403);
  expect((await intruder.request.get(`/ancora/timetables/placements/${p.A4}/move?check=${check}`)).status()).toBe(403);
  // The planner's check id in the other planner's own workspace: no check.
  const own = await intruder.request.get(`/ancora/planning/${ids.other['plan:A']}/workspace/regions?sel=${ids.other.A1}&check=${check}`);
  expect(own.status()).toBe(200);
  const text = await own.text();
  expect(text).not.toContain('ancora-canvas-moving');
  expect(text).toContain('Find the valid destinations');
  // A viewer and an anonymous visitor: refused.
  const viewer = await browser.newContext();
  const vpage = await viewer.newPage();
  await login(vpage, 'viewer');
  expect((await vpage.request.get(url.pathname + url.search)).status()).toBe(403);
  await viewer.close();
  const anonymous = await browser.newContext();
  expect((await (await anonymous.newPage()).request.get(url.pathname + url.search)).status()).toBe(403);
  await anonymous.close();
  // A forged step without a valid form token changes nothing.
  const forged = await page.request.post(url.pathname + url.search, { form: { op_commit_5_1: 'Move here', form_id: 'ancora_scheduling_placement_move', form_build_id: 'form-forged', form_token: 'forged' } });
  expect(forged.status()).toBe(200);
  await page.goto(workspace({ lens: 'teacher', obj: p.TC, sel: p.A4 }));
  await expect(page.locator(`[data-ancora-placement="${p.A4}"]`)).toContainText('08:00–09:30');
  await planner.close();
  await other.close();
});
