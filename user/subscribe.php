<?php
require 'config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim($_POST['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('error', 'Please enter a valid email address.');
    } else {
        try { $pdo->prepare('INSERT INTO subscribers(email) VALUES(?)')->execute([$email]); flash('success', 'Thanks for subscribing!'); }
        catch (PDOException $e) { flash('success', 'You are already subscribed. Thank you!'); }
    }
}
$back = $_SERVER['HTTP_REFERER'] ?? 'index.php';
if (parse_url($back, PHP_URL_HOST) !== ($_SERVER['HTTP_HOST'] ?? '') && strpos($back, '://') !== false) $back = 'index.php';
header('Location: ' . $back);
exit;
