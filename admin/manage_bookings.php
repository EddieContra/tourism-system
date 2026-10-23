<?php
require_once __DIR__ . '/includes/auth.php';
$admin = require_admin();

$filters = ['all' => null, 'pending' => 0, 'confirmed' => 1, 'cancelled' => 2];
$filter = $_GET['status'] ?? 'all';
if (!array_key_exists($filter, $filters)) {
    $filter = 'all';
}

// ---------------------------------------------------------------------------
// Confirm / cancel a booking.
// ---------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['booking_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    $updates = [
        'confirm' => ["UPDATE tblbooking SET status = 1, CancelledBy = NULL WHERE BookingId = ? AND status = 0", 'confirmed', 'could not be confirmed (it may already be confirmed or cancelled)'],
        'cancel'  => ["UPDATE tblbooking SET status = 2, CancelledBy = 'a' WHERE BookingId = ? AND status <> 2", 'cancelled', 'is already cancelled'],
    ];

    if (isset($updates[$action])) {
        [$sql, $event, $failure] = $updates[$action];
        $stmt = $dbh->prepare($sql);
        $stmt->execute([$id]);

        if ($stmt->rowCount()) {
            $info = $dbh->prepare(
                'SELECT b.*, p.PackageName, p.PackagePrice, u.FullName
                   FROM tblbooking b
                   LEFT JOIN tbltourpackages p ON p.PackageId = b.PackageId
                   LEFT JOIN tblusers u ON u.EmailId = b.UserEmail
                  WHERE b.BookingId = ?'
            );
            $info->execute([$id]);
            $b = $info->fetch();
            $emailed = send_booking_email($event, [
                'email' => $b->UserEmail, 'name' => $b->FullName ?? 'traveller', 'id' => $id,
                'package' => $b->PackageName ?? 'your trip', 'from' => $b->FromDate, 'to' => $b->ToDate,
                'travellers' => (int) $b->Travellers, 'total' => (int) $b->PackagePrice * (int) $b->Travellers,
            ]);
            flash('success', "Booking #BK-$id $event." . ($emailed ? ' The customer has been emailed.' : ''));
        } else {
            flash('warning', "Booking #BK-$id $failure.");
        }
    }
    redirect('manage_bookings.php?status=' . urlencode($filter));
}

$counts = $dbh->query('SELECT status, COUNT(*) AS n FROM tblbooking GROUP BY status')->fetchAll(PDO::FETCH_KEY_PAIR);
$counts = ['all' => array_sum($counts), 'pending' => $counts[0] ?? 0, 'confirmed' => $counts[1] ?? 0, 'cancelled' => $counts[2] ?? 0];

$sql = 'SELECT b.*, p.PackageName, p.PackagePrice, u.FullName, u.MobileNumber
          FROM tblbooking b
          LEFT JOIN tbltourpackages p ON p.PackageId = b.PackageId
          LEFT JOIN tblusers u ON u.EmailId = b.UserEmail'
     . ($filters[$filter] !== null ? ' WHERE b.status = ?' : '')
     . ' ORDER BY b.status = 0 DESC, b.FromDate ASC';
$stmt = $dbh->prepare($sql);
$stmt->execute($filters[$filter] !== null ? [$filters[$filter]] : []);
$bookings = $stmt->fetchAll();

$statuses = [0 => ['Pending', 'pending'], 1 => ['Confirmed', 'confirmed'], 2 => ['Cancelled', 'cancelled']];

$pageTitle = 'Bookings';
$activeNav = 'bookings';
require __DIR__ . '/includes/layout_start.php';
?>
<section class="panel">
  <div class="panel-header">
    <ul class="nav nav-pills nav-pills-admin">
      <?php foreach (['all' => 'All', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'cancelled' => 'Cancelled'] as $key => $label): ?>
        <li class="nav-item">
          <a class="nav-link<?= $filter === $key ? ' active' : '' ?>" href="?status=<?= $key ?>"><?= $label ?><span class="count"><?= (int) $counts[$key] ?></span></a>
        </li>
      <?php endforeach; ?>
    </ul>
    <div class="input-icon" style="min-width: 240px">
      <i class="bi bi-search" aria-hidden="true"></i>
      <input class="form-control form-control-sm" type="search" placeholder="Search bookings…" aria-label="Search bookings" data-table-filter="#bookings-table">
    </div>
  </div>
  <div class="table-responsive">
    <table class="table table-admin" id="bookings-table">
      <thead>
        <tr><th>Booking</th><th>Customer</th><th>Package</th><th>Travel dates</th><th>Total</th><th>Status</th></tr>
      </thead>
      <tbody>
        <?php foreach ($bookings as $b): [$label, $class] = $statuses[(int) $b->status] ?? ['Unknown', 'pending']; ?>
          <tr>
            <td class="fw-semibold nowrap">#BK-<?= (int) $b->BookingId ?><span class="cell-sub">Requested <?= e(format_date($b->RegDate)) ?></span></td>
            <td>
              <?= e($b->FullName ?? 'Deleted user') ?>
              <span class="cell-sub"><a href="mailto:<?= e($b->UserEmail) ?>"><?= e($b->UserEmail) ?></a></span><?php if ($b->MobileNumber): ?><span class="cell-sub"><?= e($b->MobileNumber) ?></span><?php endif; ?>
            </td>
            <td class="col-min">
              <?= e($b->PackageName ?? '—') ?>
              <?php if ($b->Comment): ?><span class="cell-sub" title="<?= e($b->Comment) ?>"><i class="bi bi-chat-left-text me-1"></i><?= e(mb_strimwidth($b->Comment, 0, 50, '…')) ?></span><?php endif; ?>
            </td>
            <td class="text-nowrap"><?= e(format_date($b->FromDate, 'M j')) ?> – <?= e(format_date($b->ToDate, 'M j, Y')) ?></td>
            <td class="text-nowrap"><?= $b->PackagePrice !== null ? e(format_price($b->PackagePrice * $b->Travellers)) : '—' ?><span class="cell-sub"><?= (int) $b->Travellers ?> <?= (int) $b->Travellers === 1 ? 'guest' : 'guests' ?></span></td>
            <td class="nowrap">
              <span class="status-badge status-<?= $class ?>"><?= $label ?></span>
              <?php if ((int) $b->status === 2): ?><span class="cell-sub">by <?= $b->CancelledBy === 'u' ? 'customer' : 'staff' ?></span><?php endif; ?>
              <?php if ((int) $b->status !== 2): ?>
                <div class="table-actions flex-column align-items-start mt-2">
                  <?php if ((int) $b->status === 0): ?>
                    <form method="post"><?= csrf_field() ?>
                      <input type="hidden" name="booking_id" value="<?= (int) $b->BookingId ?>">
                      <button class="btn btn-primary btn-sm" name="action" value="confirm" type="submit">Confirm</button>
                    </form>
                  <?php endif; ?>
                  <form method="post" data-confirm="Cancel booking #BK-<?= (int) $b->BookingId ?>? The customer will see it as cancelled."><?= csrf_field() ?>
                    <input type="hidden" name="booking_id" value="<?= (int) $b->BookingId ?>">
                    <button class="btn btn-outline-danger btn-sm" name="action" value="cancel" type="submit">Cancel</button>
                  </form>
                </div>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <tr class="no-results"><td colspan="6" class="text-center text-muted py-4">No bookings match your search.</td></tr>
        <?php if (!$bookings): ?><tr><td colspan="6" class="text-center text-muted py-5">No bookings in this view.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
<?php require __DIR__ . '/includes/layout_end.php'; ?>
