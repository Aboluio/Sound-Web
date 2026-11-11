<?php
/* Spark Admin shell for Sound Web. Set $pageTitle (and optionally $pageSub, $crumb) before including. */
if (!isset($pageTitle)) $pageTitle = 'Admin Panel';
if (!isset($pageSub))   $pageSub   = '';
if (!isset($crumb))     $crumb     = 'Menu';
$flash = get_flash();
$cur   = basename($_SERVER['PHP_SELF']);
$me    = $_SESSION['user'] ?? ['full_name' => 'Administrator', 'username' => 'admin'];
$meName = $me['full_name'] ?: $me['username'];

/* Live notifications: newest user, upload and subscriber */
$notes = [];
try {
    foreach ($pdo->query("SELECT full_name,created_at FROM users WHERE role='user' ORDER BY created_at DESC LIMIT 2") as $r)
        $notes[] = ['bg-primary', 'bi-person-plus-fill', 'New user registered: <strong>' . h($r['full_name']) . '</strong>', $r['created_at'], 'admin_users.php'];
    foreach ($pdo->query("SELECT title,created_at FROM media ORDER BY created_at DESC LIMIT 2") as $r)
        $notes[] = ['bg-success', 'bi-music-note-beamed', 'New upload: <strong>' . h($r['title']) . '</strong>', $r['created_at'], 'media.php'];
    foreach ($pdo->query("SELECT email,created_at FROM subscribers ORDER BY created_at DESC LIMIT 1") as $r)
        $notes[] = ['bg-warning text-dark', 'bi-envelope-paper-fill', 'New subscriber: <strong>' . h($r['email']) . '</strong>', $r['created_at'], 'admin_subscribers.php'];
    usort($notes, fn($a, $b) => strcmp($b[3], $a[3]));
    $notes = array_slice($notes, 0, 4);
} catch (Throwable $e) { $notes = []; }

