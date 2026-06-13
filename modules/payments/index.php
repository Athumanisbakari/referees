<?php
require_once __DIR__ . '/../../includes/functions.php';
requireRole(['Admin', 'Finance']);

$pageTitle = 'Payment Verification';
$pdo = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf_token'] ?? '')) {
    $paymentId = (int) $_POST['payment_id'];
    $action = $_POST['action'];

    if ($action === 'verify') {
        $pdo->prepare("UPDATE payments SET status = 'Verified', verified_by = ?, verified_at = NOW() WHERE id = ?")
            ->execute([$_SESSION['user_id'], $paymentId]);
        setFlash('success', 'Payment verified.');
    } elseif ($action === 'pay') {
        $pdo->prepare("UPDATE payments SET status = 'Paid', paid_at = NOW() WHERE id = ?")
            ->execute([$paymentId]);
        setFlash('success', 'Payment marked as paid.');
    } elseif ($action === 'reject') {
        $pdo->prepare("UPDATE payments SET status = 'Rejected' WHERE id = ?")
            ->execute([$paymentId]);
        setFlash('success', 'Payment rejected.');
    } elseif ($action === 'create') {
        $stmt = $pdo->prepare('
            INSERT INTO payments (referee_id, match_id, amount, payment_type, payment_method, reference_number, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            (int) $_POST['referee_id'], $_POST['match_id'] ?: null,
            (float) $_POST['amount'], $_POST['payment_type'],
            $_POST['payment_method'], $_POST['reference_number'] ?? null, $_POST['notes'] ?? null,
        ]);
        setFlash('success', 'Payment record created.');
    }
    redirect(APP_URL . '/modules/payments/index.php');
}

$payments = $pdo->query("
    SELECT p.*, u.full_name AS referee_name, m.home_team, m.away_team,
           vu.full_name AS verified_by_name
    FROM payments p
    JOIN referees r ON p.referee_id = r.id
    JOIN users u ON r.user_id = u.id
    LEFT JOIN matches m ON p.match_id = m.id
    LEFT JOIN users vu ON p.verified_by = vu.id
    ORDER BY p.created_at DESC
")->fetchAll();

$referees = $pdo->query("
    SELECT r.id, u.full_name FROM referees r JOIN users u ON r.user_id = u.id
    WHERE r.registration_status = 'Approved' ORDER BY u.full_name
")->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h4><i class="bi bi-cash-coin me-2"></i>Payment Verification</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPaymentModal">
        <i class="bi bi-plus-lg me-1"></i>New Payment
    </button>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Referee</th>
                        <th>Match</th>
                        <th>Amount</th>
                        <th>Type</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payments)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">No payment records</td></tr>
                    <?php else: ?>
                        <?php foreach ($payments as $p): ?>
                            <tr>
                                <td><?= sanitize($p['referee_name']) ?></td>
                                <td><?= $p['home_team'] ? sanitize($p['home_team']) . ' vs ' . sanitize($p['away_team']) : '-' ?></td>
                                <td><strong><?= formatCurrency($p['amount']) ?></strong></td>
                                <td><?= sanitize($p['payment_type']) ?></td>
                                <td><?= sanitize($p['payment_method']) ?></td>
                                <td><?= getStatusBadge($p['status']) ?></td>
                                <td><?= formatDate($p['created_at']) ?></td>
                                <td>
                                    <?php if ($p['status'] === 'Pending'): ?>
                                        <form method="POST" class="d-inline">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="payment_id" value="<?= $p['id'] ?>">
                                            <input type="hidden" name="action" value="verify">
                                            <button class="btn btn-sm btn-success" title="Verify"><i class="bi bi-check-lg"></i></button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($p['status'] === 'Verified'): ?>
                                        <form method="POST" class="d-inline">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="payment_id" value="<?= $p['id'] ?>">
                                            <input type="hidden" name="action" value="pay">
                                            <button class="btn btn-sm btn-primary" title="Mark Paid"><i class="bi bi-cash"></i></button>
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

<div class="modal fade" id="createPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="create">
                <div class="modal-header"><h5 class="modal-title">New Payment</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
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
                        <label class="form-label">Amount (TZS)</label>
                        <input type="number" name="amount" class="form-control" required min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Type</label>
                        <select name="payment_type" class="form-select">
                            <option value="Match Allowance">Match Allowance</option>
                            <option value="Travel">Travel</option>
                            <option value="Bonus">Bonus</option>
                            <option value="Training">Training</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-select">
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Mobile Money">Mobile Money</option>
                            <option value="Cash">Cash</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
