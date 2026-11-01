// The destination-check latency of TODAY's move page (#130), measured, not
// changed (#141 §7.7). On the disposable site only: each run presses "Find
// the valid destinations", which queues a check (its own table, never a
// generation job, never academic data); nothing is moved.
//
// Per run:
// - request: from the click until the "Checking…" page is shown (the POST,
//   the redirect and the page);
// - server: from that page until the move page, polled every 100 ms by a
//   separate GET, shows the destination grid (the worker's queue wait and
//   the check's run);
// - detection: from then until the page itself shows the grid (its own
//   reload every 2 s, placement-move.js);
// - total: click to grid on screen.
// The worker runs with its default idle sleep (5 s) unless the site script
// was told otherwise; the results say which. No threshold is asserted.
const { test } = require('@playwright/test');
const { ids, login, record } = require('./support');

const runs = Number(process.env.ANCORA_LATENCY_RUNS || 20);

const stats = (values) => {
  const v = [...values].sort((a, b) => a - b);
  const at = (q) => v[Math.min(v.length - 1, Math.ceil(v.length * q) - 1)];
  return { n: v.length, p50: at(0.5), p95: at(0.95), max: v[v.length - 1], min: v[0] };
};

async function measure(page, placement) {
  await page.goto(`/ancora/timetables/placements/${placement}/move`);
  const t0 = Date.now();
  await Promise.all([page.waitForURL(/check=\d+/), page.click('input[value="Find the valid destinations"], button:has-text("Find the valid destinations")')]);
  const tPending = Date.now();
  const url = page.url();
  let tServer = null;
  const poller = (async () => {
    while (tServer === null) {
      const r = await page.request.get(url);
      if ((await r.text()).includes('id="ancora-move-grid"')) {
        tServer = Date.now();
        return;
      }
      await new Promise((resolve) => setTimeout(resolve, 100));
    }
  })();
  await page.waitForSelector('#ancora-move-grid', { timeout: 90000 });
  const tUi = Date.now();
  await poller;
  return {
    request: tPending - t0,
    server: Math.max(0, tServer - tPending),
    detection: Math.max(0, tUi - tServer),
    total: tUi - t0,
  };
}

test('destination-check latency of the move page', async ({ browser }) => {
  test.setTimeout(runs * 2 * 60000);
  const fixtures = [
    // The #133 fixture: two dated plans and an undated one (plan A's A1).
    { name: 'cross-plan (#133) fixture', user: 'planner', placements: [ids.planner.A1, ids.planner.A2, ids.planner.A3, ids.planner.A4] },
    // The E2E organization of #130 (8 activities, placed by a real run).
    { name: 'E2E (#130) organization', user: 'other', placements: ids.e2e.placements },
  ];
  const out = { worker_idle_sleep_s: Number(process.env.ANCORA_WORKER_IDLE_SLEEP || 5), runs_per_fixture: runs, fixtures: [] };
  for (const fixture of fixtures) {
    const context = await browser.newContext();
    const page = await context.newPage();
    await login(page, fixture.user);
    const samples = [];
    for (let i = 0; i < runs; i++) {
      samples.push(await measure(page, fixture.placements[i % fixture.placements.length]));
    }
    await context.close();
    const summary = { name: fixture.name, samples };
    for (const part of ['request', 'server', 'detection', 'total']) {
      summary[part] = stats(samples.map((s) => s[part]));
    }
    out.fixtures.push(summary);
    console.log(JSON.stringify({ fixture: fixture.name, request: summary.request, server: summary.server, detection: summary.detection, total: summary.total }));
  }
  record('latency', out);
});
