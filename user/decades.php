<?php
require 'config.php';
$pageTitle = 'Decades'; $active = 'decades';
$decs = $pdo->query("SELECT FLOOR(year/10)*10 d, COUNT(*) total FROM media WHERE year>0 GROUP BY d ORDER BY d DESC")->fetchAll();
$sel = (int)($_GET['d'] ?? 0); $items = [];
if ($sel > 0) { $st = $pdo->prepare("SELECT * FROM media WHERE year BETWEEN ? AND ? ORDER BY year DESC, id DESC LIMIT 60"); $st->execute([$sel, $sel + 9]); $items = $st->fetchAll(); $pageTitle = $sel . 's'; }
require 'header.php'; layout_open();
p2_head('Time machine', $sel ? $sel . 's' : 'Decades', $sel ? plural(count($items), 'track') . ' from the ' . $sel . 's.' : 'Travel back and play music by the decade it came out.',
  $sel ? '<div class="p2-actions"><a class="btn btn-sg-outline" href="decades.php"><i class="bi bi-arrow-left"></i> All decades</a></div>' : '');
if (!$sel): ?>
  <div class="ge-bento"><?php foreach ($decs as $i => $d) echo p2_tile('decades.php?d=' . (int)$d['d'], 'calendar3', (int)$d['d'] . 's', plural((int)$d['total'], 'track'), $i); ?></div>
  <?php if (!$decs) p2_empty('calendar3', 'No years yet', 'Decades appear when tracks are uploaded with a release year.'); ?>
<?php else: if ($items) p2_grid($items); else p2_empty('calendar3', 'Nothing here', 'No tracks from this decade.'); endif;
layout_close(); require 'footer.php';
