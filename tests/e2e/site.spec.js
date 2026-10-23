// End-to-end tests for the public website and the staff portal.
// Run against a freshly imported database: see README → "Running tests".
const { test, expect } = require('@playwright/test');
const path = require('path');

const FIXTURES = path.join(__dirname, 'fixtures');
const run = Date.now();
const customer = { name: 'Test Traveller', email: `traveller${run}@example.com`, password: 'Travel123' };

/** ISO date `days` from today. */
const inDays = (days) => {
  const d = new Date();
  d.setDate(d.getDate() + days);
  return d.toISOString().slice(0, 10);
};

/** Text of the flash/alert messages on the page, waiting for the next page load to show one. */
const flashText = async (page) => {
  await page.waitForLoadState('domcontentloaded');
  const alerts = page.locator('.flash-stack .alert, .admin-content .alert, .admin-login .alert');
  await alerts.first().waitFor({ timeout: 10_000 }).catch(() => {});
  return (await alerts.allInnerTexts()).join(' | ');
};

async function signOutCustomer(page) {
  await page.locator('.site-nav .dropdown-toggle').click();
  await page.getByRole('button', { name: 'Sign out' }).click();
}

async function adminSignIn(page, username, password) {
  await page.goto('admin/index.php');
  await page.fill('#username', username);
  await page.fill('#password', password);
  await page.getByRole('button', { name: 'Sign in' }).click();
}

test.describe.configure({ mode: 'serial' });

test.beforeEach(async ({ page }) => {
  page.on('dialog', (dialog) => dialog.accept());
});

// ---------------------------------------------------------------------------
// Platform-level checks
// ---------------------------------------------------------------------------

test('security headers are sent', async ({ request }) => {
  const res = await request.get('index.php');
  const headers = res.headers();
  expect(headers['content-security-policy']).toContain("default-src 'self'");
  expect(headers['x-frame-options']).toBe('SAMEORIGIN');
  expect(headers['x-content-type-options']).toBe('nosniff');
  expect(headers['set-cookie']).toMatch(/HttpOnly/i);
});

test('pages render without CSP violations or script errors', async ({ page }) => {
  const problems = [];
  page.on('console', (msg) => { if (msg.type() === 'error') problems.push(msg.text()); });
  page.on('pageerror', (err) => problems.push(err.message));
  for (const url of ['index.php', 'packages.php', 'package_details.php?pkgid=1', 'login.php', 'register.php', 'forgot_password.php', 'admin/index.php']) {
    await page.goto(url);
    await page.waitForLoadState('networkidle');
  }
  expect(problems).toEqual([]);
});

test('missing pages return a friendly 404', async ({ page }) => {
  const res = await page.goto('package_details.php?pkgid=99999');
  expect(res.status()).toBe(404);
  await expect(page.getByRole('heading', { name: /wandered off the trail/i })).toBeVisible();
});

test('POST without a CSRF token is rejected', async ({ request }) => {
  const res = await request.post('contact.php', { form: { name: 'x', email: 'x@example.com', subject: 's', message: 'no token here' }, maxRedirects: 0 });
  expect(res.status()).toBe(403);
});

// ---------------------------------------------------------------------------
// Customer journey
// ---------------------------------------------------------------------------

test('visitor can search packages', async ({ page }) => {
  await page.goto('packages.php?q=kilimanjaro');
  await expect(page.locator('.package-card')).toHaveCount(1);
  await page.goto('packages.php?q=zzzz-no-match');
  await expect(page.getByText('No packages match your search')).toBeVisible();
});

test('contact form creates an enquiry', async ({ page }) => {
  await page.goto('index.php#contact');
  await page.fill('#c-name', 'E2E Visitor');
  await page.fill('#c-email', 'visitor@example.com');
  await page.fill('#c-subject', `E2E enquiry ${run}`);
  await page.fill('#c-message', 'Hello, testing the contact form end to end.');
  await page.getByRole('button', { name: 'Send message' }).click();
  expect(await flashText(page)).toContain('message has been sent');
});

test('customer can register and is signed in', async ({ page, request }) => {
  await page.goto('register.php');
  await page.fill('#fname', customer.name);
  await page.fill('#email', customer.email);
  await page.fill('#mobilenumber', '+255 700 111 222');
  await page.fill('#password', customer.password);
  await page.fill('#password_confirmation', customer.password);
  await page.getByRole('button', { name: 'Create account' }).click();
  expect(await flashText(page)).toContain('Welcome to Five Star Tours');

  const availability = await (await request.get(`check_availability.php?email=${encodeURIComponent(customer.email)}`)).json();
  expect(availability.available).toBe(false);
});

