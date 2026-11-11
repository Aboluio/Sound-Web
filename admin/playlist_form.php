<?php
require 'config.php';
$id = (int)($_GET['id'] ?? 0);
$pl = ['name' => '', 'description' => '', 'cover_image' => ''];
$selected = [];
if ($id) {
    $s = $pdo->prepare('SELECT * FROM playlists WHERE id=?'); $s->execute([$id]); $pl = $s->fetch();
    if (!$pl) exit('Playlist not found.');
    $s = $pdo->prepare('SELECT media_id FROM playlist_media WHERE playlist_id=? ORDER BY position, media_id'); $s->execute([$id]);
    $selected = array_map('intval', array_column($s->fetchAll(), 'media_id'));
}
$all = $pdo->query('SELECT id,type,title,artist,cover_image FROM media ORDER BY created_at DESC, id DESC')->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $desc  = trim($_POST['description'] ?? '');
    $cover = $pl['cover_image'];
    $ids   = array_values(array_unique(array_map('intval', (array)($_POST['media_ids'] ?? []))));
    if ($name === '') $error = 'Playlist name is required.';

    if (!$error && isset($_FILES['cover_file']) && $_FILES['cover_file']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['cover_file']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) $error = 'Cover must be JPG, PNG or WEBP.';
        else {
            $dir = dirname(__DIR__) . '/uploads';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $fn = bin2hex(random_bytes(12)) . '.' . $ext;
            if (move_uploaded_file($_FILES['cover_file']['tmp_name'], $dir . '/' . $fn)) $cover = '../uploads/' . $fn; else $error = 'Could not save the cover image.';
        }
    }

    if (!$error) {
        try {
            $pdo->beginTransaction();
            if ($id) {
                $pdo->prepare('UPDATE playlists SET name=?, description=?, cover_image=? WHERE id=?')->execute([$name, $desc, $cover, $id]);
                $pdo->prepare('DELETE FROM playlist_media WHERE playlist_id=?')->execute([$id]);
            } else {
                $pdo->prepare('INSERT INTO playlists(name,description,cover_image) VALUES(?,?,?)')->execute([$name, $desc, $cover]);
                $id = (int)$pdo->lastInsertId();
            }
            $valid = $pdo->prepare('SELECT 1 FROM media WHERE id=?');
            $ins = $pdo->prepare('INSERT INTO playlist_media(playlist_id,media_id,position) VALUES(?,?,?)');
            $pos = 0;
            foreach ($ids as $mid) {
                $valid->execute([$mid]);
                if ($valid->fetch()) $ins->execute([$id, $mid, $pos++]);
            }
            $pdo->commit();
            flash('success', 'Playlist saved. It is now live on the website.');
            header('Location: admin_playlists.php'); exit;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = 'Could not save the playlist: ' . $e->getMessage();
        }
    }
    $pl['name'] = $name; $pl['description'] = $desc; $selected = $ids;
}

