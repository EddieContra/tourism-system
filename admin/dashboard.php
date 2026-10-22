<?php
require_once __DIR__ . '/includes/auth.php';
$admin = require_admin();

$stats = $dbh->query(
    "SELECT
        (SELECT COUNT(*) FROM tblbooking)                    AS bookings,
        (SELECT COUNT(*) FROM tblbooking WHERE status = 0)   AS pending,
        (SELECT COUNT(*) FROM tblbooking WHERE status = 1)   AS confirmed,
        (SELECT COUNT(*) FROM tblbooking WHERE status = 2)   AS cancelled,
        (SELECT COUNT(*) FROM tbltourpackages)               AS packages,
        (SELECT COUNT(*) FROM tblusers)                      AS customers,
        (SELECT COUNT(*) FROM tblenquiry WHERE Status = 0)   AS enquiries,
        (SELECT COALESCE(SUM(p.PackagePrice * b.Travellers), 0)
           FROM tblbooking b JOIN tbltourpackages p ON p.PackageId = b.PackageId
          WHERE b.status = 1)                                AS revenue"
)->fetch();

$recent = $dbh->query(
    'SELECT b.*, p.PackageName, u.FullName
       FROM tblbooking b
       LEFT JOIN tbltourpackages p ON p.PackageId = b.PackageId
       LEFT JOIN tblusers u ON u.EmailId = b.UserEmail
      ORDER BY b.RegDate DESC
      LIMIT 6'
)->fetchAll();

$enquiries = $dbh->query('SELECT * FROM tblenquiry ORDER BY Status ASC, PostingDate DESC LIMIT 4')->fetchAll();

$topPackages = $dbh->query(
    'SELECT p.PackageName, COUNT(b.BookingId) AS total
       FROM tbltourpackages p
       LEFT JOIN tblbooking b ON b.PackageId = p.PackageId AND b.status <> 2
      GROUP BY p.PackageId, p.PackageName
      ORDER BY total DESC, p.PackageName
      LIMIT 5'
)->fetchAll();
$maxBookings = max(1, ...array_map(fn ($p) => (int) $p->total, $topPackages ?: [(object) ['total' => 1]]));

$statuses = [0 => ['Pending', 'pending'], 1 => ['Confirmed', 'confirmed'], 2 => ['Cancelled', 'cancelled']];
$totalBookings = max(1, (int) $stats->bookings);

$pageTitle = 'Dashboard';
$activeNav = 'dashboard';
require __DIR__ . '/includes/layout_start.php';
?>

<p class="text-muted mb-4">Good to see you, <?= e($admin->FirstName) ?>. Here’s what’s happening at Five Star Tours today.</p>

<div class="row g-3 mb-4">
  <div class="col-sm-6 col-xl-3">
    <a class="stat-card" href="manage_bookings.php?status=pending">
      <span class="icon-badge amber"><i class="bi bi-hourglass-split"></i></span>
      <div><div class="value"><?= (int) $stats->pending ?></div><div class="label">Bookings awaiting review</div></div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a class="stat-card" href="manage_bookings.php?status=confirmed">
      <span class="icon-badge"><i class="bi bi-cash-stack"></i></span>
      <div><div class="value"><?= e(format_price($stats->revenue)) ?></div><div class="label">Confirmed booking value</div></div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a class="stat-card" href="manage_users.php">
      <span class="icon-badge blue"><i class="bi bi-people"></i></span>
      <div><div class="value"><?= (int) $stats->customers ?></div><div class="label">Registered customers</div></div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a class="stat-card" href="manage_enquiries.php">
      <span class="icon-badge rose"><i class="bi bi-envelope"></i></span>
      <div><div class="value"><?= (int) $stats->enquiries ?></div><div class="label">Unread enquiries</div></div>
    </a>
  </div>
</div>

