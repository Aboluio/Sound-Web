<?php
require 'config.php';
$pageTitle = 'Trending Now'; $active = 'trending';
/* score = real plays + a bonus for fresh uploads (fades over 30 days) */
$score = "(m.plays + GREATEST(0, 30 - DATEDIFF(NOW(), m.created_at)))";
$songs  = $pdo->query("SELECT m.* FROM media m WHERE m.type='music' ORDER BY $score DESC, m.id DESC LIMIT 18")->fetchAll();
$videos = $pdo->query("SELECT m.* FROM media m WHERE m.type='video' ORDER BY $score DESC, m.id DESC LIMIT 12")->fetchAll();
require 'header.php'; layout_open();
p2_head('Hot right now', 'Trending Now', 'What people are playing and what just dropped, mixed into one live list.');
if ($songs): ?>
<section class="nw-group"><h2 class="nw-group-h"><span>Trending songs</span><small><?= plural(count($songs), 'song') ?></small></h2><?php p2_grid($songs); ?></section>
<?php endif; if ($videos): ?>
<section class="nw-group"><h2 class="nw-group-h"><span>Trending videos</span><small><?= plural(count($videos), 'video') ?></small></h2><?php p2_grid($videos); ?></section>
<?php endif; if (!$songs && !$videos) p2_empty('fire', 'Nothing trending yet', 'Trending fills up as songs and videos get played.');
layout_close(); require 'footer.php';
