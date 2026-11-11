<?php
require 'config.php';

/* ---- numbers from the website's database ---- */
$q = fn($sql) => (int)$pdo->query($sql)->fetchColumn();
$total  = $q("SELECT COUNT(*) FROM media");
$music  = $q("SELECT COUNT(*) FROM media WHERE type='music'");
$videos = $q("SELECT COUNT(*) FROM media WHERE type='video'");
$users  = $q("SELECT COUNT(*) FROM users WHERE role='user'");
$plays  = $q("SELECT COALESCE(SUM(plays),0) FROM media");
$cats   = $q("SELECT COUNT(*) FROM categories");
$subs   = $q("SELECT COUNT(*) FROM subscribers");
$lists  = $q("SELECT COUNT(*) FROM playlist_items");
$revs   = $q("SELECT COUNT(*) FROM reviews");
$albums  = $q("SELECT COUNT(DISTINCT album) FROM media WHERE album IS NOT NULL AND album<>''");
$artists = $q("SELECT COUNT(DISTINCT artist) FROM media WHERE artist IS NOT NULL AND artist<>''");
$pending = $q("SELECT COUNT(*) FROM song_requests WHERE status='pending'");

/* per-day series for the last 12 days (running totals for the sparklines) */
$days = [];
for ($i = 11; $i >= 0; $i--) $days[date('Y-m-d', strtotime("-$i day"))] = ['m' => 0, 'u' => 0];
$from = array_key_first($days);
$baseMedia = $q("SELECT COUNT(*) FROM media WHERE created_at < '$from'");
$baseUsers = $q("SELECT COUNT(*) FROM users WHERE role='user' AND created_at < '$from'");
foreach ($pdo->query("SELECT DATE(created_at) d, COUNT(*) c FROM media WHERE created_at >= '$from' GROUP BY DATE(created_at)") as $r) if (isset($days[$r['d']])) $days[$r['d']]['m'] = (int)$r['c'];
foreach ($pdo->query("SELECT DATE(created_at) d, COUNT(*) c FROM users WHERE role='user' AND created_at >= '$from' GROUP BY DATE(created_at)") as $r) if (isset($days[$r['d']])) $days[$r['d']]['u'] = (int)$r['c'];
$sm = []; $su = []; $rm = $baseMedia; $ru = $baseUsers;
foreach ($days as $d) { $rm += $d['m']; $ru += $d['u']; $sm[] = $rm; $su[] = $ru; }
$weekMedia = $q("SELECT COUNT(*) FROM media WHERE created_at >= '" . date('Y-m-d', strtotime('-7 day')) . "'");
$weekUsers = $q("SELECT COUNT(*) FROM users WHERE role='user' AND created_at >= '" . date('Y-m-d', strtotime('-7 day')) . "'");

/* uploads per month, last 8 months, music vs video */
$labels = []; $mm = []; $vv = []; $idx = [];
for ($i = 7; $i >= 0; $i--) { $k = date('Y-m', strtotime(date('Y-m-01') . " -$i month")); $idx[$k] = count($labels); $labels[] = date('M', strtotime($k . '-01')); $mm[] = 0; $vv[] = 0; }
$since = date('Y-m-01', strtotime('-7 month'));
foreach ($pdo->query("SELECT type, created_at FROM media WHERE created_at >= '$since'") as $r) {
    $k = substr($r['created_at'], 0, 7);
    if (isset($idx[$k])) { if ($r['type'] === 'video') $vv[$idx[$k]]++; else $mm[$idx[$k]]++; }
}

$top    = $pdo->query("SELECT m.*, c.name category_name FROM media m LEFT JOIN categories c ON c.id=m.category_id ORDER BY m.plays DESC, m.created_at DESC LIMIT 5")->fetchAll();
$latest = $pdo->query("SELECT m.*, c.name category_name FROM media m LEFT JOIN categories c ON c.id=m.category_id ORDER BY m.created_at DESC, m.id DESC LIMIT 6")->fetchAll();
$newest = $latest[0] ?? null;

$bars = [
  ['Music tracks', $music], ['Videos', $videos], ['Registered users', $users],
  ['Playlist saves', $lists], ['Reviews', $revs], ['Newsletter subscribers', $subs],
];
$maxBar = max(1, ...array_column($bars, 1));

