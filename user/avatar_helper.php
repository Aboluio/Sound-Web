<?php
// Shared avatar helpers (user panel). Required from config.php.
if (!function_exists('avatar_url')) {
    function avatar_dir_fs(): string { return __DIR__ . '/../uploads/avatars/'; }
    function avatar_dir_url(): string { return '../uploads/avatars/'; }

    // URL only if the filename belongs to THIS user id and the file exists; else null.
    function avatar_url(?string $file, int $uid, ?string $dir = null, ?string $url = null): ?string {
        $dir = $dir ?? avatar_dir_fs(); $url = $url ?? avatar_dir_url();
        if (!$file || !preg_match('/^u' . $uid . '_[a-f0-9]{32}\.(jpg|png|webp)$/', $file)) return null;
        $p = $dir . $file;
        return is_file($p) ? $url . $file . '?v=' . filemtime($p) : null;
    }
    function avatar_delete(?string $file, int $uid, ?string $dir = null): void {
        $dir = $dir ?? avatar_dir_fs();
        if ($file && preg_match('/^u' . $uid . '_[a-f0-9]{32}\.(jpg|png|webp)$/', $file) && is_file($dir . $file)) @unlink($dir . $file);
    }
    // Avatar of the logged-in user, read from the DB by session id (null = none).
    function current_avatar_url(PDO $pdo): ?string {
        static $done = false, $res = null;
        if ($done) return $res;
        $done = true;
        $uid = (int)($_SESSION['user']['id'] ?? 0);
        if ($uid <= 0) return null;
        try {
            $q = $pdo->prepare('SELECT avatar FROM users WHERE id=?'); $q->execute([$uid]);
            $res = avatar_url($q->fetchColumn() ?: null, $uid);
        } catch (Throwable $e) { $res = null; }
        return $res;
    }
}
