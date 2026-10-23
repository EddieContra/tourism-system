<?php
/**
 * Public site header: <head>, navigation and flash messages.
 *
 * Optional variables set by the including page:
 *   $pageTitle       string  Title shown in the browser tab
 *   $pageDescription string  Meta description
 *   $activeNav       string  'home' | 'packages' | 'account'
 *   $navOverlay      bool    Transparent navbar on top of a hero image
 *   $pageImage       string  Image shown when the page is shared (relative path)
 *   $preloadImage    string  Above-the-fold image to preload (relative path)
 */
$pageTitle = isset($pageTitle) ? $pageTitle . ' · ' . APP_NAME : APP_NAME . ' · Safaris & Tours in Arusha, Tanzania';
$pageDescription ??= 'Five Star Tours is an Arusha-based tour company offering curated safaris, mountain treks and cultural experiences across northern Tanzania.';
$activeNav ??= '';
$navOverlay ??= false;
$pageImage ??= 'assets/img/hero.jpg';
$preloadImage ??= null;
$flashes = take_flashes();
$flashIcons = ['success' => 'check-circle-fill', 'danger' => 'exclamation-octagon-fill', 'warning' => 'exclamation-triangle-fill', 'info' => 'info-circle-fill'];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?></title>
  <meta name="description" content="<?= e($pageDescription) ?>">
  <meta name="theme-color" content="#15402f">
  <meta property="og:title" content="<?= e($pageTitle) ?>">
  <meta property="og:description" content="<?= e($pageDescription) ?>">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="<?= e(APP_NAME) ?>">
  <meta property="og:image" content="<?= e(app_url($pageImage)) ?>">
  <meta name="twitter:card" content="summary_large_image">
  <script src="assets/js/head.js"></script>
  <link rel="icon" href="assets/img/favicon.svg" type="image/svg+xml">
  <link rel="preload" href="assets/fonts/inter-latin-400-normal.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="assets/fonts/playfair-display-latin-700-normal.woff2" as="font" type="font/woff2" crossorigin>
  <?php if ($preloadImage): ?><link rel="preload" href="<?= e($preloadImage) ?>" as="image" fetchpriority="high"><?php endif; ?>
  <link rel="stylesheet" href="assets/vendor/bootstrap/bootstrap.min.css">
  <link rel="stylesheet" href="assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
  <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>

<nav class="navbar navbar-expand-lg sticky-top site-nav<?= $navOverlay ? ' nav-overlay' : '' ?>" aria-label="Main navigation">
  <div class="container">
    <a class="navbar-brand" href="index.php">
      <span class="brand-mark" aria-hidden="true"><i class="bi bi-compass"></i></span>
      Five Star Tours
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
      <i class="bi bi-list fs-2"></i>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link<?= $activeNav === 'home' ? ' active' : '' ?>" href="index.php"<?= $activeNav === 'home' ? ' aria-current="page"' : '' ?>>Home</a></li>
        <li class="nav-item"><a class="nav-link<?= $activeNav === 'packages' ? ' active' : '' ?>" href="packages.php"<?= $activeNav === 'packages' ? ' aria-current="page"' : '' ?>>Packages</a></li>
        <li class="nav-item"><a class="nav-link" href="index.php#about">About</a></li>
        <li class="nav-item"><a class="nav-link" href="index.php#gallery">Gallery</a></li>
        <li class="nav-item"><a class="nav-link" href="index.php#contact">Contact</a></li>
      </ul>
      <div class="d-flex align-items-lg-center gap-2 flex-column flex-lg-row">
        <?php if (user_logged_in()): ?>
          <div class="dropdown">
            <button class="btn btn-outline-primary btn-sm dropdown-toggle w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-person-circle me-1"></i><?= e($_SESSION['login2'] ?? 'My account') ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
              <li><a class="dropdown-item" href="tour_history.php"><i class="bi bi-suitcase2 me-2"></i>My bookings</a></li>
              <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
              <li><a class="dropdown-item" href="change_password.php"><i class="bi bi-shield-lock me-2"></i>Change password</a></li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <form action="logout.php" method="post">
                  <?= csrf_field() ?>
                  <button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Sign out</button>
                </form>
              </li>
            </ul>
          </div>
        <?php else: ?>
          <a class="nav-link px-lg-2" href="login.php">Sign in</a>
          <a class="btn btn-accent btn-sm" href="register.php">Create account</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<?php if ($flashes): ?>
  <div class="flash-stack" role="status" aria-live="polite">
    <?php foreach ($flashes as $flash): ?>
      <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show">
        <i class="bi bi-<?= $flashIcons[$flash['type']] ?? 'info-circle-fill' ?>" aria-hidden="true"></i>
        <div>
          <?= e($flash['message']) ?>
          <?php if (!empty($flash['link'])): ?>
            <a href="<?= e($flash['link'][0]) ?>" class="d-block mt-1"><?= e($flash['link'][1]) ?></a>
          <?php endif; ?>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<main id="main">