<div class="row g-4">
  <div class="col-xxl-8">
    <section class="panel h-100">
      <div class="panel-header">
        <h2>Recent bookings</h2>
        <a class="btn btn-outline-primary btn-sm" href="manage_bookings.php">View all</a>
      </div>
      <div class="table-responsive">
        <table class="table table-admin">
          <thead><tr><th>Booking</th><th>Customer</th><th>Package</th><th>Travel dates</th><th>Status</th></tr></thead>
          <tbody>
            <?php foreach ($recent as $b): [$label, $class] = $statuses[(int) $b->status] ?? ['Unknown', 'pending']; ?>
              <tr>
                <td class="fw-semibold nowrap">#BK-<?= (int) $b->BookingId ?><span class="cell-sub"><?= e(format_date($b->RegDate)) ?></span></td>
                <td><?= e($b->FullName ?? 'Deleted user') ?><span class="cell-sub"><?= e($b->UserEmail) ?></span></td>
                <td class="col-min"><?= e($b->PackageName ?? '—') ?></td>
                <td class="text-nowrap"><?= e(format_date($b->FromDate, 'M j')) ?> – <?= e(format_date($b->ToDate, 'M j, Y')) ?></td>
                <td><span class="status-badge status-<?= $class ?>"><?= $label ?></span></td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$recent): ?><tr><td colspan="5" class="text-center text-muted py-4">No bookings yet.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </div>

  <div class="col-xxl-4">
    <div class="row g-4">
    <div class="col-md-6 col-xxl-12">
    <section class="panel h-100">
      <div class="panel-header"><h2>Bookings overview</h2><span class="text-muted small"><?= (int) $stats->bookings ?> total</span></div>
      <div class="panel-body">
        <div class="status-bar mb-3" role="img" aria-label="<?= (int) $stats->pending ?> pending, <?= (int) $stats->confirmed ?> confirmed, <?= (int) $stats->cancelled ?> cancelled">
          <span style="width: <?= round($stats->confirmed / $totalBookings * 100, 1) ?>%; background: #2f8a5b"></span>
          <span style="width: <?= round($stats->pending / $totalBookings * 100, 1) ?>%; background: var(--fst-amber)"></span>
          <span style="width: <?= round($stats->cancelled / $totalBookings * 100, 1) ?>%; background: #c65b5b"></span>
        </div>
        <div class="d-flex justify-content-between small">
          <span><i class="legend-dot" style="background:#2f8a5b"></i>Confirmed <strong><?= (int) $stats->confirmed ?></strong></span>
          <span><i class="legend-dot" style="background:var(--fst-amber)"></i>Pending <strong><?= (int) $stats->pending ?></strong></span>
          <span><i class="legend-dot" style="background:#c65b5b"></i>Cancelled <strong><?= (int) $stats->cancelled ?></strong></span>
        </div>
      </div>
    </section>
    </div>
    <div class="col-md-6 col-xxl-12">
    <section class="panel h-100">
      <div class="panel-header"><h2>Most booked packages</h2><a class="small" href="manage_packages.php"><?= (int) $stats->packages ?> packages</a></div>
      <div class="panel-body d-grid gap-3">
        <?php foreach ($topPackages as $p): ?>
          <div>
            <div class="d-flex justify-content-between small mb-1"><span class="text-truncate me-2"><?= e($p->PackageName) ?></span><strong><?= (int) $p->total ?></strong></div>
            <div class="progress" style="height: 6px" role="progressbar" aria-label="<?= e($p->PackageName) ?> bookings" aria-valuenow="<?= (int) $p->total ?>" aria-valuemin="0" aria-valuemax="<?= $maxBookings ?>">
              <div class="progress-bar" style="width: <?= round($p->total / $maxBookings * 100) ?>%; background: var(--fst-green-600)"></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
    </div>
    </div>
  </div>

  <div class="col-12">
    <section class="panel">
      <div class="panel-header">
        <h2>Latest enquiries</h2>
        <a class="btn btn-outline-primary btn-sm" href="manage_enquiries.php">Open inbox</a>
      </div>
      <div class="table-responsive">
        <table class="table table-admin">
          <thead><tr><th>From</th><th>Subject</th><th>Received</th><th>Status</th></tr></thead>
          <tbody>
            <?php foreach ($enquiries as $q): ?>
              <tr>
                <td><?= e($q->FullName) ?><span class="cell-sub"><?= e($q->EmailId) ?></span></td>
                <td><?= e($q->Subject) ?></td>
                <td class="text-nowrap"><?= e(format_date($q->PostingDate, 'M j, Y H:i')) ?></td>
                <td><?= (int) $q->Status === 1 ? '<span class="status-badge status-confirmed">Read</span>' : '<span class="status-badge status-pending">New</span>' ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$enquiries): ?><tr><td colspan="4" class="text-center text-muted py-4">No enquiries yet.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </div>
</div>

<?php require __DIR__ . '/includes/layout_end.php'; ?>
