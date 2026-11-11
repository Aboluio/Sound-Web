<?php
require 'config.php';
$pageTitle = 'Privacy Policy'; $active = 'privacy';
require 'header.php'; layout_open();
p2_head('Legal', 'Privacy Policy', 'Last updated: ' . date('F Y') . '.'); ?>
<article class="p2-prose">
  <h2>What we collect</h2><p>When you sign up we store your name, username, email, phone, address and a hashed password. We also keep your playlist, reviews and recently played list so the site can work for you.</p>
  <h2>How we use it</h2><p>Your details are used only to run your account and the features of <?= h($SITE['name']) ?>. We do not sell your information.</p>
  <h2>Newsletter</h2><p>If you subscribe with your email in the footer, we use it only to tell you about new releases. Contact us to be removed.</p>
  <h2>Cookies</h2><p>We use a session cookie to keep you logged in and your browser storage to remember the player queue. Nothing else.</p>
  <h2>Your choices</h2><p>You can clear your play history any time, and ask us to delete your account by writing to <a href="mailto:<?= h($SITE['email']) ?>"><?= h($SITE['email']) ?></a>.</p>
</article>
<?php layout_close(); require 'footer.php';