test('customer can book, sees escaped comment, and double-click books once', async ({ page }) => {
  await page.goto('login.php');
  await page.fill('#email', customer.email);
  await page.fill('#password', customer.password);
  await page.getByRole('button', { name: 'Sign in' }).click();

  await page.goto('package_details.php?pkgid=1');
  await page.fill('#fromdate', inDays(10));
  await page.fill('#todate', inDays(12));
  await page.fill('#travellers', '3');
  await expect(page.locator('#booking-total')).toContainText('5,400');
  await page.fill('#comment', '<script>alert(1)</script> window seat');
  await page.getByRole('button', { name: 'Request booking' }).dblclick();

  await expect(page).toHaveURL(/tour_history\.php/);
  expect(await flashText(page)).toContain('received');
  await expect(page.locator('.booking-item')).toHaveCount(1);
  expect(await page.content()).toContain('&lt;script&gt;alert(1)&lt;/script&gt;');
});

test('server rejects invalid booking dates', async ({ page }) => {
  await page.goto('login.php');
  await page.fill('#email', customer.email);
  await page.fill('#password', customer.password);
  await page.getByRole('button', { name: 'Sign in' }).click();

  const submitWithDates = async (from, to) => {
    await page.goto('package_details.php?pkgid=1');
    // Set the values directly (no input events, no min/max) so the page's own
    // date corrections are bypassed and only the server-side checks apply.
    await page.evaluate(([f, t]) => {
      document.querySelectorAll('#booking-form input[type=date]').forEach((i) => { i.removeAttribute('min'); i.removeAttribute('max'); });
      document.querySelector('#fromdate').value = f;
      document.querySelector('#todate').value = t;
    }, [from, to]);
    await Promise.all([
      page.waitForURL(/package_details\.php\?pkgid=1#book/),
      page.getByRole('button', { name: 'Request booking' }).click(),
    ]);
    return flashText(page);
  };

  expect(await submitWithDates(inDays(-5), inDays(-3))).toContain('must start tomorrow');
  expect(await submitWithDates(inDays(10), inDays(60))).toContain('at most 30 days');
  expect(await submitWithDates(inDays(12), inDays(10))).toContain('cannot be before');
});

test('customer can cancel, update profile and change password', async ({ page }) => {
  await page.goto('login.php');
  await page.fill('#email', customer.email);
  await page.fill('#password', customer.password);
  await page.getByRole('button', { name: 'Sign in' }).click();

  await page.goto('tour_history.php');
  await page.getByRole('button', { name: 'Cancel booking' }).first().click();
  expect(await flashText(page)).toContain('has been cancelled');

  await page.goto('profile.php');
  await page.fill('#name', `${customer.name} Updated`);
  await page.getByRole('button', { name: 'Save changes' }).click();
  expect(await flashText(page)).toContain('updated');

  await page.goto('change_password.php');
  await page.fill('#password', customer.password);
  await page.fill('#newpassword', 'Travel456');
  await page.fill('#confirmpassword', 'Travel456');
  await page.getByRole('button', { name: 'Update password' }).click();
  expect(await flashText(page)).toContain('changed');
  customer.password = 'Travel456';

  await signOutCustomer(page);
  expect(await flashText(page)).toContain('signed out');
});

test('account pages require sign-in and redirect back afterwards', async ({ page }) => {
  await page.goto('tour_history.php');
  await expect(page).toHaveURL(/login\.php/);
  await page.fill('#email', customer.email);
  await page.fill('#password', 'wrong-password1');
  await page.getByRole('button', { name: 'Sign in' }).click();
  expect(await flashText(page)).toContain('incorrect');

  await page.fill('#email', customer.email);
  await page.fill('#password', customer.password);
  await page.getByRole('button', { name: 'Sign in' }).click();
  await expect(page).toHaveURL(/tour_history\.php/);
});

test('password reset link works once', async ({ page }) => {
  await page.goto('forgot_password.php');
  await page.fill('#email', customer.email);
  await page.getByRole('button', { name: 'Send reset link' }).click();
  const link = await page.locator('.flash-stack a').getAttribute('href');
  expect(link).toContain('reset_password.php?token=');

  await page.goto(link);
  await page.fill('#password', 'Reset789x');
  await page.fill('#password_confirmation', 'Reset789x');
  await page.getByRole('button', { name: 'Reset password' }).click();
  expect(await flashText(page)).toContain('has been reset');

  await page.goto(link);
  expect(await flashText(page)).toContain('invalid or has expired');

  await page.goto('login.php');
  await page.fill('#email', customer.email);
  await page.fill('#password', 'Reset789x');
  await page.getByRole('button', { name: 'Sign in' }).click();
  expect(await flashText(page)).toContain('Welcome back');
});

test('repeated failed sign-ins are throttled', async ({ page }) => {
  const victim = `nobody${run}@example.com`;
  for (let i = 0; i < 5; i++) {
    await page.goto('login.php');
    await page.fill('#email', victim);
    await page.fill('#password', `guess${i}x1`);
    await page.getByRole('button', { name: 'Sign in' }).click();
    expect(await flashText(page)).toContain('incorrect');
  }
  await page.fill('#email', victim);
  await page.fill('#password', 'guess6x1');
  await page.getByRole('button', { name: 'Sign in' }).click();
  expect(await flashText(page)).toContain('Too many sign-in attempts');
});

// ---------------------------------------------------------------------------
// Staff portal
// ---------------------------------------------------------------------------

test('admin pages require sign-in and reject wrong passwords', async ({ page }) => {
  await page.goto('admin/dashboard.php');
  await expect(page).toHaveURL(/admin\/index\.php/);
  await adminSignIn(page, 'admin', 'not-the-password');
  expect(await flashText(page)).toContain('Invalid');
});

test('admin manages bookings and enquiries', async ({ page }) => {
  await adminSignIn(page, 'admin', 'Admin@1234');
  await expect(page).toHaveURL(/dashboard\.php/);

  await page.goto('admin/manage_bookings.php?status=pending');
  await page.getByRole('button', { name: 'Confirm' }).first().click();
  expect(await flashText(page)).toContain('confirmed');

  await page.goto('admin/manage_enquiries.php');
  const row = page.locator('tr', { hasText: `E2E enquiry ${run}` });
  await expect(row).toBeVisible();
  await row.getByRole('button', { name: 'Mark read' }).click();
  expect(await flashText(page)).toContain('marked as read');
});

test('admin creates, edits and deletes a package with safe uploads', async ({ page }) => {
  const name = `E2E Cultural Tour ${run}`;
  await adminSignIn(page, 'admin', 'Admin@1234');

  await page.goto('admin/package_form.php');
  await page.fill('#packagename', name);
  await page.fill('#packagetype', 'Cultural');
  await page.fill('#packagelocation', 'Arusha, Tanzania');
  await page.fill('#packageduration', '1 day');
  await page.fill('#packageprice', '150');
  await page.fill('#packagefeatures', 'Maasai market, Coffee farm visit');
  await page.fill('#packagedetails', 'A relaxed day exploring Arusha town, its markets and a local coffee farm.');
  await page.setInputFiles('#packageimage', path.join(FIXTURES, 'not-an-image.jpg'));
  await page.getByRole('button', { name: 'Publish package' }).click();
  expect(await flashText(page)).toContain('Only JPG');
  await expect(page.locator('#packagename')).toHaveValue(name);

  await page.setInputFiles('#packageimage', path.join(FIXTURES, 'photo.jpg'));
  await page.getByRole('button', { name: 'Publish package' }).click();
  expect(await flashText(page)).toContain('Package created');

  await page.getByRole('link', { name: `Edit ${name}` }).click();
  await page.fill('#packageprice', '175');
  await page.getByRole('button', { name: 'Save changes' }).click();
  expect(await flashText(page)).toContain('Package updated');

  await page.getByRole('button', { name: `Delete ${name}` }).click();
  expect(await flashText(page)).toContain('was deleted');

  await page.getByRole('button', { name: 'Delete Serengeti National Park' }).click();
  expect(await flashText(page)).toContain('cannot be deleted');
});

test('roles are enforced for staff accounts', async ({ page }) => {
  const username = `eve${run}`;
  await adminSignIn(page, 'admin', 'Admin@1234');
  await page.goto('admin/staff_form.php');
  await page.fill('#firstname', 'Eve');
  await page.fill('#lastname', 'Tester');
  await page.fill('#username', username);
  await page.fill('#email', 'eve@example.com');
  await page.selectOption('#role', 'User');
  await page.fill('#password', 'EveStaff1');
  await page.getByRole('button', { name: 'Create account' }).click();
  expect(await flashText(page)).toContain('Account created');

  await page.locator('.admin-user').click();
  await page.locator('.dropdown-menu').getByRole('button', { name: 'Sign out' }).click();
  expect(await flashText(page)).toContain('signed out');

  await adminSignIn(page, username, 'EveStaff1');
  await expect(page).toHaveURL(/dashboard\.php/);
  await expect(page.locator('.admin-sidebar').getByRole('link', { name: 'Staff' })).toHaveCount(0);
  await page.goto('admin/staff.php');
  await expect(page).toHaveURL(/dashboard\.php/);
  expect(await flashText(page)).toContain('permission');
});
