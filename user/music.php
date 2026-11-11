<?php
require 'config.php';
$pageTitle = 'Music'; $active = 'music';

$cats = $pdo->query("SELECT id,name FROM categories ORDER BY name")->fetchAll();
$catId = (int)($_GET['category'] ?? 0);
$catName = '';
foreach ($cats as $c) if ((int)$c['id'] === $catId) $catName = $c['name'];
if (!$catName) $catId = 0;
$cw = $catId ? ' AND category_id=' . $catId : '';

$onRepeat = $pdo->query("SELECT * FROM media WHERE type='music' $cw ORDER BY plays DESC, created_at DESC, id DESC LIMIT 6")->fetchAll();
$songs    = $pdo->query("SELECT * FROM media WHERE type='music' $cw ORDER BY created_at DESC, id DESC LIMIT 36")->fetchAll();
$total    = (int)$pdo->query("SELECT COUNT(*) FROM media WHERE type='music' $cw")->fetchColumn();
$artists  = (int)$pdo->query("SELECT COUNT(DISTINCT artist) FROM media WHERE type='music' AND artist<>'' $cw")->fetchColumn();

require 'header.php';
layout_open();
?>
<section class="mu-room">
  <div class="mu-intro">
    <span class="mu-tag"><i class="bi bi-soundwave"></i> Listening room</span>
    <h1>Music</h1>
    <p><?= h(plural($total, 'song')) ?> from <?= h(plural($artists, 'artist')) ?>. Press play and keep browsing &mdash; the player stays with you.</p>
    <div class="mu-bars" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div>
    <?php if ($onRepeat): ?><button type="button" class="btn btn-sg btn-lg js-play-first"><i class="bi bi-play-fill"></i> Play the top songs</button><?php endif; ?>
  </div>
  <div class="mu-repeat">
    <h2>On repeat</h2>
    <?php if ($onRepeat): ?>
    <div class="track-table" data-queue>
      <?php foreach ($onRepeat as $i => $m): ?>
      <div class="tt-row">
        <span class="tt-n"><?= $i + 1 ?></span>
        <span class="tt-thumb"><?= thumb_inner($m) ?></span>
        <a class="tt-info" href="detail.php?id=<?= (int)$m['id'] ?>"><b><?= h($m['title']) ?></b><small><?= h($m['artist'] ?: 'Unknown artist') ?></small></a>
        <span class="tt-dur"><?= h(fmt_duration($m['duration'])) ?></span>
        <button type="button" class="btn btn-sg btn-icon js-play" <?= track_attrs($m) ?> aria-label="Play <?= h($m['title']) ?>"><i class="bi bi-play-fill"></i></button>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?><div class="empty"><i class="bi bi-music-note-beamed"></i><p>No songs yet. The administrator can add songs from the Admin Panel.</p></div><?php endif; ?>
  </div>
</section>

<nav class="mu-moods" aria-label="Genres">
  <a class="<?= $catId ? '' : 'on' ?>" href="music.php">All songs</a>
  <?php foreach ($cats as $c): ?><a class="<?= $catId === (int)$c['id'] ? 'on' : '' ?>" href="music.php?category=<?= (int)$c['id'] ?>"><i class="bi bi-<?= cat_icon($c['name']) ?>"></i> <?= h($c['name']) ?></a><?php endforeach; ?>
</nav>

<?php if ($songs): ?>
<section class="mu-all">
  <h2 class="mu-h"><?= $catName ? h($catName) : 'Newest songs' ?></h2>
  <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-xl-6 g-3 g-xl-4" data-queue>
    <?php foreach ($songs as $m) echo music_card($m); ?>
  </div>
  <?php if ($total > count($songs)): ?><p class="mu-more"><a class="btn btn-sg-outline" href="browse.php?type=music<?= $catId ? '&amp;category=' . $catId : '' ?>">See all <?= number_format($total) ?> songs</a></p><?php endif; ?>
</section>
<?php endif; ?>
<?php layout_close(); require 'footer.php'; ?>
