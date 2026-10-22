<?php
require_once __DIR__ . '/includes/auth.php';
$admin = require_admin();
require_staff_manager($admin);

$id = (int) ($_GET['id'] ?? 0);
$member = null;
if ($id) {
    $stmt = $dbh->prepare('SELECT * FROM tbladmin WHERE ID = ?');
    $stmt->execute([$id]);
    $member = $stmt->fetch();
    if (!$member || !can_manage_account($admin, $member)) {
        flash('danger', 'You cannot edit this account.');
        redirect('staff.php');
    }
}

// Super admins can grant any role; admins can create admins and staff.
$roles = $admin->AdminName === 'Superuser' ? ['User', 'Admin', 'Superuser'] : ['User', 'Admin'];
$fields = ['firstname', 'lastname', 'username', 'email', 'mobile', 'staffid', 'role'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [];
    foreach ($fields as $field) {
        $data[$field] = trim((string) ($_POST[$field] ?? ''));
    }
    $password = (string) ($_POST['password'] ?? '');

    $errors = [];
    if ($data['firstname'] === '' || $data['lastname'] === '') {
        $errors[] = 'First and last name are required.';
    }
    if (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $data['username'])) {
        $errors[] = 'Username must be 3–50 characters (letters, numbers, dot, dash or underscore).';
    }
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if ($data['mobile'] !== '' && !preg_match('/^\+?[0-9 ()-]{7,20}$/', $data['mobile'])) {
        $errors[] = 'Please enter a valid mobile number.';
    }
    if (!in_array($data['role'], $roles, true)) {
        $errors[] = 'Please choose a valid role.';
    }
    if ($member && $member->ID === $admin->ID && $data['role'] !== $admin->AdminName) {
        $errors[] = 'You cannot change your own role.';
    }
    if (!$member || $password !== '') {
        if ($problem = password_problem($password)) {
            $errors[] = $problem;
        }
    }

    $taken = $dbh->prepare('SELECT 1 FROM tbladmin WHERE UserName = ? AND ID <> ?');
    $taken->execute([$data['username'], $id]);
    if ($taken->fetchColumn()) {
        $errors[] = 'That username is already taken.';
    }

    if ($errors) {
        remember_input($fields);
        flash('danger', implode(' ', $errors));
        redirect('staff_form.php' . ($id ? '?id=' . $id : ''));
    }

    $params = [$data['firstname'], $data['lastname'], $data['username'], $data['email'], $data['mobile'] ?: null, $data['staffid'] ?: null, $data['role']];
    if ($member) {
        $sql = 'UPDATE tbladmin SET FirstName = ?, LastName = ?, UserName = ?, Email = ?, MobileNumber = ?, Staffid = ?, AdminName = ?';
        if ($password !== '') {
            $sql .= ', Password = ?';
            $params[] = password_hash($password, PASSWORD_DEFAULT);
        }
        $params[] = $id;
        $dbh->prepare($sql . ' WHERE ID = ?')->execute($params);
        flash('success', 'Staff account updated' . ($password !== '' ? ' and password reset.' : '.'));
    } else {
        $params[] = password_hash($password, PASSWORD_DEFAULT);
        $dbh->prepare('INSERT INTO tbladmin (FirstName, LastName, UserName, Email, MobileNumber, Staffid, AdminName, Password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute($params);
        flash('success', sprintf('Account created. %s can now sign in as “%s”.', $data['firstname'], $data['username']));
    }
    forget_input();
    redirect('staff.php');
}

$value = fn (string $field, ?string $stored) => isset($_SESSION['_old'][$field]) ? old($field) : e($stored);
$currentRole = $_SESSION['_old']['role'] ?? ($member->AdminName ?? 'User');

$pageTitle = $member ? 'Edit staff member' : 'Add staff member';
$activeNav = 'staff';
require __DIR__ . '/includes/layout_start.php';
?>
<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb small">
    <li class="breadcrumb-item"><a href="staff.php">Staff</a></li>
    <li class="breadcrumb-item active" aria-current="page"><?= $member ? e($member->FirstName . ' ' . $member->LastName) : 'New staff member' ?></li>
  </ol>
</nav>

<section class="panel" style="max-width: 860px">
  <div class="panel-header"><h2><?= $member ? 'Account details' : 'New account' ?></h2></div>
  <div class="panel-body">
    <form method="post" class="needs-validation" novalidate autocomplete="off">
      <?= csrf_field() ?>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label" for="firstname">First name</label>
          <input class="form-control" id="firstname" name="firstname" value="<?= $value('firstname', $member->FirstName ?? '') ?>" required maxlength="100">
        </div>
        <div class="col-md-6">
          <label class="form-label" for="lastname">Last name</label>
          <input class="form-control" id="lastname" name="lastname" value="<?= $value('lastname', $member->LastName ?? '') ?>" required maxlength="100">
        </div>
        <div class="col-md-6">
          <label class="form-label" for="username">Username</label>
          <input class="form-control" id="username" name="username" value="<?= $value('username', $member->UserName ?? '') ?>" required pattern="[A-Za-z0-9._\-]{3,50}">
          <div class="invalid-feedback">3–50 letters, numbers, dots, dashes or underscores.</div>
        </div>
        <div class="col-md-6">
          <label class="form-label" for="staffid">Staff ID <span class="text-muted fw-normal">(optional)</span></label>
          <input class="form-control" id="staffid" name="staffid" value="<?= $value('staffid', $member->Staffid ?? '') ?>" maxlength="50">
        </div>
        <div class="col-md-6">
          <label class="form-label" for="email">Email</label>
          <input class="form-control" type="email" id="email" name="email" value="<?= $value('email', $member->Email ?? '') ?>" required maxlength="200">
        </div>
        <div class="col-md-6">
          <label class="form-label" for="mobile">Mobile <span class="text-muted fw-normal">(optional)</span></label>
          <input class="form-control" type="tel" id="mobile" name="mobile" value="<?= $value('mobile', $member->MobileNumber ?? '') ?>" maxlength="20">
        </div>
        <div class="col-md-6">
          <label class="form-label" for="role">Role</label>
          <select class="form-select" id="role" name="role" required>
            <?php foreach ($roles as $role): ?>
              <option value="<?= $role ?>"<?= $role === $currentRole ? ' selected' : '' ?>><?= role_label($role) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label" for="password"><?= $member ? 'Reset password' : 'Temporary password' ?></label>
          <div class="position-relative">
            <input class="form-control" type="password" id="password" name="password" minlength="8" autocomplete="new-password"<?= $member ? '' : ' required' ?>>
            <button class="password-toggle" type="button" data-password-toggle="password" aria-label="Show password"><i class="bi bi-eye"></i></button>
          </div>
          <div class="form-text"><?= $member ? 'Leave blank to keep the current password.' : 'At least 8 characters with letters and numbers. Share it securely.' ?></div>
        </div>
        <div class="col-12 d-flex gap-2 pt-2">
          <button class="btn btn-primary" type="submit"><?= $member ? 'Save changes' : 'Create account' ?></button>
          <a class="btn btn-light" href="staff.php">Cancel</a>
        </div>
      </div>
    </form>
  </div>
</section>
<?php forget_input(); require __DIR__ . '/includes/layout_end.php'; ?>
