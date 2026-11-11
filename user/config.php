<?php
// User panel bootstrap: everything lives in the shared ../config.php
require __DIR__ . '/../config.php';

// The user panel opens only after Login / Sign Up (started from the landing page).
$__page = basename($_SERVER['SCRIPT_NAME']);
if (!in_array($__page, ['login.php', 'register.php', 'logout.php', 'api.php', 'welcome.php', 'faq.php', 'privacy.php', 'terms.php', 'sitemap.php'], true) && !logged_in()) {
    header('Location: login.php');
    exit;
}
require __DIR__ . '/partials.php';
require __DIR__ . '/avatar_helper.php';
