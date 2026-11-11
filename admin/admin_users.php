<?php
require 'config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0); $role = $_POST['role'] ?? 'user';
    if (in_array($role, ['user', 'admin'], true) && $id !== (int)$_SESSION['user']['id']) {
        $pdo->prepare('UPDATE users SET role=? WHERE id=?')->execute([$role, $id]);
        flash('success', 'User role updated.');
    }
    header('Location: admin_users.php'); exit;
}
$users = $pdo->query('SELECT * FROM users WHERE role=\'user\' ORDER BY created_at DESC, id DESC')->fetchAll();
$pageTitle = 'Users';
$pageSub   = 'People who registered on the website. Admin accounts are not listed here.';
$crumb     = 'People';
require 'header.php';
?>
    <div class="table-card-custom">
      <div class="table-header-control">
        <div class="table-search-box">
          <i class="bi bi-search table-search-icon"></i>
          <input type="text" class="table-search-input" placeholder="Search users..." data-table-filter="#users-table">
        </div>
        <div class="table-filter-group"><span class="table-pagination-info"><?= count($users) ?> account<?= count($users) === 1 ? '' : 's' ?></span></div>
      </div>
      <div class="table-responsive">
        <table class="table-custom" id="users-table">
          <thead><tr><th>User</th><th>Username</th><th>Phone</th><th>Joined</th><th>Role</th><th>Change role</th></tr></thead>
          <tbody>
          <?php foreach ($users as $u): $self = (int)$u['id'] === (int)$_SESSION['user']['id']; ?>
            <tr data-row>
              <td><div class="table-user-cell">
                <?= mx_avatar($u, 'sm') ?>
                                <div><div class="table-user-name"><?= h($u['full_name']) ?></div><div class="table-user-sub"><?= h($u['email']) ?></div></div>
              </div></td>
              <td>@<?= h($u['username']) ?></td>
              <td><?= h($u['phone'] ?: '—') ?></td>
              <td><?= h(date('M d, Y', strtotime($u['created_at']))) ?></td>
              <td><span class="badge-table <?= $u['role'] === 'admin' ? 'admin' : 'success' ?>"><?= $u['role'] === 'admin' ? 'Admin' : 'User' ?></span></td>
              <td>
                <?php if (!$self): ?>
                <form method="post" class="d-flex gap-2 align-items-center"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                  <select class="form-select-custom form-control-custom-sm" name="role" style="min-width:110px"><option value="user" <?= $u['role'] === 'user' ? 'selected' : '' ?>>User</option><option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option></select>
                  <button class="btn-custom btn-custom-sm btn-custom-primary">Save</button>
                </form>
                <?php else: ?><span class="text-muted-green">You (current admin)</span><?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$users): ?><tr><td colspan="6"><div class="sg-empty"><i class="bi bi-people"></i>No users yet.</div></td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
<?php require 'footer.php'; ?>
