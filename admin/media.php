<?php
require 'config.php';
$q    = trim($_GET['q'] ?? '');
$type = $_GET['type'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$per  = 10;
$where = []; $args = [];
if ($q !== '') { $where[] = '(m.title LIKE ? OR m.artist LIKE ? OR m.album LIKE ? OR m.genre LIKE ?)'; array_push($args, "%$q%", "%$q%", "%$q%", "%$q%"); }
if (in_array($type, ['music', 'video'], true)) { $where[] = 'm.type=?'; $args[] = $type; } else { $type = ''; }
$w = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$c = $pdo->prepare("SELECT COUNT(*) FROM media m $w"); $c->execute($args); $total = (int)$c->fetchColumn();
$pages = max(1, (int)ceil($total / $per)); $page = min($page, $pages);
$st = $pdo->prepare("SELECT m.*, c.name category_name FROM media m LEFT JOIN categories c ON c.id=m.category_id $w ORDER BY m.created_at DESC, m.id DESC LIMIT $per OFFSET " . (($page - 1) * $per));
$st->execute($args); $items = $st->fetchAll();
function mlink($p) { global $q, $type; return 'media.php?' . http_build_query(array_filter(['q' => $q, 'type' => $type, 'page' => $p > 1 ? $p : null])); }

$pageTitle = 'All Music & Videos';
$pageSub   = 'Every song and video that appears on your website.';
$crumb     = 'Library';
$headerExtra = '<a class="btn-date-picker" href="media_form.php"><i class="bi bi-plus-lg"></i><span>Add Music / Video</span></a>';
require 'header.php';
?>
    <div class="table-card-custom">
      <form class="table-header-control" method="get" action="media.php">
        <div class="table-search-box">
          <i class="bi bi-search table-search-icon"></i>
          <input type="text" name="q" value="<?= h($q) ?>" class="table-search-input" placeholder="Search title, artist, album or genre...">
        </div>
        <div class="table-filter-group">
          <div class="dropdown">
            <button class="btn-table-action dropdown-toggle" type="button" id="dropdownType" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-funnel"></i> <?= $type === 'music' ? 'Music only' : ($type === 'video' ? 'Videos only' : 'All types') ?>
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownType">
              <li><a class="dropdown-item" href="<?= h('media.php?' . http_build_query(array_filter(['q' => $q]))) ?>">All types</a></li>
              <li><a class="dropdown-item" href="<?= h('media.php?' . http_build_query(array_filter(['q' => $q, 'type' => 'music']))) ?>">Music only</a></li>
              <li><a class="dropdown-item" href="<?= h('media.php?' . http_build_query(array_filter(['q' => $q, 'type' => 'video']))) ?>">Videos only</a></li>
            </ul>
          </div>
          <?php if ($type): ?><input type="hidden" name="type" value="<?= h($type) ?>"><?php endif; ?>
          <button class="btn-table-action" type="submit"><i class="bi bi-search"></i> Search</button>
        </div>
      </form>

      <div class="table-responsive">
        <table class="table-custom">
          <thead><tr><th>Title</th><th>Type</th><th>Album</th><th>Category</th><th>Year</th><th>Plays</th><th>Added</th><th class="text-center">Actions</th></tr></thead>
          <tbody>
          <?php foreach ($items as $m): ?>
            <tr>
              <td><div class="table-user-cell">
                <span class="sg-thumb"><?php if (!empty($m['cover_image'])): ?><img src="<?= h($m['cover_image']) ?>" alt=""><?php else: ?><i class="bi bi-<?= $m['type'] === 'video' ? 'play-fill' : 'music-note-beamed' ?>"></i><?php endif; ?></span>
                <div><div class="table-user-name"><?= h($m['title']) ?></div><div class="table-user-sub"><?= h($m['artist'] ?: 'Unknown artist') ?></div></div>
              </div></td>
              <td><span class="badge-table <?= $m['type'] === 'video' ? 'video' : 'music' ?>"><?= $m['type'] === 'video' ? 'Video' : 'Music' ?></span></td>
              <td><?= h($m['album'] ?: '—') ?></td>
              <td><?= h($m['category_name'] ?: '—') ?></td>
              <td><?= h($m['year'] ?: '—') ?></td>
              <td class="table-amount"><?= number_format((int)$m['plays']) ?></td>
              <td><?= h(date('M d, Y', strtotime($m['created_at']))) ?></td>
              <td><div class="d-flex justify-content-center gap-1">
                <a href="media_view.php?id=<?= (int)$m['id'] ?>" class="table-btn-action" title="View details"><i class="bi bi-eye"></i></a>
                <a href="media_form.php?id=<?= (int)$m['id'] ?>" class="table-btn-action" title="Edit"><i class="bi bi-pencil"></i></a>
                <form class="inline" method="post" action="delete_media.php" onsubmit="return confirm('Delete this media?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$m['id'] ?>"><button class="table-btn-action delete" title="Delete"><i class="bi bi-trash"></i></button></form>
              </div></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$items): ?><tr><td colspan="8"><div class="sg-empty"><i class="bi bi-music-note-list"></i><?= $q || $type ? 'Nothing matches your search.' : 'No media uploaded yet.' ?></div></td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>

      <div class="table-footer-control">
        <span class="table-pagination-info"><?= $total ? 'Showing ' . (($page - 1) * $per + 1) . ' to ' . min($total, $page * $per) . ' of ' . number_format($total) . ' entries' : 'No entries' ?></span>
        <?php if ($pages > 1): ?>
        <nav aria-label="Page navigation">
          <ul class="pagination mb-0 gap-1">
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>"><a class="page-link border-0" href="<?= h(mlink($page - 1)) ?>"><i class="bi bi-chevron-left"></i></a></li>
            <?php for ($i = 1; $i <= $pages; $i++): ?><li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link border-0" href="<?= h(mlink($i)) ?>"><?= $i ?></a></li><?php endfor; ?>
            <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>"><a class="page-link border-0" href="<?= h(mlink($page + 1)) ?>"><i class="bi bi-chevron-right"></i></a></li>
          </ul>
        </nav>
        <?php endif; ?>
      </div>
    </div>
<?php require 'footer.php'; ?>
