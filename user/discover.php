<?php
require 'config.php';
$pageTitle = 'Discover'; $active = 'discover';
$songs  = $pdo->query("SELECT * FROM media WHERE type='music' ORDER BY RAND() LIMIT 12")->fetchAll();
$videos = $pdo->query("SELECT * FROM media WHERE type='video' ORDER BY RAND() LIMIT 6")->fetchAll();
require 'header.php'; layout_open();
p2_head('Surprise me', 'Discover', 'A fresh random pick every time you open this page.',
  '<div class="p2-actions"><button type="button" class="btn btn-sg js-play-first"><i class="bi bi-play-fill"></i> Play these songs</button> <a class="btn btn-sg-outline" href="discover.php"><i class="bi bi-shuffle"></i> Shuffle again</a></div>');
if ($songs): ?><section class="nw-group" id="trending"><h2 class="nw-group-h"><span>Songs for you</span></h2><?php p2_grid($songs); ?></section><?php endif;
if ($videos): ?><section class="nw-group"><h2 class="nw-group-h"><span>Videos to watch</span></h2><?php p2_grid($videos); ?></section><?php endif;
if (!$songs && !$videos) p2_empty('shuffle', 'Nothing to discover yet', 'Upload songs and videos from the Admin Panel first.');
layout_close(); require 'footer.php';
