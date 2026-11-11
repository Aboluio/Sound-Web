<?php
if (!isset($pageTitle)) $pageTitle = 'Home';
if (!isset($active)) $active = '';
$flash = get_flash();

/* Bell: the 5 most recent uploads */
$newItems = $pdo->query("SELECT id,type,title,artist,cover_image,created_at FROM media ORDER BY created_at DESC, id DESC LIMIT 5")->fetchAll();
$hasNew = false;
foreach ($newItems as $n) if (strtotime($n['created_at']) >= strtotime('-3 days')) { $hasNew = true; break; }

$navItems = ['home' => ['index.php', 'Home'], 'music' => ['music.php', 'Music'], 'videos' => ['videos.php', 'Videos'],
    'charts' => ['charts.php', 'Top Charts'], 'new' => ['new.php', 'New Releases'], 'albums' => ['albums.php', 'Albums'], 'artists' => ['artists.php', 'Artists'],
    'genres' => ['genres.php', 'Genres'], 'playlists' => ['playlists.php', 'Playlists'],
    'history' => ['history.php', 'Recently Played'], 'trending' => ['trending.php', 'Trending Now'], 'discover' => ['discover.php', 'Discover'],
    'radio' => ['radio.php', 'Radio'], 'languages' => ['languages.php', 'Languages'], 'decades' => ['decades.php', 'Decades'],
    'request' => ['request.php', 'Request a Song'], 'faq' => ['faq.php', 'FAQ']];
/* Desktop bar shows the main five; the rest live in the Library menu */
$mainKeys = ['home', 'music', 'videos', 'charts', 'new'];
$browseKeys = ['albums', 'artists', 'genres', 'playlists', 'history'];
$discoverKeys = ['trending', 'discover', 'radio', 'languages', 'decades', 'request'];
$libInfo = ['albums' => ['disc', 'Albums', 'Collections, cover to cover'], 'artists' => ['person-badge', 'Artists', 'Everyone on the label'],
    'genres' => ['grid-1x2', 'Genres', 'Pick a mood or style'], 'playlists' => ['collection-play', 'Playlists', 'Curated & your saved songs'],
    'history' => ['clock-history', 'Recently Played', 'What you listened to last'],
    'trending' => ['fire', 'Trending Now', 'Hot songs and videos today'], 'discover' => ['shuffle', 'Discover', 'A random pick, just for you'],
    'radio' => ['broadcast', 'Radio', 'Endless shuffled stations'], 'languages' => ['translate', 'Languages', 'Listen in your language'],
    'decades' => ['calendar3', 'Decades', 'Music from every era'], 'request' => ['send', 'Request a Song', 'Tell us what to add']];
$pgKey = preg_replace('/[^a-z0-9]+/', '-', strtolower(basename($_SERVER['SCRIPT_NAME'] ?? 'page', '.php')));
$me = $_SESSION['user'] ?? null;
$home = $me ? 'index.php' : '../index.php';
?><!doctype html>
<html lang="en" data-bs-theme="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="description" content="<?= h($SITE['name']) ?> — stream your favorite songs and videos in one place.">
<meta name="theme-color" content="#08060b">
<meta name="csrf" content="<?= h(csrf_token()) ?>">
<title><?= h($pageTitle) ?> | <?= h($SITE['name']) ?></title>
<link rel="icon" href="../assets/images/favicon.png" type="image/png"><link rel="shortcut icon" href="../assets/images/favicon.ico">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700;800&family=Fraunces:opsz,wght@9..144,400;9..144,600;9..144,700&family=Manrope:wght@400;500;600;700;800&family=Sora:wght@500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/libs/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="../assets/libs/bootstrap-icons/bootstrap-icons.css">
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="premium.css?v=4">
<link rel="stylesheet" href="site.css?v=4">
<link rel="stylesheet" href="luxe.css?v=1">
<link rel="stylesheet" href="prime.css?v=1">
<link rel="stylesheet" href="elite.css?v=1">
<link rel="stylesheet" href="signature.css?v=1">
<link rel="stylesheet" href="pages.css?v=1">
<link rel="stylesheet" href="melovix.css?v=1">
<link rel="stylesheet" href="mx.css?v=1">
<link rel="stylesheet" href="pages2.css?v=1">
<link rel="stylesheet" href="slider.css?v=2">
<script>document.documentElement.className+=" js"</script>
</head>
<body id="top" class="pg-<?= h($pgKey) ?><?= $me ? '' : ' is-guest' ?>">
<a class="sx-skip" href="#main">Skip to content</a>

