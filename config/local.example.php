<?php
/**
 * Production / hosting overrides
 *
 * 1. Copy this file to config/local.php
 * 2. Fill in your hosting details
 * 3. Never commit config/local.php to public repositories
 */

// Public site URL (must use https:// on live hosting for PWA + GPS)
define('APP_URL', 'https://yoursite.infinityfreeapp.com');

// MySQL credentials from your hosting control panel
define('DB_HOST', 'sqlXXX.infinityfree.com');
define('DB_NAME', 'if0_XXXXXX_ilala_referees');
define('DB_USER', 'if0_XXXXXX');
define('DB_PASS', 'your_database_password');

// Free hosts usually limit uploads to about 10-64 MB
define('MAX_VIDEO_SIZE', 32 * 1024 * 1024);
define('MAX_DOCUMENT_SIZE', 8 * 1024 * 1024);
