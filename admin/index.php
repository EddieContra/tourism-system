<?php
require_once __DIR__ . '/includes/auth.php';

if (current_admin()) {
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $stmt = $dbh->prepare('SELECT * FROM tbladmin WHERE UserName = ?');
    $stmt->execute([$username]);
    $staff = $stmt->fetch();

    if ($staff && verify_password($password, $staff->Password, $rehash)) {
        if ((int) $staff->Status !== 1) {
            flash('danger', 'Your account has been deactivated. Please contact an administrator.');
            redirect('index.php');
        }
        if ($rehash) {
            $dbh->prepare('UPDATE tbladmin SET Password = ? WHERE ID = ?')->execute([$rehash, $staff->ID]);
        }
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int) $staff->ID;
        flash('success', 'Welcome back, ' . $staff->FirstName . '!');
        redirect('dashboard.php');
    }

    $_SESSION['_old'] = ['username' => $username];
    flash('danger', 'Invalid username or password.');
    redirect('index.php');
}

$flashes = take_flashes();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title>Staff sign in · Five Star Tours</title>
  <link rel="icon" href="../assets/img/favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="../assets/vendor/bootstrap/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../assets/css/app.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<main class="admin-login">
  <div class="card-surface">
    <div class="text-center mb-4">
      <span class="brand-mark mx-auto mb-3" style="width:52px;height:52px;font-size:1.5rem"><i class="bi bi-compass"></i></span>
      <h1 class="h3 mb-1">Staff portal</h1>
      <p class="text-muted mb-0">Sign in to manage Five Star Tours</p>
    </div>

    <?php foreach ($flashes as $flash): ?>
      <div class="alert alert-<?= e($flash['type']) ?> py-2 small" role="alert"><?= e($flash['message']) ?></div>
    <?php endforeach; ?>

    <form method="post" class="needs-validation" novalidate>
      <?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label" for="username">Username</label>
        <div class="input-icon">
          <i class="bi bi-person" aria-hidden="true"></i>
          <input class="form-control" id="username" name="username" value="<?= old('username') ?>" required autocomplete="username" autofocus>
        </div>
      </div>
      <div class="mb-2">
        <div class="d-flex justify-content-between">
          <label class="form-label" for="password">Password</label>
          <a class="small" href="forgot_password.php">Forgot password?</a>
        </div>
        <div class="input-icon position-relative">
          <i class="bi bi-lock" aria-hidden="true"></i>
          <input class="form-control" type="password" id="password" name="password" required autocomplete="current-password">
          <button class="password-toggle" type="button" data-password-toggle="password" aria-label="Show password"><i class="bi bi-eye"></i></button>
        </div>
      </div>
      <button class="btn btn-primary btn-lg w-100 mt-4" type="submit">Sign in</button>
    </form>

    <?php if (APP_ENV === 'local'): ?>
      <div class="demo-hint mt-4">
        <strong><i class="bi bi-info-circle me-1"></i>Demo accounts</strong><br>
        Admin: <code>admin</code> / <code>Admin@1234</code><br>
        Staff: <code>staff</code> / <code>Staff@1234</code>
      </div>
    <?php endif; ?>

    <p class="text-center mt-4 mb-0 small"><a href="../index.php"><i class="bi bi-arrow-left me-1"></i>Back to website</a></p>
  </div>
</main>
<script src="../assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
<script src="../assets/js/app.js"></script>
</body>
</html>
<?php forget_input(); ?>
