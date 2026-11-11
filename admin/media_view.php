<?php
require 'config.php';
$id = (int)($_GET['id'] ?? 0);
$st = $pdo->prepare('SELECT m.*, c.name category_name FROM media m LEFT JOIN categories c ON c.id=m.category_id WHERE m.id=?');
$st->execute([$id]); $m = $st->fetch();
if (!$m) { flash('danger', 'That item was not found.'); header('Location: media.php'); exit; }

$rv = $pdo->prepare('SELECT COUNT(*) n, COALESCE(AVG(rating),0) a FROM reviews WHERE media_id=?'); $rv->execute([$id]); $rv = $rv->fetch();
$reviews = $pdo->prepare('SELECT r.rating, r.review, r.created_at, u.full_name, u.username FROM reviews r LEFT JOIN users u ON u.id=r.user_id WHERE r.media_id=? ORDER BY r.created_at DESC LIMIT 20');
$reviews->execute([$id]); $reviews = $reviews->fetchAll();

$isVideo = $m['type'] === 'video';
$src = (string)$m['file_path'];
if ($src !== '' && !preg_match('#^(https?:)?//|^/|^\.\./#i', $src)) $src = '../' . ltrim($src, './');

$pageTitle = $m['title'];
$pageSub   = 'Full details of this ' . ($isVideo ? 'video' : 'song') . ' - viewed inside the admin panel.';
$crumb     = 'Library';
$headerExtra = '<a class="btn-date-picker" href="media_form.php?id=' . (int)$m['id'] . '"><i class="bi bi-pencil"></i><span>Edit</span></a>';
require 'header.php';
?>
    <div class="mb-3"><a href="media.php" class="btn-table-action text-decoration-none"><i class="bi bi-arrow-left"></i> Back to Music &amp; Videos</a></div>
    <div class="table-card-custom p-4">
      <div class="sw-view">
        <div>
          <div class="sw-cover"><?php if ($m['cover_image']): ?><img src="<?= h($m['cover_image']) ?>" alt=""><?php else: ?><i class="bi bi-<?= $isVideo ? 'play-fill' : 'music-note-beamed' ?>"></i><?php endif; ?></div>
        </div>
        <div>
          <span class="badge-table <?= $isVideo ? 'video' : 'music' ?>"><?= $isVideo ? 'Video' : 'Music' ?></span>
          <h2 class="mt-2 mb-1"><?= h($m['title']) ?></h2>
          <div class="text-muted-green"><?= h($m['artist'] ?: 'Unknown artist') ?></div>

          <?php if ($src !== ''): ?>
            <?php if ($isVideo): ?>
              <video class="sw-player" controls preload="metadata" src="<?= h($src) ?>"></video>
            <?php else: ?>
              <audio class="sw-player" style="background:none" controls preload="metadata" src="<?= h($src) ?>"></audio>
            <?php endif; ?>
          <?php endif; ?>

          <div class="sw-meta">
            <div><small>Album</small><b><?= h($m['album'] ?: '—') ?></b></div>
            <div><small>Category</small><b><?= h($m['category_name'] ?: '—') ?></b></div>
            <div><small>Genre</small><b><?= h($m['genre'] ?: '—') ?></b></div>
            <div><small>Language</small><b><?= h($m['language'] ?: '—') ?></b></div>
            <div><small>Year</small><b><?= h($m['year'] ?: '—') ?></b></div>
            <div><small>Duration</small><b><?= $m['duration'] ? h(fmt_duration($m['duration'])) : '—' ?></b></div>
            <div><small>Plays</small><b><?= number_format((int)$m['plays']) ?></b></div>
            <div><small>Reviews</small><b><?= (int)$rv['n'] ?><?= $rv['n'] ? ' &middot; ' . number_format((float)$rv['a'], 1) . ' / 5' : '' ?></b></div>
            <div><small>Added</small><b><?= h(date('M d, Y', strtotime($m['created_at']))) ?></b></div>
          </div>

          <?php if (trim((string)$m['description']) !== ''): ?>
            <h6 class="mt-3">Description</h6>
            <div class="sw-desc"><?= h($m['description']) ?></div>
          <?php endif; ?>
        </div>
      </div>

      <?php if ($reviews): ?>
      <hr class="my-4">
      <h6>Reviews</h6>
      <?php foreach ($reviews as $r): ?>
        <div class="py-2 border-bottom" style="border-color:rgba(255,255,255,.08)!important">
          <b><?= h($r['full_name'] ?: $r['username'] ?: 'User') ?></b>
          <span class="text-warning ms-2"><?= str_repeat('★', (int)$r['rating']) . str_repeat('☆', max(0, 5 - (int)$r['rating'])) ?></span>
          <small class="text-muted-green ms-2"><?= h(date('M d, Y', strtotime($r['created_at']))) ?></small>
          <?php if (trim((string)$r['review']) !== ''): ?><div class="text-muted-green"><?= h($r['review']) ?></div><?php endif; ?>
        </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>
<?php require 'footer.php'; ?>
