<?php
require 'config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: media.php'); exit; }
$id = (int)($_POST['id'] ?? 0);
$s = $pdo->prepare('SELECT file_path, cover_image FROM media WHERE id=?'); $s->execute([$id]); $m = $s->fetch();
if ($m) {
    $pdo->prepare('DELETE FROM reviews WHERE media_id=?')->execute([$id]);
    $pdo->prepare('DELETE FROM playlist_items WHERE media_id=?')->execute([$id]);
    $pdo->prepare('DELETE FROM media WHERE id=?')->execute([$id]);
    // remove the uploaded files too (only inside /uploads, and only if no other row still uses them)
    foreach ([$m['file_path'], $m['cover_image']] as $p) {
        if (!$p || strpos($p, '../uploads/') !== 0) continue;
        $c = $pdo->prepare('SELECT COUNT(*) FROM media WHERE file_path=? OR cover_image=?'); $c->execute([$p, $p]);
        $f = dirname(__DIR__) . '/uploads/' . basename($p);
        if ((int)$c->fetchColumn() === 0 && is_file($f)) @unlink($f);
    }
}
flash('success', 'Media deleted.');
header('Location: media.php'); exit;
