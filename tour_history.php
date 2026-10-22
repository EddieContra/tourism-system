<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_user();

$email = current_user_email();

/** A booking can be cancelled while it is active and starts more than 24 hours from now. */
function can_cancel(object $booking): bool
{
    return in_array((int) $booking->status, [0, 1], true)
        && strtotime($booking->FromDate . ' 00:00:00') > time() + 86400;
}

// ---------------------------------------------------------------------------
// Cancel a booking (POST + CSRF).
// ---------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_id'])) {
    $stmt = $dbh->prepare('SELECT * FROM tblbooking WHERE BookingId = ? AND UserEmail = ?');
    $stmt->execute([(int) $_POST['cancel_id'], $email]);
    $booking = $stmt->fetch();

    if (!$booking) {
        flash('danger', 'Booking not found.');
    } elseif (!can_cancel($booking)) {
        flash('warning', 'This booking can no longer be cancelled online. Bookings can be cancelled up to 24 hours before departure — please contact us for help.');
    } else {
        $dbh->prepare("UPDATE tblbooking SET status = 2, CancelledBy = 'u' WHERE BookingId = ? AND UserEmail = ?")
            ->execute([$booking->BookingId, $email]);
        flash('success', sprintf('Booking #BK-%d has been cancelled.', $booking->BookingId));
    }
    redirect('tour_history.php');
}

$stmt = $dbh->prepare(
    'SELECT b.*, p.PackageName, p.PackageImage, p.PackagePrice, p.PackageLocation
       FROM tblbooking b
       LEFT JOIN tbltourpackages p ON p.PackageId = b.PackageId
      WHERE b.UserEmail = ?
      ORDER BY b.RegDate DESC'
);
$stmt->execute([$email]);
$bookings = $stmt->fetchAll();

$statuses = [0 => ['Pending', 'pending'], 1 => ['Confirmed', 'confirmed'], 2 => ['Cancelled', 'cancelled']];

$pageTitle = 'My bookings';
$pageHeading = 'My bookings';
$accountTab = 'bookings';
$activeNav = 'account';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/partials/account_start.php';
?>

<?php if (!$bookings): ?>
  <div class="card-surface empty-state">
    <span class="icon-badge"><i class="bi bi-suitcase2"></i></span>
    <h2 class="h4">No trips booked yet</h2>
    <p class="text-muted">When you book a package it will appear here so you can track its status.</p>
    <a href="packages.php" class="btn btn-primary">Browse packages</a>
  </div>
<?php else: ?>
  <div class="d-grid gap-3">
    <?php foreach ($bookings as $b): ?>
      <?php [$statusLabel, $statusClass] = $statuses[(int) $b->status] ?? ['Unknown', 'pending']; ?>
      <article class="booking-item">
        <div class="row g-0">
          <div class="col-md-3">
            <img src="<?= e(package_image_url($b->PackageImage)) ?>" alt="" loading="lazy">
          </div>
          <div class="col-md-9">
            <div class="p-3 p-md-4">
              <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                <div>
                  <small class="text-muted">Booking #BK-<?= (int) $b->BookingId ?> · requested <?= e(format_date($b->RegDate)) ?></small>
                  <h2 class="h5 mb-0 mt-1">
                    <?php if ($b->PackageName): ?>
                      <a class="text-reset text-decoration-none" href="package_details.php?pkgid=<?= (int) $b->PackageId ?>"><?= e($b->PackageName) ?></a>
                    <?php else: ?>
                      Package no longer available
                    <?php endif; ?>
                  </h2>
                </div>
                <span class="status-badge status-<?= $statusClass ?> align-self-start">
                  <?= $statusLabel ?><?php if ((int) $b->status === 2): ?> by <?= $b->CancelledBy === 'a' ? 'Five Star Tours' : 'you' ?><?php endif; ?>
                </span>
              </div>
              <div class="package-meta mb-2">
                <span><i class="bi bi-calendar-event"></i><?= e(format_date($b->FromDate)) ?> – <?= e(format_date($b->ToDate)) ?></span>
                <span><i class="bi bi-people"></i><?= (int) $b->Travellers ?> <?= (int) $b->Travellers === 1 ? 'traveller' : 'travellers' ?></span>
                <?php if ($b->PackagePrice !== null): ?>
                  <span><i class="bi bi-cash-coin"></i><?= e(format_price($b->PackagePrice * $b->Travellers)) ?> total</span>
                <?php endif; ?>
              </div>
              <?php if ($b->Comment): ?>
                <p class="small text-muted mb-2"><i class="bi bi-chat-left-text me-1"></i><?= e($b->Comment) ?></p>
              <?php endif; ?>
              <?php if (can_cancel($b)): ?>
                <form method="post" class="mt-3" data-confirm="Cancel booking #BK-<?= (int) $b->BookingId ?>? This cannot be undone.">
                  <?= csrf_field() ?>
                  <input type="hidden" name="cancel_id" value="<?= (int) $b->BookingId ?>">
                  <button class="btn btn-outline-danger btn-sm" type="submit"><i class="bi bi-x-circle me-1"></i>Cancel booking</button>
                </form>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php
require __DIR__ . '/includes/partials/account_end.php';
require __DIR__ . '/includes/footer.php';
