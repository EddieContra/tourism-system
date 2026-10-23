<?php
require_once __DIR__ . '/includes/auth.php';
$admin = require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first  = trim((string) ($_POST['firstname'] ?? ''));
    $last   = trim((string) ($_POST['lastname'] ?? ''));
    $email  = trim((string) ($_POST['email'] ?? ''));
    $mobile = trim((string) ($_POST['mobilenumber'] ?? ''));

    $errors = [];
    if ($first === '' || $last === '') {
        $errors[] = 'First and last name are required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if ($mobile !== '' && !preg_match('/^\+?[0-9 ()-]{7,20}$/', $mobile)) {
        $errors[] = 'Please enter a valid mobile number.';
    }

    $photo = $admin->Photo;
    $hasUpload = ($_FILES['photo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    if (!$errors && $hasUpload) {
        try {
            $photo = store_uploaded_image($_FILES['photo'], profile_upload_dir());
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }
    }

    if ($errors) {
        flash('danger', implode(' ', $errors));
        redirect('profile.php');
    }

    $dbh->prepare('UPDATE tbladmin SET FirstName = ?, LastName = ?, Email = ?, MobileNumber = ?, Photo = ? WHERE ID = ?')
        ->execute([$first, $last, $email, $mobile ?: null, $photo, $admin->ID]);
    if ($hasUpload && $admin->Photo && $admin->Photo !== $photo && $admin->Photo !== 'avatar.jpg') {
        @unlink(profile_upload_dir() . '/' . basename($admin->Photo));
    }
    flash('success', 'Your profile has been updated.');
    redirect('profile.php');
}

$pageTitle = 'My profile';
$activeNav = 'profile';
require __DIR__ . '/includes/layout_start.php';
?>
<form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
  <?= csrf_field() ?>
  <div class="row g-4">
    <div class="col-lg-4">
      <section class="panel text-center">
        <div class="panel-body">
          <img id="photo-preview" src="<?= e(profile_image_url($admin->Photo)) ?>" alt="Profile photo" class="rounded-circle mb-3" width="120" height="120" style="object-fit: cover">
          <h2 class="h5 mb-0"><?= e($admin->FirstName . ' ' . $admin->LastName) ?></h2>
          <p class="text-muted small mb-3"><?= e(role_label($admin->AdminName)) ?> · @<?= e($admin->UserName) ?></p>
          <label class="btn btn-light btn-sm" for="photo"><i class="bi bi-camera me-1"></i>Change photo</label>
          <input class="d-none" type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp,image/gif" data-preview="#photo-preview">
          <p class="form-text mb-0">JPG, PNG, WebP or GIF up to 5 MB. Click “Save changes” to apply.</p>
        </div>
      </section>
    </div>
    <div class="col-lg-8">
      <section class="panel">
        <div class="panel-header"><h2>Personal details</h2></div>
        <div class="panel-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" for="firstname">First name</label>
              <input class="form-control" id="firstname" name="firstname" value="<?= e($admin->FirstName) ?>" required maxlength="100">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="lastname">Last name</label>
              <input class="form-control" id="lastname" name="lastname" value="<?= e($admin->LastName) ?>" required maxlength="100">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="email">Email</label>
              <input class="form-control" type="email" id="email" name="email" value="<?= e($admin->Email) ?>" required maxlength="200">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="mobilenumber">Mobile</label>
              <input class="form-control" type="tel" id="mobilenumber" name="mobilenumber" value="<?= e($admin->MobileNumber) ?>" maxlength="20">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="username">Username</label>
              <input class="form-control" id="username" value="<?= e($admin->UserName) ?>" disabled>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="staffid">Staff ID</label>
              <input class="form-control" id="staffid" value="<?= e($admin->Staffid ?: '—') ?>" disabled>
            </div>
            <div class="col-12 pt-2"><button class="btn btn-primary" type="submit">Save changes</button></div>
          </div>
        </div>
      </section>
    </div>
  </div>
</form>
<?php require __DIR__ . '/includes/layout_end.php'; ?>
