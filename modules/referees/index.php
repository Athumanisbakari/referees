<?php
require_once __DIR__ . '/../../includes/functions.php';
requireRole(['Admin', 'Assigner']);

$pageTitle = 'Referee Registration';
$pdo = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    $refereeId = (int) ($_POST['referee_id'] ?? 0);

    if ($action === 'approve') {
        $pdo->prepare("UPDATE referees SET registration_status = 'Approved' WHERE id = ?")->execute([$refereeId]);
        $ref = $pdo->prepare('SELECT user_id FROM referees WHERE id = ?');
        $ref->execute([$refereeId]);
        $userId = $ref->fetchColumn();
        createNotification($userId, 'Registration Approved', 'Your referee registration has been approved.', 'System');
        setFlash('success', 'Referee approved.');
    } elseif ($action === 'reject') {
        $pdo->prepare("UPDATE referees SET registration_status = 'Rejected' WHERE id = ?")->execute([$refereeId]);
        setFlash('success', 'Referee registration rejected.');
    } elseif ($action === 'suspend') {
        $pdo->prepare("UPDATE referees SET registration_status = 'Suspended' WHERE id = ?")->execute([$refereeId]);
        setFlash('success', 'Referee suspended.');
    }
    redirect(APP_URL . '/modules/referees/index.php');
}

$statusFilter = $_GET['status'] ?? '';
$sql = "
    SELECT r.*, u.full_name, u.email, u.phone, u.username
    FROM referees r
    JOIN users u ON r.user_id = u.id
";
$params = [];
if ($statusFilter) {
    $sql .= ' WHERE r.registration_status = ?';
    $params[] = $statusFilter;
}
$sql .= ' ORDER BY r.registered_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$referees = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h4><i class="bi bi-person-badge me-2"></i>Referee Registration</h4>
    <div class="d-flex gap-2">
        <a href="?status=Pending" class="btn btn-sm btn-outline-warning">Pending</a>
        <a href="?status=Approved" class="btn btn-sm btn-outline-success">Approved</a>
        <a href="?" class="btn btn-sm btn-outline-secondary">All</a>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Specialization</th>
                        <th>Experience</th>
                        <th>License #</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($referees)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">No referees found</td></tr>
                    <?php else: ?>
                        <?php foreach ($referees as $ref): ?>
                            <tr>
                                <td>
                                    <strong><?= sanitize($ref['full_name']) ?></strong><br>
                                    <small class="text-muted"><?= sanitize($ref['email']) ?></small>
                                </td>
                                <td><?= sanitize($ref['category']) ?></td>
                                <td><?= sanitize($ref['specialization']) ?></td>
                                <td><?= (int) $ref['years_experience'] ?> yrs</td>
                                <td><?= sanitize($ref['license_number'] ?? '-') ?></td>
                                <td><?= getStatusBadge($ref['registration_status']) ?></td>
                                <td><?= formatDate($ref['registered_at']) ?></td>
                                <td>
                                    <a href="view.php?id=<?= $ref['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if ($ref['registration_status'] === 'Pending'): ?>
                                        <form method="POST" class="d-inline">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="action" value="approve">
                                            <input type="hidden" name="referee_id" value="<?= $ref['id'] ?>">
                                            <button class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i></button>
                                        </form>
                                        <form method="POST" class="d-inline">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="action" value="reject">
                                            <input type="hidden" name="referee_id" value="<?= $ref['id'] ?>">
                                            <button class="btn btn-sm btn-danger"><i class="bi bi-x-lg"></i></button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
