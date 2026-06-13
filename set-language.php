<?php
require_once __DIR__ . '/includes/functions.php';

$lang = $_GET['lang'] ?? '';
$redirect = $_GET['redirect'] ?? APP_URL . '/dashboard.php';

if (in_array($lang, SUPPORTED_LANGUAGES, true)) {
    $_SESSION['lang'] = $lang;
}

if (!str_starts_with($redirect, APP_URL) && !str_starts_with($redirect, '/')) {
    $redirect = APP_URL . '/dashboard.php';
}

redirect($redirect);
