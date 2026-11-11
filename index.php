<?php
/* Sound Web — entry point. Logged-in users go to their panel, visitors see the welcome page.
   (Welcome, Login, Sign Up and every other page share one navbar and one footer: user/header.php + user/footer.php) */
session_name('soundgroup');
if (session_status() === PHP_SESSION_NONE) session_start();
if (!empty($_SESSION['user'])) {
    header('Location: ' . (($_SESSION['user']['role'] ?? '') === 'admin' ? 'admin/' : 'user/index.php'));
    exit;
}
header('Location: user/welcome.php');
exit;
