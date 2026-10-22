<?php
require_once __DIR__ . '/includes/bootstrap.php';

$next = safe_next($_GET['next'] ?? $_POST['next'] ?? null, 'index.php');

if (user_logged_in()) {
    redirect($next);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim((string) ($_POST['fname'] ?? ''));
    $mobile   = trim((string) ($_POST['mobilenumber'] ?? ''));
    $email    = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirm  = (string) ($_POST['password_confirmation'] ?? '');

    $errors = [];
    if ($name === '' || mb_strlen($name) > 100) {
        $errors[] = 'Please enter your full name.';
    }
    if (!preg_match('/^\+?[0-9 ()-]{7,20}$/', $mobile)) {
        $errors[] = 'Please enter a valid mobile number.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
        $errors[] = 'Please enter a valid email address.';
    }
    if ($problem = password_problem($password)) {
        $errors[] = $problem;
    } elseif ($password !== $confirm) {
        $errors[] = 'The passwords do not match.';
    }

    if (!$errors) {
        $exists = $dbh->prepare('SELECT 1 FROM tblusers WHERE EmailId = ?');
        $exists->execute([$email]);
        if ($exists->fetchColumn()) {
            $errors[] = 'An account with this email already exists. Try signing in instead.';
        }
    }

    if ($errors) {
        remember_input(['fname', 'mobilenumber', 'email']);
        flash('danger', implode(' ', $errors));
        redirect('register.php?next=' . urlencode($next));
    }

    $stmt = $dbh->prepare('INSERT INTO tblusers (FullName, MobileNumber, EmailId, Password) VALUES (?, ?, ?, ?)');
    $stmt->execute([$name, $mobile, $email, password_hash($password, PASSWORD_DEFAULT)]);

    login_user((object) ['EmailId' => $email, 'FullName' => $name]);
    forget_input();
    flash('success', 'Welcome to Five Star Tours, ' . $name . '! Your account is ready.');
    redirect($next);
}

$pageTitle = 'Create account';
require __DIR__ . '/includes/header.php';
?>
<div class="auth-wrap">
  <div class="auth-media" style="background-image: url('assets/img/gallery/smb.jpg')">
    <div>
      <blockquote>“Adventure is worthwhile in itself.”</blockquote>
      <p class="text-white-50 mb-0">Create a free account to book tours and keep track of your trips.</p>
    </div>
  </div>
  <div class="auth-form">
    <div class="inner">
      <h1 class="h2 mb-2">Create your account</h1>
      <p class="text-muted mb-4">It only takes a minute.</p>

      <form method="post" class="needs-validation" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="next" value="<?= e($next) ?>">
        <div class="mb-3">
          <label class="form-label" for="fname">Full name</label>
          <input class="form-control" id="fname" name="fname" value="<?= old('fname') ?>" required maxlength="100" autocomplete="name">
          <div class="invalid-feedback">Please enter your full name.</div>
        </div>
        <div class="mb-3">
          <label class="form-label" for="email">Email address</label>
          <input class="form-control" type="email" id="email" name="email" value="<?= old('email') ?>" required maxlength="150" autocomplete="email" data-check-email aria-describedby="email-availability">
          <div id="email-availability" class="form-text" aria-live="polite"></div>
        </div>
        <div class="mb-3">
          <label class="form-label" for="mobilenumber">Mobile number</label>
          <input class="form-control" type="tel" id="mobilenumber" name="mobilenumber" value="<?= old('mobilenumber') ?>" required pattern="\+?[0-9 ()-]{7,20}" autocomplete="tel" placeholder="+255 7XX XXX XXX">
          <div class="invalid-feedback">Please enter a valid mobile number.</div>
        </div>
        <div class="mb-3">
          <label class="form-label" for="password">Password</label>
          <div class="position-relative">
            <input class="form-control" type="password" id="password" name="password" required minlength="8" pattern="(?=.*[A-Za-z])(?=.*\d).{8,}" autocomplete="new-password" aria-describedby="pw-help">
            <button class="password-toggle" type="button" data-password-toggle="password" aria-label="Show password"><i class="bi bi-eye"></i></button>
          </div>
          <div id="pw-help" class="form-text">At least 8 characters, including letters and numbers.</div>
        </div>
        <div class="mb-2">
          <label class="form-label" for="password_confirmation">Confirm password</label>
          <input class="form-control" type="password" id="password_confirmation" name="password_confirmation" required data-match="password" autocomplete="new-password">
          <div class="invalid-feedback">Passwords must match.</div>
        </div>
        <button class="btn btn-primary btn-lg w-100 mt-4" type="submit">Create account</button>
      </form>

      <p class="text-center text-muted mt-4 mb-0">Already have an account? <a href="login.php<?= $next !== 'index.php' ? '?next=' . urlencode($next) : '' ?>">Sign in</a></p>
    </div>
  </div>
</div>
<?php forget_input(); require __DIR__ . '/includes/footer.php'; ?>
