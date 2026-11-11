<?php
/* Reusable HTML pieces for the user panel (cards, rows, icons). */

/* Ids of the current user's playlist (for the "added" state of the + buttons) */
$MY_PL = [];
if (logged_in()) {
    $st = $pdo->prepare('SELECT media_id FROM playlist_items WHERE user_id=?');
    $st->execute([$_SESSION['user']['id']]);
    $MY_PL = array_map('intval', array_column($st->fetchAll(), 'media_id'));
}

function media_src(string $p): string {          // make sure the path works from /user pages
    $p = trim($p);
    if ($p === '' || preg_match('#^(https?:)?//|^/|^\.\./#i', $p)) return $p;
    return '../' . ltrim($p, './');
}
function track_attrs(array $m): string {
    $m['file_path'] = media_src((string)$m['file_path']);
    if (preg_match('/\.(mp3|wav|ogg|m4a)$/i', $m['file_path'])) $m['type'] = 'music';
    return 'data-id="' . (int)$m['id'] . '" data-type="' . h($m['type']) . '" data-title="' . h($m['title']) .
        '" data-artist="' . h($m['artist'] ?: 'Unknown artist') . '" data-src="' . h($m['file_path']) .
        '" data-cover="' . h($m['cover_image'] ?? '') . '" data-dur="' . (int)($m['duration'] ?? 0) . '"';
}

function thumb_inner(array $m): string {
    if (!empty($m['cover_image'])) {
        return '<img src="' . h($m['cover_image']) . '" alt="' . h($m['title']) . '" loading="lazy">';
    }
    if ($m['type'] === 'video') {   // no artwork uploaded: show the first frame of the video itself
        return '<video src="' . h($m['file_path']) . '#t=1" preload="metadata" muted playsinline tabindex="-1"></video>';
    }
    return '<span class="ph"><i class="bi bi-music-note-beamed"></i></span>';
}

/* Set to true to show the "+" (add to playlist) buttons again. */
$SHOW_PLUS = false;

function pl_button(array $m): string {
    global $MY_PL, $SHOW_PLUS;
    if (!$SHOW_PLUS) return '';
    $on = in_array((int)$m['id'], $MY_PL, true);
    return '<button type="button" class="pl-btn js-pl' . ($on ? ' on' : '') . '" data-id="' . (int)$m['id'] .
        '" title="' . ($on ? 'Remove from my playlist' : 'Add to my playlist') . '" aria-label="Toggle playlist">' .
        '<i class="bi ' . ($on ? 'bi-check-lg' : 'bi-plus-lg') . '"></i></button>';
}

function card_meta(array $m): string {
    $dur = fmt_duration($m['duration'] ?? 0);
    $tag = $m['genre'] ?? '';
    $tag = $tag !== '' ? $tag : ($m['type'] === 'video' ? 'Video' : 'Song');
    return '<div class="m-meta"><span><i class="bi bi-' . ($m['type'] === 'video' ? 'camera-video' : 'music-note-beamed') . '"></i> ' . h($tag) . '</span>' . ($dur ? '<span>' . $dur . '</span>' : '') . '</div>';
}

function plays_badge(array $m): string {
    $n = (int)($m['plays'] ?? 0);
    return $n > 0 ? '<span class="m-badge"><i class="bi bi-headphones"></i> ' . ($n >= 1000 ? round($n / 1000, 1) . 'K' : $n) . '</span>' : '';
}

function music_card(array $m): string {
    return '<div class="col"><article class="m-card">' .
        '<div class="m-thumb"><a class="stretch" href="detail.php?id=' . (int)$m['id'] . '" aria-label="' . h($m['title']) . '"></a>' .
        thumb_inner($m) . plays_badge($m) . pl_button($m) .
        '<span class="m-eq" aria-hidden="true"><i></i><i></i><i></i><i></i></span>' .
        '<button type="button" class="play-fab js-play" ' . track_attrs($m) . ' aria-label="Play ' . h($m['title']) . '"><i class="bi bi-play-fill"></i></button></div>' .
        '<div class="m-body"><a class="m-title" href="detail.php?id=' . (int)$m['id'] . '">' . h($m['title']) . '</a>' .
        '<div class="m-sub">' . h($m['artist'] ?: 'Unknown artist') . '</div>' . card_meta($m) . '</div></article></div>';
}

