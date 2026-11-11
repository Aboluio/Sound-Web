<?php
require 'config.php';
$pageTitle = 'Playlists'; $active = 'playlists';

/* ---------- one playlist made by the admin: playlists.php?id=5 ---------- */
$viewId = (int)($_GET['id'] ?? 0);
if ($viewId) {
    $st = $pdo->prepare('SELECT * FROM playlists WHERE id=?'); $st->execute([$viewId]); $pl = $st->fetch();
    if (!$pl) { header('Location: playlists.php'); exit; }
    $pageTitle = $pl['name'];
    $tracks = site_playlist_items($pdo, $viewId);
    $mosaic = array_slice($tracks, 0, 4);
    require 'header.php';
    layout_open();
?>
<div class="pl-studio">
  <aside class="pl-side">
    <?php if ($pl['cover_image']): ?>
      <div class="pl-mosaic" style="display:block" aria-hidden="true"><span style="display:block;width:100%;height:100%"><img src="<?= h($pl['cover_image']) ?>" alt=""></span></div>
    <?php else: ?>
      <div class="pl-mosaic<?= $mosaic ? '' : ' is-empty' ?>" aria-hidden="true">
        <?php foreach ($mosaic as $m): ?><span><?= thumb_inner($m) ?></span><?php endforeach; ?>
        <?php if (!$mosaic): ?><i class="bi bi-music-note-list"></i><?php endif; ?>
      </div>
    <?php endif; ?>
    <span class="pl-label">Playlist</span>
    <h1><?= h($pl['name']) ?></h1>
    <p><?= $pl['description'] ? h($pl['description']) . ' &middot; ' : '' ?><?= h(plural(count($tracks), 'item')) ?></p>
    <?php if ($tracks): ?><button type="button" class="btn btn-sg js-play-first"><i class="bi bi-play-fill"></i> Play all</button><?php endif; ?>
    <a class="btn btn-sg-outline mt-2" href="playlists.php"><i class="bi bi-arrow-left"></i> All playlists</a>
  </aside>

  <div class="pl-main">
  <?php if (!$tracks): ?>
    <div class="empty"><i class="bi bi-music-note-list"></i><h3>This playlist is empty</h3><p>Songs and videos will appear here once the administrator adds them.</p></div>
  <?php else: ?>
    <div class="track-table" data-queue>
      <?php foreach ($tracks as $i => $m): ?>
        <div class="tt-row">
          <span class="tt-n"><?= $i + 1 ?></span>
          <span class="tt-thumb"><?= thumb_inner($m) ?></span>
          <a class="tt-info" href="detail.php?id=<?= (int)$m['id'] ?>"><b><?= h($m['title']) ?></b><small><?= h($m['artist'] ?: 'Unknown artist') ?></small></a>
          <span class="tt-type"><?= $m['type'] === 'video' ? 'Video' : 'Song' ?></span>
          <span class="tt-dur"><?= h(fmt_duration($m['duration'])) ?></span>
          <?php if ($m['type'] === 'music'): ?>
            <button type="button" class="btn btn-sg btn-icon js-play" <?= track_attrs($m) ?> aria-label="Play"><i class="bi bi-play-fill"></i></button>
          <?php else: ?>
            <a class="btn btn-sg btn-icon" href="detail.php?id=<?= (int)$m['id'] ?>" aria-label="Watch"><i class="bi bi-play-fill"></i></a>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  </div>
</div>
<?php
    layout_close(); require 'footer.php';
    exit;
}

/* ---------- playlists page: admin playlists + the user's own list ---------- */
$items = my_playlist($pdo);
$siteLists = site_playlists($pdo);
require 'header.php';
layout_open();
?>
<?php if ($siteLists): ?>
<section class="sec" id="site-playlists" style="padding-top:clamp(20px,3vw,40px)">
  <div class="sec-head"><h2><span class="sec-ic"><i class="bi bi-collection-play-fill"></i></span> Playlists</h2><span class="text-secondary small">Picked by <?= h($SITE['name']) ?></span></div>
  <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-xl-6 g-3 g-xl-4">
    <?php foreach ($siteLists as $p) echo playlist_card($p); ?>
  </div>
</section>
<?php endif; ?>

<?php $mosaic = array_slice($items, 0, 4); ?>
<div class="pl-studio">
  <aside class="pl-side">
    <div class="pl-mosaic<?= $mosaic ? '' : ' is-empty' ?>" aria-hidden="true">
      <?php foreach ($mosaic as $m): ?><span><?= thumb_inner($m) ?></span><?php endforeach; ?>
      <?php if (!$mosaic): ?><i class="bi bi-music-note-list"></i><?php endif; ?>
    </div>
    <span class="pl-label">Your collection</span>
    <h1>My Playlist</h1>
    <p><?= logged_in() ? plural(count($items), 'item') . ' saved to your account' : 'Log in to save songs and videos' ?></p>
    <?php if (logged_in() && $items): ?><button type="button" class="btn btn-sg js-play-first"><i class="bi bi-play-fill"></i> Play all</button><?php endif; ?>
  </aside>

  <div class="pl-main">
  <?php if (!logged_in()): ?>
    <div class="empty"><i class="bi bi-person-circle"></i><h3>Log in to use playlists</h3><p>Save your favorite songs and videos with the + button.</p><a class="btn btn-sg" href="login.php">Log in</a> <a class="btn btn-sg-outline" href="register.php">Create account</a></div>
  <?php elseif (!$items): ?>
    <div class="empty"><i class="bi bi-music-note-list"></i><h3>Your playlist is empty</h3><p>Tap the + button on any song or video to add it here.</p><a class="btn btn-sg" href="browse.php">Explore library</a></div>
  <?php else: ?>
    <div class="track-table" data-queue>
      <?php foreach ($items as $i => $m): ?>
        <div class="tt-row">
          <span class="tt-n"><?= $i + 1 ?></span>
          <span class="tt-thumb"><?= thumb_inner($m) ?></span>
          <a class="tt-info" href="detail.php?id=<?= (int)$m['id'] ?>"><b><?= h($m['title']) ?></b><small><?= h($m['artist'] ?: 'Unknown artist') ?></small></a>
          <span class="tt-type"><?= $m['type'] === 'video' ? 'Video' : 'Song' ?></span>
          <span class="tt-dur"><?= h(fmt_duration($m['duration'])) ?></span>
          <?php if ($m['type'] === 'music'): ?>
            <button type="button" class="btn btn-sg btn-icon js-play" <?= track_attrs($m) ?> aria-label="Play"><i class="bi bi-play-fill"></i></button>
          <?php else: ?>
            <a class="btn btn-sg btn-icon" href="detail.php?id=<?= (int)$m['id'] ?>" aria-label="Watch"><i class="bi bi-play-fill"></i></a>
          <?php endif; ?>
          <button type="button" class="btn btn-sg-outline btn-icon js-pl on js-pl-remove" data-id="<?= (int)$m['id'] ?>" aria-label="Remove"><i class="bi bi-trash3"></i></button>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  </div>
</div>
<?php layout_close(); require 'footer.php'; ?>
