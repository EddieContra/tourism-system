<?php
require_once __DIR__ . '/includes/auth.php';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title>Forgot password · Five Star Tours staff portal</title>
  <link rel="icon" href="../assets/img/favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="../assets/vendor/bootstrap/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../assets/css/app.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<main class="admin-login">
  <div class="card-surface text-center">
    <span class="icon-badge mx-auto mb-3"><i class="bi bi-key"></i></span>
    <h1 class="h3 mb-2">Forgot your password?</h1>
    <p class="text-muted">For security, staff passwords can only be reset by an administrator.</p>
    <p class="text-muted">Please ask an Admin or Super admin to open <strong>Staff → Edit</strong> on your account and set a temporary password. You can change it after signing in.</p>
    <a class="btn btn-primary mt-2" href="index.php"><i class="bi bi-arrow-left me-1"></i>Back to sign in</a>
  </div>
</main>
</body>
</html>
