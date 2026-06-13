<?php
require_once __DIR__ . '/../../includes/functions.php';
requireRole(['Admin', 'Assigner']);

$pageTitle = 'License Management';
$pdo = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $stmt = $pdo->prepare('
            INSERT INTO licenses (referee_id, license_type, license_number, issue_date, expiry_date, issuing_authority, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            (int) $_POST['referee_id'], $_POST['license_type'], $_POST['license_number'],
            $_POST['issue_date'], $_POST['expiry_date'],
            $_POST['issuing_authority'] ?? 'TFF', 'Active',
        ]);

        $pdo->prepare('UPDATE referees SET license_number = ? WHERE id = ?')
            ->execute([$_POST['license_number'], (int) $_POST['referee_id']]);

        setFlash('success', 'License issued.');
    } elseif ($action === 'update_status') {
        $pdo->prepare('UPDATE licenses SET status = ? WHERE id = ?')
            ->execute([$_POST['status'], (int) $_POST['license_id']]);
        setFlash('success', 'License status updated.');
    }
    redirect(APP_URL . '/modules/licenses/index.php');
}

$licenses = $pdo->query("
    SELECT l.*, u.full_name AS referee_name
    FROM licenses l
    JOIN referees r ON l.referee_id = r.id
    JOIN users u ON r.user_id = u.id
    ORDER BY l.expiry_date ASC
")->fetchAll();

$referees = $pdo->query("
    SELECT r.id, u.full_name FROM referees r JOIN users u ON r.user_id = u.id
    WHERE r.registration_status = 'Approved' ORDER BY u.full_name
")->fetchAll();

$expiringSoon = array_filter($licenses, fn($l) => $l['status'] === 'Active' && strtotime($l['expiry_date']) < strtotime('+30 days'));

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h4><i class="bi bi-card-checklist me-2"></i>License Management</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#issueLicenseModal">
        <i class="bi bi-plus-lg me-1"></i>Issue License
    </button>
</div>

<?php if (!empty($expiringSoon)): ?>
<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <strong><?= count($expiringSoon) ?> license(s)</strong> expiring within 30 days.
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Referee</th>
                    <th>License #</th>
                    <th>Type</th>
                    <th>Issue Date</th>
                    <th>Expiry Date</th>
                    <th>Authority</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($licenses)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No licenses on record</td></tr>
                <?php else: ?>
                    <?php foreach ($licenses as $l): ?>
                        <tr class="<?= strtotime($l['expiry_date']) < time() && $l['status'] === 'Active' ? 'table-danger' : '' ?>">
                            <td><?= sanitize($l['referee_name']) ?></td>
                            <td><code><?= sanitize($l['license_number']) ?></code></td>
                            <td><?= sanitize($l['license_type']) ?></td>
                            <td><?= formatDate($l['issue_date']) ?></td>
                            <td><?= formatDate($l['expiry_date']) ?></td>
                            <td><?= sanitize($l['issuing_authority']) ?></td>
                            <td><?= getStatusBadge($l['status']) ?></td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="license_id" value="<?= $l['id'] ?>">
                                    <select name="status" class="form-select form-select-sm" style="width:auto;display:inline-block" onchange="this.form.submit()">
                                        <?php foreach (['Active','Expired','Suspended','Revoked'] as $s): ?>
                                            <option value="<?= $s ?>" <?= $l['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="issueLicenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="create">
                <div class="modal-header"><h5 class="modal-title">Issue License</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Referee</label>
                        <select name="referee_id" class="form-select" required>
                            <?php foreach ($referees as $r): ?>
                                <option value="<?= $r['id'] ?>"><?= sanitize($r['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">License Number</label>
                        <input type="text" name="license_number" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">License Type</label>
                        <select name="license_type" class="form-select">
                            <option value="Local">Local</option>
                            <option value="Regional">Regional</option>
                            <option value="National">National</option>
                            <option value="FIFA">FIFA</option>
                        </select>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Issue Date</label>
                            <input type="date" name="issue_date" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Expiry Date</label>
                            <input type="date" name="expiry_date" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Issue License</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
