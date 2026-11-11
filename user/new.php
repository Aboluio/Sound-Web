<?php
require 'config.php';
$pageTitle = 'New Releases'; $active = 'new';
$type = $_GET['type'] ?? ''; if (!in_array($type, ['music', 'video'], true)) $type = '';
$w = $type ? ' WHERE type=' . $pdo->quote($type) : '';
$items = $pdo->query("SELECT * FROM media$w ORDER BY created_at DESC, id DESC LIMIT 48")->fetchAll();

/* newest upload gets the big feature card, the rest are grouped by how recent they are */
$feat = $items[0] ?? null;
$rest = array_slice($items, 1);
$groups = ['This week' => [], 'This month' => [], 'Earlier' => []];
foreach ($rest as $m) {
    $t = strtotime($m['created_at']);
    if ($t >= strtotime('-7 days')) $groups['This week'][] = $m;
    elseif ($t >= strtotime('-30 days')) $groups['This month'][] = $m;
    else $groups['Earlier'][] = $m;
}
require 'header.php';
layout_open();
?>
<section class="nw-hero">
  <div class="nw-copy">
    <span class="nw-pill"><i class="bi bi-stars"></i> Just dropped</span>
    <h1>New<br>Releases</h1>
    <p>The latest songs and videos, newest first. <?= number_format(count($items)) ?> fresh <?= count($items) === 1 ? 'upload' : 'uploads' ?> waiting for you.</p>
    <div class="sx-tabs" role="group" aria-label="Filter">
      <a class="<?= $type === '' ? 'on' : '' ?>" href="new.php">All</a>
      <a class="<?= $type === 'music' ? 'on' : '' ?>" href="new.php?type=music"><i class="bi bi-music-note-beamed"></i> Songs</a>
      <a class="<?= $type === 'video' ? 'on' : '' ?>" href="new.php?type=video"><i class="bi bi-camera-video"></i> Videos</a>
    </div>
  </div>
  <?php if ($feat): ?>
  <div class="nw-feat" data-queue>
    <div class="nw-feat-art"><a class="stretch" href="detail.php?id=<?= (int)$feat['id'] ?>" aria-label="<?= h($feat['title']) ?>"></a><?= thumb_inner($feat) ?></div>
    <div class="nw-feat-info">
      <span class="nw-tag">Latest <?= $feat['type'] === 'video' ? 'video' : 'song' ?></span>
      <a class="nw-feat-title" href="detail.php?id=<?= (int)$feat['id'] ?>"><?= h($feat['title']) ?></a>
      <span class="nw-feat-sub"><?= h($feat['artist'] ?: 'Unknown artist') ?></span>
      <?php if ($feat['type'] === 'music'): ?>
        <button type="button" class="btn btn-sg js-play" <?= track_attrs($feat) ?>><i class="bi bi-play-fill"></i> Play now</button>
      <?php else: ?>
        <a class="btn btn-sg" href="detail.php?id=<?= (int)$feat['id'] ?>"><i class="bi bi-play-fill"></i> Watch now</a>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
</section>

<?php foreach ($groups as $label => $list): if (!$list) continue; ?>
<section class="nw-group">
  <h2 class="nw-group-h"><span><?= h($label) ?></span><small><?= h(plural(count($list), 'upload')) ?></small></h2>
  <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-xl-6 g-3 g-xl-4" data-queue>
    <?php foreach ($list as $m) echo media_card($m); ?>
  </div>
</section>
<?php endforeach; ?>

<?php if (!$items): ?>
<div class="empty"><i class="bi bi-stars"></i><h3>Nothing new yet</h3><p>New songs and videos appear here as soon as the administrator uploads them.</p></div>
<?php endif; ?>
<?php layout_close(); require 'footer.php'; ?>
