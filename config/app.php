<?php
/**
 * Application Configuration - Ilala Smart Referee Management System
 */

if (file_exists(__DIR__ . '/local.php')) {
    require_once __DIR__ . '/local.php';
}

if (!defined('APP_NAME')) {
    define('APP_NAME', 'Ilala Smart Referee Management System');
}
if (!defined('APP_SHORT_NAME')) {
    define('APP_SHORT_NAME', 'Ilala Referees');
}
if (!defined('APP_VERSION')) {
    define('APP_VERSION', '1.0.0');
}
if (!defined('APP_URL')) {
    define('APP_URL', 'http://localhost/referees');
}
if (!defined('APP_TIMEZONE')) {
    define('APP_TIMEZONE', 'Africa/Dar_es_Salaam');
}

define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('VIDEO_PATH', UPLOAD_PATH . 'videos/');
define('DOCUMENT_PATH', UPLOAD_PATH . 'documents/');
define('AVATAR_PATH', UPLOAD_PATH . 'avatars/');

if (!defined('MAX_VIDEO_SIZE')) {
    define('MAX_VIDEO_SIZE', 500 * 1024 * 1024);
}
if (!defined('MAX_DOCUMENT_SIZE')) {
    define('MAX_DOCUMENT_SIZE', 10 * 1024 * 1024);
}

date_default_timezone_set(APP_TIMEZONE);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
