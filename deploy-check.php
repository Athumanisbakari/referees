<?php
/**
 * Deployment health check
 * Delete this file after your site is working.
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

header('Content-Type: text/html; charset=utf-8');

$checks = [];

function addCheck(array &$checks, string $label, bool $passed, string $detail = ''): void
{
    $checks[] = [
        'label' => $label,
        'passed' => $passed,
        'detail' => $detail,
    ];
}

addCheck($checks, 'PHP version 8.0+', version_compare(PHP_VERSION, '8.0.0', '>='), PHP_VERSION);
addCheck($checks, 'PDO extension', extension_loaded('pdo'), extension_loaded('pdo') ? 'Loaded' : 'Missing');
addCheck($checks, 'PDO MySQL extension', extension_loaded('pdo_mysql'), extension_loaded('pdo_mysql') ? 'Loaded' : 'Missing');
addCheck($checks, 'Fileinfo extension', extension_loaded('fileinfo'), extension_loaded('fileinfo') ? 'Loaded' : 'Missing');
addCheck($checks, 'Config local.php', file_exists(__DIR__ . '/config/local.php'), file_exists(__DIR__ . '/config/local.php') ? 'Production overrides found' : 'Using localhost defaults');
addCheck($checks, 'HTTPS recommended', str_starts_with(APP_URL, 'https://'), APP_URL);
addCheck($checks, 'Uploads folder writable', is_writable(UPLOAD_PATH), UPLOAD_PATH);
addCheck($checks, 'Video folder writable', is_writable(VIDEO_PATH), VIDEO_PATH);
addCheck($checks, 'Vendor assets present', file_exists(__DIR__ . '/assets/vendor/bootstrap/css/bootstrap.min.css'), 'Required for offline UI');

try {
    $pdo = getDBConnection();
    $pdo->query('SELECT 1');
    addCheck($checks, 'Database connection', true, DB_HOST . ' / ' . DB_NAME);

    $adminCount = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    addCheck($checks, 'Database tables imported', $adminCount > 0, $adminCount . ' user record(s) found');
} catch (Throwable $e) {
    addCheck($checks, 'Database connection', false, $e->getMessage());
    addCheck($checks, 'Database tables imported', false, 'Import sql/schema.sql in phpMyAdmin');
}

$passedCount = count(array_filter($checks, fn ($check) => $check['passed']));
$totalCount = count($checks);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deploy Check - <?= htmlspecialchars(APP_SHORT_NAME) ?></title>
    <style>
        body { font-family: Segoe UI, sans-serif; background: #f4f6f9; margin: 0; padding: 1.5rem; }
        .card { max-width: 720px; margin: 0 auto; background: #fff; border-radius: 12px; padding: 1.5rem; box-shadow: 0 2px 12px rgba(0,0,0,.08); }
        h1 { margin-top: 0; color: #1a5276; }
        .check { display: flex; justify-content: space-between; gap: 1rem; padding: .75rem 0; border-bottom: 1px solid #eee; }
        .ok { color: #198754; font-weight: 700; }
        .fail { color: #dc3545; font-weight: 700; }
        .detail { color: #666; font-size: .9rem; }
        .summary { margin: 1rem 0; padding: 1rem; border-radius: 8px; background: #eef5fb; }
        .warning { margin-top: 1rem; padding: 1rem; border-radius: 8px; background: #fff3cd; }
        a { color: #1a5276; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Deployment Check</h1>
        <div class="summary">
            <strong><?= $passedCount ?>/<?= $totalCount ?></strong> checks passed.
        </div>

        <?php foreach ($checks as $check): ?>
            <div class="check">
                <div>
                    <div><?= htmlspecialchars($check['label']) ?></div>
                    <?php if ($check['detail']): ?>
                        <div class="detail"><?= htmlspecialchars($check['detail']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="<?= $check['passed'] ? 'ok' : 'fail' ?>">
                    <?= $check['passed'] ? 'OK' : 'FAIL' ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="warning">
            Delete <code>deploy-check.php</code> after deployment is complete.
            Then open <a href="<?= htmlspecialchars(APP_URL) ?>/login.php">login page</a>.
        </div>
    </div>
</body>
</html>
