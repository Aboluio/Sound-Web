<?php
require 'config.php';
$id = (int)($_GET['id'] ?? 0);
$s = $pdo->prepare("SELECT m.*, c.name category_name FROM media m LEFT JOIN categories c ON c.id=m.category_id WHERE m.id=?");
$s->execute([$id]);
$m = $s->fetch();
if (!$m) { http_response_code(404); $pageTitle = 'Not found'; require 'header.php'; echo '<div class="sg-wrap"><div class="empty"><i class="bi bi-emoji-frown"></i><h3>Media not found</h3><a class="btn btn-sg" href="browse.php">Back to library</a></div></div>'; require 'footer.php'; exit; }
if (preg_match('/\.(mp3|wav|ogg|m4a)$/i', (string)$m['file_path'])) $m['type'] = 'music';   // audio file is always a song

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check(); require_login();
    if (is_admin()) { flash('error', 'Admins cannot submit reviews.'); header("Location: detail.php?id=$id"); exit; }
    $rating = max(1, min(5, (int)($_POST['rating'] ?? 5)));
    $review = trim($_POST['review'] ?? '');
    $uid = (int)$_SESSION['user']['id'];
    $e = $pdo->prepare('SELECT id FROM reviews WHERE media_id=? AND user_id=?'); $e->execute([$id, $uid]);
    if ($e->fetch()) $pdo->prepare('UPDATE reviews SET rating=?, review=?, updated_at=? WHERE media_id=? AND user_id=?')->execute([$rating, $review, date('Y-m-d H:i:s'), $id, $uid]);
    else $pdo->prepare('INSERT INTO reviews(media_id,user_id,rating,review) VALUES(?,?,?,?)')->execute([$id, $uid, $rating, $review]);
    flash('success', 'Your rating and review were saved.');
    header("Location: detail.php?id=$id#reviews"); exit;
}

$s = $pdo->prepare("SELECT AVG(rating) avg_rating, COUNT(*) total FROM reviews WHERE media_id=?"); $s->execute([$id]); $stats = $s->fetch();
$s = $pdo->prepare("SELECT r.*, u.username FROM reviews r JOIN users u ON u.id=r.user_id WHERE r.media_id=? ORDER BY r.updated_at DESC"); $s->execute([$id]); $reviews = $s->fetchAll();
$mine = null;
if (logged_in()) { $s = $pdo->prepare("SELECT * FROM reviews WHERE media_id=? AND user_id=?"); $s->execute([$id, $_SESSION['user']['id']]); $mine = $s->fetch(); }

/* More like this: same category or artist */
$rel = $pdo->prepare("SELECT * FROM media WHERE id<>? AND (artist<>'' AND artist=? OR (category_id IS NOT NULL AND category_id=?) OR type=?) ORDER BY (artist=?) DESC, created_at DESC, id DESC LIMIT 6");
$rel->execute([$id, $m['artist'], (int)$m['category_id'], $m['type'], $m['artist']]);
$related = $rel->fetchAll();

$pageTitle = $m['title']; $active = $m['type'] === 'video' ? 'videos' : 'music';
require 'header.php';
layout_open();
$isVideo = $m['type'] === 'video';
?>
<?php if (!empty($m['cover_image'])): ?><div class="dt-backdrop" style="background-image:url('<?= h($m['cover_image']) ?>')" aria-hidden="true"></div><?php else: ?><div class="dt-backdrop is-plain" aria-hidden="true"></div><?php endif; ?>
<a class="back-link" href="browse.php?type=<?= h($m['type']) ?>"><i class="bi bi-arrow-left"></i> Back to <?= $isVideo ? 'videos' : 'music' ?></a>

