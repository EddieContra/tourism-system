// Automated accessibility checks (axe-core, WCAG 2.1 A and AA) for key pages.
const { test, expect } = require('@playwright/test');
const AxeBuilder = require('@axe-core/playwright').default;

const pages = [
  'index.php',
  'packages.php',
  'package_details.php?pkgid=3',
  'login.php',
  'register.php',
  'forgot_password.php',
  'admin/index.php',
];

async function expectNoSeriousViolations(page) {
  // Finish scroll-in animations so text is measured at full opacity.
  await page.addStyleTag({ content: '.reveal{opacity:1!important;transform:none!important;transition:none!important}' });
  const results = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa']).analyze();
  const serious = results.violations.filter((v) => ['serious', 'critical'].includes(v.impact));
  expect(serious.map((v) => `${v.id}: ${v.nodes.map((n) => n.target.join(' ')).join(', ')}`)).toEqual([]);
}

for (const url of pages) {
  test(`no serious accessibility violations on ${url}`, async ({ page }) => {
    await page.goto(url);
    await expectNoSeriousViolations(page);
  });
}

test('no serious accessibility violations in the staff portal', async ({ page }) => {
  await page.goto('admin/index.php');
  await page.fill('#username', 'admin');
  await page.fill('#password', 'Admin@1234');
  await page.getByRole('button', { name: 'Sign in' }).click();
  for (const url of ['admin/dashboard.php', 'admin/manage_bookings.php', 'admin/manage_packages.php', 'admin/package_form.php?id=1', 'admin/staff.php']) {
    await page.goto(url);
    await expectNoSeriousViolations(page);
  }
});
