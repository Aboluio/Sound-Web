<?php
require 'config.php';
$pageTitle = 'Top Charts'; $active = 'charts';
$type = ($_GET['type'] ?? 'music') === 'video' ? 'video' : 'music';
$st = $pdo->prepare("SELECT * FROM media WHERE type=? ORDER BY plays DESC, created_at DESC, id DESC LIMIT 50");
$st->execute([$type]);
$items = $st->fetchAll();
require 'header.php';
layout_open();
?>
<section class="ch-hero">
  <div>
    <span class="ch-eyebrow"><i class="bi bi-fire"></i> Most played</span>
    <h1>Top Charts</h1>
    <p>Ranked by real plays. The top <?= count($items) ?> <?= $type === 'video' ? 'videos' : 'songs' ?> right now.</p>
  </div>
  <div class="sx-tabs ch-tabs">
    <a class="<?= $type === 'music' ? 'on' : '' ?>" href="charts.php?type=music"><i class="bi bi-music-note-beamed"></i> Songs</a>
    <a class="<?= $type === 'video' ? 'on' : '' ?>" href="charts.php?type=video"><i class="bi bi-camera-video"></i> Videos</a>
  </div>
</section>

<?php if ($items): ?>
<div data-queue>
  <?php $top = array_slice($items, 0, 3); $rest = array_slice($items, 3); ?>
  <div class="ch-podium ch-n<?= count($top) ?>">
    <?php foreach ($top as $i => $m): ?>
      <article class="ch-pod p<?= $i + 1 ?>">
        <span class="ch-medal"><?= $i + 1 ?></span>
        <div class="ch-art"><a class="stretch" href="detail.php?id=<?= (int)$m['id'] ?>" aria-label="<?= h($m['title']) ?>"></a><?= thumb_inner($m) ?></div>
        <div class="ch-pod-info">
          <a class="ch-pod-title" href="detail.php?id=<?= (int)$m['id'] ?>"><?= h($m['title']) ?></a>
          <span class="ch-pod-sub"><?= h($m['artist'] ?: 'Unknown artist') ?></span>
          <span class="ch-pod-plays"><i class="bi bi-headphones"></i> <?= number_format((int)$m['plays']) ?> plays</span>
        </div>
        <?php if ($type === 'music'): ?>
          <button type="button" class="ch-pod-play js-play" <?= track_attrs($m) ?> aria-label="Play <?= h($m['title']) ?>"><i class="bi bi-play-fill"></i></button>
        <?php else: ?>
          <a class="ch-pod-play" href="detail.php?id=<?= (int)$m['id'] ?>" aria-label="Watch <?= h($m['title']) ?>"><i class="bi bi-play-fill"></i></a>
        <?php endif; ?>
      </article>
    <?php endforeach; ?>
  </div>

  <?php if ($rest): ?>
  <div class="track-table chart ch-list">
    <?php foreach ($rest as $k => $m): $i = $k + 3; ?>
      <div class="tt-row rank-<?= $i + 1 ?>">
        <span class="tt-n"><?= $i + 1 ?></span>
        <span class="tt-thumb"><?= thumb_inner($m) ?></span>
        <a class="tt-info" href="detail.php?id=<?= (int)$m['id'] ?>"><b><?= h($m['title']) ?></b><small><?= h($m['artist'] ?: 'Unknown artist') ?></small></a>
        <span class="tt-type"><?= number_format((int)$m['plays']) ?> plays</span>
        <span class="tt-dur"><?= h(fmt_duration($m['duration'])) ?></span>
        <?php if ($type === 'music'): ?>
          <button type="button" class="btn btn-sg btn-icon js-play" <?= track_attrs($m) ?> aria-label="Play <?= h($m['title']) ?>"><i class="bi bi-play-fill"></i></button>
        <?php else: ?>
          <a class="btn btn-sg btn-icon" href="detail.php?id=<?= (int)$m['id'] ?>" aria-label="Watch <?= h($m['title']) ?>"><i class="bi bi-play-fill"></i></a>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
<?php else: ?>
<div class="empty"><i class="bi bi-trophy"></i><h3>No charts yet</h3><p>Charts fill up as people play songs and videos.</p></div>
<?php endif; ?>
<?php layout_close(); require 'footer.php'; ?>
