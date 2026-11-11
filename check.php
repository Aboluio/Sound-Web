<?php
/* Sound Web — quick diagnostics. Open http://localhost/syedsoundbar/check.php */
header('Content-Type: text/html; charset=utf-8');
$rows = [];
$rows[] = ['PHP version', PHP_VERSION, version_compare(PHP_VERSION, '7.4', '>=')];
foreach (['pdo_mysql', 'mbstring', 'fileinfo'] as $x) $rows[] = ["Extension $x", extension_loaded($x) ? 'loaded' : 'missing', extension_loaded($x) || $x === 'fileinfo'];
$rows[] = ['uploads folder writable', is_writable(__DIR__ . '/uploads') ? 'yes' : 'no', is_writable(__DIR__ . '/uploads')];
foreach (['user/index.php', 'user/header.php', 'user/signature.css', 'admin/index.php', 'admin/admin.css', 'database.sql'] as $f) $rows[] = ["File $f", is_file(__DIR__ . '/' . $f) ? 'found' : 'MISSING', is_file(__DIR__ . '/' . $f)];
$rows[] = ['upload_max_filesize', ini_get('upload_max_filesize'), (int)ini_get('upload_max_filesize') >= 100];
$rows[] = ['post_max_size', ini_get('post_max_size'), (int)ini_get('post_max_size') >= 100];
$db = 'not tested'; $ok = false;
try { require __DIR__ . '/config.php'; $n = (int)$pdo->query('SELECT COUNT(*) FROM media')->fetchColumn(); $db = "connected, $n media item(s)"; $ok = true; }
catch (Throwable $e) { $db = 'ERROR: ' . $e->getMessage(); }
$rows[] = ['Database', $db, $ok];
?><!doctype html><meta charset="utf-8"><title>Sound Web check</title>
<body style="margin:0;background:#0a0908;color:#f6f0e4;font-family:system-ui,sans-serif;padding:30px">
<h1 style="color:#e9c97d">Sound Web — check</h1>
<table style="border-collapse:collapse;min-width:420px">
<?php foreach ($rows as [$k, $v, $good]): ?>
<tr><td style="padding:8px 14px;border-bottom:1px solid #2a2418"><?= htmlspecialchars($k) ?></td><td style="padding:8px 14px;border-bottom:1px solid #2a2418;color:<?= $good ? '#5fd3a0' : '#ff7d8d' ?>"><?= htmlspecialchars($v) ?></td></tr>
<?php endforeach; ?>
</table>
<p style="margin-top:24px"><a style="color:#e9c97d" href="index.php">Open Website</a> &nbsp;·&nbsp; <a style="color:#e9c97d" href="admin/setup.php">Create admin (first time)</a></p>
</body>
