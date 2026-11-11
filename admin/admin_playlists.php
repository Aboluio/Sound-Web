<?php
require 'config.php';

/* Delete a playlist (the songs / videos inside it are NOT deleted) */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    $s = $pdo->prepare('SELECT cover_image FROM playlists WHERE id=?'); $s->execute([$id]); $p = $s->fetch();
    if ($p) {
        $pdo->prepare('DELETE FROM playlist_media WHERE playlist_id=?')->execute([$id]);
        $pdo->prepare('DELETE FROM playlists WHERE id=?')->execute([$id]);
        $c = $p['cover_image'];
        if ($c && strpos($c, '../uploads/') === 0) {
            $u = $pdo->prepare('SELECT (SELECT COUNT(*) FROM media WHERE cover_image=?) + (SELECT COUNT(*) FROM playlists WHERE cover_image=?)');
            $u->execute([$c, $c]);
            $f = dirname(__DIR__) . '/uploads/' . basename($c);
            if ((int)$u->fetchColumn() === 0 && is_file($f)) @unlink($f);
        }
        flash('success', 'Playlist deleted. Its songs and videos are still in the library.');
    }
    header('Location: admin_playlists.php'); exit;
}

$rows = $pdo->query('SELECT p.*, COUNT(pm.media_id) items FROM playlists p LEFT JOIN playlist_media pm ON pm.playlist_id=p.id GROUP BY p.id, p.name, p.description, p.cover_image, p.created_at ORDER BY p.created_at DESC, p.id DESC')->fetchAll();

$pageTitle = 'Playlists';
$pageSub   = 'Make playlists of songs and videos. They appear on the website under Library > Playlists.';
$crumb     = 'Library';
$headerExtra = '<a class="btn-date-picker" href="playlist_form.php"><i class="bi bi-plus-lg"></i><span>New Playlist</span></a>';
require 'header.php';
?>
    <div class="table-card-custom">
      <div class="table-responsive">
        <table class="table-custom">
          <thead><tr><th>Playlist</th><th>Items</th><th>Created</th><th class="text-center">Action</th></tr></thead>
          <tbody>
          <?php foreach ($rows as $p): ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-3">
                  <span class="sg-thumb"><?php if ($p['cover_image']): ?><img src="<?= h($p['cover_image']) ?>" alt=""><?php else: ?><i class="bi bi-music-note-list"></i><?php endif; ?></span>
                  <div><div class="table-product-name"><?= h($p['name']) ?></div><small class="text-muted-green"><?= h(mb_strimwidth((string)$p['description'], 0, 70, '...')) ?></small></div>
                </div>
              </td>
              <td><span class="badge-table success"><?= (int)$p['items'] ?> item<?= (int)$p['items'] === 1 ? '' : 's' ?></span></td>
              <td><?= h(date('M d, Y', strtotime($p['created_at']))) ?></td>
              <td>
                <div class="d-flex justify-content-center gap-1">
                  <a class="table-btn-action" href="playlist_view.php?id=<?= (int)$p['id'] ?>" title="View details"><i class="bi bi-eye"></i></a>
                  <a class="table-btn-action" href="playlist_form.php?id=<?= (int)$p['id'] ?>" title="Edit"><i class="bi bi-pencil"></i></a>
                  <form class="inline" method="post" onsubmit="return confirm('Delete this playlist? Songs and videos will remain in the library.')"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><button class="table-btn-action delete" title="Delete"><i class="bi bi-trash"></i></button></form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$rows): ?><tr><td colspan="4"><div class="sg-empty"><i class="bi bi-music-note-list"></i>No playlists yet. Click <b>New Playlist</b> to make the first one.</div></td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
<?php require 'footer.php'; ?>
