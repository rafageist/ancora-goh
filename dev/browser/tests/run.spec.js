// Phase 3 slice A (FC-29, FC-31) in a real browser, with the real worker:
// plan page > Generate > the request form with the plan and period > the run
// page, which reloads itself until the run ends > Open in the workspace, on
// the same period. Then the same scope requested by hand, without
// JavaScript ("Refresh" by hand): the same input snapshot hash as the
// prefilled request and the site script's first run.
//
// It re-runs the E2E organization of "other" (#130), so it runs after
// latency.spec.js, which measures that organization's placements as the
// site script placed them (Playwright runs the files in name order, one
// worker).
const { test } = require('@playwright/test');
const { ids, login, axe, shot, record, expect } = require('./support');

const e = ids.e2e;
const TERMINAL = /Succeeded|Partial|Cancelled|Timed out|Failed/;

/** Asserts axe finds no serious or critical violation. */
async function clean(page, label) {
  expect(await axe(page, label), `axe: ${label}`).toEqual([]);
}

/** The value of a row of the run page's details table. */
async function detail(page, title) {
  const row = page.locator('table.ancora-scheduling-job tr', { has: page.locator(`td:text-is("${title}")`) });
  return (await row.locator('td').nth(1).textContent()).trim();
}

test('Generate from the plan page, follow the run, open the workspace', async ({ page, browser }) => {
  test.setTimeout(240000);
  await login(page, 'other');
  await page.goto(`/ancora/planning/${e.plan}`);
  await clean(page, 'plan page');

  let loads = 0;
  page.on('load', () => {
    loads++;
  });
  // The period row's "Generate": the request form with the plan and the
  // period; the planner picks the specialty and the level.
  await page.click(`a[href*="plan=${e.plan}&period=${e.period}"]:text-is("Generate")`);
  await page.waitForURL(/\/ancora\/scheduling\/request\?/);
  await expect(page.locator('select[name="plan"]')).toHaveValue(String(e.plan));
  await expect(page.locator('select[name="period"]')).toHaveValue(String(e.period));
  // The legacy option defaults are unchanged.
  await expect(page.locator('input[name="room_choice"][value="nearest"]')).toBeChecked();
  await expect(page.locator('input[name="forma"][value="1"]')).toBeChecked();
  await expect(page.locator('select[name="compactar"]')).toHaveValue('1');
  await clean(page, 'request form, prefilled');
  await page.selectOption('select[name="specialty"]', String(e.specialty));
  await page.selectOption('select[name="level"]', String(e.level));
  await Promise.all([page.waitForURL(/\/ancora\/scheduling\/jobs\/\d+$/), page.click('input[value="Request the timetable"]')]);
  const runUrl = page.url();

  // Queued (the worker sleeps between polls): "Refresh" and the script.
  const pending = page.locator('[data-ancora-job-pending]');
  await expect(pending).toHaveCount(1);
  await expect(pending.locator('a:text-is("Refresh")')).toHaveAttribute('href', new URL(runUrl).pathname);
  await shot(page, 'run-01-pending');
  const loadsBefore = loads;

  // The page reloads itself until the run has ended (every 3 s, at most
  // 100 times); nothing is clicked.
  const started = Date.now();
  await expect(page.locator('table.ancora-scheduling-job')).toContainText(TERMINAL, { timeout: 180000 });
  const waited = Date.now() - started;
  const reloads = loads - loadsBefore;
  expect(reloads, 'the page reloaded itself').toBeGreaterThan(0);
  expect(page.url()).toBe(runUrl);
  await expect(page.locator('table.ancora-scheduling-job')).toContainText('Succeeded: final timetable');
  // Ended: no "Refresh", no script, and no further reload.
  await expect(pending).toHaveCount(0);
  await expect(page.locator('a:text-is("Refresh")')).toHaveCount(0);
  const settled = loads;
  await page.waitForTimeout(4000);
  expect(loads, 'no reload once the run has ended').toBe(settled);
  const prefilledHash = await detail(page, 'Input snapshot (sha256)');
  const loadsToEnd = loads;
  await shot(page, 'run-02-ended');
  await clean(page, 'run page, ended');

  // Open in the workspace: the plan's workspace on the run's period.
  await page.click('a:text-is("Open in the workspace")');
  await page.waitForURL(new RegExp(`/ancora/planning/${e.plan}/workspace\\?period=${e.period}$`));
  await page.waitForFunction(() => !document.querySelector('[data-ancora-workspace][aria-busy]'));
  await expect(page.locator('#ancora-canvas-title')).toBeVisible();
  await shot(page, 'run-03-workspace');
  await clean(page, 'workspace from the run page');

  // The same scope requested by hand, without JavaScript: the plain form,
  // and "Refresh" pressed by hand until the run has ended.
  const context = await browser.newContext({ javaScriptEnabled: false });
  const plain = await context.newPage();
  await login(plain, 'other');
  await plain.goto('/ancora/scheduling/request');
  for (const [key, value] of Object.entries({ plan: e.plan, period: e.period, specialty: e.specialty, level: e.level })) {
    await plain.selectOption(`select[name="${key}"]`, String(value));
  }
  await Promise.all([plain.waitForURL(/\/ancora\/scheduling\/jobs\/\d+$/), plain.click('input[value="Request the timetable"]')]);
  const manualUrl = plain.url();
  expect(manualUrl).not.toBe(runUrl);
  // axe needs a script in the page: scan the same run page with every
  // script of the site refused instead (the same server markup, no reload).
  const scan = await browser.newContext();
  await scan.route('**/*', (route) => (route.request().resourceType() === 'script' ? route.abort() : route.continue()));
  const scanned = await scan.newPage();
  await login(scanned, 'other');
  await scanned.goto(manualUrl);
  const scannedPending = await scanned.locator('[data-ancora-job-pending]').count();
  await shot(scanned, 'run-04-pending-no-script');
  await clean(scanned, scannedPending ? 'run page, pending' : 'run page');
  await scan.close();
  let refreshes = 0;
  for (; refreshes < 60; refreshes++) {
    if (TERMINAL.test(await plain.locator('table.ancora-scheduling-job').textContent())) {
      break;
    }
    await expect(plain.locator('[data-ancora-job-pending] a:text-is("Refresh")')).toHaveCount(1);
    await plain.waitForTimeout(3000);
    await plain.click('a:text-is("Refresh")');
    await plain.waitForLoadState('load');
  }
  await expect(plain.locator('table.ancora-scheduling-job')).toContainText('Succeeded: final timetable');
  await expect(plain.locator('a:text-is("Refresh")')).toHaveCount(0);
  const manualHash = await detail(plain, 'Input snapshot (sha256)');
  await context.close();

  // MPI inputs unchanged: the prefilled request, the manual one and the
  // site script's first run (enqueue.php) read the same input snapshot.
  expect(prefilledHash).toMatch(/^[0-9a-f]{64}$/);
  expect(manualHash).toBe(prefilledHash);
  expect(prefilledHash).toBe(e.input_hash);

  record('run-journey', {
    page_loads_from_generate_to_end: loadsToEnd,
    scanned_pending: scannedPending === 1,
    self_reloads: reloads,
    waited_ms: waited,
    manual_refreshes: refreshes,
    input_hash: { script: e.input_hash, prefilled: prefilledHash, manual: manualHash },
  });
});
