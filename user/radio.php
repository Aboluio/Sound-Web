<?php
require 'config.php';
$pageTitle = 'Radio'; $active = 'radio';
$stations = $pdo->query("SELECT c.id, c.name, COUNT(m.id) total FROM categories c JOIN media m ON m.category_id=c.id AND m.type='music' GROUP BY c.id, c.name ORDER BY c.name")->fetchAll();
$allCount = (int)$pdo->query("SELECT COUNT(*) FROM media WHERE type='music'")->fetchColumn();
$sel = $_GET['s'] ?? ''; $name = ''; $items = [];
if ($sel !== '') {
    if ($sel === 'all') { $name = 'All Songs Radio'; $items = $pdo->query("SELECT * FROM media WHERE type='music' ORDER BY RAND() LIMIT 50")->fetchAll(); }
    else { $st = $pdo->prepare("SELECT m.*, c.name cname FROM media m JOIN categories c ON c.id=m.category_id WHERE m.type='music' AND m.category_id=? ORDER BY RAND() LIMIT 50"); $st->execute([(int)$sel]); $items = $st->fetchAll(); $name = ($items[0]['cname'] ?? 'Station') . ' Radio'; }
    $pageTitle = $name ?: 'Radio';
}
require 'header.php'; layout_open();
if ($sel === ''):
  p2_head('Always on', 'Radio', 'Pick a station and it plays a shuffled mix, one song after another.'); ?>
  <div class="ge-bento">
    <?= p2_tile('radio.php?s=all', 'broadcast', 'All Songs', plural($allCount, 'song'), 0) ?>
    <?php foreach ($stations as $i => $s) echo p2_tile('radio.php?s=' . (int)$s['id'], cat_icon($s['name']), $s['name'] . ' Radio', plural((int)$s['total'], 'song'), $i + 1); ?>
  </div>
  <?php if (!$allCount) { p2_empty('broadcast', 'No stations yet', 'Stations appear when songs are uploaded.'); } ?>
<?php else:
  p2_head('Now tuned in', $name, plural(count($items), 'song') . ' in a random order.',
    '<div class="p2-actions"><button type="button" class="btn btn-sg" id="radioStart"><i class="bi bi-broadcast"></i> Start radio</button> <a class="btn btn-sg-outline" href="radio.php"><i class="bi bi-arrow-left"></i> All stations</a></div>');
  if ($items) p2_grid($items); else p2_empty('broadcast', 'Station is empty', 'No songs on this station yet.'); ?>
  <script>
  document.getElementById('radioStart')?.addEventListener('click', function () {
    var sh = document.getElementById('btnShuffle'), first = document.querySelector('[data-queue] .js-play');
    if (sh && sh.getAttribute('aria-pressed') !== 'true') sh.click();
    if (first) first.click();
  });
  </script>
<?php endif; layout_close(); require 'footer.php';
