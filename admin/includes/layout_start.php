<?php
/**
 * Admin layout (top). Expects:
 *   $admin      object  current staff member (from require_admin())
 *   $pageTitle  string
 *   $activeNav  string  key of the active sidebar item
 */
$pendingBookings = (int) $dbh->query('SELECT COUNT(*) FROM tblbooking WHERE status = 0')->fetchColumn();
$newEnquiries    = (int) $dbh->query('SELECT COUNT(*) FROM tblenquiry WHERE Status = 0')->fetchColumn();

$nav = [
    'dashboard' => ['dashboard.php', 'speedometer2', 'Dashboard', 0],
    'packages'  => ['manage_packages.php', 'map', 'Packages', 0],
    'bookings'  => ['manage_bookings.php', 'calendar-check', 'Bookings', $pendingBookings],
    'enquiries' => ['manage_enquiries.php', 'envelope', 'Enquiries', $newEnquiries],
    'customers' => ['manage_users.php', 'people', 'Customers', 0],
];
if (can_manage_staff($admin)) {
    $nav['staff'] = ['staff.php', 'person-badge', 'Staff', 0];
}
$flashes = take_flashes();
$flashIcons = ['success' => 'check-circle-fill', 'danger' => 'exclamation-octagon-fill', 'warning' => 'exclamation-triangle-fill', 'info' => 'info-circle-fill'];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title><?= e($pageTitle) ?> · Five Star Tours Admin</title>
  <link rel="icon" href="../assets/img/favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="../assets/vendor/bootstrap/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../assets/css/app.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin">
<a class="skip-link" href="#main">Skip to content</a>

<div class="admin-shell">
  <aside class="offcanvas-lg offcanvas-start admin-sidebar" tabindex="-1" id="adminSidebar" aria-label="Admin navigation">
    <div class="offcanvas-header">
      <a class="admin-brand" href="dashboard.php"><span class="brand-mark"><i class="bi bi-compass"></i></span>Five Star Tours</a>
      <button type="button" class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#adminSidebar" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body flex-column">
      <p class="nav-heading">Manage</p>
      <nav class="nav flex-column">
        <?php foreach ($nav as $key => [$href, $icon, $label, $badge]): ?>
          <a class="nav-link<?= $key === $activeNav ? ' active' : '' ?>" href="<?= $href ?>"<?= $key === $activeNav ? ' aria-current="page"' : '' ?>>
            <i class="bi bi-<?= $icon ?>"></i><span><?= $label ?></span>
            <?php if ($badge > 0): ?><span class="badge rounded-pill ms-auto"><?= $badge ?></span><?php endif; ?>
          </a>
        <?php endforeach; ?>
      </nav>
      <p class="nav-heading mt-4">Account</p>
      <nav class="nav flex-column">
        <a class="nav-link<?= $activeNav === 'profile' ? ' active' : '' ?>" href="profile.php"><i class="bi bi-person-circle"></i><span>My profile</span></a>
        <a class="nav-link<?= $activeNav === 'password' ? ' active' : '' ?>" href="change_password.php"><i class="bi bi-shield-lock"></i><span>Change password</span></a>
        <a class="nav-link" href="../index.php" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i><span>View website</span></a>
      </nav>
      <form action="logout.php" method="post" class="mt-auto pt-4">
        <?= csrf_field() ?>
        <button class="nav-link w-100 text-start border-0 bg-transparent" type="submit"><i class="bi bi-box-arrow-right"></i><span>Sign out</span></button>
      </form>
    </div>
  </aside>

  <div class="admin-main">
    <header class="admin-topbar">
      <button class="btn btn-light d-lg-none rounded-3 px-2 py-1" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-controls="adminSidebar" aria-label="Open menu">
        <i class="bi bi-list fs-4"></i>
      </button>
      <h1 class="h4 mb-0 me-auto"><?= e($pageTitle) ?></h1>
      <div class="dropdown">
        <button class="btn btn-light admin-user dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <img src="<?= e(profile_image_url($admin->Photo)) ?>" alt="" width="34" height="34">
          <span class="d-none d-sm-inline text-start lh-sm">
            <strong class="d-block"><?= e(trim($admin->FirstName . ' ' . $admin->LastName)) ?></strong>
            <small class="text-muted"><?= e(role_label($admin->AdminName)) ?></small>
          </span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
          <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>My profile</a></li>
          <li><a class="dropdown-item" href="change_password.php"><i class="bi bi-shield-lock me-2"></i>Change password</a></li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <form action="logout.php" method="post"><?= csrf_field() ?>
              <button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Sign out</button>
            </form>
          </li>
        </ul>
      </div>
    </header>

    <main id="main" class="admin-content">
      <?php foreach ($flashes as $flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show d-flex gap-2" role="alert">
          <i class="bi bi-<?= $flashIcons[$flash['type']] ?? 'info-circle-fill' ?>" aria-hidden="true"></i>
          <div><?= e($flash['message']) ?></div>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endforeach; ?>
