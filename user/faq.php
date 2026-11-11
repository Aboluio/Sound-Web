<?php
require 'config.php';
$pageTitle = 'FAQ'; $active = 'faq';
$groups = [
 'Getting started' => [
  ['Do I need an account to listen?', 'You need to sign up for free and log in to open the music and video library. It takes less than a minute.'],
  ['Is ' . $SITE['name'] . ' free?', 'Yes. Everything is free. Premium adds extra perks on top.'],
  ['How do I search for a song?', 'Use the search icon at the top. You can search by title, artist, album, year or language.'],
 ],
 'Music & videos' => [
  ['How do I play a song while browsing?', 'Press play on any song. The player stays at the bottom, so you can keep browsing while it plays.'],
  ['Where can I see what I played before?', 'Open Library, then Recently Played.'],
  ['A song I want is missing. What now?', 'Use the Request a Song page. The admin will upload it.'],
  ['What is Radio?', 'Radio plays a shuffled mix of one genre, or all songs, one after another.'],
 ],
 'Account' => [
  ['How do I save songs?', 'Add them to My Playlist from the detail page. Your playlist stays on your account.'],
  ['How do I contact support?', 'Use the Contact page, by email or WhatsApp.'],
 ],
];
require 'header.php'; layout_open();
p2_head('Help centre', 'Frequently Asked Questions', 'Quick answers about listening, playlists and your account.'); ?>
<div class="p2-faq">
<?php $n = 0; foreach ($groups as $g => $qs): ?>
  <h2 class="p2-h2"><?= h($g) ?></h2>
  <div class="accordion mb-4" id="acc<?= $n ?>">
  <?php foreach ($qs as $i => [$q, $a]): $id = 'q' . $n . '_' . $i; ?>
    <div class="accordion-item sg-acc"><h3 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $id ?>" aria-expanded="false"><?= h($q) ?></button></h3>
    <div id="<?= $id ?>" class="accordion-collapse collapse" data-bs-parent="#acc<?= $n ?>"><div class="accordion-body"><?= h($a) ?></div></div></div>
  <?php endforeach; ?></div>
<?php $n++; endforeach; ?>
  <p class="p2-muted">Still stuck? <a href="contact.php">Contact us</a>.</p>
</div>
<?php layout_close(); require 'footer.php';
