<?php
require 'config.php';

$type   = $_GET['type'] ?? '';  if (!in_array($type, ['music', 'video'], true)) $type = '';
$q      = trim($_GET['q'] ?? '');
$cat    = (int)($_GET['category'] ?? 0);
$album  = trim($_GET['album'] ?? '');
$artist = trim($_GET['artist'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$per    = 24;

$where = ' WHERE 1=1'; $args = [];
if ($type)   { $where .= ' AND m.type=?';        $args[] = $type; }
if ($cat)    { $where .= ' AND m.category_id=?'; $args[] = $cat; }
if ($album !== '')  { $where .= ' AND m.album=?';  $args[] = $album; }
if ($artist !== '') { $where .= ' AND m.artist=?'; $args[] = $artist; }
if ($q !== '') {
    $where .= ' AND (m.title LIKE ? OR m.artist LIKE ? OR m.album LIKE ? OR m.genre LIKE ? OR m.language LIKE ? OR m.year LIKE ? OR c.name LIKE ?)';
    for ($i = 0; $i < 7; $i++) $args[] = "%$q%";
}
$from = ' FROM media m LEFT JOIN categories c ON c.id=m.category_id';

$st = $pdo->prepare('SELECT COUNT(*)' . $from . $where); $st->execute($args);
$total = (int)$st->fetchColumn();
$pages = max(1, (int)ceil($total / $per));
$page  = min($page, $pages);

$st = $pdo->prepare('SELECT m.*, c.name category_name' . $from . $where . ' ORDER BY m.created_at DESC, m.id DESC LIMIT ' . $per . ' OFFSET ' . (($page - 1) * $per));
$st->execute($args);
$items = $st->fetchAll();
$cats = $pdo->query('SELECT id,name FROM categories ORDER BY name')->fetchAll();

if ($album !== '')       { $pageTitle = $album;  $kicker = 'Album'; }
elseif ($artist !== '')  { $pageTitle = $artist; $kicker = 'Artist'; }
elseif ($q !== '')       { $pageTitle = 'Search: ' . $q; $kicker = 'Results'; }
else { $pageTitle = $type === 'music' ? 'Music' : ($type === 'video' ? 'Videos' : 'Explore'); $kicker = 'Discover'; }
$active = $album !== '' ? 'albums' : ($artist !== '' ? 'artists' : ($type === 'music' ? 'music' : ($type === 'video' ? 'videos' : '')));

$qs = function (array $over = []) use ($type, $q, $cat, $album, $artist) {
    $p = array_filter(['type' => $type, 'q' => $q, 'category' => $cat ?: '', 'album' => $album, 'artist' => $artist], fn($v) => $v !== '' && $v !== null);
    foreach ($over as $k => $v) { if ($v === '' || $v === null) unset($p[$k]); else $p[$k] = $v; }
    return 'browse.php?' . http_build_query($p);
};

require 'header.php';
layout_open();
?>
<div class="page-head br-head">
  <div>
    <span class="kicker"><?= h($kicker) ?></span>
    <h1><?= h($pageTitle) ?></h1>
  </div>
  <p class="br-count"><b><?= number_format($total) ?></b> <?= $total === 1 ? 'result' : 'results' ?><?= ($album !== '' || $artist !== '') ? ' &middot; <a href="browse.php">clear filter</a>' : '' ?></p>
</div>

<form class="filter-bar" method="get" action="browse.php">
  <?php if ($album !== ''): ?><input type="hidden" name="album" value="<?= h($album) ?>"><?php endif; ?>
  <?php if ($artist !== ''): ?><input type="hidden" name="artist" value="<?= h($artist) ?>"><?php endif; ?>
  <input class="form-control" name="q" value="<?= h($q) ?>" placeholder="Search title, artist, album, year, language…" aria-label="Search">
  <select class="form-select" name="type" aria-label="Type">
    <option value="">All media</option>
    <option value="music" <?= $type === 'music' ? 'selected' : '' ?>>Music</option>
    <option value="video" <?= $type === 'video' ? 'selected' : '' ?>>Videos</option>
  </select>
  <select class="form-select" name="category" aria-label="Category">
    <option value="0">All categories</option>
    <?php foreach ($cats as $c): ?><option value="<?= (int)$c['id'] ?>" <?= $cat === (int)$c['id'] ? 'selected' : '' ?>><?= h($c['name']) ?></option><?php endforeach; ?>
  </select>
  <button class="btn btn-sg"><i class="bi bi-search"></i> Search</button>
</form>

<div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-xl-6 g-3" data-queue>
  <?php foreach ($items as $m) echo media_card($m); ?>
</div>
<?php if (!$items): ?>
  <div class="empty"><i class="bi bi-search"></i><h3>No results found</h3><p>Try another keyword or browse a different category.</p></div>
<?php endif; ?>

<?php if ($pages > 1): ?>
<nav class="sg-pager" aria-label="Pages">
  <?php if ($page > 1): ?><a href="<?= h($qs(['page' => $page - 1])) ?>"><i class="bi bi-chevron-left"></i></a><?php endif; ?>
  <?php for ($i = 1; $i <= $pages; $i++): ?><a class="<?= $i === $page ? 'on' : '' ?>" href="<?= h($qs(['page' => $i])) ?>"><?= $i ?></a><?php endfor; ?>
  <?php if ($page < $pages): ?><a href="<?= h($qs(['page' => $page + 1])) ?>"><i class="bi bi-chevron-right"></i></a><?php endif; ?>
</nav>
<?php endif; ?>
<?php layout_close(); require 'footer.php'; ?>