<header class="el-nav" id="elNav">
  <div class="el-nav-in">
    <a class="el-brand" href="<?= h($home) ?>" aria-label="<?= h($SITE['name']) ?>"><?= sx_logo('lgw1', $SITE['name']) ?></a>

    <nav class="el-menu" aria-label="Main">
      <?php foreach ($mainKeys as $k): [$url, $label] = $navItems[$k]; ?>
        <a class="el-link<?= $active === $k ? ' active' : '' ?>" href="<?= h($url) ?>"<?= $active === $k ? ' aria-current="page"' : '' ?>><?= h($label) ?></a>
      <?php endforeach; ?>
      <div class="dropdown el-lib">
        <a class="el-link dropdown-toggle<?= in_array($active, $browseKeys, true) ? ' active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" data-bs-offset="0,14" aria-expanded="false">Library</a>
        <div class="dropdown-menu el-mega">
          <?php foreach ($browseKeys as $k): [$ic, $t, $d] = $libInfo[$k]; ?>
            <a class="el-mega-item<?= $active === $k ? ' active' : '' ?>" href="<?= h($navItems[$k][0]) ?>">
              <span class="el-mega-ic"><i class="bi bi-<?= h($ic) ?>"></i></span>
              <span><b><?= h($t) ?></b><small><?= h($d) ?></small></span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="dropdown el-lib">
        <a class="el-link dropdown-toggle<?= in_array($active, $discoverKeys, true) ? ' active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" data-bs-offset="0,14" aria-expanded="false">Discover</a>
        <div class="dropdown-menu el-mega">
          <?php foreach ($discoverKeys as $k): [$ic, $t, $d] = $libInfo[$k]; ?>
            <a class="el-mega-item<?= $active === $k ? ' active' : '' ?>" href="<?= h($navItems[$k][0]) ?>">
              <span class="el-mega-ic"><i class="bi bi-<?= h($ic) ?>"></i></span>
              <span><b><?= h($t) ?></b><small><?= h($d) ?></small></span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </nav>

    <div class="el-tools">
      <form class="el-search" id="elSearch" action="browse.php" role="search">
        <button type="button" class="el-ic" id="elSearchBtn" aria-label="Search" aria-expanded="false"><i class="bi bi-search"></i></button>
        <input type="search" name="q" id="elSearchIn" value="<?= h($_GET['q'] ?? '') ?>" placeholder="Search songs, artists, albums" aria-label="Search" autocomplete="off">
      </form>

      <div class="dropdown">
        <button class="el-ic" data-bs-toggle="dropdown" data-bs-auto-close="outside" data-bs-offset="0,14" aria-label="New releases" aria-expanded="false">
          <i class="bi bi-bell"></i><?php if ($hasNew): ?><span class="el-dot"></span><?php endif; ?>
        </button>
        <div class="dropdown-menu dropdown-menu-end el-pop">
          <div class="el-pop-head">New releases</div>
          <?php foreach ($newItems as $n): ?>
            <a class="el-pop-item" href="detail.php?id=<?= (int)$n['id'] ?>">
              <span class="el-pop-thumb"><?php if ($n['cover_image']): ?><img src="<?= h($n['cover_image']) ?>" alt=""><?php else: ?><i class="bi bi-<?= $n['type'] === 'video' ? 'play-fill' : 'music-note-beamed' ?>"></i><?php endif; ?></span>
              <span class="el-pop-txt"><b><?= h($n['title']) ?></b><small><?= h($n['artist'] ?: 'Unknown artist') ?> &middot; <?= $n['type'] === 'video' ? 'Video' : 'Song' ?></small></span>
            </a>
          <?php endforeach; ?>
          <?php if (!$newItems): ?><div class="el-pop-empty">Nothing new yet.</div><?php endif; ?>
          <a class="el-pop-all" href="new.php">See all new releases</a>
        </div>
      </div>

      <a class="el-premium" href="premium.php"><i class="bi bi-gem"></i><span>Premium</span></a>

      <?php if ($me): ?>
        <div class="dropdown">
          <button class="el-user" data-bs-toggle="dropdown" data-bs-offset="0,14" aria-expanded="false">
            <?php $__av = current_avatar_url($pdo); if ($__av): ?><img src="<?= h($__av) ?>" alt="" width="34" height="34" style="object-fit:cover;border-radius:50%"><?php else: ?><span aria-hidden="true" style="width:34px;height:34px;border-radius:50%;flex:none;display:inline-flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#3a3f5c,#1f2236);color:#c9cbe0;font-size:1.15rem"><i class="bi bi-person-fill"></i></span><?php endif; ?>
            <span class="el-uname"><?= h($me['full_name'] ?: $me['username']) ?></span><i class="bi bi-chevron-down"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end el-pop el-pop-list">
            <li><a class="el-pop-link" href="account.php"><i class="bi bi-person-circle"></i> My Account</a></li>
            <li><a class="el-pop-link" href="playlists.php"><i class="bi bi-music-note-list"></i> My Playlist</a></li>
            <li><a class="el-pop-link" href="history.php"><i class="bi bi-clock-history"></i> Recently Played</a></li>
            <li><a class="el-pop-link" href="request.php"><i class="bi bi-send"></i> Request a Song</a></li>
            <li><a class="el-pop-link" href="premium.php"><i class="bi bi-gem"></i> Go Premium</a></li>
            <?php if (is_admin()): ?><li><a class="el-pop-link" href="../admin/"><i class="bi bi-speedometer2"></i> Admin Panel</a></li><?php endif; ?>
            <li><hr class="dropdown-divider"></li>
            <li><a class="el-pop-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
          </ul>
        </div>
      <?php else: ?>
        <a class="el-login" href="login.php">Log in</a>
        <a class="el-join" href="register.php">Join free</a>
      <?php endif; ?>

      <button class="el-ic el-burger" type="button" data-bs-toggle="offcanvas" data-bs-target="#sxOff" aria-controls="sxOff" aria-label="Open menu"><i class="bi bi-list"></i></button>
    </div>
  </div>
