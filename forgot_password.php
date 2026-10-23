<?php
require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('danger', 'Please enter a valid email address.');
        redirect('forgot_password.php');
    }

    $throttle = throttle_key('reset', $email);
    if (too_many_attempts($throttle, 3)) {
        flash('danger', 'Too many reset requests for this email. Please wait 15 minutes and try again.');
        redirect('forgot_password.php');
    }
    record_attempt($throttle);

    $stmt = $dbh->prepare('SELECT FullName FROM tblusers WHERE EmailId = ?');
    $stmt->execute([$email]);
    $name = $stmt->fetchColumn();

    $devLink = null;
    if ($name !== false) {
        // One active token per account; only its hash is stored.
        $token = bin2hex(random_bytes(32));
        $dbh->prepare('DELETE FROM tblpassword_resets WHERE EmailId = ?')->execute([$email]);
        $dbh->prepare('INSERT INTO tblpassword_resets (EmailId, TokenHash, ExpiresAt) VALUES (?, ?, ?)')
            ->execute([$email, hash('sha256', $token), date('Y-m-d H:i:s', time() + 3600)]);

        $link = app_url('reset_password.php?token=' . $token);
        $sent = send_mail(
            $email,
            'Reset your Five Star Tours password',
            "Hi $name,\n\nWe received a request to reset your password. Use the link below within the next hour:\n\n$link\n\nIf you didn't ask for this, you can safely ignore this email.\n\n— Five Star Tours"
        );
        if (!$sent && APP_ENV === 'local') {
            $devLink = $link;
        }
    }

    // Same response whether or not the account exists, so emails can't be probed.
    flash('success', 'If an account exists for that email, we’ve sent a link to reset your password. It expires in one hour.');
    if ($devLink) {
        flash('info', 'Development mode: email delivery is not configured, so here is the reset link.', [$devLink, 'Open reset link']);
    }
    redirect('forgot_password.php');
}

$pageTitle = 'Forgot password';
require __DIR__ . '/includes/header.php';
?>
<section class="section">
  <div class="container">
    <div class="card-surface mx-auto" style="max-width: 480px">
      <span class="icon-badge mb-3"><i class="bi bi-key"></i></span>
      <h1 class="h2 mb-2">Forgot your password?</h1>
      <p class="text-muted mb-4">Enter the email you registered with and we’ll send you a secure link to choose a new password.</p>
      <form method="post" class="needs-validation" novalidate>
        <?= csrf_field() ?>
        <div class="mb-4">
          <label class="form-label" for="email">Email address</label>
          <input class="form-control" type="email" id="email" name="email" required autocomplete="email" autofocus>
          <div class="invalid-feedback">Please enter a valid email address.</div>
        </div>
        <button class="btn btn-primary btn-lg w-100" type="submit">Send reset link</button>
      </form>
      <p class="text-center mt-4 mb-0"><a href="login.php"><i class="bi bi-arrow-left me-1"></i>Back to sign in</a></p>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
