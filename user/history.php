<?php
require 'config.php';
$pageTitle = 'Recently Played'; $active = 'history';
$uid = (int)$_SESSION['user']['id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $pdo->prepare('DELETE FROM play_history WHERE user_id=?')->execute([$uid]);
    flash('success', 'Your play history was cleared.'); header('Location: history.php'); exit;
}
$st = $pdo->prepare('SELECT m.* FROM play_history h JOIN media m ON m.id=h.media_id WHERE h.user_id=? ORDER BY h.played_at DESC LIMIT 48');
$st->execute([$uid]); $items = $st->fetchAll();
require 'header.php'; layout_open();
p2_head('Your listening', 'Recently Played', 'The last songs and videos you played, newest first.',
  $items ? '<form method="post" class="p2-actions" onsubmit="return confirm(\'Clear your play history?\')">' . csrf_field() . '<button class="btn btn-sg-outline"><i class="bi bi-trash3"></i> Clear history</button></form>' : '');
if ($items) p2_grid($items); else p2_empty('clock-history', 'Nothing played yet', 'Play a song or video and it will show up here.');
layout_close(); require 'footer.php';
