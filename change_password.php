<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_user();

$email = current_user_email();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = (string) ($_POST['password'] ?? '');
    $new     = (string) ($_POST['newpassword'] ?? '');
    $confirm = (string) ($_POST['confirmpassword'] ?? '');

    $stmt = $dbh->prepare('SELECT Password FROM tblusers WHERE EmailId = ?');
    $stmt->execute([$email]);
    $hash = $stmt->fetchColumn();

    if (!verify_password($current, $hash ?: null)) {
        flash('danger', 'Your current password is incorrect.');
    } elseif ($problem = password_problem($new)) {
        flash('danger', $problem);
    } elseif ($new !== $confirm) {
        flash('danger', 'The new passwords do not match.');
    } else {
        $dbh->prepare('UPDATE tblusers SET Password = ? WHERE EmailId = ?')
            ->execute([password_hash($new, PASSWORD_DEFAULT), $email]);
        session_regenerate_id(true);
        flash('success', 'Your password has been changed.');
    }
    redirect('change_password.php');
}

$pageTitle = 'Change password';
$pageHeading = 'Change password';
$accountTab = 'password';
$activeNav = 'account';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/partials/account_start.php';
?>
<div class="card-surface" style="max-width: 560px">
  <form method="post" class="needs-validation" novalidate>
    <?= csrf_field() ?>
    <div class="mb-3">
      <label class="form-label" for="password">Current password</label>
      <div class="position-relative">
        <input class="form-control" type="password" id="password" name="password" required autocomplete="current-password">
        <button class="password-toggle" type="button" data-password-toggle="password" aria-label="Show password"><i class="bi bi-eye"></i></button>
      </div>
    </div>
    <div class="mb-3">
      <label class="form-label" for="newpassword">New password</label>
      <div class="position-relative">
        <input class="form-control" type="password" id="newpassword" name="newpassword" required minlength="8" pattern="(?=.*[A-Za-z])(?=.*\d).{8,}" autocomplete="new-password" aria-describedby="pw-help">
        <button class="password-toggle" type="button" data-password-toggle="newpassword" aria-label="Show password"><i class="bi bi-eye"></i></button>
      </div>
      <div id="pw-help" class="form-text">At least 8 characters, including letters and numbers.</div>
    </div>
    <div class="mb-4">
      <label class="form-label" for="confirmpassword">Confirm new password</label>
      <input class="form-control" type="password" id="confirmpassword" name="confirmpassword" required data-match="newpassword" autocomplete="new-password">
      <div class="invalid-feedback">Passwords must match.</div>
    </div>
    <button class="btn btn-primary" type="submit">Update password</button>
  </form>
</div>
<?php
require __DIR__ . '/includes/partials/account_end.php';
require __DIR__ . '/includes/footer.php';
