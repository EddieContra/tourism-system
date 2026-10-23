<?php
require_once __DIR__ . '/includes/auth.php';
$admin = require_admin();

// ---------------------------------------------------------------------------
// Delete a package (only when it has no bookings, to keep booking history).
// ---------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int) $_POST['delete_id'];
    $stmt = $dbh->prepare('SELECT PackageName, PackageImage, (SELECT COUNT(*) FROM tblbooking WHERE PackageId = p.PackageId) AS bookings FROM tbltourpackages p WHERE PackageId = ?');
    $stmt->execute([$id]);
    $pkg = $stmt->fetch();

    if (!$pkg) {
        flash('danger', 'Package not found.');
    } elseif ($pkg->bookings > 0) {
        flash('warning', sprintf('“%s” has %d booking(s) and cannot be deleted. Edit it instead to keep booking history intact.', $pkg->PackageName, $pkg->bookings));
    } else {
        $dbh->prepare('DELETE FROM tbltourpackages WHERE PackageId = ?')->execute([$id]);
        if ($pkg->PackageImage && is_file(package_upload_dir() . '/' . basename($pkg->PackageImage))) {
            @unlink(package_upload_dir() . '/' . basename($pkg->PackageImage));
        }
        flash('success', sprintf('“%s” was deleted.', $pkg->PackageName));
    }
    redirect('manage_packages.php');
}

$packages = $dbh->query(
    'SELECT p.*, COUNT(b.BookingId) AS bookings
       FROM tbltourpackages p
       LEFT JOIN tblbooking b ON b.PackageId = p.PackageId
      GROUP BY p.PackageId
      ORDER BY p.Creationdate DESC'
)->fetchAll();

$pageTitle = 'Packages';
$activeNav = 'packages';
require __DIR__ . '/includes/layout_start.php';
?>
<section class="panel">
  <div class="panel-header">
    <div class="input-icon" style="min-width: 260px">
      <i class="bi bi-search" aria-hidden="true"></i>
      <input class="form-control form-control-sm" type="search" placeholder="Search packages…" aria-label="Search packages" data-table-filter="#packages-table">
    </div>
    <a class="btn btn-primary btn-sm" href="package_form.php"><i class="bi bi-plus-lg me-1"></i>New package</a>
  </div>
  <div class="table-responsive">
    <table class="table table-admin" id="packages-table">
      <thead><tr><th>Package</th><th>Type</th><th>Duration</th><th>Price / person</th><th>Bookings</th><th>Last updated</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
        <?php foreach ($packages as $p): ?>
          <tr>
            <td>
              <div class="d-flex align-items-center gap-3">
                <img class="thumb" src="<?= e(package_image_url($p->PackageImage)) ?>" alt="">
                <div><span class="fw-semibold"><?= e($p->PackageName) ?></span><span class="cell-sub"><i class="bi bi-geo-alt me-1"></i><?= e($p->PackageLocation) ?></span></div>
              </div>
            </td>
            <td><?= e($p->PackageType) ?></td>
            <td class="text-nowrap"><?= e($p->PackageDuration ?: '—') ?></td>
            <td class="text-nowrap fw-semibold"><?= e(format_price($p->PackagePrice)) ?></td>
            <td><?= (int) $p->bookings ?></td>
            <td class="text-nowrap"><?= e(format_date($p->UpdationDate ?: $p->Creationdate)) ?></td>
            <td>
              <div class="table-actions">
                <a class="btn btn-light btn-icon" href="../package_details.php?pkgid=<?= (int) $p->PackageId ?>" target="_blank" rel="noopener" title="View on website" aria-label="View <?= e($p->PackageName) ?> on website"><i class="bi bi-eye"></i></a>
                <a class="btn btn-light btn-icon" href="package_form.php?id=<?= (int) $p->PackageId ?>" title="Edit" aria-label="Edit <?= e($p->PackageName) ?>"><i class="bi bi-pencil"></i></a>
                <form method="post" data-confirm="Delete “<?= e($p->PackageName) ?>”? This cannot be undone."><?= csrf_field() ?>
                  <input type="hidden" name="delete_id" value="<?= (int) $p->PackageId ?>">
                  <button class="btn btn-light btn-icon text-danger" type="submit" title="Delete" aria-label="Delete <?= e($p->PackageName) ?>"><i class="bi bi-trash"></i></button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        <tr class="no-results"><td colspan="7" class="text-center text-muted py-4">No packages match your search.</td></tr>
        <?php if (!$packages): ?><tr><td colspan="7" class="text-center text-muted py-5">No packages yet. <a href="package_form.php">Create the first one</a>.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
<?php require __DIR__ . '/includes/layout_end.php'; ?>
