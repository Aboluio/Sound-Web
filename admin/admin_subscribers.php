<?php
require 'config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo->prepare('DELETE FROM subscribers WHERE id=?')->execute([(int)($_POST['id'] ?? 0)]);
    flash('success', 'Subscriber removed.'); header('Location: admin_subscribers.php'); exit;
}
$subs = $pdo->query('SELECT * FROM subscribers ORDER BY id DESC')->fetchAll();
$pageTitle = 'Subscribers';
$pageSub   = 'Emails collected from the newsletter box in the website footer.';
$crumb     = 'People';
require 'header.php';
?>
    <div class="table-card-custom">
      <div class="table-header-control">
        <div class="table-search-box">
          <i class="bi bi-search table-search-icon"></i>
          <input type="text" class="table-search-input" placeholder="Search emails..." data-table-filter="#subs-table">
        </div>
        <div class="table-filter-group"><span class="table-pagination-info"><?= count($subs) ?> subscriber<?= count($subs) === 1 ? '' : 's' ?></span></div>
      </div>
      <div class="table-responsive">
        <table class="table-custom" id="subs-table">
          <thead><tr><th>Email</th><th>Subscribed</th><th class="text-center">Action</th></tr></thead>
          <tbody>
          <?php foreach ($subs as $s): ?>
            <tr data-row>
              <td><div class="table-user-cell"><span class="sg-thumb"><i class="bi bi-envelope"></i></span><div class="table-user-name"><?= h($s['email']) ?></div></div></td>
              <td><?= h(date('M d, Y • h:i A', strtotime($s['created_at']))) ?></td>
              <td><div class="d-flex justify-content-center">
                <form class="inline" method="post" onsubmit="return confirm('Remove this subscriber?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$s['id'] ?>"><button class="table-btn-action delete" title="Remove"><i class="bi bi-trash"></i></button></form>
              </div></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$subs): ?><tr><td colspan="3"><div class="sg-empty"><i class="bi bi-envelope-paper"></i>No subscribers yet.</div></td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
<?php require 'footer.php'; ?>
