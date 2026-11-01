// Real browser history (#141 §7.4): 20 transitions of the workspace, each
// compared with a full load of the same URL. This script decided the region
// loader (a GET fetch of the regions endpoint, against Drupal AJAX; see
// docs/ux/planning-workspace.md §8) and now guards it:
// - every transition is in place (no full page load);
// - after each one the page shows exactly what a full load of its URL shows
//   (perspective, record, selection, canvas, inspector, period);
// - the focus is on the defined target (canvas or inspector heading, or the
//   block the inspector was closed from);
// - axe finds no serious or critical violation.
const { test } = require('@playwright/test');
const { ids, login, axe, record, workspace, shown, expect } = require('./support');

test('20 transitions: in place, back and forward, focus, axe', async ({ page, context }) => {
  await login(page, 'planner');
  const responses = [];
  page.on('response', async (r) => {
    if (/\/workspace\/regions/.test(r.url())) {
      const body = await r.body().catch(() => Buffer.alloc(0));
      responses.push({ status: r.status(), bytes: body.length, cache: r.headers()['x-drupal-dynamic-cache'] || null, url: r.url().length });
    }
  });
  // The script weight of the workspace page (first load).
  const scripts = [];
  const onScript = async (r) => {
    if (r.request().resourceType() === 'script') {
      scripts.push((await r.body().catch(() => Buffer.alloc(0))).length);
    }
  };
  page.on('response', onScript);
  await page.goto(workspace());
  await page.waitForLoadState('networkidle');
  page.off('response', onScript);
  const truth = await context.newPage();
  const p = ids.planner;
  const steps = [
    ['click', '.ancora-wsnav__lenses a:text("Teachers")', 'lens'],
    ['click', '.ancora-wsnav__list a:has-text("Teacher TA")', 'record'],
    ['click', `[data-ancora-placement="${p.A2}"]`, 'select'],
    ['click', `[data-ancora-placement="${p.A1}"]`, 'select'],
    ['click', '.ancora-inspector__close', 'close'],
    ['click', '.ancora-wsnav__lenses a:text("Rooms")', 'lens'],
    ['click', '.ancora-wsnav__list a:has-text("Room L1")', 'record'],
    ['click', `[data-ancora-placement="${p.A3}"]`, 'select'],
    ['click', '.ancora-inspector__pivots a:has-text("Teacher TB")', 'record'],
    ['back'],
    ['back'],
    ['back'],
    ['forward'],
    ['forward'],
    ['click', '.ancora-wsnav__lenses a:text("All activities")', 'lens'],
    ['click', `[data-ancora-placement="${p.A4}"]`, 'select'],
    ['back'],
    ['back'],
    ['forward'],
    ['click', '.ancora-wsnav__lenses a:text("Groups")', 'lens'],
  ];
  const result = { transitions: [], mismatches: 0, focusMisses: 0, axe: 0, fullLoads: 0 };
  let loads = 0;
  page.on('load', () => {
    loads++;
  });
  for (const [action, selector, kind] of steps) {
    const before = loads;
    const t0 = Date.now();
    if (action === 'click') {
      await page.click(selector);
    }
    else {
      await page.evaluate((a) => (a === 'back' ? history.back() : history.forward()), action);
    }
    // Done when no region is busy (checked twice around a short pause).
    await page.waitForFunction(() => !document.querySelector('[data-ancora-workspace][aria-busy]'));
    await page.waitForTimeout(150);
    await page.waitForFunction(() => !document.querySelector('[data-ancora-workspace][aria-busy]'));
    const ms = Date.now() - t0;
    const state = await shown(page);
    await truth.goto(page.url());
    const expected = await shown(truth);
    const match = JSON.stringify(state) === JSON.stringify(expected);
    const arrived = kind || (await page.evaluate(() => history.state && history.state.kind));
    const focus = await page.evaluate(() => {
      const a = document.activeElement;
      return a ? a.id || (a.dataset && a.dataset.ancoraPlacement ? `block:${a.dataset.ancoraPlacement}` : a.tagName) : null;
    });
    const want = { lens: 'ancora-canvas-title', record: 'ancora-canvas-title', select: 'ancora-inspector-title', load: 'ancora-canvas-title' }[arrived];
    const focusOk = arrived === 'close' ? String(focus).startsWith('block:') : focus === want;
    const violations = (await axe(page, `step ${result.transitions.length + 1}`)).length;
    result.mismatches += match ? 0 : 1;
    result.focusMisses += focusOk ? 0 : 1;
    result.axe += violations;
    result.fullLoads += loads - before;
    result.transitions.push({ action, kind: arrived, ms, match, focus, focusOk, violations, fullLoad: loads - before });
    if (!match) {
      console.log('MISMATCH', action, selector, JSON.stringify(state), JSON.stringify(expected));
    }
  }
  const times = result.transitions.map((t) => t.ms).sort((a, b) => a - b);
  result.p50 = times[Math.floor(times.length * 0.5)];
  result.p95 = times[Math.min(times.length - 1, Math.ceil(times.length * 0.95) - 1)];
  result.bytes = { median: responses.map((r) => r.bytes).sort((a, b) => a - b)[Math.floor(responses.length / 2)] || 0 };
  result.scripts = { files: scripts.length, bytes: scripts.reduce((a, b) => a + b, 0) };
  result.dynamicCache = [...new Set(responses.map((r) => r.cache))];
  result.requests = responses.length;
  record('history', result);
  console.log(JSON.stringify({ mismatches: result.mismatches, focusMisses: result.focusMisses, axe: result.axe, fullLoads: result.fullLoads, p50: result.p50, p95: result.p95, bytes: result.bytes, scripts: result.scripts, requests: result.requests }));
  expect(result.fullLoads).toBe(0);
  expect(result.mismatches).toBe(0);
  expect(result.focusMisses).toBe(0);
  expect(result.axe).toBe(0);
});
