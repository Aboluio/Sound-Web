<?php
require 'config.php';
$pageTitle = 'Artists'; $active = 'artists';
$artists = $pdo->query("SELECT artist, MAX(cover_image) cover_image, COUNT(*) total
                        FROM media WHERE artist<>'' GROUP BY artist ORDER BY artist")->fetchAll();
require 'header.php';
layout_open();
?>
<?php
$byLetter = [];
foreach ($artists as $a) {
    $L = strtoupper(substr(trim($a['artist']), 0, 1));
    if (!preg_match('/^[A-Z]$/', $L)) $L = '#';
    $byLetter[$L][] = $a;
}
ksort($byLetter);
?>
<section class="ar-hero">
  <h1>Artists</h1>
  <p><?= plural(count($artists), 'artist') ?> on <?= h($SITE['name']) ?>. Tap a name to hear everything they have released.</p>
</section>
<?php if ($artists): ?>
<nav class="ar-index" aria-label="Jump to letter">
  <?php foreach (array_keys($byLetter) as $L): ?><a href="#l-<?= $L === '#' ? 'num' : $L ?>"><?= h($L) ?></a><?php endforeach; ?>
</nav>
<?php foreach ($byLetter as $L => $list): ?>
  <section class="ar-group" id="l-<?= $L === '#' ? 'num' : $L ?>">
    <h2 class="ar-letter"><?= h($L) ?></h2>
    <div class="ar-grid">
      <?php foreach ($list as $a): $href = 'browse.php?artist=' . urlencode($a['artist']); ?>
        <a class="ar-card" href="<?= h($href) ?>">
          <span class="ar-photo"><?php if (!empty($a['cover_image'])): ?><img src="<?= h($a['cover_image']) ?>" alt="" loading="lazy"><?php else: ?><i class="bi bi-person-fill"></i><?php endif; ?></span>
          <b><?= h($a['artist']) ?></b>
          <small><?= plural((int)$a['total'], 'item') ?></small>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
<?php endforeach; ?>
<?php else: ?>
<div class="empty"><i class="bi bi-people"></i><h3>No artists yet</h3><p>Artists appear automatically when songs or videos are added with an artist name.</p></div>
<?php endif; ?>
<?php layout_close(); require 'footer.php'; ?>
