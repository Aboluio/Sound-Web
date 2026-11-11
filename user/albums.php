<?php
require 'config.php';
$pageTitle = 'Albums'; $active = 'albums';
$q = trim($_GET['q'] ?? '');
$where = "type='music' AND album<>''"; $args = [];
if ($q !== '') { $where .= ' AND (album LIKE ? OR artist LIKE ?)'; $args = ["%$q%", "%$q%"]; }
$st = $pdo->prepare("SELECT album, MAX(artist) artist, MAX(cover_image) cover_image, COUNT(*) songs, MAX(created_at) latest
                     FROM media WHERE $where GROUP BY album ORDER BY latest DESC");
$st->execute($args);
$albums = $st->fetchAll();
require 'header.php';
layout_open();
?>
<section class="al-hero">
  <div>
    <span class="al-eyebrow">The record shelf</span>
    <h1>Albums</h1>
    <p><?= number_format(count($albums)) ?> <?= count($albums) === 1 ? 'album' : 'albums' ?><?= $q !== '' ? ' matching &ldquo;' . h($q) . '&rdquo; &middot; <a href="albums.php">clear search</a>' : ' to pull off the shelf. Hover a sleeve to see the record.' ?></p>
    <form class="al-search" method="get" action="albums.php" role="search">
      <i class="bi bi-search" aria-hidden="true"></i>
      <input name="q" value="<?= h($q) ?>" placeholder="Search album or artist" aria-label="Search albums">
      <button class="btn btn-sg" type="submit">Search</button>
    </form>
  </div>
  <div class="al-vinyl" aria-hidden="true"><i></i></div>
</section>

<?php if ($albums): ?>
<div class="al-shelf">
  <?php foreach ($albums as $a):
    $href = 'browse.php?type=music&album=' . urlencode($a['album']); ?>
    <a class="al-card" href="<?= h($href) ?>">
      <span class="al-sleeve">
        <span class="al-disc" aria-hidden="true"></span>
        <span class="al-cover"><?php if (!empty($a['cover_image'])): ?><img src="<?= h($a['cover_image']) ?>" alt="" loading="lazy"><?php else: ?><i class="bi bi-disc-fill"></i><?php endif; ?></span>
      </span>
      <b><?= h($a['album']) ?></b>
      <small><?= h($a['artist'] ?: 'Various Artists') ?> &middot; <?= h(plural((int)$a['songs'], 'song')) ?></small>
    </a>
  <?php endforeach; ?>
</div>
<?php else: ?>
<div class="empty"><i class="bi bi-disc"></i><h3>No albums yet</h3><p>Albums appear automatically when songs are added with an album name in the Admin Panel.</p></div>
<?php endif; ?>
<?php layout_close(); require 'footer.php'; ?>
