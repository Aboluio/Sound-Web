<?php
require 'config.php';
$pageTitle = 'Site Map'; $active = 'sitemap';
$map = [
 'Explore' => [['index.php','Home'],['music.php','Music'],['videos.php','Videos'],['charts.php','Top Charts'],['new.php','New Releases'],['trending.php','Trending Now'],['discover.php','Discover']],
 'Library' => [['albums.php','Albums'],['artists.php','Artists'],['genres.php','Genres'],['languages.php','Languages'],['decades.php','Decades'],['radio.php','Radio'],['playlists.php','Playlists'],['history.php','Recently Played']],
 'Account' => [['account.php','My Account'],['request.php','Request a Song'],['premium.php','Premium'],['login.php','Log in'],['register.php','Join free']],
 'Help' => [['about.php','About'],['contact.php','Contact'],['faq.php','FAQ'],['privacy.php','Privacy Policy'],['terms.php','Terms & Conditions']],
];
require 'header.php'; layout_open();
p2_head('Every page', 'Site Map', 'All the pages of ' . $SITE['name'] . ' in one place.'); ?>
<div class="p2-map">
<?php foreach ($map as $g => $links): ?>
  <section class="p2-card"><h2 class="p2-h2"><?= h($g) ?></h2><ul><?php foreach ($links as [$u, $l]): ?><li><a href="<?= h($u) ?>"><?= h($l) ?><i class="bi bi-arrow-right"></i></a></li><?php endforeach; ?></ul></section>
<?php endforeach; ?>
</div>
<?php layout_close(); require 'footer.php';
