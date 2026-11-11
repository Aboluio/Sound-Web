<?php
require 'config.php';   // admin-only + CSRF already enforced here
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php'); exit; }
$id = (int)$_SESSION['user']['id'];
if (!empty($_POST['remove'])) {
    foreach (glob(dirname(__DIR__) . '/uploads/avatar_' . $id . '.*') ?: [] as $old) @unlink($old);
    flash('success', 'Profile photo removed.');
} else {
$f = $_FILES['photo'] ?? null;
if (!$f || $f['error'] !== UPLOAD_ERR_OK) flash('error', 'Photo could not be uploaded.');
else {
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true) || !@getimagesize($f['tmp_name'])) flash('error', 'Photo must be a JPG, PNG or WEBP image.');
    elseif ($f['size'] > 5 * 1024 * 1024) flash('error', 'Photo must be 5 MB or smaller.');
    else {
        $dir = dirname(__DIR__) . '/uploads';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        foreach (glob($dir . '/avatar_' . $id . '.*') ?: [] as $old) @unlink($old);
        if (move_uploaded_file($f['tmp_name'], $dir . '/avatar_' . $id . '.' . $ext)) flash('success', 'Profile photo updated.');
        else flash('error', 'Could not save the photo.');
    }
}
}
$back = basename(parse_url($_SERVER['HTTP_REFERER'] ?? '', PHP_URL_PATH) ?: 'index.php');
header('Location: ' . (preg_match('/^[a-z_]+\.php$/', $back) ? $back : 'index.php')); exit;
