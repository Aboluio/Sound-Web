<?php
require 'config.php';
$pageTitle = 'About'; $active = 'about';
$stats = [
  'Songs'   => (int)$pdo->query("SELECT COUNT(*) FROM media WHERE type='music'")->fetchColumn(),
  'Videos'  => (int)$pdo->query("SELECT COUNT(*) FROM media WHERE type='video'")->fetchColumn(),
  'Artists' => (int)$pdo->query("SELECT COUNT(DISTINCT artist) FROM media WHERE artist<>''")->fetchColumn(),
  'Members' => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn(),
];
require 'header.php';
?>
<div class="sg-wrap">
  <header class="ab-hero">
    <h1>A home for songs you can stream, save and talk about.</h1>
    <p class="ab-by">About <?= h($SITE['name']) ?></p>
  </header>

  <div class="ab-stats">
    <?php foreach ($stats as $l => $n): ?><div><strong><?= number_format($n) ?></strong><small><?= h($l) ?></small></div><?php endforeach; ?>
  </div>

  <div class="ab-body">
    <div class="ab-col">
      <h2>What you can do here</h2>
      <p>Browse music and videos by genre, artist or album. Press play on any song and keep browsing while it plays. Add favourites to your own playlist and rate or review anything you have heard.</p>
    </div>
    <div class="ab-col">
      <h2>Where the content comes from</h2>
      <p>Every song, video, album and artist is uploaded by the site administrator. Trending lists are built from real plays, so what rises is what people actually listen to.</p>
    </div>
  </div>

  <ol class="ab-steps">
    <li><b>Browse</b><span>Find something by genre, artist or album.</span></li>
    <li><b>Play</b><span>The player stays at the bottom while you keep exploring.</span></li>
    <li><b>Save</b><span>Build a playlist and leave a rating when you are done.</span></li>
  </ol>

  <a class="btn btn-sg ab-cta" href="browse.php"><i class="bi bi-play-fill"></i> Explore the library</a>
</div>
<?php require 'footer.php'; ?>