function video_card(array $m): string {
    $dur = fmt_duration($m['duration'] ?? 0);
    return '<div class="col"><article class="m-card v-card">' .
        '<div class="m-thumb"><a class="stretch" href="detail.php?id=' . (int)$m['id'] . '" aria-label="Watch ' . h($m['title']) . '"></a>' .
        thumb_inner($m) . plays_badge($m) . pl_button($m) . '<span class="v-play"><i class="bi bi-play-fill"></i></span>' .
        ($dur ? '<span class="dur-badge">' . $dur . '</span>' : '') . '</div>' .
        '<div class="m-body"><a class="m-title" href="detail.php?id=' . (int)$m['id'] . '">' . h($m['title']) . '</a>' .
        '<div class="m-sub">' . h($m['artist'] ?: 'Unknown artist') . '</div>' . card_meta($m) . '</div></article></div>';
}

function media_card(array $m): string {
    if (preg_match('/\.(mp3|wav|ogg|m4a)$/i', (string)$m['file_path'])) $m['type'] = 'music';
    return $m['type'] === 'video' ? video_card($m) : music_card($m);
}

function album_card(array $a): string {
    $href = 'browse.php?type=music&album=' . urlencode($a['album']);
    $img = !empty($a['cover_image'])
        ? '<img src="' . h($a['cover_image']) . '" alt="' . h($a['album']) . '" loading="lazy">'
        : '<span class="ph"><i class="bi bi-disc-fill"></i></span>';
    return '<div class="col"><article class="m-card"><div class="m-thumb"><a class="stretch" href="' . h($href) . '" aria-label="' . h($a['album']) . '"></a>' . $img . '</div>' .
        '<a class="m-title" href="' . h($href) . '">' . h($a['album']) . '</a>' .
        '<div class="m-sub">' . h($a['artist'] ?: 'Various Artists') . '</div>' .
        '<div class="m-dur">' . plural((int)$a['songs'], 'Song') . '</div></article></div>';
}

function artist_card(array $a): string {
    $href = 'browse.php?artist=' . urlencode($a['artist']);
    $img = !empty($a['cover_image'])
        ? '<img src="' . h($a['cover_image']) . '" alt="' . h($a['artist']) . '" loading="lazy">'
        : '<span class="ph"><i class="bi bi-person-fill"></i></span>';
    return '<div class="col"><article class="m-card artist"><div class="m-thumb round"><a class="stretch" href="' . h($href) . '" aria-label="' . h($a['artist']) . '"></a>' . $img . '</div>' .
        '<a class="m-title text-center" href="' . h($href) . '">' . h($a['artist']) . '</a>' .
        '<div class="m-sub text-center">' . plural((int)$a['total'], 'item') . '</div></article></div>';
}

/* Icon for a category chip, chosen from its name */
function cat_icon(string $name): string {
    $n = strtolower($name);
    $map = ['pop' => 'music-note-beamed', 'rock' => 'lightning-charge-fill', 'hip' => 'mic-fill', 'rap' => 'mic-fill',
        'edm' => 'soundwave', 'electro' => 'soundwave', 'dance' => 'soundwave', 'classic' => 'music-note', 'punjabi' => 'heart-fill',
        'sad' => 'emoji-frown', 'love' => 'heart-fill', 'romantic' => 'heart-fill', 'qawwali' => 'stars', 'ghazal' => 'stars',
        'bollywood' => 'film', 'lofi' => 'headphones', 'lo-fi' => 'headphones', 'folk' => 'vinyl-fill', 'devotional' => 'stars', 'naat' => 'stars'];
    foreach ($map as $k => $icon) if (strpos($n, $k) !== false) return $icon;
    return 'vinyl-fill';
}

/* Render one row of "My Playlist" (also used by api.php for live updates) */
function playlist_row_data(array $m): array {
    return ['id' => (int)$m['id'], 'type' => $m['type'], 'title' => $m['title'], 'artist' => $m['artist'] ?: 'Unknown artist',
        'src' => $m['file_path'], 'cover' => $m['cover_image'] ?? '', 'dur' => (int)($m['duration'] ?? 0),
        'durText' => fmt_duration($m['duration'] ?? 0)];
}

