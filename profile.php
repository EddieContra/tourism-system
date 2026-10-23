<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_user();

$email = current_user_email();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name   = trim((string) ($_POST['name'] ?? ''));
    $mobile = trim((string) ($_POST['mobileno'] ?? ''));

    if ($name === '' || mb_strlen($name) > 100) {
        flash('danger', 'Please enter your full name.');
    } elseif (!preg_match('/^\+?[0-9 ()-]{7,20}$/', $mobile)) {
        flash('danger', 'Please enter a valid mobile number.');
    } else {
        $dbh->prepare('UPDATE tblusers SET FullName = ?, MobileNumber = ? WHERE EmailId = ?')
            ->execute([$name, $mobile, $email]);
        $_SESSION['login2'] = $name;
        flash('success', 'Your profile has been updated.');
    }
    redirect('profile.php');
}

$stmt = $dbh->prepare('SELECT * FROM tblusers WHERE EmailId = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

$pageTitle = 'My profile';
$pageHeading = 'Profile';
$accountTab = 'profile';
$activeNav = 'account';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/partials/account_start.php';
?>
<div class="card-surface">
  <form method="post" class="needs-validation" novalidate>
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label" for="name">Full name</label>
        <input class="form-control" id="name" name="name" value="<?= e($user->FullName) ?>" required maxlength="100" autocomplete="name">
        <div class="invalid-feedback">Please enter your full name.</div>
      </div>
      <div class="col-md-6">
        <label class="form-label" for="mobileno">Mobile number</label>
        <input class="form-control" type="tel" id="mobileno" name="mobileno" value="<?= e($user->MobileNumber) ?>" required pattern="\+?[0-9 ()-]{7,20}" autocomplete="tel">
        <div class="invalid-feedback">Please enter a valid mobile number.</div>
      </div>
      <div class="col-md-6">
        <label class="form-label" for="email">Email address</label>
        <input class="form-control" id="email" value="<?= e($user->EmailId) ?>" disabled>
        <div class="form-text">Your email is used to sign in and can’t be changed.</div>
      </div>
      <div class="col-md-6">
        <label class="form-label" for="since">Member since</label>
        <input class="form-control" id="since" value="<?= e(format_date($user->RegDate, 'F j, Y')) ?>" disabled>
      </div>
      <div class="col-12 pt-2">
        <button class="btn btn-primary" type="submit">Save changes</button>
      </div>
    </div>
  </form>
</div>
<?php
require __DIR__ . '/includes/partials/account_end.php';
require __DIR__ . '/includes/footer.php';