$pageTitle = $id ? 'Edit Playlist' : 'New Playlist';
$pageSub   = 'Pick the songs and videos that belong in this playlist.';
$crumb     = 'Library';
require 'header.php';
?>
    <style>
      .pl-pick{border:1px solid #E1E8E4;border-radius:16px;background:#fff;overflow:hidden}
      .pl-pick-top{display:flex;gap:.7rem;align-items:center;padding:.8rem;border-bottom:1px solid #E9EFEF;background:#F8FAF9}
      .pl-pick-top input{flex:1}
      .pl-pick-list{max-height:420px;overflow:auto}
      .pl-pick-row{display:flex;align-items:center;gap:.9rem;padding:.6rem .9rem;border-bottom:1px solid #F0F4F2;cursor:pointer;margin:0}
      .pl-pick-row:hover{background:#F4F8F5}
      .pl-pick-row input{width:18px;height:18px;flex:0 0 18px;accent-color:#072F1F}
      .pl-pick-row .t{min-width:0;flex:1}
      .pl-pick-row .t b{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
      .pl-pick-row .t small{color:var(--text-muted-green)}
      .pl-count{font-weight:700;white-space:nowrap}
    </style>

    <?php if ($error): ?>
    <div class="alert-custom alert-custom-danger mb-4">
      <i class="bi bi-exclamation-triangle-fill alert-custom-icon"></i>
      <div class="alert-custom-content"><?= h($error) ?></div>
    </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="card border-light shadow-sm p-4 mb-4">
      <?= csrf_field() ?>
      <div class="sg-form-grid">
        <div class="sg-section"><b>01</b><div><strong>Playlist details</strong><small>Name and optional cover picture.</small></div></div>

        <div>
          <label class="form-label-custom" for="p-name">Playlist name *</label>
          <input class="form-control-custom" id="p-name" name="name" value="<?= h($pl['name']) ?>" placeholder="e.g. Best of Qawwali" required>
        </div>
        <div>
          <label class="form-label-custom" for="p-cover">Cover picture</label>
          <input class="form-control-custom" type="file" id="p-cover" name="cover_file" accept=".jpg,.jpeg,.png,.webp">
          <img id="p-prev" alt="" src="<?= h($pl['cover_image']) ?>" style="<?= $pl['cover_image'] ? '' : 'display:none;' ?>width:100%;max-height:150px;object-fit:cover;border-radius:12px;margin-top:10px">
        </div>
        <div class="full">
          <label class="form-label-custom" for="p-desc">Description</label>
          <textarea class="form-control-custom" id="p-desc" name="description" rows="3"><?= h($pl['description']) ?></textarea>
        </div>

        <div class="sg-section"><b>02</b><div><strong>Songs &amp; videos</strong><small>Tick everything you want inside this playlist.</small></div></div>

        <div class="full">
          <?php if (!$all): ?>
            <div class="sg-empty"><i class="bi bi-collection-play"></i>Library is empty. <a href="media_form.php">Add a song or video</a> first.</div>
          <?php else: ?>
          <div class="pl-pick">
            <div class="pl-pick-top">
              <input type="search" class="form-control-custom" id="pl-filter" placeholder="Search title or artist...">
              <span class="pl-count"><span id="pl-n"><?= count($selected) ?></span> selected</span>
            </div>
            <div class="pl-pick-list" id="pl-list">
              <?php foreach ($all as $m): ?>
              <label class="pl-pick-row" data-s="<?= h(strtolower($m['title'] . ' ' . $m['artist'])) ?>">
                <input type="checkbox" name="media_ids[]" value="<?= (int)$m['id'] ?>" <?= in_array((int)$m['id'], $selected, true) ? 'checked' : '' ?>>
                <span class="sg-thumb"><?php if ($m['cover_image']): ?><img src="<?= h($m['cover_image']) ?>" alt=""><?php else: ?><i class="bi bi-<?= $m['type'] === 'video' ? 'camera-video' : 'music-note-beamed' ?>"></i><?php endif; ?></span>
                <span class="t"><b><?= h($m['title']) ?></b><small><?= h($m['artist'] ?: 'Unknown artist') ?></small></span>
                <span class="badge-table <?= $m['type'] === 'video' ? 'video' : 'music' ?>"><?= $m['type'] === 'video' ? 'Video' : 'Song' ?></span>
              </label>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>
        </div>

        <div class="full d-flex flex-wrap gap-2 align-items-center">
          <button class="btn-custom btn-custom-primary" type="submit"><i class="bi bi-check2-circle"></i> <?= $id ? 'Save changes' : 'Create playlist' ?></button>
          <a class="btn-custom btn-custom-secondary" href="admin_playlists.php">Cancel</a>
        </div>
      </div>
    </form>

    <script>
    (function(){
      var f=document.getElementById('pl-filter'),list=document.getElementById('pl-list'),n=document.getElementById('pl-n'),
          cv=document.getElementById('p-cover'),pv=document.getElementById('p-prev');
      if(list){
        var rows=[].slice.call(list.querySelectorAll('.pl-pick-row'));
        function count(){n.textContent=list.querySelectorAll('input:checked').length;}
        list.addEventListener('change',count);
        if(f)f.addEventListener('input',function(){var q=f.value.trim().toLowerCase();rows.forEach(function(r){r.style.display=(!q||r.dataset.s.indexOf(q)>-1)?'':'none';});});
      }
      if(cv)cv.addEventListener('change',function(){var x=this.files&&this.files[0];if(x&&x.type.indexOf('image/')===0){pv.src=URL.createObjectURL(x);pv.style.display='block';}});
    })();
    </script>
<?php require 'footer.php'; ?>