<div class="detail-card">
  <?php if ($isVideo): ?>
    <video class="d-player js-media-el" data-id="<?= (int)$m['id'] ?>" controls playsinline preload="metadata" <?= $m['cover_image'] ? 'poster="' . h($m['cover_image']) . '"' : '' ?>><source src="<?= h($m['file_path']) ?>" type="video/<?= h((['webm'=>'webm','mov'=>'mp4','m4v'=>'mp4'])[strtolower(pathinfo($m['file_path'], PATHINFO_EXTENSION))] ?? 'mp4') ?>"></video>
  <?php else: ?>
    <div class="d-music">
      <div class="d-cover"><?= thumb_inner($m) ?></div>
      <div class="d-music-info">
        <span class="kicker">Song</span>
        <h1><?= h($m['title']) ?></h1>
        <p class="text-secondary"><?= h($m['artist'] ?: 'Unknown artist') ?><?= $m['album'] ? ' · ' . h($m['album']) : '' ?></p>
        <div class="d-actions" data-queue>
          <button type="button" class="btn btn-sg js-play" <?= track_attrs($m) ?>><i class="bi bi-play-fill"></i> Play</button>
          <?= pl_button($m) ?>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php if ($isVideo): ?>
  <div class="d-title-row">
    <div><h1 class="d-h1"><?= h($m['title']) ?></h1><p class="text-secondary mb-0"><?= h($m['artist'] ?: 'Unknown artist') ?></p></div>
    <?= pl_button($m) ?>
  </div>
<?php endif; ?>

<div class="d-meta">
  <?php foreach (['album' => 'Album', 'year' => 'Year', 'genre' => 'Genre', 'language' => 'Language', 'category_name' => 'Category'] as $key => $label): if ($m[$key]): ?>
    <span><small><?= $label ?></small><b><?= h($m[$key]) ?></b></span>
  <?php endif; endforeach; ?>
  <?php if ($m['duration']): ?><span><small>Duration</small><b><?= h(fmt_duration($m['duration'])) ?></b></span><?php endif; ?>
  <span><small>Plays</small><b><?= number_format((int)$m['plays']) ?></b></span>
  <span><small>Added</small><b><?= h(date('d M Y', strtotime($m['created_at']))) ?></b></span>
</div>
<?php if (trim((string)$m['description']) !== ''): ?><p class="d-desc"><?= nl2br(h($m['description'])) ?></p><?php endif; ?>

<section class="sec" id="reviews">
  <div class="sec-head"><h2><span class="sec-ic"><i class="bi bi-star-fill"></i></span> Reviews &amp; Ratings</h2>
    <span class="text-secondary small"><i class="bi bi-star-fill text-warning"></i> <?= $stats['avg_rating'] ? number_format((float)$stats['avg_rating'], 1) : 'No ratings yet' ?> / 5 · <?= (int)$stats['total'] ?> review(s)</span></div>

  <?php if (logged_in() && !is_admin()): ?>
    <form method="post" class="review-form">
      <?= csrf_field() ?>
      <div class="row g-3">
        <div class="col-md-3"><label class="form-label">Your rating</label>
          <select class="form-select" name="rating"><?php for ($i = 5; $i >= 1; $i--): ?><option value="<?= $i ?>" <?= ((int)($mine['rating'] ?? 5) === $i ? 'selected' : '') ?>><?= $i ?> / 5 stars</option><?php endfor; ?></select></div>
        <div class="col-md-9"><label class="form-label">Your review</label>
          <textarea class="form-control" name="review" rows="3" maxlength="2000" placeholder="Share your thoughts…"><?= h($mine['review'] ?? '') ?></textarea></div>
      </div>
      <button class="btn btn-sg mt-3"><i class="bi bi-check2-circle"></i> Save review</button>
    </form>
  <?php elseif (!logged_in()): ?>
    <div class="review-form text-secondary">Please <a href="login.php">login</a> to add a rating and review.</div>
  <?php endif; ?>

  <?php foreach ($reviews as $r): ?>
    <div class="review"><div class="d-flex justify-content-between"><strong><?= h($r['username']) ?></strong><span class="stars"><?= str_repeat('★', (int)$r['rating']) . str_repeat('☆', 5 - (int)$r['rating']) ?></span></div>
      <?php if (trim((string)$r['review']) !== ''): ?><p><?= nl2br(h($r['review'])) ?></p><?php endif; ?>
      <small class="text-secondary"><?= h(date('d M Y', strtotime($r['updated_at']))) ?></small></div>
  <?php endforeach; ?>
</section>

<?php if ($related): ?>
<section class="sec">
  <div class="sec-head"><h2><span class="sec-ic blue"><i class="bi bi-collection-play"></i></span> More like this</h2></div>
  <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-xl-6 g-3" data-queue>
    <?php foreach ($related as $r) echo media_card($r); ?>
  </div>
</section>
<?php endif; ?>
<?php layout_close(); require 'footer.php'; ?>
