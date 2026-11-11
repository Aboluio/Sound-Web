<?php
require 'config.php';
$pageTitle = 'Home'; $active = 'home';

$cats = $pdo->query("SELECT id,name FROM categories ORDER BY id")->fetchAll();
$catId = (int)($_GET['category'] ?? 0);
$catName = '';
foreach ($cats as $c) if ((int)$c['id'] === $catId) $catName = $c['name'];
if (!$catName) $catId = 0;
$cw = $catId ? ' AND category_id=' . $catId : '';

$trendMusic  = $pdo->query("SELECT * FROM media WHERE type='music' $cw ORDER BY plays DESC, created_at DESC, id DESC LIMIT 6")->fetchAll();
$trendVideos = $pdo->query("SELECT * FROM media WHERE type='video' $cw ORDER BY plays DESC, created_at DESC, id DESC LIMIT 6")->fetchAll();
$albums      = $pdo->query("SELECT album, MAX(artist) artist, MAX(cover_image) cover_image, COUNT(*) songs, MAX(created_at) latest
                            FROM media WHERE type='music' AND album<>'' $cw GROUP BY album ORDER BY latest DESC LIMIT 6")->fetchAll();
$chart       = $pdo->query("SELECT * FROM media WHERE type='music' $cw ORDER BY plays DESC, created_at DESC, id DESC LIMIT 10")->fetchAll();
/* slider: every song / video that has a cover picture gets a slide (newest first); change LIMIT 8 to show more */
$featured    = $pdo->query("SELECT * FROM media WHERE cover_image <> '' ORDER BY created_at DESC, id DESC LIMIT 8")->fetchAll();
$homeLists   = site_playlists($pdo, 6);

require 'header.php';
?>
<section class="sx-hero carousel slide carousel-fade" id="heroCarousel" data-bs-ride="carousel" data-bs-interval="6000" data-bs-pause="false" data-bs-touch="true" aria-label="Featured songs">
  <?php $slides = 1 + count($featured); /* intro slide with the hero image + one slide per song cover */ ?>
  <?php if ($slides > 1): ?>
  <div class="carousel-indicators">
    <?php for ($i = 0; $i < $slides; $i++): ?><button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?= $i ?>" class="<?= $i ? '' : 'active' ?>" aria-label="Slide <?= $i + 1 ?>"></button><?php endfor; ?>
  </div>
  <?php endif; ?>

  <div class="carousel-inner">
    <div class="carousel-item active">
      <div class="sx-slide">
        <div class="sx-bg"></div>
        <div class="sg-wrap"><div class="sx-grid">
          <div class="sx-copy">
            <span class="sx-eyebrow"><i class="bi bi-stars"></i> Your music <b></b> Your vibe <b></b> Always</span>
            <h1>Discover<br>Amazing <span class="sx-grad">Music</span></h1>
            <p>Stream your favourite songs, enjoy unlimited music and videos &mdash; all in one beautiful place.</p>
            <div class="sx-btns"><button type="button" class="btn btn-sg btn-lg js-play-first"><i class="bi bi-play-fill"></i> Start Listening</button><a class="btn btn-sg-outline btn-lg" href="browse.php"><i class="bi bi-collection-play"></i> Browse Library</a></div>
          </div>
          <div class="sx-art sx-logo-art"><span class="sx-logo-ring"></span><img class="sx-logo-img" src="../assets/images/soundweb-logo.png" alt="<?= h($SITE['name']) ?>" width="460" height="460" fetchpriority="high"></div>
        </div></div>
      </div>
    </div>
  <?php foreach ($featured as $n => $f): ?>
    <div class="carousel-item">
      <div class="sx-slide sx-banner">
        <div class="sx-bg" style="background-image:url('<?= h($f['cover_image']) ?>')" role="img" aria-label="<?= h($f['title']) ?>"></div>
        <div class="sg-wrap">
          <div class="sx-copy">
            <span class="sx-eyebrow"><i class="bi bi-lightning-charge-fill"></i> <?= $n === 0 ? 'Latest' : 'New' ?> <?= $f['type'] === 'video' ? 'video' : 'release' ?></span>
            <h2 class="sx-song"><?= h($f['title']) ?><small><?= h($f['artist'] ?: 'Unknown artist') ?><?= $f['album'] ? ' &middot; ' . h($f['album']) : '' ?></small></h2>
            <div class="sx-btns">
              <?php if ($f['type'] === 'music'): ?>
                <button type="button" class="btn btn-sg btn-lg js-play" <?= track_attrs($f) ?>><i class="bi bi-play-fill"></i> Play Now</button>
              <?php else: ?>
                <a class="btn btn-sg btn-lg" href="detail.php?id=<?= (int)$f['id'] ?>"><i class="bi bi-play-fill"></i> Watch Now</a>
              <?php endif; ?>
              <a class="btn btn-sg-outline btn-lg" href="detail.php?id=<?= (int)$f['id'] ?>">View Details</a>
            </div>
            <div class="sx-mini">
              <span><i class="bi bi-headphones"></i> <?= number_format((int)$f['plays']) ?> plays</span>
              <?php if ($f['duration']): ?><span><i class="bi bi-clock"></i> <?= h(fmt_duration($f['duration'])) ?></span><?php endif; ?>
              <?php if ($f['genre']): ?><span><i class="bi bi-tag"></i> <?= h($f['genre']) ?></span><?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  </div>

  <?php if ($slides > 1): ?>
  <div class="sx-ctrl" role="group" aria-label="Slider controls">
    <button type="button" data-bs-target="#heroCarousel" data-bs-slide="prev" aria-label="Previous slide"><i class="bi bi-chevron-left"></i></button>
    <button type="button" id="heroPause" aria-label="Pause slider"><i class="bi bi-pause-fill"></i></button>
    <span id="heroCount">1 / <?= $slides ?></span>
    <button type="button" data-bs-target="#heroCarousel" data-bs-slide="next" aria-label="Next slide"><i class="bi bi-chevron-right"></i></button>
  </div>
  <script>
  document.addEventListener('DOMContentLoaded',function(){
    var el=document.getElementById('heroCarousel'),b=document.getElementById('heroPause'),cnt=document.getElementById('heroCount');
    if(!el||!b||!window.bootstrap)return;
    var c=bootstrap.Carousel.getOrCreateInstance(el,{interval:5000,ride:'carousel',pause:false,touch:true,wrap:true}),on=true,n=el.querySelectorAll('.carousel-item').length;
    c.cycle();
    b.addEventListener('click',function(){on=!on;if(on)c.cycle();else c.pause();
      b.firstElementChild.className='bi bi-'+(on?'pause-fill':'play-fill');b.setAttribute('aria-label',on?'Pause slider':'Play slider');});
    el.addEventListener('slid.bs.carousel',function(e){cnt.textContent=(e.to+1)+' / '+n;});
    document.addEventListener('keydown',function(e){if(e.target.closest&&e.target.closest('input,textarea,select'))return;
      if(e.key==='ArrowLeft')c.prev();else if(e.key==='ArrowRight')c.next();});
  });
  </script>
  <?php endif; ?>
</section>

<div class="sg-wrap">
  <nav class="sx-cats" aria-label="Categories">
    <a class="sx-cat<?= $catId ? '' : ' active' ?>" href="index.php"><i class="bi bi-grid-fill"></i>All</a>
    <?php foreach ($cats as $c): ?>
      <a class="sx-cat<?= $catId === (int)$c['id'] ? ' active' : '' ?>" href="index.php?category=<?= (int)$c['id'] ?>"><i class="bi bi-<?= cat_icon($c['name']) ?>"></i><?= h($c['name']) ?></a>
    <?php endforeach; ?>
  </nav>
</div>

<?php layout_open(); ?>
  <?php if ($catName): ?><p class="filter-note">Showing <b><?= h($catName) ?></b> &middot; <a href="index.php">clear filter</a></p><?php endif; ?>

  <?php if (count($chart) >= 3): ?>
  <section class="sec" id="charts">
    <div class="sec-head"><h2><span class="sec-ic"><i class="bi bi-trophy-fill"></i></span> Top Charts</h2><span class="text-secondary small">Most played songs</span></div>
    <div class="track-table chart sx-chart" data-queue>
      <?php foreach ($chart as $i => $m): ?>
        <div class="tt-row rank-<?= $i + 1 ?>">
          <span class="tt-n"><?= $i + 1 ?></span>
          <span class="tt-thumb"><?= thumb_inner($m) ?></span>
          <a class="tt-info" href="detail.php?id=<?= (int)$m['id'] ?>"><b><?= h($m['title']) ?></b><small><?= h($m['artist'] ?: 'Unknown artist') ?></small></a>
          <span class="tt-type"><?= number_format((int)$m['plays']) ?> plays</span>
          <span class="tt-dur"><?= h(fmt_duration($m['duration'])) ?></span>
          <button type="button" class="btn btn-sg btn-icon js-play" <?= track_attrs($m) ?> aria-label="Play <?= h($m['title']) ?>"><i class="bi bi-play-fill"></i></button>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <section class="sec" id="trending">
    <div class="sec-head"><h2><span class="sec-ic"><i class="bi bi-graph-up-arrow"></i></span> Trending Music</h2><a class="view-all" href="music.php<?= $catId ? '&category=' . $catId : '' ?>">View All <i class="bi bi-arrow-right"></i></a></div>
    <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-xl-6 g-3 g-xl-4" data-queue>
      <?php foreach ($trendMusic as $m) echo music_card($m); ?>
    </div>
    <?php if (!$trendMusic): ?><div class="empty"><i class="bi bi-music-note-beamed"></i><p>No songs here yet. The administrator can add songs from the Admin Panel.</p></div><?php endif; ?>
  </section>

  <section class="sx-prem" aria-labelledby="sxPremH">
    <div class="sx-prem-copy">
      <span class="sx-eyebrow gold"><i class="bi bi-gem"></i> <?= h($SITE['name']) ?> Premium</span>
      <h2 id="sxPremH">Listen without limits.</h2>
      <p>Ad-free listening, unlimited streaming and more &mdash; coming soon. Be the first to know.</p>
      <a class="sx-gold lg" href="premium.php">See plans <i class="bi bi-arrow-right"></i></a>
    </div>
    <ul class="sx-prem-perks">
      <li><i class="bi bi-slash-circle"></i><b>Ad-free</b><small>Nothing between you and the music</small></li>
      <li><i class="bi bi-infinity"></i><b>Unlimited</b><small>Stream as much as you like</small></li>
      <li><i class="bi bi-arrow-repeat"></i><b>Resume</b><small>Pick up right where you stopped</small></li>
    </ul>
  </section>

  <section class="sec">
    <div class="sec-head"><h2><span class="sec-ic blue"><i class="bi bi-play-fill"></i></span> Trending Videos</h2><a class="view-all" href="browse.php?type=video<?= $catId ? '&category=' . $catId : '' ?>">View All <i class="bi bi-arrow-right"></i></a></div>
    <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-xl-6 g-3 g-xl-4">
      <?php foreach ($trendVideos as $m) echo video_card($m); ?>
    </div>
    <?php if (!$trendVideos): ?><div class="empty"><i class="bi bi-play-circle"></i><p>No videos here yet. Upload MP4 videos from the Admin Panel.</p></div><?php endif; ?>
  </section>

  <?php if ($homeLists): ?>
  <section class="sec">
    <div class="sec-head"><h2><span class="sec-ic"><i class="bi bi-collection-play-fill"></i></span> Playlists</h2><a class="view-all" href="playlists.php">View All <i class="bi bi-arrow-right"></i></a></div>
    <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-xl-6 g-3 g-xl-4">
      <?php foreach ($homeLists as $p) echo playlist_card($p); ?>
    </div>
  </section>
  <?php endif; ?>

  <?php if ($albums): ?>
  <section class="sec">
    <div class="sec-head"><h2><span class="sec-ic blue"><i class="bi bi-disc-fill"></i></span> Latest Albums</h2><a class="view-all" href="albums.php">View All <i class="bi bi-arrow-right"></i></a></div>
    <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-xl-6 g-3 g-xl-4">
      <?php foreach ($albums as $a) echo album_card($a); ?>
    </div>
  </section>
  <?php endif; ?>
<?php layout_close(); ?>
<?php require 'footer.php'; ?>
