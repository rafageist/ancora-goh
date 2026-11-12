// The S1a planning workspace (#141) in a real browser: the read-only
// journey with JavaScript, without it and with the keyboard only, narrow
// screens, axe in every state, access and the legacy routes.
const { test } = require('@playwright/test');
const { ids, login, axe, shot, workspace, shown, expect } = require('./support');

const p = ids.planner;

/** Waits until no region is loading. */
async function settled(page) {
  await page.waitForFunction(() => !document.querySelector('[data-ancora-workspace][aria-busy]'));
}

/** Asserts axe finds no serious or critical violation. */
async function clean(page, label) {
  expect(await axe(page, label), `axe: ${label}`).toEqual([]);
}

test('Resume, then the read-only journey in place (JavaScript)', async ({ page }) => {
  await login(page, 'planner');
  // Opening a workspace records it for "Resume".
  await page.goto(workspace({ period: p['period:A'] }));
  await page.goto('/ancora');
  const resume = page.locator('a[data-ancora-resume]');
  await expect(resume).toHaveText('Resume Autumn A, Week A');
  await shot(page, '01-overview-resume');
  await clean(page, 'overview');

  let loads = 0;
  page.on('load', () => {
    loads++;
  });
  await resume.click();
  await page.waitForURL(/\/workspace\?period=/);
  await settled(page);
  await shot(page, '02-workspace-all');
  await clean(page, 'workspace: all activities');

  // The teacher perspective, then Teacher TA: in place.
  await page.click('.ancora-wsnav__lenses a:text("Teachers")');
  await settled(page);
  await page.click('.ancora-wsnav__list a:has-text("Teacher TA")');
  await settled(page);
  await expect(page.locator('#ancora-canvas-title')).toHaveText('Teacher TA: 2 activities');
  await expect(page).toHaveURL(new RegExp(`period=${p['period:A']}&lens=teacher&obj=${p.TA}$`));

  // A2 is ONE block across slots 1-2 and the break between them.
  const a2 = page.locator(`[data-ancora-placement="${p.A2}"]`);
  await expect(a2).toHaveCount(1);
  await expect(a2).toContainText('08:00–11:30');
  await expect(a2).toContainText('break 09:30–10:00');
  await expect(a2.locator('.ancora-block__break')).toHaveCount(1);
  const geometry = await page.evaluate((id) => {
    const block = document.querySelector(`[data-ancora-placement="${id}"]`).getBoundingClientRect();
    const day = block.x + 5;
    const cells = [...document.querySelectorAll('.ancora-canvas__day:first-of-type .ancora-canvas__cell')].map((c) => c.getBoundingClientRect());
    return { top: block.top, bottom: block.bottom, rows: cells.slice(0, 3).map((c) => [c.top, c.bottom]), day };
  }, p.A2);
  // From the top of slot 1 to the bottom of slot 2 (the break row between).
  expect(Math.abs(geometry.top - geometry.rows[0][0])).toBeLessThan(4);
  expect(Math.abs(geometry.bottom - geometry.rows[2][1])).toBeLessThan(4);
  // A1 has one cross-plan warning (teacher TA in plan B on 2026-09-15).
  const a1 = page.locator(`[data-ancora-placement="${p.A1}"]`);
  await expect(a1).toContainText('1 cross-plan warning');
  await shot(page, '03-teacher-ta');
  await clean(page, 'teacher perspective');

  // The inspector: read-only details and the warning.
  await a1.click();
  await settled(page);
  await expect(page.locator('#ancora-inspector-title')).toBeFocused();
  await expect(page.locator('.ancora-inspector__conflicts')).toContainText('in plan Intensive B, period Week B, day 2, slot 2, on 2026-09-15 10:00–10:30');
  await expect(page.locator('.ancora-inspector')).toContainText('Day 2, slot 2 (10:00–11:30)');
  // Its only form is the move of #130 (S1b), for those who may move it.
  await expect(page.locator('.ancora-inspector form')).toHaveCount(1);
  await expect(page.locator('.ancora-inspector form#ancora-move-form')).toHaveCount(1);
  await shot(page, '04-inspector-a1');
  await clean(page, 'inspector');

  // The same activity from the room's perspective: selection kept.
  await page.click('.ancora-inspector__pivots a:has-text("Room L1")');
  await settled(page);
  await expect(page.locator('#ancora-canvas-title')).toHaveText('Room L1: 2 activities');
  await expect(page.locator('.ancora-block.is-selected')).toHaveAttribute('data-ancora-placement', String(p.A1));
  expect(loads, 'no full page load since the workspace opened').toBe(1);

  // Reload, back and forward restore everything.
  const before = await shown(page);
  await page.reload();
  expect(await shown(page)).toEqual(before);
  await page.goBack();
  await settled(page);
  await expect(page.locator('#ancora-canvas-title')).toHaveText('Teacher TA: 2 activities');
  await expect(page.locator('.ancora-block.is-selected')).toHaveAttribute('data-ancora-placement', String(p.A1));
  await page.goForward();
  await settled(page);
  expect(await shown(page)).toEqual(before);
  await clean(page, 'after history');
});

