<?php
require 'config.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? ''; $id = (int)($_POST['id'] ?? 0); $name = trim($_POST['name'] ?? '');
    try {
        if ($action === 'add' && $name) { $pdo->prepare('INSERT INTO categories(name) VALUES(?)')->execute([$name]); flash('success', 'Category added.'); }
        elseif ($action === 'delete' && $id) {
            $pdo->prepare('DELETE FROM categories WHERE id=?')->execute([$id]);
            $pdo->prepare('UPDATE media SET category_id=NULL WHERE category_id=?')->execute([$id]);
            flash('success', 'Category deleted.');
        }
        header('Location: admin_categories.php'); exit;
    } catch (PDOException $e) { $error = 'Category name already exists.'; }
}
$cats = $pdo->query('SELECT c.*, COUNT(m.id) media_count FROM categories c LEFT JOIN media m ON m.category_id=c.id GROUP BY c.id, c.name, c.created_at ORDER BY c.name')->fetchAll();
$pageTitle = 'Categories';
$pageSub   = 'Categories such as Pop, Rock, Punjabi appear as chips under the website slider.';
$crumb     = 'Library';
require 'header.php';
?>
    <div class="row g-4">
      <div class="col-lg-4">
        <div class="card border-light shadow-sm p-4">
          <h5 class="card-title mb-3">Add a category</h5>
          <?php if ($error): ?><div class="alert-custom alert-custom-danger sg-alert-inline"><i class="bi bi-exclamation-triangle-fill alert-custom-icon"></i><div class="alert-custom-content"><?= h($error) ?></div></div><?php endif; ?>
          <form method="post">
            <?= csrf_field() ?><input type="hidden" name="action" value="add">
            <label class="form-label-custom" for="cat-name">Category name</label>
            <input class="form-control-custom mb-3" id="cat-name" name="name" placeholder="e.g. Qawwali" required>
            <button class="btn-custom btn-custom-primary w-100"><i class="bi bi-plus-lg"></i> Add category</button>
          </form>
        </div>
      </div>
      <div class="col-lg-8">
        <div class="table-card-custom">
          <div class="table-responsive">
            <table class="table-custom">
              <thead><tr><th>Category</th><th>Media</th><th>Created</th><th class="text-center">Action</th></tr></thead>
              <tbody>
              <?php foreach ($cats as $c): ?>
                <tr>
                  <td class="table-product-name"><?= h($c['name']) ?></td>
                  <td><span class="badge-table success"><?= (int)$c['media_count'] ?> item<?= (int)$c['media_count'] === 1 ? '' : 's' ?></span></td>
                  <td><?= h(date('M d, Y', strtotime($c['created_at']))) ?></td>
                  <td><div class="d-flex justify-content-center">
                    <form class="inline" method="post" onsubmit="return confirm('Delete this category? Media will remain available.')"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>"><button class="table-btn-action delete" title="Delete"><i class="bi bi-trash"></i></button></form>
                  </div></td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$cats): ?><tr><td colspan="4"><div class="sg-empty"><i class="bi bi-tags"></i>No categories yet.</div></td></tr><?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
<?php require 'footer.php'; ?>
