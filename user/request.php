<?php
require 'config.php';
$pageTitle = 'Request a Song'; $active = 'request';
$uid = (int)$_SESSION['user']['id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $t = trim($_POST['song_title'] ?? ''); $a = trim($_POST['artist'] ?? ''); $msg = trim($_POST['message'] ?? '');
    $open = $pdo->prepare("SELECT COUNT(*) FROM song_requests WHERE user_id=? AND status='pending'"); $open->execute([$uid]);
    if ($t === '') flash('error', 'Please enter the song or video name.');
    elseif ((int)$open->fetchColumn() >= 5) flash('error', 'You already have 5 pending requests. Please wait until some are added.');
    else {
        $pdo->prepare('INSERT INTO song_requests(user_id,song_title,artist,message) VALUES(?,?,?,?)')->execute([$uid, mb_substr($t, 0, 190), mb_substr($a, 0, 150), mb_substr($msg, 0, 1000)]);
        flash('success', 'Request sent. We will add it soon.');
    }
    header('Location: request.php'); exit;
}
$st = $pdo->prepare('SELECT * FROM song_requests WHERE user_id=? ORDER BY id DESC LIMIT 20'); $st->execute([$uid]); $mine = $st->fetchAll();
require 'header.php'; layout_open();
p2_head('Not here yet?', 'Request a Song', 'Tell us what you want to hear and the admin will upload it.'); ?>
<div class="p2-split">
  <form class="p2-card" method="post"><?= csrf_field() ?>
    <label class="form-label">Song or video name *</label><input class="form-control mb-3" name="song_title" maxlength="190" required>
    <label class="form-label">Artist</label><input class="form-control mb-3" name="artist" maxlength="150">
    <label class="form-label">Message (optional)</label><textarea class="form-control mb-3" name="message" rows="3" maxlength="1000"></textarea>
    <button class="btn btn-sg"><i class="bi bi-send"></i> Send request</button>
  </form>
  <div class="p2-card">
    <h2 class="p2-h2">Your requests</h2>
    <?php foreach ($mine as $r): ?>
      <div class="p2-req"><span><b><?= h($r['song_title']) ?></b><small><?= h($r['artist'] ?: 'Unknown artist') ?></small></span>
        <em class="p2-st <?= h($r['status']) ?>"><?= $r['status'] === 'added' ? 'Added' : 'Pending' ?></em></div>
    <?php endforeach; if (!$mine) echo '<p class="p2-muted">No requests yet.</p>'; ?>
  </div>
</div>
<?php layout_close(); require 'footer.php';
