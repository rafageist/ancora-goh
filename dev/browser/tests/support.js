// Shared helpers of the workspace browser tests (#141).
const fs = require('fs');
const path = require('path');
const AxeBuilder = require('@axe-core/playwright').default;
const { expect } = require('@playwright/test');

const ids = JSON.parse(fs.readFileSync(process.env.ANCORA_IDS, 'utf8'));
const out = process.env.ANCORA_BROWSER_OUT || path.join(__dirname, '..', 'test-results');
fs.mkdirSync(path.join(out, 'screenshots'), { recursive: true });

const password = (name) => fs.readFileSync(path.join(process.env.ANCORA_SECRETS, name), 'utf8').trim();

/** Logs in through the login form (a disposable site). */
async function login(page, name) {
  await page.goto('/user/login');
  await page.fill('#edit-name', name);
  await page.fill('#edit-pass', password(name));
  await Promise.all([page.waitForURL((url) => !url.pathname.startsWith('/user/login')), page.click('#edit-submit')]);
}

/** Runs axe; returns the serious and critical violations. */
async function axe(page, label) {
  const result = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa']).analyze();
  const bad = result.violations.filter((v) => v.impact === 'serious' || v.impact === 'critical');
  if (bad.length) {
    console.log(`axe (${label}):`, JSON.stringify(bad.map((v) => ({ id: v.id, impact: v.impact, nodes: v.nodes.map((n) => n.target.join(' ')).slice(0, 5) }))));
  }
  return bad;
}

/** Saves a full-page screenshot under screenshots/. */
async function shot(page, name) {
  await page.screenshot({ path: path.join(out, 'screenshots', `${name}.png`), fullPage: true });
}

/** Writes a JSON result under the output directory. */
function record(name, data) {
  fs.writeFileSync(path.join(out, `${name}.json`), JSON.stringify(data, null, 2));
}

/** The workspace URL of the planner's plan A with a query. */
function workspace(query = {}, plan = ids.planner['plan:A']) {
  const q = new URLSearchParams(query).toString();
  return `/ancora/planning/${plan}/workspace${q ? `?${q}` : ''}`;
}

/** The state the page shows: what the server rendered for the URL. */
async function shown(page) {
  return page.evaluate(() => ({
    canvas: document.querySelector('#ancora-canvas-title')?.textContent.trim(),
    selected: document.querySelector('.ancora-block.is-selected')?.dataset.ancoraPlacement || null,
    lens: document.querySelector('.ancora-wsnav__lenses a[aria-current]')?.textContent.trim(),
    record: document.querySelector('.ancora-wsnav__list a[aria-current]')?.textContent.trim() || null,
    inspector: document.querySelector('#ancora-inspector-title')?.textContent.replace(/\s+/g, ' ').trim(),
    period: document.querySelector('.ancora-wsbar__periods a[aria-current]')?.textContent.trim() || null,
  }));
}

module.exports = { ids, out, login, axe, shot, record, workspace, shown, expect };
