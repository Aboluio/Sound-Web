<?php
require 'config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    if (($_POST['do'] ?? '') === 'delete') $pdo->prepare('DELETE FROM song_requests WHERE id=?')->execute([$id]);
    else $pdo->prepare("UPDATE song_requests SET status=IF(status='pending','added','pending') WHERE id=?")->execute([$id]);
    flash('success', 'Request updated.'); header('Location: admin_requests.php'); exit;
}
$rows = $pdo->query('SELECT r.*, u.full_name, u.username FROM song_requests r LEFT JOIN users u ON u.id=r.user_id ORDER BY r.status, r.id DESC')->fetchAll();
$pageTitle = 'Song Requests';
$pageSub   = 'Songs and videos that users asked for. Upload them, then mark the request as added.';
$crumb     = 'People';
require 'header.php';
?>
    <div class="table-card-custom">
      <div class="table-header-control">
        <div class="table-search-box"><i class="bi bi-search table-search-icon"></i><input type="text" class="table-search-input" placeholder="Search requests..." data-table-filter="#req-table"></div>
        <div class="table-filter-group"><span class="table-pagination-info"><?= count($rows) ?> request<?= count($rows) === 1 ? '' : 's' ?></span></div>
      </div>
      <div class="table-responsive">
        <table class="table-custom" id="req-table">
          <thead><tr><th>Song</th><th>Requested by</th><th>Message</th><th>Status</th><th>Date</th><th class="text-center">Action</th></tr></thead>
          <tbody>
          <?php foreach ($rows as $r): ?>
            <tr data-row>
              <td><div class="table-user-name"><?= h($r['song_title']) ?></div><small><?= h($r['artist']) ?></small></td>
              <td><?= h($r['full_name'] ?: $r['username'] ?: 'Deleted user') ?></td>
              <td><?= h($r['message']) ?></td>
              <td><span class="badge <?= $r['status'] === 'added' ? 'bg-success' : 'bg-warning text-dark' ?>"><?= $r['status'] === 'added' ? 'Added' : 'Pending' ?></span></td>
              <td><?= h(date('M d, Y', strtotime($r['created_at']))) ?></td>
              <td><div class="d-flex justify-content-center gap-1">
                <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button class="table-btn-action" title="Mark <?= $r['status'] === 'added' ? 'pending' : 'added' ?>"><i class="bi bi-check2-circle"></i></button></form>
                <form method="post" onsubmit="return confirm('Delete this request?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="do" value="delete"><button class="table-btn-action delete" title="Delete"><i class="bi bi-trash"></i></button></form>
              </div></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$rows): ?><tr><td colspan="6"><div class="sg-empty"><i class="bi bi-send"></i>No song requests yet.</div></td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
<?php require 'footer.php'; ?>
