<?php
require 'config.php';
$id = (int)($_GET['id'] ?? 0);
$st = $pdo->prepare('SELECT * FROM playlists WHERE id=?'); $st->execute([$id]); $p = $st->fetch();
if (!$p) { flash('danger', 'That playlist was not found.'); header('Location: admin_playlists.php'); exit; }
$it = $pdo->prepare('SELECT m.id, m.type, m.title, m.artist, m.cover_image, m.duration, m.plays FROM playlist_media pm JOIN media m ON m.id=pm.media_id WHERE pm.playlist_id=? ORDER BY pm.position, m.id');
$it->execute([$id]); $items = $it->fetchAll();

$pageTitle = $p['name'];
$pageSub   = 'Playlist details - viewed inside the admin panel.';
$crumb     = 'Library';
$headerExtra = '<a class="btn-date-picker" href="playlist_form.php?id=' . (int)$p['id'] . '"><i class="bi bi-pencil"></i><span>Edit</span></a>';
require 'header.php';
?>
    <div class="mb-3"><a href="admin_playlists.php" class="btn-table-action text-decoration-none"><i class="bi bi-arrow-left"></i> Back to Playlists</a></div>
    <div class="table-card-custom p-4">
      <div class="sw-view">
        <div><div class="sw-cover"><?php if ($p['cover_image']): ?><img src="<?= h($p['cover_image']) ?>" alt=""><?php else: ?><i class="bi bi-music-note-list"></i><?php endif; ?></div></div>
        <div>
          <h2 class="mb-1"><?= h($p['name']) ?></h2>
          <div class="text-muted-green mb-2"><?= count($items) ?> item<?= count($items) === 1 ? '' : 's' ?> &middot; created <?= h(date('M d, Y', strtotime($p['created_at']))) ?></div>
          <?php if (trim((string)$p['description']) !== ''): ?><div class="sw-desc"><?= h($p['description']) ?></div><?php endif; ?>
        </div>
      </div>
      <div class="table-responsive mt-4">
        <table class="table-custom">
          <thead><tr><th>#</th><th>Title</th><th>Type</th><th>Duration</th><th>Plays</th><th class="text-center">View</th></tr></thead>
          <tbody>
          <?php foreach ($items as $n => $m): ?>
            <tr>
              <td><?= $n + 1 ?></td>
              <td><div class="table-user-cell">
                <span class="sg-thumb"><?php if ($m['cover_image']): ?><img src="<?= h($m['cover_image']) ?>" alt=""><?php else: ?><i class="bi bi-<?= $m['type'] === 'video' ? 'play-fill' : 'music-note-beamed' ?>"></i><?php endif; ?></span>
                <div><div class="table-user-name"><?= h($m['title']) ?></div><div class="table-user-sub"><?= h($m['artist'] ?: 'Unknown artist') ?></div></div>
              </div></td>
              <td><span class="badge-table <?= $m['type'] === 'video' ? 'video' : 'music' ?>"><?= $m['type'] === 'video' ? 'Video' : 'Music' ?></span></td>
              <td><?= $m['duration'] ? h(fmt_duration($m['duration'])) : '—' ?></td>
              <td><?= number_format((int)$m['plays']) ?></td>
              <td class="text-center"><a class="table-btn-action" href="media_view.php?id=<?= (int)$m['id'] ?>" title="View"><i class="bi bi-eye"></i></a></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$items): ?><tr><td colspan="6"><div class="sg-empty"><i class="bi bi-music-note-list"></i>This playlist is empty.</div></td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
<?php require 'footer.php'; ?>
