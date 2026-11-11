</main>
<?php
$st = [
    'Songs'   => (int)$pdo->query("SELECT COUNT(*) FROM media WHERE type='music'")->fetchColumn(),
    'Videos'  => (int)$pdo->query("SELECT COUNT(*) FROM media WHERE type='video'")->fetchColumn(),
    'Albums'  => (int)$pdo->query("SELECT COUNT(DISTINCT album) FROM media WHERE album<>''")->fetchColumn(),
    'Artists' => (int)$pdo->query("SELECT COUNT(DISTINCT artist) FROM media WHERE artist<>''")->fetchColumn(),
    'Members' => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn(),
    'Reviews' => (int)$pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn(),
];
$allCats = $pdo->query("SELECT id,name FROM categories ORDER BY name LIMIT 16")->fetchAll();
?>
<footer class="el-foot">
  <div class="el-foot-wrap">

    <section class="el-foot-top" aria-labelledby="elFootH">
      <div>
        <h2 id="elFootH">Music that follows you everywhere.</h2>
        <p>Songs, videos and albums in one place. Join the list and hear about every new release first.</p>
      </div>
      <form class="el-news" method="post" action="subscribe.php">
        <?= csrf_field() ?>
        <input type="email" name="email" placeholder="Your email address" required aria-label="Email address">
        <button type="submit">Subscribe <i class="bi bi-arrow-right"></i></button>
      </form>
    </section>

    <div class="el-foot-grid">
      <div class="el-foot-brand">
        <a class="el-brand" href="index.php"><?= sx_logo('lgw3', $SITE['name']) ?></a>
        <p><?= h($SITE['tagline']) ?> A player that stays with you while you browse, and playlists that stay on your account.</p>
      </div>
      <nav aria-label="Explore">
        <h3>Explore</h3>
        <ul>
          <li><a href="index.php">Home</a></li><li><a href="music.php">Music</a></li><li><a href="videos.php">Videos</a></li>
          <li><a href="charts.php">Top Charts</a></li><li><a href="new.php">New Releases</a></li><li><a href="trending.php">Trending Now</a></li><li><a href="discover.php">Discover</a></li>
        </ul>
      </nav>
      <nav aria-label="Library">
        <h3>Library</h3>
        <ul><li><a href="albums.php">Albums</a></li><li><a href="artists.php">Artists</a></li><li><a href="genres.php">Genres</a></li><li><a href="languages.php">Languages</a></li><li><a href="decades.php">Decades</a></li><li><a href="radio.php">Radio</a></li><li><a href="playlists.php">My Playlist</a></li></ul>
      </nav>
      <nav aria-label="Account">
        <h3>Account</h3>
        <ul>
          <?php if (logged_in()): ?><li><a href="account.php">My Account</a></li><?php else: ?><li><a href="login.php">Log in</a></li><li><a href="register.php">Join free</a></li><?php endif; ?>
          <?php if (logged_in()): ?><li><a href="history.php">Recently Played</a></li><li><a href="request.php">Request a Song</a></li><?php endif; ?>
          <li><a href="premium.php">Premium</a></li><li><a href="about.php">About</a></li><li><a href="faq.php">FAQ</a></li><li><a href="contact.php">Contact</a></li>
        </ul>
      </nav>
    </div>

    <div class="el-foot-stats" aria-label="Library in numbers">
      <?php foreach ($st as $label => $n): ?><div><strong><?= number_format($n) ?></strong><small><?= h($label) ?></small></div><?php endforeach; ?>
    </div>

    <?php if ($allCats): ?>
    <div class="el-foot-genres" aria-label="Genres">
      <?php foreach ($allCats as $c): ?><a href="browse.php?category=<?= (int)$c['id'] ?>"><?= h($c['name']) ?></a><?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="el-foot-bottom">
      <span>&copy; <?= date('Y') ?> <?= h($SITE['name']) ?>. All rights reserved.</span>
      <span class="el-legal"><a href="terms.php">Terms &amp; Conditions</a><a href="privacy.php">Privacy Policy</a><a href="sitemap.php">Site Map</a></span>
      <a class="el-top" href="#top" id="sxTop" aria-label="Back to top"><i class="bi bi-arrow-up"></i></a>
    </div>
  </div>
  <div class="el-watermark" aria-hidden="true"><?= h(strtoupper($SITE['name'])) ?></div>
</footer>

<?php
/* Mobile tab bar (shown under 768px) */
$pmPage = basename($_SERVER['SCRIPT_NAME'] ?? '');
$pmAcc  = in_array($pmPage, ['account.php', 'login.php', 'register.php'], true);
$pmTabs = [
    ['index.php', 'Home', 'house-door-fill', ($active ?? '') === 'home'],
    ['music.php', 'Music', 'music-note-beamed', ($active ?? '') === 'music'],
    ['videos.php', 'Videos', 'play-btn-fill', ($active ?? '') === 'videos'],
    ['playlists.php', 'Playlist', 'collection-play-fill', ($active ?? '') === 'playlists'],
    [logged_in() ? 'account.php' : 'login.php', logged_in() ? 'Account' : 'Login', 'person-fill', $pmAcc],
];
?>
<nav class="pm-tabs" aria-label="Quick navigation">
  <?php foreach ($pmTabs as [$u, $l, $ic, $on]): ?>
    <a class="pm-tab<?= $on ? ' on' : '' ?>" href="<?= h($u) ?>"<?= $on ? ' aria-current="page"' : '' ?>><i class="bi bi-<?= h($ic) ?>"></i><span><?= h($l) ?></span></a>
  <?php endforeach; ?>
