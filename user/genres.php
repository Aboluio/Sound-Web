<?php
require 'config.php';
$pageTitle = 'Genres'; $active = 'genres';
$genres = $pdo->query("SELECT c.id, c.name, COUNT(m.id) total FROM categories c LEFT JOIN media m ON m.category_id=c.id GROUP BY c.id, c.name ORDER BY c.name")->fetchAll();
require 'header.php';
?>
<div class="sg-wrap">
  <header class="ge-hero"><h1>Genres</h1><p>Pick a mood or a style and jump straight in.</p></header>
  <div class="ge-bento">
    <?php foreach ($genres as $i => $g): ?>
      <a class="ge-tile t<?= $i % 7 ?> c<?= $i % 6 ?>" href="browse.php?category=<?= (int)$g['id'] ?>">
        <i class="bi bi-<?= cat_icon($g['name']) ?>"></i>
        <span class="ge-name"><?= h($g['name']) ?></span>
        <small><?= plural((int)$g['total'], 'track') ?></small>
      </a>
    <?php endforeach; ?>
  </div>
  <?php if (!$genres): ?><div class="empty"><i class="bi bi-vinyl"></i><h3>No genres yet</h3><p>The administrator adds genres in Admin &rsaquo; Categories.</p></div><?php endif; ?>
</div>
<?php require 'footer.php'; ?>
