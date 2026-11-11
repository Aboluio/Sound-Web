<?php
// Admin panel bootstrap (Spark Admin). Every admin page needs a logged-in administrator,
// except login.php and setup.php (setup only works while no administrator exists yet).
require __DIR__ . '/../config.php';
$__page = basename($_SERVER['SCRIPT_NAME']);
if (!in_array($__page, ['setup.php', 'login.php'], true)) {
    if (!is_admin()) { header('Location: login.php'); exit; }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Upload bigger than post_max_size: PHP drops $_POST/$_FILES, so the token looks missing.
        if (empty($_POST) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
            http_response_code(413);
            exit('Upload too large (' . round($_SERVER['CONTENT_LENGTH'] / 1048576, 1) . ' MB). PHP post_max_size is ' . ini_get('post_max_size') . ' and upload_max_filesize is ' . ini_get('upload_max_filesize') . '. Raise both in php.ini (XAMPP > Apache > Config > PHP), restart Apache, then go back and try again.');
        }
        csrf_check();
    }
}

/* Time like "5 mins ago" for notifications / lists */
function ago($ts) {
    $d = max(0, time() - strtotime($ts));
    if ($d < 60) return 'just now';
    if ($d < 3600) return plural((int)floor($d / 60), 'min') . ' ago';
    if ($d < 86400) return plural((int)floor($d / 3600), 'hour') . ' ago';
    return plural((int)floor($d / 86400), 'day') . ' ago';
}

/* Finds the user's picture whatever the column is called: any value that looks like an image file, then checks where the file really exists on disk. */
function mx_find_pic($u) {
    $root = dirname(__DIR__);
    foreach ($u as $k => $v) {
        if (!is_string($v) || $v === '' || $k === 'password' || !preg_match('~\.(jpe?g|png|webp|gif)(\?.*)?$~i', $v)) continue;
        if (preg_match('~^(https?:)?//~', $v)) return $v;
        $rel = ltrim(preg_replace('~^(\.\./)+~', '', preg_replace('~\?.*$~', '', $v)), '/');
        foreach (['', 'uploads/', 'user/', 'user/uploads/', 'uploads/avatars/', 'uploads/profile/', 'uploads/profiles/', 'user/uploads/avatars/'] as $pre)
            if (is_file($root . '/' . $pre . $rel)) return '../' . $pre . $rel;
    }
    return '';
}

/* Profile picture: uploaded photo if any, otherwise a round person-icon placeholder. $edit adds the camera upload button. */
function mx_avatar($u, $size = 'sm', $edit = false) {
    $id = (int)($u['id'] ?? 0); $src = ''; $g = [];
    if ($id) { $g = glob(dirname(__DIR__) . '/uploads/avatar_' . $id . '.*'); if ($g) $src = '../uploads/' . basename($g[0]) . '?v=' . @filemtime($g[0]); }
    if (!$src) $src = mx_find_pic($u);
    $o = '<span class="mx-ava-wrap"><span class="mx-ava mx-ava-' . h($size) . '">' . ($src ? '<img src="' . h($src) . '" alt="" onerror="this.remove()">' : '') . '<i class="bi bi-person-fill"></i></span>';
    if ($edit) $o .= '<form class="mx-ava-form" method="post" action="profile_photo.php" enctype="multipart/form-data">' . csrf_field() . '<label class="mx-ava-cam" title="Change photo"><i class="bi bi-camera-fill"></i><input type="file" name="photo" accept=".jpg,.jpeg,.png,.webp" hidden onchange="this.form.submit()"></label></form>';
    if ($edit && $g) $o .= '<form class="mx-ava-form" method="post" action="profile_photo.php" onsubmit="return confirm(\'Remove your profile photo?\')">' . csrf_field() . '<input type="hidden" name="remove" value="1"><button type="submit" class="mx-ava-rm" title="Remove photo"><i class="bi bi-x-lg"></i></button></form>';
    return $o . '</span>';
}