</nav>

<!-- Bottom player bar (slides up when a song starts; IDs are used by app.js) -->
<div class="sx-player" id="sxPlayer" role="region" aria-label="Music player">
  <div class="sx-pl-info">
    <span class="sx-pl-thumb" id="npThumb"><i class="bi bi-music-note-beamed"></i></span>
    <span class="sx-pl-text"><b id="npTitle2">Nothing playing</b><small id="npArtist">Pick a song to start</small></span>
  </div>
  <div class="sx-pl-ctls">
    <button type="button" id="btnShuffle" class="sx-ctl sx-opt" aria-label="Shuffle" aria-pressed="false"><i class="bi bi-shuffle"></i></button>
    <button type="button" id="btnPrev" class="sx-ctl" aria-label="Previous"><i class="bi bi-skip-start-fill"></i></button>
    <button type="button" id="btnPlay" class="sx-ctl sx-main" aria-label="Play / Pause"><i class="bi bi-play-fill"></i></button>
    <button type="button" id="btnNext" class="sx-ctl" aria-label="Next"><i class="bi bi-skip-end-fill"></i></button>
    <button type="button" id="btnRepeat" class="sx-ctl sx-opt" aria-label="Repeat" aria-pressed="false"><i class="bi bi-repeat"></i></button>
  </div>
  <div class="sx-pl-seek">
    <span id="npCur">0:00</span>
    <input type="range" class="sx-range" id="npRange" min="0" max="1000" value="0" step="1" aria-label="Seek" disabled>
    <span id="npDur">0:00</span>
  </div>
  <div class="sx-pl-vol">
    <i class="bi bi-volume-up" id="sxVolIc"></i>
    <input type="range" class="sx-range" id="sxVol" min="0" max="100" value="80" aria-label="Volume">
  </div>
  <audio id="sgAudio" preload="metadata"></audio>
</div>

<div class="modal fade" id="premiumModal" tabindex="-1" aria-labelledby="premiumLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered"><div class="modal-content sg-modal">
    <div class="modal-body text-center p-4">
      <i class="bi bi-gem modal-gem"></i>
      <h3 id="premiumLabel" class="mt-2">Premium is coming soon</h3>
      <p class="text-secondary mb-4">Ad-free listening and unlimited streaming plans will be announced here. For now, enjoy everything on <?= h($SITE['name']) ?> for free.</p>
      <button class="btn btn-sg" data-bs-dismiss="modal">Got it</button>
    </div>
  </div></div>
</div>

<script src="../assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
(function(){
  /* navbar: glass + shadow after scrolling */
  var nav=document.getElementById('elNav');
  function f(){if(nav)nav.classList.toggle('is-scrolled',window.scrollY>10)}f();window.addEventListener('scroll',f,{passive:true});
  /* scroll reveal */
  var rv=document.querySelectorAll('.sec,.sx-prem,.el-foot-top,.el-foot-grid,.el-foot-stats');
  rv.forEach(function(e){e.classList.add('reveal')});
  if('IntersectionObserver' in window){var io=new IntersectionObserver(function(l){l.forEach(function(x){if(x.isIntersecting){x.target.classList.add('in');io.unobserve(x.target)}})},{threshold:.08,rootMargin:'0px 0px -40px 0px'});rv.forEach(function(e){io.observe(e)})}
  else rv.forEach(function(e){e.classList.add('in')});
  /* back to top */
  var top=document.getElementById('sxTop');
  if(top)top.addEventListener('click',function(e){e.preventDefault();window.scrollTo({top:0,behavior:'smooth'})});
  /* volume (remembered) */
  var a=document.getElementById('sgAudio'),v=document.getElementById('sxVol'),ic=document.getElementById('sxVolIc');
  if(a&&v){
    var s=null;try{s=localStorage.getItem('sg_vol')}catch(e){}
    var sv=+s;v.value=(s!==null&&!isNaN(sv)&&sv>=5)?Math.min(100,sv):80;   /* a saved volume of 0 would play in silence: start at 80 */
    a.muted=false;
    function setv(){a.volume=v.value/100;v.style.setProperty('--pos',v.value+'%');
      ic.className='bi '+(a.volume===0?'bi-volume-mute':a.volume<.5?'bi-volume-down':'bi-volume-up');
      try{localStorage.setItem('sg_vol',v.value)}catch(e){}}
    v.addEventListener('input',setv);setv();
    ic.addEventListener('click',function(){a.muted=!a.muted;ic.style.opacity=a.muted?.45:1});
  }
})();
</script>
<script src="app.js?v=7"></script>
<script src="prime.js?v=1"></script>
<script src="elite.js?v=1"></script>
<script src="signature.js?v=1"></script>
</body>
</html>
