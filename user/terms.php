<?php
require 'config.php';
$pageTitle = 'Terms & Conditions'; $active = 'terms';
require 'header.php'; layout_open();
p2_head('Legal', 'Terms & Conditions', 'Last updated: ' . date('F Y') . '.'); ?>
<article class="p2-prose">
  <h2>Using the site</h2><p>By using <?= h($SITE['name']) ?> you agree to these terms. Keep your login private and give correct details when you sign up.</p>
  <h2>Content</h2><p>All songs, videos and artwork are uploaded by the site administrator. Do not copy, resell or redistribute them without permission from their owners.</p>
  <h2>Your conduct</h2><p>Do not misuse the site, try to break into the admin area, or post abusive reviews. We may remove reviews or accounts that do.</p>
  <h2>Song requests</h2><p>Sending a request does not guarantee the song will be added.</p>
  <h2>Changes</h2><p>We may update these terms. Continuing to use the site means you accept the new version.</p>
  <h2>Contact</h2><p>Questions? Email <a href="mailto:<?= h($SITE['email']) ?>"><?= h($SITE['email']) ?></a>.</p>
</article>
<?php layout_close(); require 'footer.php';
