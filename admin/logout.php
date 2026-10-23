<?php
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    unset($_SESSION['admin_id'], $_SESSION['admin_last_seen']);
    session_regenerate_id(true);
    flash('success', 'You have been signed out.');
}
redirect('index.php');
