<?php
require_once __DIR__ . '/includes/bootstrap.php';

$pid = (int) ($_GET['pkgid'] ?? 0);
$stmt = $dbh->prepare('SELECT * FROM tbltourpackages WHERE PackageId = ?');
$stmt->execute([$pid]);
$pkg = $stmt->fetch();

if (!$pkg) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

$selfUrl = 'package_details.php?pkgid=' . $pid;

// ---------------------------------------------------------------------------
// Handle a booking request.
// ---------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!user_logged_in()) {
        flash('info', 'Please sign in to book this package.');
        redirect('login.php?next=' . urlencode($selfUrl));
    }

    $from       = (string) ($_POST['fromdate'] ?? '');
    $to         = (string) ($_POST['todate'] ?? '');
    $travellers = (int) ($_POST['travellers'] ?? 1);
    $comment    = trim((string) ($_POST['comment'] ?? ''));

    $fromDate = DateTimeImmutable::createFromFormat('!Y-m-d', $from);
    $toDate   = DateTimeImmutable::createFromFormat('!Y-m-d', $to);
    $tomorrow = new DateTimeImmutable('tomorrow');

    $errors = [];
    if (!$fromDate || !$toDate) {
        $errors[] = 'Please choose valid travel dates.';
    } elseif ($fromDate < $tomorrow) {
        $errors[] = 'Your trip must start tomorrow or later.';
    } elseif ($toDate < $fromDate) {
        $errors[] = 'The end date cannot be before the start date.';
    }
    if ($travellers < 1 || $travellers > 20) {
        $errors[] = 'Bookings are for 1 to 20 travellers. Contact us for larger groups.';
    }
    if (mb_strlen($comment) > 1000) {
        $errors[] = 'Special requests must be 1000 characters or fewer.';
    }

    if ($errors) {
        remember_input(['fromdate', 'todate', 'travellers', 'comment']);
        flash('danger', implode(' ', $errors));
        redirect($selfUrl . '#book');
    }

    $insert = $dbh->prepare('INSERT INTO tblbooking (PackageId, UserEmail, FromDate, ToDate, Travellers, Comment, status) VALUES (?, ?, ?, ?, ?, ?, 0)');
    $insert->execute([$pid, current_user_email(), $from, $to, $travellers, $comment ?: null]);
    $bookingId = (int) $dbh->lastInsertId();

    forget_input();
    flash('success', sprintf('Booking #BK-%d received! We’ll confirm availability shortly.', $bookingId));
    redirect('tour_history.php');
}

$features = array_filter(array_map('trim', preg_split('/[,\n]+/', (string) $pkg->PackageFetures)));
$related = $dbh->prepare('SELECT * FROM tbltourpackages WHERE PackageId <> ? ORDER BY RAND() LIMIT 3');
$related->execute([$pid]);
$related = $related->fetchAll();

$pageTitle = $pkg->PackageName;
$pageDescription = mb_strimwidth(preg_replace('/\s+/', ' ', (string) $pkg->PackageDetails), 0, 160, '…');
$activeNav = 'packages';
$navOverlay = true;
require __DIR__ . '/includes/header.php';
?>

<section class="detail-hero" style="background-image: url('<?= e(package_image_url($pkg->PackageImage)) ?>')">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-3">
        <li class="breadcrumb-item"><a class="text-white text-decoration-none" href="index.php">Home</a></li>
        <li class="breadcrumb-item"><a class="text-white text-decoration-none" href="packages.php">Packages</a></li>
        <li class="breadcrumb-item active text-white-50" aria-current="page"><?= e($pkg->PackageName) ?></li>
      </ol>
    </nav>
    <h1 class="mb-3"><?= e($pkg->PackageName) ?></h1>
    <div class="d-flex flex-wrap gap-2">
      <?php if ($pkg->PackageLocation): ?><span class="chip"><i class="bi bi-geo-alt"></i><?= e($pkg->PackageLocation) ?></span><?php endif; ?>
      <?php if ($pkg->PackageDuration): ?><span class="chip"><i class="bi bi-clock"></i><?= e($pkg->PackageDuration) ?></span><?php endif; ?>
      <?php if ($pkg->PackageType): ?><span class="chip"><i class="bi bi-compass"></i><?= e($pkg->PackageType) ?></span><?php endif; ?>
      <span class="chip"><i class="bi bi-hash"></i>PKG-<?= (int) $pkg->PackageId ?></span>
    </div>
  </div>
