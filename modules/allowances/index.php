<?php
require_once __DIR__ . '/../../includes/functions.php';
requireRole(['Admin', 'Finance']);

$pageTitle = 'Match Allowance Management';
$pdo = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $stmt = $pdo->prepare('
            INSERT INTO match_allowances (role_type, match_type, amount, effective_from)
            VALUES (?, ?, ?, ?)
        ');
        $stmt->execute([
            $_POST['role_type'], $_POST['match_type'],
            (float) $_POST['amount'], $_POST['effective_from'],
        ]);
        setFlash('success', 'Allowance rate added.');
    } elseif ($action === 'toggle') {
        $pdo->prepare('UPDATE match_allowances SET is_active = NOT is_active WHERE id = ?')
            ->execute([(int) $_POST['allowance_id']]);
        setFlash('success', 'Allowance status updated.');
    }
    redirect(APP_URL . '/modules/allowances/index.php');
}

$allowances = $pdo->query('SELECT * FROM match_allowances ORDER BY role_type, match_type')->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h4><i class="bi bi-wallet2 me-2"></i>Match Allowance Management</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAllowanceModal">
        <i class="bi bi-plus-lg me-1"></i>Add Rate
    </button>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Role</th>
                    <th>Match Type</th>
                    <th>Amount</th>
                    <th>Effective From</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allowances as $a): ?>
                    <tr>
                        <td><?= sanitize($a['role_type']) ?></td>
                        <td><?= sanitize($a['match_type']) ?></td>
                        <td><strong><?= formatCurrency($a['amount']) ?></strong></td>
                        <td><?= formatDate($a['effective_from']) ?></td>
                        <td><?= $a['is_active'] ? getStatusBadge('Active') : getStatusBadge('Suspended') ?></td>
                        <td>
                            <form method="POST" class="d-inline">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="allowance_id" value="<?= $a['id'] ?>">
                                <button class="btn btn-sm btn-outline-warning"><i class="bi bi-toggle-on"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="addAllowanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="create">
                <div class="modal-header"><h5 class="modal-title">Add Allowance Rate</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Role Type</label>
                        <select name="role_type" class="form-select">
                            <option value="Center Referee">Center Referee</option>
                            <option value="Assistant Referee">Assistant Referee</option>
                            <option value="Fourth Official">Fourth Official</option>
                            <option value="VAR">VAR</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Match Type</label>
                        <select name="match_type" class="form-select">
                            <option value="League">League</option>
                            <option value="Cup">Cup</option>
                            <option value="Friendly">Friendly</option>
                            <option value="Training">Training</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount (TZS)</label>
                        <input type="number" name="amount" class="form-control" required min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Effective From</label>
                        <input type="date" name="effective_from" class="form-control" required value="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Rate</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
