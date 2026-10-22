<?php
require_once __DIR__ . '/includes/bootstrap.php';

$next = safe_next($_GET['next'] ?? $_POST['next'] ?? null, 'index.php');

if (user_logged_in()) {
    redirect($next);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $stmt = $dbh->prepare('SELECT * FROM tblusers WHERE EmailId = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && verify_password($password, $user->Password, $rehash)) {
        if ($rehash) {
            $dbh->prepare('UPDATE tblusers SET Password = ? WHERE id = ?')->execute([$rehash, $user->id]);
        }
        login_user($user);
        flash('success', 'Welcome back, ' . $user->FullName . '!');
        redirect($next);
    }

    remember_input(['email']);
    flash('danger', 'The email or password you entered is incorrect.');
    redirect('login.php?next=' . urlencode($next));
}

$pageTitle = 'Sign in';
require __DIR__ . '/includes/header.php';
?>
<div class="auth-wrap">
  <div class="auth-media" style="background-image: url('assets/img/gallery/twiga.jpg')">
    <div>
      <blockquote>“Take only memories, leave only footprints.”</blockquote>
      <p class="text-white-50 mb-0">Sign in to manage your bookings and plan your next adventure.</p>
    </div>
  </div>
  <div class="auth-form">
    <div class="inner">
      <h1 class="h2 mb-2">Welcome back</h1>
      <p class="text-muted mb-4">Sign in to your Five Star Tours account.</p>

      <form method="post" class="needs-validation" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="next" value="<?= e($next) ?>">
        <div class="mb-3">
          <label class="form-label" for="email">Email address</label>
          <div class="input-icon">
            <i class="bi bi-envelope" aria-hidden="true"></i>
            <input class="form-control" type="email" id="email" name="email" value="<?= old('email') ?>" required autocomplete="email" autofocus>
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

      <p class="text-center text-muted mt-4 mb-0">Don’t have an account? <a href="register.php<?= $next !== 'index.php' ? '?next=' . urlencode($next) : '' ?>">Create one</a></p>
    </div>
  </div>
</div>
<?php forget_input(); require __DIR__ . '/includes/footer.php'; ?>