function nav_link($file, $icon, $label, $cur, $extra = []) {
    $on = ($cur === $file) || in_array($cur, $extra, true);
    echo '<li class="sidebar-menu-item"><a href="' . $file . '" class="sidebar-menu-link' . ($on ? ' active' : '') . '" title="' . h($label) . '"><i class="bi ' . $icon . '"></i><span>' . h($label) . '</span></a></li>';
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= h($pageTitle) ?> | Sound Web Admin</title>
  <meta name="description" content="Sound Web admin panel powered by Spark Admin">
  <link rel="icon" type="image/png" href="<?= BASE ?>/assets/images/favicon.ico">
  <link rel="stylesheet" href="<?= BASE ?>/assets/libs/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?= BASE ?>/assets/libs/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" href="<?= BASE ?>/assets/libs/apexcharts/apexcharts.css">
  <link rel="stylesheet" href="<?= BASE ?>/assets/libs/flatpickr/flatpickr.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" media="print" onload="this.media='all'">
  <link rel="stylesheet" href="<?= BASE ?>/assets/css/main.css?v=<?= @filemtime(__DIR__ . '/../assets/css/main.css') ?>">
  <link rel="stylesheet" href="<?= BASE ?>/admin/admin.css?v=<?= @filemtime(__DIR__ . '/admin.css') ?>">
<script>try{document.documentElement.setAttribute("data-theme",localStorage.getItem("sgTheme")||"dark")}catch(e){document.documentElement.setAttribute("data-theme","dark")}</script>
</head>
<body>

  <div class="sidebar-wrapper" id="sidebar">
    <a href="index.php" class="sidebar-brand">
      <img src="<?= BASE ?>/assets/images/soundweb-logo-sm.png" alt="" class="sw-logo"><span>Sound Web</span>
    </a>

    <div class="flex-grow-1 overflow-y-auto">
      <div class="sidebar-menu-section">
        <div class="sidebar-menu-title">Overview</div>
        <ul class="sidebar-menu-list">
          <?php nav_link('index.php', 'bi-grid-1x2-fill', 'Dashboard', $cur); ?>
        </ul>
      </div>
      <div class="sidebar-menu-section">
        <div class="sidebar-menu-title">Music</div>
        <ul class="sidebar-menu-list">
          <?php nav_link('media.php', 'bi-music-note-beamed', 'Songs & Videos', $cur); ?>
          <?php nav_link('media_form.php', 'bi-cloud-arrow-up', 'Upload Media', $cur); ?>
          <?php nav_link('admin_categories.php', 'bi-tags', 'Genres & Categories', $cur); ?>
          <?php nav_link('admin_playlists.php', 'bi-music-note-list', 'Playlists', $cur, ['playlist_form.php']); ?>
        </ul>
      </div>
      <div class="sidebar-menu-section">
        <div class="sidebar-menu-title">Community</div>
        <ul class="sidebar-menu-list">
          <?php nav_link('admin_users.php', 'bi-people', 'Users', $cur); ?>
          <?php nav_link('admin_subscribers.php', 'bi-gem', 'Subscribers', $cur); ?>
          <?php nav_link('admin_requests.php', 'bi-send', 'Song Requests', $cur); ?>
        </ul>
      </div>
      <div class="sidebar-menu-section">
        <div class="sidebar-menu-title">System</div>
        <ul class="sidebar-menu-list">
          <li class="sidebar-menu-item"><a href="../user/index.php" class="sidebar-menu-link" title="View Website"><i class="bi bi-globe2"></i><span>View Website</span></a></li>
          <li class="sidebar-menu-item"><a href="logout.php" class="sidebar-menu-link" title="Logout"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a></li>
        </ul>
      </div>
    </div>

    <div class="sidebar-profile">
      
      <?= mx_avatar($me, 'lg', true) ?>
      <div class="sidebar-profile-info">
        <div class="sidebar-profile-name"><?= h($meName) ?></div>
        <div class="sidebar-profile-email">Administrator</div>
      </div>
    </div>
  </div>

  <div class="main-wrapper">

    <header class="navbar-custom">
      <div class="navbar-left">
        <button class="btn-desktop-toggle d-none d-xl-flex align-items-center justify-content-center me-3" id="desktop-sidebar-toggle" aria-label="Minimize Sidebar">
          <i class="bi bi-chevron-bar-left"></i>
        </button>
        <button class="sidebar-toggle-btn me-2" id="sidebar-toggle" aria-label="Toggle Navigation">
          <i class="bi bi-list"></i>
        </button>

        <div class="dropdown ms-2">
          <button class="btn-quick-action dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="quick-actions-dropdown">
            <i class="bi bi-plus-lg"></i>
            <span>Create</span>
          </button>
          <ul class="dropdown-menu dropdown-menu-quick-action" aria-labelledby="quick-actions-dropdown">
            <li class="dropdown-header">Quick Action Shortcuts</li>
            <li><a class="dropdown-item" href="media_form.php?type=music"><i class="bi bi-music-note-beamed"></i> New Song</a></li>
            <li><a class="dropdown-item" href="media_form.php?type=video"><i class="bi bi-camera-video"></i> New Video</a></li>
            <li><a class="dropdown-item" href="playlist_form.php"><i class="bi bi-music-note-list"></i> New Playlist</a></li>
            <li><a class="dropdown-item" href="admin_categories.php"><i class="bi bi-tags"></i> New Category</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="../user/index.php"><i class="bi bi-globe2"></i> View Website</a></li>
          </ul>
        </div>
      </div>

      <form class="navbar-search-wrapper" action="media.php" method="get" role="search">
        <input type="text" class="navbar-search-input" name="q" placeholder="Search songs, videos, artists..." id="main-search" value="<?= h($_GET['q'] ?? '') ?>">
        <button class="navbar-search-btn" type="submit" aria-label="Search"><i class="bi bi-search"></i></button>
      </form>

      <div class="navbar-actions">
        <span class="mx-date d-none d-lg-inline-flex"><i class="bi bi-calendar3"></i><?= date('D, M j, Y') ?></span>
        <button class="navbar-action-btn me-1" aria-label="Toggle dark / light mode" id="btn-theme" title="Dark / Light mode">
          <i class="bi bi-moon-stars-fill"></i>
        </button>
        <button class="navbar-action-btn me-1" aria-label="Toggle Fullscreen" id="btn-fullscreen">
          <i class="bi bi-arrows-fullscreen"></i>
        </button>
        <div class="dropdown">
          <button class="navbar-action-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="btn-notifications" data-bs-auto-close="outside">
            <i class="bi bi-bell"></i>
            <?php if ($notes): ?><span class="navbar-action-badge"></span><?php endif; ?>
          </button>
          <div class="dropdown-menu dropdown-menu-end dropdown-menu-notification p-0" aria-labelledby="btn-notifications">
            <div class="notification-header">
              <h6 class="notification-title">Notifications</h6>
            </div>
            <div class="notification-list">
              <?php foreach ($notes as $n): ?>
              <a href="<?= h($n[4]) ?>" class="notification-item">
                <div class="notification-icon <?= $n[0] ?>"><i class="bi <?= $n[1] ?>"></i></div>
                <div class="notification-content">
                  <p class="notification-text"><?= $n[2] ?></p>
                  <span class="notification-time"><?= h(ago($n[3])) ?></span>
                </div>
              </a>
              <?php endforeach; ?>
              <?php if (!$notes): ?><div class="p-3 text-center text-muted-green">Nothing new yet.</div><?php endif; ?>
            </div>
            <a href="media.php" class="notification-footer">Open Library</a>
          </div>
        </div>

        <div class="dropdown ms-2">
          <button class="navbar-profile-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="profile-dropdown">
            
            <?= mx_avatar($me, 'md') ?>
            <span class="navbar-profile-name d-none d-md-inline"><?= h($meName) ?></span>
            <i class="bi bi-chevron-down navbar-profile-caret"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-profile" aria-labelledby="profile-dropdown">
            <li class="dropdown-header">Welcome !</li>
            <li><a class="dropdown-item" href="admin_users.php"><i class="bi bi-people"></i> Manage Users</a></li>
            <li><a class="dropdown-item" href="../user/index.php"><i class="bi bi-globe2"></i> View Website</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
          </ul>
        </div>
      </div>
    </header>

    <div class="page-header">
      <div>
        <h1 class="page-title"><?= h($pageTitle) ?></h1>
        <?php if ($pageSub): ?><p class="page-subtitle"><?= h($pageSub) ?></p><?php endif; ?>
      </div>
      <?php if ($cur !== 'index.php'): ?>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted-green">Home</a></li>
          <li class="breadcrumb-item text-muted-green"><?= h($crumb) ?></li>
          <li class="breadcrumb-item active text-main" aria-current="page"><?= h($pageTitle) ?></li>
        </ol>
      </nav>
      <?php endif; ?>
      <?= $headerExtra ?? '' ?>
    </div>

    <?php if ($flash): $ok = ($flash[0] === 'success'); ?>
    <div class="alert-custom <?= $ok ? 'alert-custom-success' : 'alert-custom-danger' ?> mb-4" id="flash">
      <i class="bi <?= $ok ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?> alert-custom-icon"></i>
      <div class="alert-custom-content"><?= h($flash[1]) ?></div>
      <button class="alert-custom-close" type="button" aria-label="Close" onclick="this.parentElement.remove();"><i class="bi bi-x-lg"></i></button>
    </div>
    <?php endif; ?>
