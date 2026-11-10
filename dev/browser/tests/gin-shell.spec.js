const { test, expect } = require('@playwright/test');
const { ids, login, axe, shot, workspace } = require('./support');

const plan = ids.planner['plan:A'];
const period = ids.planner['period:A'];

async function shell(page, path, active) {
  const response = await page.goto(path);
  expect(response.status()).toBe(200);
  await expect(page.locator('.ancora-shell')).toBeVisible();
  await expect(page.locator('.ancora-sidebar')).toBeVisible();
  await expect(page.locator('nav[aria-label="Academic navigation"]')).toBeVisible();
  await expect(page.locator('nav[aria-label="Academic navigation"] a', { hasText: active }).first()).toBeVisible();
  expect(await page.locator('.site-header nav, .gin--navigation-top-bar--offset').count()).toBe(0);
  expect(await axe(page, path)).toEqual([]);
}

test('Gin shell is shared across overview, form, workspace and timetable', async ({ page }) => {
  await login(page, 'planner');
  await shell(page, '/ancora', 'Overview');
  await shot(page, 'gin-overview');
  await shell(page, '/admin/ancora/plans/add', 'Plans & periods');
  await expect(page.locator('#edit-label-0-value')).toBeVisible();
  await expect(page.getByRole('button', { name: 'Save' })).toBeVisible();
  await expect(page.getByRole('button', { name: 'Save' })).toBeInViewport();
  const saveStyle = await page.getByRole('button', { name: 'Save' }).evaluate((element) => {
    const rect = element.getBoundingClientRect();
    const style = getComputedStyle(element);
    return { x: rect.x, y: rect.y, width: rect.width, height: rect.height, color: style.color, background: style.backgroundColor, opacity: style.opacity };
  });
  expect(saveStyle.width).toBeGreaterThan(20);
  expect(saveStyle.height).toBeGreaterThan(20);
  await expect(page.locator('nav[aria-label="Academic navigation"] a.is-active-trail', { hasText: 'Plans & periods' })).toBeVisible();
  await shot(page, 'gin-add-plan');
  await page.fill('#edit-label-0-value', 'Gin shell disposable plan');
  await page.fill('#edit-code-0-value', 'GINSHELL');
  await page.fill('#edit-starts-on-0-value-date', '2027-03-01');
  await page.fill('#edit-ends-on-0-value-date', '2027-03-31');
  await page.getByRole('button', { name: 'Save' }).click();
  await expect(page.locator('main')).toContainText('Created the plan Gin shell disposable plan.');
  const editLink = page.getByRole('link', { name: 'Edit Gin shell disposable plan' });
  await expect(editLink).toBeVisible();
  const ownPlan = await editLink.getAttribute('href');
  await page.goto(ownPlan);
  await expect(page.locator('#edit-label-0-value')).toHaveValue('Gin shell disposable plan');
  await shell(page, workspace({ period }), 'Timetables');
  await expect(page.locator('[data-ancora-workspace]')).toBeVisible();
  await expect(page.locator('nav[aria-label="Academic navigation"] a.is-active-trail', { hasText: 'Timetables' })).toBeVisible();
  await expect(page.locator('nav[aria-label="Academic navigation"] a.is-active-trail', { hasText: 'Plans & periods' })).toHaveCount(0);
  await shot(page, 'gin-workspace');
  await shell(page, `/ancora/planning/${plan}/timetable?period=${period}`, 'Timetables');
  await expect(page.locator('.ancora-timetable__grid')).toBeVisible();
  await shot(page, 'gin-timetable');
  await shell(page, '/ancora/scheduling/jobs', 'Scheduling jobs');
  await expect(page.locator('nav[aria-label="Academic navigation"] a.is-active-trail', { hasText: 'Scheduling jobs' })).toBeVisible();
  const overflow = await page.evaluate(() => document.documentElement.scrollWidth > innerWidth + 1);
  expect(overflow, 'no document-level horizontal overflow').toBe(false);
});

test('mobile menu toggles with keyboard and keeps content reachable', async ({ page, browser }) => {
  await page.setViewportSize({ width: 390, height: 844 });
  await login(page, 'planner');
  await page.goto('/ancora');
  const toggle = page.locator('.ancora-sidebar__toggle');
  await expect(toggle).toHaveAttribute('aria-expanded', 'false');
  await toggle.focus();
  await page.keyboard.press('Enter');
  await expect(toggle).toHaveAttribute('aria-expanded', 'true');
  await expect(page.locator('nav[aria-label="Academic navigation"]')).toBeVisible();
  await page.keyboard.press('Escape');
  await expect(toggle).toHaveAttribute('aria-expanded', 'false');
  const overflow = await page.evaluate(() => document.documentElement.scrollWidth > innerWidth + 1);
  expect(overflow, 'no document-level horizontal overflow').toBe(false);
  expect(await axe(page, 'mobile navigation')).toEqual([]);
  const plain = await browser.newContext({ javaScriptEnabled: false, storageState: await page.context().storageState(), viewport: { width: 390, height: 844 } });
  const plainPage = await plain.newPage();
  await plainPage.goto('/ancora');
  await expect(plainPage.locator('nav[aria-label="Academic navigation"]')).toBeVisible();
  await plain.close();
});

test('administration links are visible to admins and academic routes keep one shell', async ({ page }) => {
  await login(page, 'admin');
  await page.goto('/admin');
  await expect(page.locator('.ancora-shell')).toBeVisible();
  await expect(page.locator('.ancora-sidebar__admin')).toBeVisible();
  await page.locator('.ancora-sidebar__admin').evaluate((details) => { details.open = true; });
  await expect(page.locator('nav[aria-label="Administration navigation"] a').first()).toBeVisible();
  await expect(page.locator('nav[aria-label="Administration navigation"] a[href="/ancora/planning"]')).toHaveCount(0);
  await expect(page.locator('nav[aria-label="Academic navigation"]')).toBeVisible();
  await page.goto('/admin/ancora/plans/add');
  await expect(page.locator('.ancora-shell')).toBeVisible();
});
