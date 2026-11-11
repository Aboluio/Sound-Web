<?php
require 'config.php';
$pageTitle = 'Premium'; $active = 'premium';
$plans = [
  ['Free', 'Free forever', false, ['Stream every song and video', 'Playlists, ratings and reviews', 'Resume where you stopped'], 'Start free', 'register.php'],
  ['Premium', 'Coming soon', true, ['Everything in Free', 'Ad-free listening', 'Unlimited streaming'], '', ''],
];
require 'header.php';
?>
<div class="sg-wrap">
  <header class="pr-hero">
    <span class="pr-chip"><i class="bi bi-gem"></i> <?= h($SITE['name']) ?> Premium</span>
    <h1>Listen your way</h1>
    <p>Free is complete today. Premium adds an ad-free, unlimited experience when it launches.</p>
  </header>

  <div class="pr-plans">
    <?php foreach ($plans as [$name, $price, $hot, $perks, $cta, $url]): ?>
      <section class="pr-plan<?= $hot ? ' is-hot' : '' ?>">
        <?php if ($hot): ?><span class="pr-ribbon">Coming soon</span><?php endif; ?>
        <h2><?= h($name) ?></h2>
        <div class="pr-price"><?= h($price) ?></div>
        <ul><?php foreach ($perks as $p): ?><li><i class="bi bi-check2-circle"></i> <?= h($p) ?></li><?php endforeach; ?></ul>
        <?php if ($cta): ?><a class="btn btn-sg-outline w-100 justify-content-center" href="<?= h($url) ?>"><?= h($cta) ?></a>
        <?php else: ?>
          <form class="news-form pr-notify" method="post" action="subscribe.php"><?= csrf_field() ?>
            <input type="email" name="email" placeholder="Email me when it launches" required aria-label="Email address">
            <button type="submit" aria-label="Notify me"><i class="bi bi-bell"></i></button></form>
        <?php endif; ?>
      </section>
    <?php endforeach; ?>
  </div>

  <section class="pr-why" aria-label="What Premium adds">
    <div><i class="bi bi-slash-circle"></i><b>Ad-free</b><small>Nothing between you and the music.</small></div>
    <div><i class="bi bi-infinity"></i><b>Unlimited</b><small>Stream as much as you like.</small></div>
    <div><i class="bi bi-arrow-repeat"></i><b>Resume</b><small>Pick up right where you stopped.</small></div>
  </section>
</div>
<?php require 'footer.php'; ?>
