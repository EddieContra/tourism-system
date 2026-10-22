<?php
require_once __DIR__ . '/includes/auth.php';
$admin = require_admin();

$customers = $dbh->query(
    'SELECT u.*, COUNT(b.BookingId) AS bookings, SUM(b.status = 1) AS confirmed
       FROM tblusers u
       LEFT JOIN tblbooking b ON b.UserEmail = u.EmailId
      GROUP BY u.id
      ORDER BY u.RegDate DESC'
)->fetchAll();

$initials = function (string $name): string {
    $parts = preg_split('/\s+/', trim($name));
    return strtoupper(mb_substr($parts[0] ?? '', 0, 1) . mb_substr(end($parts) ?: '', 0, 1));
};

$pageTitle = 'Customers';
$activeNav = 'customers';
require __DIR__ . '/includes/layout_start.php';
?>
<section class="panel">
  <div class="panel-header">
    <p class="text-muted mb-0 small"><?= count($customers) ?> registered customers</p>
    <div class="input-icon" style="min-width: 240px">
      <i class="bi bi-search" aria-hidden="true"></i>
      <input class="form-control form-control-sm" type="search" placeholder="Search customers…" aria-label="Search customers" data-table-filter="#customers-table">
    </div>
  </div>
  <div class="table-responsive">
    <table class="table table-admin" id="customers-table">
      <thead><tr><th>Customer</th><th>Mobile</th><th>Bookings</th><th>Confirmed</th><th>Joined</th><th>Last updated</th></tr></thead>
      <tbody>
        <?php foreach ($customers as $c): ?>
          <tr>
            <td>
              <div class="d-flex align-items-center gap-3">
                <span class="avatar"><?= e($initials($c->FullName)) ?></span>
                <div><span class="fw-semibold"><?= e($c->FullName) ?></span><span class="cell-sub"><a href="mailto:<?= e($c->EmailId) ?>"><?= e($c->EmailId) ?></a></span></div>
              </div>
            </td>
            <td class="text-nowrap"><?= e($c->MobileNumber ?: '—') ?></td>
            <td><?= (int) $c->bookings ?></td>
            <td><?= (int) $c->confirmed ?></td>
            <td class="text-nowrap"><?= e(format_date($c->RegDate)) ?></td>
            <td class="text-nowrap"><?= e(format_date($c->UpdationDate)) ?></td>
          </tr>
        <?php endforeach; ?>
        <tr class="no-results"><td colspan="6" class="text-center text-muted py-4">No customers match your search.</td></tr>
        <?php if (!$customers): ?><tr><td colspan="6" class="text-center text-muted py-5">No customers have registered yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
<?php require __DIR__ . '/includes/layout_end.php'; ?>
