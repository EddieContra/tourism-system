<?php
require_once __DIR__ . '/includes/auth.php';
$admin = require_admin();
require_staff_manager($admin);

$view = ($_GET['view'] ?? 'active') === 'inactive' ? 'inactive' : 'active';

// ---------------------------------------------------------------------------
// Deactivate / reactivate an account.
// ---------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['staff_id'])) {
    $id = (int) $_POST['staff_id'];
    $stmt = $dbh->prepare('SELECT * FROM tbladmin WHERE ID = ?');
    $stmt->execute([$id]);
    $target = $stmt->fetch();

    if (!$target || !can_manage_account($admin, $target)) {
        flash('danger', 'You cannot change this account.');
    } elseif ($target->ID === $admin->ID) {
        flash('warning', 'You cannot deactivate your own account.');
    } else {
        $status = ($_POST['action'] ?? '') === 'restore' ? 1 : 0;
        $dbh->prepare('UPDATE tbladmin SET Status = ? WHERE ID = ?')->execute([$status, $id]);
        flash('success', sprintf('%s %s has been %s.', $target->FirstName, $target->LastName, $status ? 'reactivated' : 'deactivated'));
    }
    redirect('staff.php?view=' . $view);
}

$stmt = $dbh->prepare("SELECT * FROM tbladmin WHERE Status = ? ORDER BY FIELD(AdminName, 'Superuser', 'Admin', 'User'), FirstName");
$stmt->execute([$view === 'active' ? 1 : 0]);
$staff = $stmt->fetchAll();
$counts = $dbh->query('SELECT Status, COUNT(*) FROM tbladmin GROUP BY Status')->fetchAll(PDO::FETCH_KEY_PAIR);

$pageTitle = 'Staff';
$activeNav = 'staff';
require __DIR__ . '/includes/layout_start.php';
?>
<section class="panel">
  <div class="panel-header">
    <ul class="nav nav-pills nav-pills-admin">
      <li class="nav-item"><a class="nav-link<?= $view === 'active' ? ' active' : '' ?>" href="?view=active">Active<span class="count"><?= (int) ($counts[1] ?? 0) ?></span></a></li>
      <li class="nav-item"><a class="nav-link<?= $view === 'inactive' ? ' active' : '' ?>" href="?view=inactive">Deactivated<span class="count"><?= (int) ($counts[0] ?? 0) ?></span></a></li>
    </ul>
    <a class="btn btn-primary btn-sm" href="staff_form.php"><i class="bi bi-person-plus me-1"></i>Add staff member</a>
  </div>
  <div class="table-responsive">
    <table class="table table-admin">
      <thead><tr><th>Name</th><th>Username</th><th>Staff ID</th><th>Role</th><th>Contact</th><th>Joined</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
        <?php foreach ($staff as $s): $manageable = can_manage_account($admin, $s); ?>
          <tr>
            <td>
              <div class="d-flex align-items-center gap-3">
                <img class="rounded-circle" src="<?= e(profile_image_url($s->Photo)) ?>" alt="" width="38" height="38" style="object-fit: cover">
                <span class="fw-semibold"><?= e($s->FirstName . ' ' . $s->LastName) ?><?= $s->ID === $admin->ID ? ' <span class="badge text-bg-light">You</span>' : '' ?></span>
              </div>
            </td>
            <td><code><?= e($s->UserName) ?></code></td>
            <td><?= e($s->Staffid ?: '—') ?></td>
            <td><span class="badge rounded-pill <?= $s->AdminName === 'User' ? 'text-bg-light' : 'text-bg-success' ?>"><?= e(role_label($s->AdminName)) ?></span></td>
            <td><?= e($s->Email) ?><span class="cell-sub"><?= e($s->MobileNumber) ?></span></td>
            <td class="text-nowrap"><?= e(format_date($s->AdminRegdate)) ?></td>
            <td>
              <div class="table-actions">
                <?php if ($manageable): ?>
                  <a class="btn btn-light btn-sm" href="staff_form.php?id=<?= (int) $s->ID ?>"><i class="bi bi-pencil me-1"></i>Edit</a>
                  <?php if ($s->ID !== $admin->ID): ?>
                    <form method="post"<?= $view === 'active' ? ' data-confirm="Deactivate ' . e($s->FirstName) . '? They will no longer be able to sign in."' : '' ?>><?= csrf_field() ?>
                      <input type="hidden" name="staff_id" value="<?= (int) $s->ID ?>">
                      <?php if ($view === 'active'): ?>
                        <button class="btn btn-outline-danger btn-sm" name="action" value="deactivate" type="submit">Deactivate</button>
                      <?php else: ?>
                        <button class="btn btn-primary btn-sm" name="action" value="restore" type="submit">Reactivate</button>
                      <?php endif; ?>
                    </form>
                  <?php endif; ?>
                <?php else: ?>
                  <span class="text-muted small">Super admin only</span>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$staff): ?><tr><td colspan="7" class="text-center text-muted py-5">No <?= $view === 'active' ? 'active' : 'deactivated' ?> staff accounts.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
<p class="text-muted small mt-3"><i class="bi bi-info-circle me-1"></i><strong>Staff</strong> can manage packages, bookings, enquiries and customers. <strong>Admins</strong> can also manage staff accounts. Only <strong>Super admins</strong> can manage other super admins.</p>
<?php require __DIR__ . '/includes/layout_end.php'; ?>
