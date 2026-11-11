<?php
/* Sound Web — public welcome page (first page for visitors who are not logged in).
   Uses the same header (navbar) and footer as every other page. */
require 'config.php';
if (logged_in()) { header('Location: ' . (is_admin() ? '../admin/' : 'index.php')); exit; }
$pageTitle = 'Stream. Enjoy. Repeat.'; $active = '';
$cnt = [
    'Songs'   => (int)$pdo->query("SELECT COUNT(*) FROM media WHERE type='music'")->fetchColumn(),
    'Videos'  => (int)$pdo->query("SELECT COUNT(*) FROM media WHERE type='video'")->fetchColumn(),
    'Genres'  => (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn(),
    'Artists' => (int)$pdo->query("SELECT COUNT(DISTINCT artist) FROM media WHERE artist<>''")->fetchColumn(),
];
require 'header.php';
?>
<section class="mv-hero">
  <div class="mv-hero-in">
    <div>
      <span class="mv-kicker"><i class="bi bi-soundwave"></i> Songs &amp; Videos</span>
      <h1>Stream. Enjoy. <span>Repeat.</span></h1>
      <p><?= h($SITE['name']) ?> brings songs and videos together in one place. Save your favourites to a playlist, rate what you hear and pick up where you stopped.</p>
      <div class="mv-cta">
        <a class="mv-btn pri" href="register.php"><i class="bi bi-person-plus-fill"></i> Sign Up free</a>
        <a class="mv-btn ghost" href="login.php"><i class="bi bi-box-arrow-in-right"></i> Login</a>
      </div>
    </div>
    <div class="mv-art"><img src="../<?= h($SITE['logo']) ?>" alt="<?= h($SITE['name']) ?> logo" width="430" height="430"></div>
  </div>
</section>

<div class="mv-stats" aria-label="Library in numbers">
  <?php foreach ($cnt as $label => $n): ?><div><strong><?= number_format($n) ?></strong><small><?= h($label) ?></small></div><?php endforeach; ?>
</div>

<section class="mv-sec" id="features">
  <h2>Everything you need to listen</h2>
  <p class="lead">Built for people who just want to press play.</p>
  <div class="mv-feats">
    <div class="mv-feat"><i class="bi bi-music-note-list"></i><h3>Playlists that stay</h3><p>Add any song or video to your playlist and it is waiting there next time you log in.</p></div>
    <div class="mv-feat"><i class="bi bi-soundwave"></i><h3>A player that follows you</h3><p>Browse from page to page while the music keeps playing in the bottom player.</p></div>
    <div class="mv-feat"><i class="bi bi-star-fill"></i><h3>Rate and review</h3><p>Give every track a star rating and say what you think. Top charts follow real plays.</p></div>
  </div>
</section>

<div class="mv-wrap">
  <section class="mv-band">
    <h2>Your next favourite song is already here</h2>
    <p>Browse by genre, artist or album, or search for exactly what you want.</p>
    <div class="mv-cta"><a class="mv-btn pri" href="register.php">Create free account</a><a class="mv-btn ghost" href="login.php">Login</a></div>
  </section>
</div>
<?php require 'footer.php'; ?>