test('An undated plan: "not evaluated" on the bar, the blocks and the inspector', async ({ page }) => {
  await login(page, 'planner');
  await page.goto(workspace({ sel: p.U1 }, p['plan:U']));
  await expect(page.locator('.ancora-wsbar__cross')).toContainText('Cross-plan conflicts not evaluated: dates unknown (the plan has no first day).');
  await expect(page.locator(`[data-ancora-placement="${p.U1}"]`)).toContainText('cross-plan conflicts not evaluated');
  await expect(page.locator('.ancora-inspector')).toContainText('Dates unknown / cross-plan conflicts not evaluated');
  await expect(page.locator('.ancora-inspector')).not.toContainText('No overlap found');
  await shot(page, '05-undated');
  await clean(page, 'undated plan');
});

test('Without JavaScript: the same state through plain links', async ({ browser }) => {
  const context = await browser.newContext({ javaScriptEnabled: false });
  const page = await context.newPage();
  await login(page, 'planner');
  await page.goto(workspace());
  await page.click('.ancora-wsnav__lenses a:text("Teachers")');
  await page.click('.ancora-wsnav__list a:has-text("Teacher TA")');
  await expect(page).toHaveURL(new RegExp(`lens=teacher&obj=${p.TA}$`));
  await page.click(`[data-ancora-placement="${p.A2}"]`);
  await expect(page).toHaveURL(new RegExp(`sel=${p.A2}$`));
  await expect(page.locator('.ancora-inspector')).toContainText('Break inside');
  // The filter needs the script, so it stays hidden.
  await expect(page.locator('[data-ancora-filter]')).toBeHidden();
  await page.click('.ancora-inspector__close');
  await expect(page).not.toHaveURL(/sel=/);
  await shot(page, '06-no-javascript');
  const url = page.url();
  await context.close();
  // axe runs in the page, which a context without JavaScript cannot do: scan
  // the same URL with every script request refused instead (the same
  // server markup, no enhancement).
  const scan = await browser.newContext();
  await scan.route('**/*', (route) => (route.request().resourceType() === 'script' ? route.abort() : route.continue()));
  const plain = await scan.newPage();
  await login(plain, 'planner');
  await plain.goto(url);
  await expect(plain.locator('[data-ancora-filter]')).toBeHidden();
  await clean(plain, 'no javascript');
  await scan.close();
});

test('Keyboard only: perspective, record, roving focus, inspector, Escape', async ({ page }) => {
  await login(page, 'planner');
  await page.goto(workspace());
  const tabTo = async (locator, key = 'Tab') => {
    for (let i = 0; i < 60; i++) {
      await page.keyboard.press(key);
      if (await locator.evaluate((element) => element === document.activeElement)) {
        return;
      }
    }
    throw new Error(`no focus on ${await locator.evaluate((element) => element.outerHTML)}`);
  };
  await tabTo(page.locator('#ancora-workspace-nav [data-ancora-nav="lens"]').filter({ hasText: /^Teachers/ }));
  await page.keyboard.press('Enter');
  await settled(page);
  await expect(page.locator('#ancora-canvas-title')).toBeFocused();
  await tabTo(page.locator('#ancora-workspace-nav [data-ancora-nav="record"]').filter({ hasText: /^Teacher TA/ }), 'Shift+Tab');
  await page.keyboard.press('Enter');
  await settled(page);
  await expect(page.locator('#ancora-canvas-title')).toBeFocused();
  // One tab stop in the canvas, then the arrows.
  await page.keyboard.press('Tab');
  await expect(page.locator(`[data-ancora-placement="${p.A2}"]`)).toBeFocused();
  await page.keyboard.press('ArrowRight');
  await expect(page.locator(`[data-ancora-placement="${p.A1}"]`)).toBeFocused();
  await page.keyboard.press('Enter');
  await settled(page);
  await expect(page.locator('#ancora-inspector-title')).toBeFocused();
  await expect(page.locator('#ancora-inspector-title')).toContainText('Mathematics');
  await page.keyboard.press('Escape');
  await settled(page);
  await expect(page.locator(`[data-ancora-placement="${p.A1}"]`)).toBeFocused();
  await expect(page).not.toHaveURL(/sel=/);
  await clean(page, 'keyboard');
});