</header>

<!-- Mobile / tablet menu -->
<div class="offcanvas offcanvas-end el-off" tabindex="-1" id="sxOff" aria-label="Menu">
  <div class="offcanvas-header">
    <a class="el-brand" href="<?= h($home) ?>"><?= sx_logo('lgw2', $SITE['name']) ?></a>
    <button type="button" class="el-ic" data-bs-dismiss="offcanvas" aria-label="Close menu"><i class="bi bi-x-lg"></i></button>
  </div>
  <div class="offcanvas-body">
    <form class="el-off-search" action="browse.php" role="search">
      <i class="bi bi-search"></i>
      <input type="search" name="q" placeholder="Search songs, artists, albums" aria-label="Search">
    </form>
    <nav class="el-off-links" aria-label="Mobile">
      <?php foreach ($navItems as $k => [$url, $label]): ?>
        <a class="<?= $active === $k ? 'active' : '' ?>" href="<?= h($url) ?>"><?= h($label) ?><i class="bi bi-arrow-right"></i></a>
      <?php endforeach; ?>
      <a class="gold<?= $active === 'premium' ? ' active' : '' ?>" href="premium.php">Premium<i class="bi bi-gem"></i></a>
    </nav>
    <div class="el-off-cta">
      <?php if ($me): ?>
        <a class="btn btn-sg-outline" href="account.php"><i class="bi bi-person-circle"></i> My Account</a>
        <a class="btn btn-sg-outline" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
      <?php else: ?>
        <a class="btn btn-sg" href="register.php">Join free</a>
        <a class="btn btn-sg-outline" href="login.php">Log in</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<main class="sg-main" id="main">
<?php if ($flash): ?><div class="sg-wrap"><div class="alert sg-alert alert-<?= h($flash[0] === 'error' ? 'danger' : $flash[0]) ?> js-flash mt-3" role="status"><?= h($flash[1]) ?></div></div><?php endif; ?>
<?php
/* Full-width page body. The player now lives in the bottom bar (see footer.php). */
function layout_open() { echo '<div class="sg-wrap sg-full">'; }
function layout_close() { echo '</div>'; }
