<?php
require_once __DIR__ . '/../../includes/functions.php';
requireLogin();

$pageTitle = 'Notifications';
$pdo = getDBConnection();
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';

    if ($action === 'mark_read') {
        $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?')
            ->execute([(int) $_POST['notification_id'], $userId]);
    } elseif ($action === 'mark_all_read') {
        $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?')
            ->execute([$userId]);
        setFlash('success', 'All notifications marked as read.');
    }
    redirect(APP_URL . '/modules/notifications/index.php');
}

$notifications = $pdo->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50');
$notifications->execute([$userId]);
$notifications = $notifications->fetchAll();

$typeIcons = [
    'Assignment' => 'bi-person-check',
    'Payment'    => 'bi-cash-coin',
    'Training'   => 'bi-mortarboard',
    'License'    => 'bi-card-checklist',
    'Match'      => 'bi-trophy',
    'System'     => 'bi-gear',
];

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h4><i class="bi bi-bell me-2"></i>Notifications</h4>
    <form method="POST" class="d-inline">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="mark_all_read">
        <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-check-all me-1"></i>Mark All Read</button>
    </form>
</div>

<div class="card">
    <div class="list-group list-group-flush">
        <?php if (empty($notifications)): ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-bell-slash fs-1 d-block mb-2"></i>
                No notifications
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $n): ?>
                <div class="list-group-item<?= !$n['is_read'] ? ' bg-light' : '' ?>">
                    <div class="d-flex align-items-start gap-3">
                        <div class="notification-icon">
                            <i class="bi <?= $typeIcons[$n['type']] ?? 'bi-bell' ?> fs-4 text-primary"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between">
                                <strong><?= sanitize($n['title']) ?></strong>
                                <small class="text-muted"><?= formatDateTime($n['created_at']) ?></small>
                            </div>
                            <p class="mb-1 text-muted"><?= sanitize($n['message']) ?></p>
                            <div class="d-flex gap-2">
                                <?php if ($n['link']): ?>
                                    <a href="<?= sanitize($n['link']) ?>" class="btn btn-sm btn-outline-primary">View</a>
                                <?php endif; ?>
                                <?php if (!$n['is_read']): ?>
                                    <form method="POST" class="d-inline">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="mark_read">
                                        <input type="hidden" name="notification_id" value="<?= $n['id'] ?>">
                                        <button class="btn btn-sm btn-link">Mark as read</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if (!$n['is_read']): ?>
                            <span class="badge bg-primary rounded-pill">New</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
