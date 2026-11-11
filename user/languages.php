<?php
require 'config.php';
$pageTitle = 'Languages'; $active = 'languages';
$langs = $pdo->query("SELECT language, COUNT(*) total FROM media WHERE language<>'' GROUP BY language ORDER BY total DESC, language")->fetchAll();
$sel = trim($_GET['lang'] ?? ''); $items = [];
if ($sel !== '') { $st = $pdo->prepare("SELECT * FROM media WHERE language=? ORDER BY created_at DESC, id DESC LIMIT 60"); $st->execute([$sel]); $items = $st->fetchAll(); $pageTitle = $sel; }
require 'header.php'; layout_open();
p2_head('Browse by', $sel !== '' ? $sel : 'Languages', $sel !== '' ? plural(count($items), 'track') . ' in this language.' : 'Pick the language you want to listen in.',
  $sel !== '' ? '<div class="p2-actions"><a class="btn btn-sg-outline" href="languages.php"><i class="bi bi-arrow-left"></i> All languages</a></div>' : '');
if ($sel === ''): ?>
  <div class="ge-bento"><?php foreach ($langs as $i => $l) echo p2_tile('languages.php?lang=' . urlencode($l['language']), 'translate', $l['language'], plural((int)$l['total'], 'track'), $i); ?></div>
  <?php if (!$langs) p2_empty('translate', 'No languages yet', 'Languages appear when tracks are uploaded with a language.'); ?>
<?php else: if ($items) p2_grid($items); else p2_empty('translate', 'Nothing here', 'No tracks in this language.'); endif;
layout_close(); require 'footer.php';
