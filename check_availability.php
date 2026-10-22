<?php
/** JSON endpoint used by the registration form to check whether an email is free. */
require_once __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/json');

$email = trim((string) ($_GET['email'] ?? ''));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['available' => false, 'error' => 'invalid_email']);
    exit;
}

$stmt = $dbh->prepare('SELECT 1 FROM tblusers WHERE EmailId = ?');
$stmt->execute([$email]);

echo json_encode(['available' => !$stmt->fetchColumn()]);
