// The #147 review cases (#141): Resume after an in-place period change, and
// overlapping navigations (delayed and out-of-order region responses,
// rapid lens and record changes, fast Back and Forward).
const { test } = require('@playwright/test');
const { ids, login, workspace, shown, expect } = require('./support');

const p = ids.planner;
const settled = async (page) => {
  await page.waitForFunction(() => !document.querySelector('[data-ancora-workspace][aria-busy]'));
};

/** Delays region responses whose URL matches, by the given milliseconds. */
async function delay(page, rules) {
  await page.route('**/workspace/regions**', async (route) => {
    const url = route.request().url();
    const rule = rules.find(([pattern]) => pattern.test(url));
    if (rule) {
      await new Promise((resolve) => setTimeout(resolve, rule[1]));
    }
    await route.continue().catch(() => {});
  });
}

/** The page agrees with a full load of its own URL. */
async function consistent(page, context) {
  const truth = await context.newPage();
  await truth.goto(page.url());
  const expected = await shown(truth);
  await truth.close();
  return { state: await shown(page), expected };
}

test('Resume offers the period last shown, also after an in-place change', async ({ page }) => {
  await login(page, 'planner');
  await page.goto(workspace({ period: p['period:A'] }));
  await page.click('.ancora-wsbar__periods a:text("Week A2")');
  await settled(page);
  await expect(page).toHaveURL(new RegExp(`period=${p['period:A2']}`));
  await page.goto('/ancora');
  await expect(page.locator('a[data-ancora-resume]')).toHaveText('Resume Autumn A, Week A2');
});

test('A slow older response never replaces a newer one', async ({ page, context }) => {
  await login(page, 'planner');
  await page.goto(workspace());
  // Teachers is slow; Rooms, clicked right after, is fast.
  await delay(page, [[/lens=teacher(&|$)/, 1500]]);
  await page.click('.ancora-wsnav__lenses a:text("Teachers")');
  await page.click('.ancora-wsnav__lenses a:text("Rooms")');
  await page.waitForTimeout(2500);
  await settled(page);
  await expect(page).toHaveURL(/lens=room/);
  const { state, expected } = await consistent(page, context);
  expect(state).toEqual(expected);
  await expect(page.locator('#ancora-canvas-title')).toBeFocused();
});

test('Rapid record changes: the last one wins', async ({ page, context }) => {
  await login(page, 'planner');
  await page.goto(workspace({ lens: 'teacher' }));
  await delay(page, [[new RegExp(`obj=${p.TA}(&|$)`), 1200], [new RegExp(`obj=${p.TB}(&|$)`), 600]]);
  for (const name of ['Teacher TA', 'Teacher TB', 'Teacher TC']) {
    await page.click(`.ancora-wsnav__list a:has-text("${name}")`);
  }
  await page.waitForTimeout(2000);
  await settled(page);
  await expect(page).toHaveURL(new RegExp(`obj=${p.TC}$`));
  await expect(page.locator('#ancora-canvas-title')).toHaveText('Teacher TC: 1 activity');
  const { state, expected } = await consistent(page, context);
  expect(state).toEqual(expected);
  // The history has one entry per click, in order: Back goes to TB.
  await page.goBack();
  await settled(page);
  await expect(page).toHaveURL(new RegExp(`obj=${p.TB}$`));
  await expect(page.locator('#ancora-canvas-title')).toHaveText('Teacher TB: 1 activity');
});

test('Fast Back and Forward: the page shows the URL it ends on', async ({ page, context }) => {
  await login(page, 'planner');
  await page.goto(workspace({ lens: 'teacher' }));
  for (const name of ['Teacher TA', 'Teacher TB', 'Teacher TC']) {
    await page.click(`.ancora-wsnav__list a:has-text("${name}")`);
    await settled(page);
  }
  // Going back to TB is slow; the second Back (to TA) is fast.
  await delay(page, [[new RegExp(`obj=${p.TB}(&|$)`), 1500]]);
  await page.evaluate(() => {
    history.back();
    setTimeout(() => history.back(), 100);
  });
  await page.waitForTimeout(2500);
  await settled(page);
  await expect(page).toHaveURL(new RegExp(`obj=${p.TA}$`));
  const { state, expected } = await consistent(page, context);
  expect(state).toEqual(expected);
  await page.evaluate(() => {
    history.forward();
    setTimeout(() => history.forward(), 100);
  });
  await page.waitForTimeout(2500);
  await settled(page);
  await expect(page).toHaveURL(new RegExp(`obj=${p.TC}$`));
  const after = await consistent(page, context);
  expect(after.state).toEqual(after.expected);
});
