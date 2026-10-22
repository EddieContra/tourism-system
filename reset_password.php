<?php
require_once __DIR__ . '/includes/bootstrap.php';

$token = (string) ($_GET['token'] ?? $_POST['token'] ?? '');

$stmt = $dbh->prepare('SELECT * FROM tblpassword_resets WHERE TokenHash = ? AND ExpiresAt > ?');
$stmt->execute([hash('sha256', $token), date('Y-m-d H:i:s')]);
$reset = $token !== '' ? $stmt->fetch() : false;

if (!$reset) {
    flash('danger', 'This password reset link is invalid or has expired. Please request a new one.');
    redirect('forgot_password.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = (string) ($_POST['password'] ?? '');
    $confirm  = (string) ($_POST['password_confirmation'] ?? '');

    $error = password_problem($password) ?? ($password !== $confirm ? 'The passwords do not match.' : null);
    if ($error) {
        flash('danger', $error);
        redirect('reset_password.php?token=' . urlencode($token));
    }

    $dbh->prepare('UPDATE tblusers SET Password = ? WHERE EmailId = ?')
        ->execute([password_hash($password, PASSWORD_DEFAULT), $reset->EmailId]);
    $dbh->prepare('DELETE FROM tblpassword_resets WHERE EmailId = ?')->execute([$reset->EmailId]);

    flash('success', 'Your password has been reset. You can now sign in with your new password.');
    redirect('login.php');
}

$pageTitle = 'Choose a new password';
require __DIR__ . '/includes/header.php';
?>
<section class="section">
  <div class="container">
    <div class="card-surface mx-auto" style="max-width: 480px">
      <span class="icon-badge mb-3"><i class="bi bi-shield-lock"></i></span>
      <h1 class="h2 mb-2">Choose a new password</h1>
      <p class="text-muted mb-4">Resetting the password for <strong><?= e($reset->EmailId) ?></strong>.</p>
      <form method="post" class="needs-validation" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <div class="mb-3">
          <label class="form-label" for="password">New password</label>
          <div class="position-relative">
            <input class="form-control" type="password" id="password" name="password" required minlength="8" pattern="(?=.*[A-Za-z])(?=.*\d).{8,}" autocomplete="new-password" aria-describedby="pw-help">
            <button class="password-toggle" type="button" data-password-toggle="password" aria-label="Show password"><i class="bi bi-eye"></i></button>
          </div>
          <div id="pw-help" class="form-text">At least 8 characters, including letters and numbers.</div>
        </div>
        <div class="mb-4">
          <label class="form-label" for="password_confirmation">Confirm new password</label>
          <input class="form-control" type="password" id="password_confirmation" name="password_confirmation" required data-match="password" autocomplete="new-password">
          <div class="invalid-feedback">Passwords must match.</div>
        </div>
        <button class="btn btn-primary btn-lg w-100" type="submit">Reset password</button>
      </form>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
