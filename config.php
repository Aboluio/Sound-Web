<?php
/**
 * Sound Web — shared configuration (used by both /user and /admin panels).
 * Edit the DATABASE and SITE INFO sections below if needed.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_name('soundgroup');
    session_start();
}

/* ---------- DATABASE (XAMPP defaults) ---------- */
$DB_HOST = 'localhost';
$DB_NAME = 'songvideo';
$DB_USER = 'root';
$DB_PASS = '';

/* ---------- SITE INFO (shown in the footer) ---------- */
$SITE = [
    'name'    => 'Sound Web',
    'tagline' => 'Song and Video.',
    'logo'    => 'assets/images/soundweb-logo.png',
    'email'   => 'hello@soundweb.com',   // <-- change to your real email
    'phone'   => '+92 300 0000000',        // <-- change to your real number
];

$dsn = getenv('SG_DB_DSN') ?: "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";
$PDO_OPTS = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC];

/* Friendly error page instead of a bare text line */
function sg_db_error($title, $lines) {
    http_response_code(500);
    echo '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Sound Web setup</title>'
       . '<body style="margin:0;min-height:100vh;display:grid;place-items:center;background:#0a0908;color:#f6f0e4;font-family:system-ui,sans-serif;padding:20px">'
       . '<div style="max-width:560px;padding:32px;border-radius:24px;background:#14110d;border:1px solid rgba(233,201,125,.25)">'
       . '<h1 style="margin:0 0 10px;font-size:24px;color:#e9c97d">' . $title . '</h1><ol style="line-height:1.8;color:#c9c0ad;padding-left:20px">';
    foreach ($lines as $l) echo '<li>' . $l . '</li>';
    echo '</ol><p style="color:#8c8472;font-size:13px">After fixing, refresh this page. Diagnostics: <a style="color:#e9c97d" href="' . htmlspecialchars(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/x/y')) . '/check.php') . '">check.php</a></p></div></body>';
    exit;
}

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $PDO_OPTS);
} catch (PDOException $e) {
    $code = (int)$e->getCode();
    if ($code === 1049 || stripos($e->getMessage(), 'Unknown database') !== false) {
        /* database does not exist yet: create it and import database.sql automatically */
        try {
            $boot = new PDO("mysql:host=$DB_HOST;charset=utf8mb4", $DB_USER, $DB_PASS, $PDO_OPTS);
            $boot->exec("CREATE DATABASE IF NOT EXISTS `$DB_NAME` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $boot->exec("USE `$DB_NAME`");
            $sql = @file_get_contents(__DIR__ . '/songvideo.sql');
            if ($sql !== false) {
                $sql = preg_replace('/^\s*--.*$/m', '', $sql);
                foreach (explode(";\n", $sql) as $stmt) {
                    $stmt = trim($stmt);
                    if ($stmt === '' || stripos($stmt, 'CREATE DATABASE') === 0 || stripos($stmt, 'USE ') === 0) continue;
                    $boot->exec($stmt);
                }
            }
            $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $PDO_OPTS);
        } catch (PDOException $e2) {
            sg_db_error('Could not create the database', ['Open phpMyAdmin and import <b>songvideo.sql</b> manually.', 'Check the database login at the top of <b>config.php</b>.', 'Error: ' . htmlspecialchars($e2->getMessage())]);
        }
    } else {
        sg_db_error('Database is not reachable', ['Open the <b>XAMPP Control Panel</b> and click <b>Start</b> on both <b>Apache</b> and <b>MySQL</b>.', 'Make sure the folder is <b>C:\xampp\htdocs\syedsoundbar</b> (not a folder inside a folder).', 'If MySQL has a password, set it in <b>config.php</b> ($DB_PASS).', 'Error: ' . htmlspecialchars($e->getMessage())]);
    }
}