</section>

<section class="section-sm">
  <div class="container">
    <div class="row g-4 g-lg-5">
      <div class="col-lg-7 col-xl-8">
        <h2 class="h3 mb-3">Overview</h2>
        <div class="text-muted fs-6 lh-lg mb-5">
          <?php foreach (preg_split('/\R{2,}/', trim((string) $pkg->PackageDetails)) as $paragraph): ?>
            <p><?= nl2br(e(trim($paragraph))) ?></p>
          <?php endforeach; ?>
        </div>

        <?php if ($features): ?>
          <h2 class="h3 mb-3">Highlights &amp; inclusions</h2>
          <ul class="include-list mb-5">
            <?php foreach ($features as $feature): ?>
              <li><i class="bi bi-check-circle-fill"></i><span><?= e($feature) ?></span></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>

        <div class="card-surface bg-sand shadow-none">
          <h2 class="h5 mb-3"><i class="bi bi-info-circle me-2 text-amber"></i>Good to know</h2>
          <ul class="check-list small">
            <li><i class="bi bi-dot"></i>Prices are per person and include park entry fees and transport in a 4x4 safari vehicle.</li>
            <li><i class="bi bi-dot"></i>Bookings are confirmed by our team, usually within 24 hours.</li>
            <li><i class="bi bi-dot"></i>You can cancel free of charge from your account up to 24 hours before departure.</li>
          </ul>
        </div>
      </div>

      <div class="col-lg-5 col-xl-4" id="book">
        <div class="card-surface booking-card">
          <div class="price mb-4"><small>From</small><strong><?= e(format_price($pkg->PackagePrice)) ?></strong> <span class="text-muted">/ person</span></div>

          <form id="booking-form" method="post" action="<?= e($selfUrl) ?>" class="needs-validation" novalidate data-price="<?= (int) $pkg->PackagePrice ?>">
            <?= csrf_field() ?>
            <div class="row g-3">
              <div class="col-6">
                <label class="form-label" for="fromdate">Start date</label>
                <input class="form-control" type="date" id="fromdate" name="fromdate" value="<?= old('fromdate') ?>" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                <div class="invalid-feedback">Choose a start date.</div>
              </div>
              <div class="col-6">
                <label class="form-label" for="todate">End date</label>
                <input class="form-control" type="date" id="todate" name="todate" value="<?= old('todate') ?>" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                <div class="invalid-feedback">Choose an end date.</div>
              </div>
              <div class="col-12">
                <label class="form-label" for="travellers">Travellers</label>
                <input class="form-control" type="number" id="travellers" name="travellers" value="<?= old('travellers', '1') ?>" min="1" max="20" required>
              </div>
              <div class="col-12">
                <label class="form-label" for="comment">Special requests <span class="text-muted fw-normal">(optional)</span></label>
                <textarea class="form-control" id="comment" name="comment" rows="3" maxlength="1000" placeholder="Dietary needs, room preferences, pickup location…"><?= old('comment') ?></textarea>
              </div>
            </div>

            <div class="total-row my-4">
              <span class="text-muted">Estimated total</span>
              <strong class="fs-4" id="booking-total"><?= e(format_price($pkg->PackagePrice)) ?></strong>
            </div>

            <?php if (user_logged_in()): ?>
              <button class="btn btn-accent btn-lg w-100" type="submit">Request booking</button>
              <p class="form-text text-center mt-2 mb-0">No payment is taken now — we’ll confirm first.</p>
            <?php else: ?>
              <a class="btn btn-accent btn-lg w-100" href="login.php?next=<?= urlencode($selfUrl . '#book') ?>">Sign in to book</a>
              <p class="form-text text-center mt-2 mb-0">New here? <a href="register.php">Create a free account</a></p>
            <?php endif; ?>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<?php if ($related): ?>
  <section class="section-sm bg-sand">
    <div class="container">
      <h2 class="mb-4">You might also like</h2>
      <div class="row g-4">
        <?php foreach ($related as $pkg): ?>
          <div class="col-md-6 col-lg-4"><?php require __DIR__ . '/includes/partials/package_card.php'; ?></div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
<?php endif; ?>

<?php forget_input(); require __DIR__ . '/includes/footer.php'; ?>