$pageTitle = 'Dashboard';
$pageSub   = 'A live view of your music and video library, listeners and plays.';
$headerExtra = '<a class="btn-date-picker" href="media_form.php"><i class="bi bi-cloud-arrow-up"></i><span>Add Music / Video</span></a>';
$useCharts = true;
$pageData  = ['growth' => ['labels' => $labels, 'music' => $mm, 'video' => $vv], 'mix' => ['music' => $music, 'video' => $videos], 'spark' => ['media' => $sm, 'users' => $su]];
require 'header.php';
?>
    <div class="row g-4">

      <div class="col-12">
        <div class="row g-4">
          <div class="col-6 col-xl-3">
            <a class="mx-stat" href="media.php?type=music">
              <span class="mx-stat-icon"><i class="bi bi-music-note-beamed"></i></span>
              <span class="mx-stat-label">Total Songs</span>
              <span class="mx-stat-value"><?= number_format($music) ?></span>
              <div class="mx-stat-spark"><div id="income-sparkline"></div></div>
            </a>
          </div>
          <div class="col-6 col-xl-3">
            <a class="mx-stat" href="media.php">
              <span class="mx-stat-icon"><i class="bi bi-disc"></i></span>
              <span class="mx-stat-label">Total Albums</span>
              <span class="mx-stat-value"><?= number_format($albums) ?></span>
              
            </a>
          </div>
          <div class="col-6 col-xl-3">
            <a class="mx-stat" href="media.php">
              <span class="mx-stat-icon"><i class="bi bi-mic"></i></span>
              <span class="mx-stat-label">Total Artists</span>
              <span class="mx-stat-value"><?= number_format($artists) ?></span>
              
            </a>
          </div>
          <div class="col-6 col-xl-3">
            <a class="mx-stat" href="admin_users.php">
              <span class="mx-stat-icon"><i class="bi bi-people"></i></span>
              <span class="mx-stat-label">Total Users</span>
              <span class="mx-stat-value"><?= number_format($users) ?></span>
              <div class="mx-stat-spark"><div id="return-sparkline"></div></div>
            </a>
          </div>
          <div class="col-6 col-xl-3">
            <a class="mx-stat" href="media.php?type=video">
              <span class="mx-stat-icon"><i class="bi bi-camera-video"></i></span>
              <span class="mx-stat-label">Music Videos</span>
              <span class="mx-stat-value"><?= number_format($videos) ?></span>
              
            </a>
          </div>
          <div class="col-6 col-xl-3">
            <a class="mx-stat" href="media.php">
              <span class="mx-stat-icon"><i class="bi bi-play-circle"></i></span>
              <span class="mx-stat-label">Total Plays</span>
              <span class="mx-stat-value"><?= number_format($plays) ?></span>
              
            </a>
          </div>
          <div class="col-6 col-xl-3">
            <a class="mx-stat" href="admin_subscribers.php">
              <span class="mx-stat-icon"><i class="bi bi-gem"></i></span>
              <span class="mx-stat-label">Subscribers</span>
              <span class="mx-stat-value"><?= number_format($subs) ?></span>
              
            </a>
          </div>
          <div class="col-6 col-xl-3">
            <a class="mx-stat" href="admin_requests.php">
              <span class="mx-stat-icon"><i class="bi bi-hourglass-split"></i></span>
              <span class="mx-stat-label">Pending Requests</span>
              <span class="mx-stat-value"><?= number_format($pending) ?></span>
              
            </a>
          </div>
        </div>
      </div>

      <div class="col-xl-9 col-lg-8">
        <div class="row g-4">
          <div class="col-12">
            <div class="card mb-0">
              <div class="card-header mb-2">
                <h2 class="card-title">Library Growth</h2>
                <div class="d-flex gap-3 align-items-center">
                  <div class="chart-legend-item"><span class="legend-dot bg-forest-medium"></span><span class="chart-legend-label">Music</span></div>
                  <div class="chart-legend-item"><span class="legend-dot bg-lime-accent"></span><span class="chart-legend-label">Videos</span></div>
                </div>
              </div>
              <div class="d-flex align-items-baseline gap-2 mb-3">
                <span class="stat-value-amount"><?= number_format($plays) ?></span>
                <span class="trend-badge trend-up fs-xs">total plays across <?= number_format($total) ?> item<?= $total === 1 ? '' : 's' ?></span>
              </div>
              <div id="revenue-chart"></div>
            </div>
          </div>

          <div class="col-md-7 d-flex flex-column">
            <div class="card h-100 flex-grow-1">
              <div class="card-header">
                <h2 class="card-title">Most Played</h2>
                <a class="card-more-btn" href="media.php" aria-label="Open library"><i class="bi bi-three-dots"></i></a>
              </div>
              <div class="transaction-list">
                <?php foreach ($top as $m): ?>
                <div class="transaction-item">
                  <div class="transaction-icon bg-forest-light text-lime"><i class="bi bi-<?= $m['type'] === 'video' ? 'camera-video' : 'music-note-beamed' ?>"></i></div>
                  <div class="transaction-info">
                    <div class="transaction-name"><?= h($m['title']) ?></div>
                    <div class="transaction-date"><?= h($m['artist'] ?: 'Unknown artist') ?><?= $m['category_name'] ? ' &bull; ' . h($m['category_name']) : '' ?></div>
                  </div>
                  <div class="transaction-amount text-success"><?= number_format((int)$m['plays']) ?> plays</div>
                </div>
                <?php endforeach; ?>
                <?php if (!$top): ?><div class="sg-empty"><i class="bi bi-music-note-list"></i>No songs yet.</div><?php endif; ?>
              </div>
            </div>
          </div>

          <div class="col-md-5 d-flex flex-column">
            <div class="card h-100 flex-grow-1">
              <div class="card-header">
                <h2 class="card-title">Website Overview</h2>
                <span class="text-muted-green fs-xs"><?= $cats ?> categories</span>
              </div>
              <?php foreach ($bars as [$label, $val]): $pct = max($val ? 6 : 0, (int)round($val / $maxBar * 100)); ?>
              <div class="progress-container">
                <div class="progress-label-row"><span class="progress-label"><?= h($label) ?></span><span class="progress-value"><?= number_format($val) ?></span></div>
                <div class="progress" role="progressbar" aria-label="<?= h($label) ?>" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100">
                  <div class="progress-bar bg-lime-accent" style="width:<?= $pct ?>%"></div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-lg-4">
        <div class="right-panel-wrapper d-flex flex-column gap-4 h-100">
          <div class="card flex-grow-1 d-flex flex-column justify-content-between mb-0">
            <div class="card-header mb-1"><h2 class="card-title">Content Mix</h2></div>
            <div id="views-chart"></div>
            <div class="chart-legends-container">
              <div class="chart-legend-item"><span class="legend-dot bg-lime-accent"></span><span class="text-muted-green">Music (<?= $music ?>)</span></div>
              <div class="chart-legend-item"><span class="legend-dot bg-forest-medium"></span><span class="text-muted-green">Videos (<?= $videos ?>)</span></div>
            </div>
          </div>

          <div class="promo-banner-card">
            <svg class="promo-banner-bg-shape" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
              <g transform="translate(50,50)">
                <rect x="-6" y="-45" width="12" height="90" rx="6" ry="6" fill="#B4F105" />
                <rect x="-6" y="-45" width="12" height="90" rx="6" ry="6" fill="#B4F105" transform="rotate(60)" />
                <rect x="-6" y="-45" width="12" height="90" rx="6" ry="6" fill="#B4F105" transform="rotate(120)" />
              </g>
            </svg>
            <h3 class="promo-title">Keep your listeners coming back.</h3>
            <p class="promo-desc">Publish a new song or video and it appears on the website straight away.</p>
            <a class="btn-promo text-decoration-none d-inline-block" href="media_form.php" id="btn-promo-action">Upload now</a>
          </div>
        </div>
      </div>

      <div class="col-12">
        <div class="table-card-custom">
          <div class="table-header-control">
            <h2 class="card-title mb-0">Recent Uploads</h2>
            <div class="table-filter-group"><a class="btn-table-action text-decoration-none" href="media.php"><i class="bi bi-collection-play"></i> View all</a></div>
          </div>
          <div class="table-responsive">
            <table class="table-custom">
              <thead><tr><th>Title</th><th>Type</th><th>Category</th><th>Plays</th><th>Added</th><th class="text-center">Actions</th></tr></thead>
              <tbody>
              <?php foreach ($latest as $m): ?>
                <tr>
                  <td><div class="table-user-cell">
                    <span class="sg-thumb"><?php if (!empty($m['cover_image'])): ?><img src="<?= h($m['cover_image']) ?>" alt=""><?php else: ?><i class="bi bi-<?= $m['type'] === 'video' ? 'play-fill' : 'music-note-beamed' ?>"></i><?php endif; ?></span>
                    <div><div class="table-user-name"><?= h($m['title']) ?></div><div class="table-user-sub"><?= h($m['artist'] ?: 'Unknown artist') ?></div></div>
                  </div></td>
                  <td><span class="badge-table <?= $m['type'] === 'video' ? 'video' : 'music' ?>"><?= $m['type'] === 'video' ? 'Video' : 'Music' ?></span></td>
                  <td><?= h($m['category_name'] ?: '—') ?></td>
                  <td class="table-amount"><?= number_format((int)$m['plays']) ?></td>
                  <td><?= h(date('M d, Y', strtotime($m['created_at']))) ?></td>
                  <td><div class="d-flex justify-content-center gap-1">
                    <a href="media_form.php?id=<?= (int)$m['id'] ?>" class="table-btn-action" title="Edit"><i class="bi bi-pencil"></i></a>
                    <form class="inline" method="post" action="delete_media.php" onsubmit="return confirm('Delete this media?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$m['id'] ?>"><button class="table-btn-action delete" title="Delete"><i class="bi bi-trash"></i></button></form>
                  </div></td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$latest): ?><tr><td colspan="6"><div class="sg-empty"><i class="bi bi-cloud-arrow-up"></i>No media uploaded yet. <a href="media_form.php">Add your first song or MP4 video.</a></div></td></tr><?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
<?php require 'footer.php'; ?>
