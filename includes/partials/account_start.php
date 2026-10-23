<?php
/** Opening markup for account pages. Expects $accountTab and $pageHeading. */
$accountLinks = [
    'bookings' => ['tour_history.php', 'suitcase2', 'My bookings'],
    'profile'  => ['profile.php', 'person', 'Profile'],
    'password' => ['change_password.php', 'shield-lock', 'Change password'],
];
?>
<section class="section-sm">
  <div class="container">
    <div class="row g-4">
      <aside class="col-lg-3">
        <div class="card-surface p-3">
          <div class="d-flex align-items-center gap-3 p-2 mb-2">
            <span class="icon-badge rounded-circle"><i class="bi bi-person"></i></span>
            <div class="text-truncate">
              <strong class="d-block text-truncate"><?= e($_SESSION['login2'] ?? '') ?></strong>
              <small class="text-muted text-truncate d-block"><?= e(current_user_email()) ?></small>
            </div>
          </div>
          <nav class="list-group account-nav" aria-label="Account">
            <?php foreach ($accountLinks as $key => [$href, $icon, $label]): ?>
              <a class="list-group-item list-group-item-action<?= $key === $accountTab ? ' active' : '' ?>" href="<?= $href ?>"<?= $key === $accountTab ? ' aria-current="page"' : '' ?>>
                <i class="bi bi-<?= $icon ?>"></i><?= $label ?>
              </a>
            <?php endforeach; ?>
          </nav>
        </div>
      </aside>
      <div class="col-lg-9">
        <h1 class="h2 mb-4"><?= e($pageHeading) ?></h1>
