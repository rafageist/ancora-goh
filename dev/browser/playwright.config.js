// Playwright configuration of the workspace browser tests (#141). The site
// is started by modern/scripts/browser-journey.sh, which sets BASE_URL.
const { defineConfig, devices } = require('@playwright/test');

const out = process.env.ANCORA_BROWSER_OUT || `${__dirname}/test-results`;

module.exports = defineConfig({
  testDir: './tests',
  outputDir: `${out}/playwright`,
  // One disposable site, one worker: the tests share its data.
  workers: 1,
  fullyParallel: false,
  retries: 0,
  timeout: 120000,
  expect: { timeout: 10000 },
  reporter: [['list']],
  use: {
    ...devices['Desktop Chrome'],
    baseURL: process.env.BASE_URL,
    viewport: { width: 1440, height: 900 },
    trace: 'retain-on-failure',
  },
});
