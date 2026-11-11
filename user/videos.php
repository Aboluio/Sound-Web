<?php
require 'config.php';
$pageTitle = 'Videos'; $active = 'videos';

$cats = $pdo->query("SELECT id,name FROM categories ORDER BY name")->fetchAll();
$catId = (int)($_GET['category'] ?? 0);
$catName = '';
foreach ($cats as $c) if ((int)$c['id'] === $catId) $catName = $c['name'];
if (!$catName) $catId = 0;
$cw = $catId ? ' AND category_id=' . $catId : '';

$hero   = $pdo->query("SELECT * FROM media WHERE type='video' $cw ORDER BY plays DESC, created_at DESC, id DESC LIMIT 1")->fetch();
/* slider: top 6 videos (most played first) - read-only query, database untouched */
$slides = $pdo->query("SELECT * FROM media WHERE type='video' $cw ORDER BY (cover_image <> '') DESC, plays DESC, created_at DESC, id DESC LIMIT 6")->fetchAll();
$newest = $pdo->query("SELECT * FROM media WHERE type='video' $cw ORDER BY created_at DESC, id DESC LIMIT 8")->fetchAll();
$all    = $pdo->query("SELECT * FROM media WHERE type='video' $cw ORDER BY created_at DESC, id DESC LIMIT 36")->fetchAll();
$total  = (int)$pdo->query("SELECT COUNT(*) FROM media WHERE type='video' $cw")->fetchColumn();

require 'header.php';
layout_open();
?>
<?php if ($slides): $vn = count($slides); ?>
<section class="vd-slider carousel slide carousel-fade" id="vdCarousel" data-bs-ride="carousel" data-bs-interval="5500" data-bs-pause="false" data-bs-touch="true" aria-label="Featured videos">
  <?php if ($vn > 1): ?>
  <div class="carousel-indicators">
    <?php for ($i = 0; $i < $vn; $i++): ?><button type="button" data-bs-target="#vdCarousel" data-bs-slide-to="<?= $i ?>" class="<?= $i ? '' : 'active' ?>" aria-label="Slide <?= $i + 1 ?>"></button><?php endfor; ?>
  </div>
  <?php endif; ?>
  <div class="carousel-inner">
    <?php foreach ($slides as $n => $v): ?>
    <div class="carousel-item<?= $n ? '' : ' active' ?>">
      <div class="vd-slide">
        <div class="vd-bg"<?= !empty($v['cover_image']) ? ' style="background-image:url(\'' . h($v['cover_image']) . '\')"' : '' ?>></div>
        <div class="vd-copy">
          <span class="vd-tag"><i class="bi bi-camera-reels-fill"></i> <?= $n === 0 ? 'Now showing' : 'Featured video' ?></span>
          <h1><?= h($v['title']) ?></h1>
          <p><?= h($v['artist'] ?: 'Unknown artist') ?><?= $v['genre'] ? ' &middot; ' . h($v['genre']) : '' ?> &middot; <?= number_format((int)$v['plays']) ?> views<?= !empty($v['duration']) ? ' &middot; ' . h(fmt_duration($v['duration'])) : '' ?></p>
          <div class="vd-btns">
            <a class="btn btn-sg btn-lg" href="detail.php?id=<?= (int)$v['id'] ?>"><i class="bi bi-play-fill"></i> Watch now</a>
            <a class="btn btn-sg-outline btn-lg" href="charts.php?type=video"><i class="bi bi-bar-chart-fill"></i> Top videos</a>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php if ($vn > 1): ?>
  <div class="vd-ctrl" role="group" aria-label="Slider controls">
    <button type="button" data-bs-target="#vdCarousel" data-bs-slide="prev" aria-label="Previous video"><i class="bi bi-chevron-left"></i></button>
    <span id="vdCount">1 / <?= $vn ?></span>
    <button type="button" data-bs-target="#vdCarousel" data-bs-slide="next" aria-label="Next video"><i class="bi bi-chevron-right"></i></button>
  </div>
  <script>
  document.addEventListener('DOMContentLoaded',function(){
    var el=document.getElementById('vdCarousel'),cnt=document.getElementById('vdCount');
    if(!el||!window.bootstrap)return;
    bootstrap.Carousel.getOrCreateInstance(el,{interval:5500,ride:'carousel',pause:false,touch:true,wrap:true}).cycle();
    el.addEventListener('slid.bs.carousel',function(e){cnt.textContent=(e.to+1)+' / <?= $vn ?>';});
  });
  </script>
  <?php endif; ?>
</section>
<?php else: ?>
<section class="vd-slider vd-empty"><div class="vd-slide"><div class="vd-bg"></div><div class="vd-copy"><span class="vd-tag"><i class="bi bi-camera-reels-fill"></i> Cinema</span><h1>Videos</h1><p>No videos yet. Upload MP4 files from the Admin Panel and they show up here.</p></div></div></section>
<?php endif; ?>

<nav class="vd-genres" aria-label="Genres">
  <a class="<?= $catId ? '' : 'on' ?>" href="videos.php">All</a>
  <?php foreach ($cats as $c): ?><a class="<?= $catId === (int)$c['id'] ? 'on' : '' ?>" href="videos.php?category=<?= (int)$c['id'] ?>"><?= h($c['name']) ?></a><?php endforeach; ?>
</nav>

<?php if ($newest): ?>
<section class="vd-block">
  <h2 class="vd-h">Just added</h2>
  <div class="vd-rail"><?php foreach ($newest as $m) echo video_card($m); ?></div>
</section>
<?php endif; ?>

<?php if ($all): ?>
<section class="vd-block">
  <h2 class="vd-h"><?= $catName ? h($catName) : 'All videos' ?><small><?= h(plural($total, 'video')) ?></small></h2>
  <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-3 g-3 g-xl-4">
    <?php foreach ($all as $m) echo video_card($m); ?>
  </div>
  <?php if ($total > count($all)): ?><p class="vd-more"><a class="btn btn-sg-outline" href="browse.php?type=video<?= $catId ? '&amp;category=' . $catId : '' ?>">See all <?= number_format($total) ?> videos</a></p><?php endif; ?>
</section>
<?php elseif ($hero): ?>
<div class="empty"><i class="bi bi-play-circle"></i><p>No videos in this genre yet.</p></div>
<?php endif; ?>
<?php layout_close(); require 'footer.php'; ?>