test('Narrow screens: a drawer, and the canvas as a list without overflow', async ({ page }) => {
  await login(page, 'planner');
  await page.setViewportSize({ width: 1024, height: 768 });
  await page.goto(workspace({ lens: 'teacher', obj: p.TA }));
  await page.click(`[data-ancora-placement="${p.A2}"]`);
  await settled(page);
  const drawer = await page.evaluate(() => getComputedStyle(document.querySelector('.ancora-inspector')).position);
  expect(drawer).toBe('fixed');
  await expect(page.locator('#ancora-inspector-title')).toBeFocused();
  await shot(page, '07-drawer-1024');
  await clean(page, 'drawer');
  await page.keyboard.press('Escape');
  await settled(page);
  await expect(page.locator(`[data-ancora-placement="${p.A2}"]`)).toBeFocused();

  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto(workspace({ lens: 'teacher', obj: p.TA }));
  expect(await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth)).toBeLessThanOrEqual(0);
  await shot(page, '08-narrow-390');
  await clean(page, 'narrow');
});

test('Access: viewer, another owner, anonymous and forged values', async ({ browser }) => {
  const plan = p['plan:A'];
  const regions = (query = '') => `/ancora/planning/${plan}/workspace/regions${query}`;
  const as = async (name) => {
    const context = await browser.newContext();
    const page = await context.newPage();
    if (name) {
      await login(page, name);
    }
    return { context, page };
  };

  // The viewer: no editing workspace; its own viewer page is unchanged.
  const viewer = await as('viewer');
  expect((await viewer.page.request.get(workspace())).status()).toBe(403);
  expect((await viewer.page.request.get(regions())).status()).toBe(403);
  expect((await viewer.page.request.get('/ancora/timetables/viewer')).status()).toBe(200);
  expect((await viewer.page.request.get(`/ancora/planning/${plan}/timetable`)).status()).toBe(403);
  await viewer.context.close();

  // Anonymous: refused.
  const anonymous = await as(null);
  expect((await anonymous.page.request.get(workspace())).status()).toBe(403);
  expect((await anonymous.page.request.get(regions())).status()).toBe(403);
  await anonymous.context.close();

  // Another owner: the planner's plan is refused; the same URL pattern of
  // its own plan shows only its own records (two-user isolation).
  const other = await as('other');
  const query = `?lens=teacher&obj=${p.TA}`;
  expect((await other.page.request.get(regions(query))).status()).toBe(403);
  const own = await other.page.request.get(`/ancora/planning/${ids.other['plan:A']}/workspace/regions?lens=teacher&obj=${ids.other.TA}`);
  expect(own.status()).toBe(200);
  const ownText = await own.text();
  expect(ownText).toContain('Teacher OTA');
  expect(ownText).not.toContain('Teacher TA<');
  await other.context.close();

  const planner = await as('planner');
  const mine = await planner.page.request.get(regions(query));
  expect(mine.status()).toBe(200);
  expect(await mine.text()).toContain('Teacher TA: 2 activities');
  // Forged or foreign values: 404, never another owner's data.
  for (const forged of [
    `?period=${ids.other['period:A']}`,
    '?period=abc',
    '?lens=resource',
    `?lens=teacher&obj=${ids.other.TA}`,
    `?obj=${p.TA}`,
    `?sel=${ids.other.A1}`,
    `?sel=${p.U1}`,
    '?sel[]=1',
  ]) {
    expect((await planner.page.request.get(workspace() + forged)).status(), forged).toBe(404);
    expect((await planner.page.request.get(regions(forged))).status(), forged).toBe(404);
  }
  // The legacy routes keep working.
  for (const url of [
    `/ancora/planning/${plan}/timetable?period=${p['period:A']}`,
    `/ancora/timetables/explorer?period=${p['period:A']}&teacher=${p.TA}`,
    `/ancora/timetables/placements/${p.A1}/move`,
    `/ancora/planning/${plan}`,
    '/ancora',
  ]) {
    expect((await planner.page.request.get(url)).status(), url).toBe(200);
  }
  await planner.context.close();
});
