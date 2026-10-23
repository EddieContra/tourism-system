<?php
require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php#contact');
}

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$phone   = trim($_POST['phone'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

$errors = [];
if ($name === '' || mb_strlen($name) > 100) {
    $errors[] = 'Please enter your name.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
    $errors[] = 'Please enter a valid email address.';
}
if ($phone !== '' && !preg_match('/^\+?[0-9 ()-]{7,20}$/', $phone)) {
    $errors[] = 'Please enter a valid phone number.';
}
if ($subject === '' || mb_strlen($subject) > 150) {
    $errors[] = 'Please add a subject.';
}
if (mb_strlen($message) < 10 || mb_strlen($message) > 2000) {
    $errors[] = 'Your message should be between 10 and 2000 characters.';
}

if ($errors) {
    remember_input(['name', 'email', 'phone', 'subject', 'message']);
    flash('danger', implode(' ', $errors));
    redirect('index.php#contact');
}

$stmt = $dbh->prepare('INSERT INTO tblenquiry (FullName, EmailId, MobileNumber, Subject, Description) VALUES (?, ?, ?, ?, ?)');
$stmt->execute([$name, $email, $phone ?: null, $subject, $message]);

flash('success', 'Thanks, ' . $name . '! Your message has been sent — we’ll reply within one business day.');
redirect('index.php#contact');
