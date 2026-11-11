<?php
/* Small JSON endpoint used by the player and playlist buttons. */
require 'config.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function out($data, $code = 200) { http_response_code($code); echo json_encode($data); exit; }
function playlist_json(PDO $pdo) { return array_map('playlist_row_data', my_playlist($pdo)); }

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'playlist') {
    out(['ok' => true, 'items' => playlist_json($pdo)]);
}

/* The player asks which of its remembered songs still exist (deleted songs / missing files are dropped). */
if ($action === 'validate') {
    $ids = array_values(array_unique(array_filter(array_map('intval', explode(',', (string)($_GET['ids'] ?? ''))))));
    $ids = array_slice($ids, 0, 100);
    $items = [];
    if ($ids) {
        $st = $pdo->prepare('SELECT * FROM media WHERE id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')');
        $st->execute($ids);
        foreach ($st->fetchAll() as $m) {
            $m['file_path'] = media_src((string)$m['file_path']);
            if (preg_match('/\.(mp3|wav|ogg|m4a)$/i', $m['file_path'])) $m['type'] = 'music';
            $local = preg_match('#^(https?:)?//#i', $m['file_path']) ? true : is_file(__DIR__ . '/' . $m['file_path']);
            if ($local) $items[] = playlist_row_data($m);
        }
    }
    out(['ok' => true, 'items' => $items]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(['ok' => false, 'error' => 'POST required'], 405);
csrf_check();
$id = (int)($_POST['id'] ?? 0);
$st = $pdo->prepare('SELECT id FROM media WHERE id=?'); $st->execute([$id]);
if (!$st->fetch()) out(['ok' => false, 'error' => 'Not found'], 404);

if ($action === 'play') {
    if (empty($_SESSION['counted'][$id])) {            // count each item once per visit
        $pdo->prepare('UPDATE media SET plays = plays + 1 WHERE id=?')->execute([$id]);
        $_SESSION['counted'][$id] = 1;
    }
    if (logged_in()) {   // Recently Played page
        try { $pdo->prepare('REPLACE INTO play_history(user_id,media_id,played_at) VALUES(?,?,NOW())')->execute([(int)$_SESSION['user']['id'], $id]); } catch (Throwable $e) {}
    }
    out(['ok' => true]);
}

if ($action === 'toggle') {
    if (!logged_in()) out(['ok' => false, 'login' => true], 401);
    $uid = (int)$_SESSION['user']['id'];
    $st = $pdo->prepare('SELECT 1 FROM playlist_items WHERE user_id=? AND media_id=?'); $st->execute([$uid, $id]);
    if ($st->fetch()) { $pdo->prepare('DELETE FROM playlist_items WHERE user_id=? AND media_id=?')->execute([$uid, $id]); $added = false; }
    else { $pdo->prepare('INSERT INTO playlist_items(user_id,media_id) VALUES(?,?)')->execute([$uid, $id]); $added = true; }
    out(['ok' => true, 'added' => $added, 'items' => playlist_json($pdo)]);
}

out(['ok' => false, 'error' => 'Unknown action'], 400);
