<?php
require_once __DIR__ . '/includes/bootstrap.php';

// Logging out changes state, so it only happens via a CSRF-protected POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    unset($_SESSION['login'], $_SESSION['login2']);
    session_regenerate_id(true);
    flash('success', 'You have been signed out. See you soon!');
}
redirect('index.php');