function my_playlist(PDO $pdo, int $limit = 0): array {
    if (!logged_in()) return [];
    $sql = 'SELECT m.* FROM playlist_items p JOIN media m ON m.id=p.media_id WHERE p.user_id=? ORDER BY p.added_at DESC, m.id DESC';
    if ($limit) $sql .= ' LIMIT ' . (int)$limit;
    $st = $pdo->prepare($sql);
    $st->execute([$_SESSION['user']['id']]);
    return $st->fetchAll();
}

/* Brand logo: the Sound Web round logo image + the name. $uid is kept only so old calls still work. */
function sx_logo(string $uid, string $name): string {
    return '<img class="logo-img" src="' . h(BASE) . '/assets/images/soundweb-logo-sm.png" alt="" width="46" height="46">' .
           '<span class="brand-text">' . h(strtoupper($name)) . '</span>';
}

/* ---------- Playlists made by the admin ---------- */
function site_playlists(PDO $pdo, int $limit = 0): array {
    $sql = "SELECT p.*, COUNT(pm.media_id) items,
              (SELECT m2.cover_image FROM playlist_media pm2 JOIN media m2 ON m2.id = pm2.media_id
                WHERE pm2.playlist_id = p.id AND m2.cover_image <> '' ORDER BY pm2.position LIMIT 1) first_cover
            FROM playlists p LEFT JOIN playlist_media pm ON pm.playlist_id = p.id
            GROUP BY p.id, p.name, p.description, p.cover_image, p.created_at
            ORDER BY p.created_at DESC, p.id DESC";
    if ($limit) $sql .= ' LIMIT ' . (int)$limit;
    try { return $pdo->query($sql)->fetchAll(); } catch (Throwable $e) { return []; }
}

function site_playlist_items(PDO $pdo, int $id): array {
    $st = $pdo->prepare('SELECT m.* FROM playlist_media pm JOIN media m ON m.id = pm.media_id WHERE pm.playlist_id = ? ORDER BY pm.position, m.id');
    $st->execute([$id]);
    return $st->fetchAll();
}

function playlist_card(array $p): string {
    $href = 'playlists.php?id=' . (int)$p['id'];
    $cover = $p['cover_image'] ?: ($p['first_cover'] ?? '');
    $img = $cover
        ? '<img src="' . h($cover) . '" alt="' . h($p['name']) . '" loading="lazy">'
        : '<span class="ph"><i class="bi bi-music-note-list"></i></span>';
    return '<div class="col"><article class="m-card"><div class="m-thumb"><a class="stretch" href="' . h($href) . '" aria-label="' . h($p['name']) . '"></a>' . $img . '</div>' .
        '<a class="m-title" href="' . h($href) . '">' . h($p['name']) . '</a>' .
        '<div class="m-sub">' . h(plural((int)$p['items'], 'item')) . '</div></article></div>';
}

/* ---------- Helpers for the extra pages (trending, radio, languages ...) ---------- */
function p2_head(string $kicker, string $title, string $sub = '', string $right = ''): void {
    echo '<div class="page-head br-head p2-head"><div><span class="kicker">' . h($kicker) . '</span><h1>' . h($title) . '</h1>' .
         ($sub !== '' ? '<p class="p2-sub">' . h($sub) . '</p>' : '') . '</div>' . $right . '</div>';
}
function p2_grid(array $items): void {
    echo '<div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-xl-6 g-3" data-queue>';
    foreach ($items as $m) echo media_card($m);
    echo '</div>';
}
function p2_empty(string $icon, string $title, string $text): void {
    echo '<div class="empty"><i class="bi bi-' . h($icon) . '"></i><h3>' . h($title) . '</h3><p>' . h($text) . '</p></div>';
}
function p2_tile(string $href, string $icon, string $name, string $sub, int $i, bool $on = false): string {
    return '<a class="ge-tile t' . ($i % 7) . ' c' . ($i % 6) . ($on ? ' p2-on' : '') . '" href="' . h($href) . '"><i class="bi bi-' . h($icon) . '"></i>' .
           '<span class="ge-name">' . h($name) . '</span><small>' . h($sub) . '</small></a>';
}