/* Web path of the project root, e.g. "" or "/syedsoundbar" (scripts live one level deep). */
define('BASE', rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/x/y'))), '/'));

/* ---------- Small helpers ---------- */
function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function logged_in() { return !empty($_SESSION['user']); }
function is_admin() { return logged_in() && ($_SESSION['user']['role'] ?? '') === 'admin'; }
function require_login() { if (!logged_in()) { header('Location: ' . BASE . '/user/login.php'); exit; } }
function require_admin() {
    if (!logged_in()) { header('Location: ' . BASE . '/admin/login.php'); exit; }
    if (!is_admin()) { http_response_code(403); exit('Admin access required.'); }
}
function flash($type, $msg) { $_SESSION['flash'] = [$type, $msg]; }
function get_flash() { $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f; }

function csrf_token() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_field() { return '<input type="hidden" name="csrf" value="' . h(csrf_token()) . '">'; }
function csrf_check() {
    $t = $_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!$t || !hash_equals($_SESSION['csrf'] ?? '', $t)) {
        http_response_code(419);
        exit('Security token expired. Go back, refresh the page and try again.');
    }
}

/* mm:ss from seconds */
function fmt_duration($sec) {
    $sec = (int)$sec;
    if ($sec <= 0) return '';
    return floor($sec / 60) . ':' . str_pad((string)($sec % 60), 2, '0', STR_PAD_LEFT);
}
function plural($n, $one, $many = null) { return $n . ' ' . ($n == 1 ? $one : ($many ?? $one . 's')); }

/* ---------- Automatic upgrade of older databases ---------- */
function ensure_schema(PDO $pdo) {
    if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'mysql' || !empty($_SESSION['schema_ok4'])) return;
    try {
        $has = function ($col) use ($pdo) { return (bool)$pdo->query("SHOW COLUMNS FROM media LIKE '$col'")->fetch(); };
        if (!$has('duration')) $pdo->exec("ALTER TABLE media ADD duration INT UNSIGNED NOT NULL DEFAULT 0");
        if (!$has('plays'))    $pdo->exec("ALTER TABLE media ADD plays INT UNSIGNED NOT NULL DEFAULT 0");
        $pdo->exec("CREATE TABLE IF NOT EXISTS playlist_items (
            user_id INT UNSIGNED NOT NULL, media_id INT UNSIGNED NOT NULL,
            added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (user_id, media_id)) ENGINE=InnoDB");
        $pdo->exec("CREATE TABLE IF NOT EXISTS subscribers (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, email VARCHAR(190) NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB");
        /* admin-made playlists (curated by the administrator, shown on the website) */
        $pdo->exec("CREATE TABLE IF NOT EXISTS playlists (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, name VARCHAR(150) NOT NULL,
            description TEXT NULL, cover_image VARCHAR(255) NOT NULL DEFAULT '',
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->exec("CREATE TABLE IF NOT EXISTS playlist_media (
            playlist_id INT UNSIGNED NOT NULL, media_id INT UNSIGNED NOT NULL, position INT UNSIGNED NOT NULL DEFAULT 0,
            PRIMARY KEY (playlist_id, media_id),
            CONSTRAINT fk_plm_pl FOREIGN KEY (playlist_id) REFERENCES playlists(id) ON DELETE CASCADE,
            CONSTRAINT fk_plm_media FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        /* recently played (per user) and song requests */
        $pdo->exec("CREATE TABLE IF NOT EXISTS play_history (
            user_id INT UNSIGNED NOT NULL, media_id INT UNSIGNED NOT NULL,
            played_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (user_id, media_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->exec("CREATE TABLE IF NOT EXISTS song_requests (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id INT UNSIGNED NOT NULL, song_title VARCHAR(190) NOT NULL,
            artist VARCHAR(150) NOT NULL DEFAULT '', message TEXT NULL, status ENUM('pending','added') NOT NULL DEFAULT 'pending',
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        /* repair: audio files that were saved as "video" would never play in the player */
        $pdo->exec("UPDATE media SET type='music' WHERE type<>'music' AND LOWER(file_path) REGEXP '\\\\.(mp3|wav|ogg|m4a)\$'");
        $_SESSION['schema_ok4'] = 1;
    } catch (PDOException $e) { /* keep the site usable; import database.sql manually if this fails */ }
}
ensure_schema($pdo);
