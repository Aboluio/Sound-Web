<?php
require 'config.php';
$pageTitle = 'Contact'; $active = 'contact';
$digits = preg_replace('/\D+/', '', $SITE['phone']);
$faq = [
  ['Do I need an account to listen?', 'No. Anyone can browse and play. An account lets you save a playlist and write reviews.'],
  ['How do I add a song to my playlist?', 'Log in, then tap the + button on any song or video. It is saved to My Playlist.'],
  ['Why is a genre empty?', 'Genres fill up as the administrator uploads tracks to them.'],
];
require 'header.php';
?>
<div class="sg-wrap">
  <div class="ct-split">
    <header class="ct-intro">
      <h1>Say hello.</h1>
      <p>Questions, feedback or a song you want to see here? Reach us directly.</p>
      <div class="ct-cards">
        <a class="ct-card" href="mailto:<?= h($SITE['email']) ?>"><i class="bi bi-envelope-fill"></i><span><small>Email</small><b><?= h($SITE['email']) ?></b></span></a>
        <a class="ct-card" href="https://wa.me/<?= h($digits) ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i><span><small>WhatsApp</small><b><?= h($SITE['phone']) ?></b></span></a>
      </div>
    </header>

    <section class="faq ct-faq" aria-labelledby="faqH">
      <h2 id="faqH">Common questions</h2>
      <div class="accordion" id="faqAcc">
        <?php foreach ($faq as $i => [$q, $a]): ?>
          <div class="accordion-item sg-acc">
            <h3 class="accordion-header"><button class="accordion-button<?= $i ? ' collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#f<?= $i ?>" aria-expanded="<?= $i ? 'false' : 'true' ?>"><?= h($q) ?></button></h3>
            <div id="f<?= $i ?>" class="accordion-collapse collapse<?= $i ? '' : ' show' ?>" data-bs-parent="#faqAcc"><div class="accordion-body"><?= h($a) ?></div></div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  </div>
</div>
<?php require 'footer.php'; ?>
