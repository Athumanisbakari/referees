<?php
require_once __DIR__ . '/../../includes/functions.php';
requireRole(['Admin', 'Assigner']);

$pageTitle = 'New Assignment';
$pdo = getDBConnection();

$matches = $pdo->query("
    SELECT id, home_team, away_team, match_date, kickoff_time
    FROM matches WHERE status = 'Scheduled' AND match_date >= CURDATE()
    ORDER BY match_date, kickoff_time
")->fetchAll();

$referees = $pdo->query("
    SELECT r.id, u.full_name, r.specialization, r.category
    FROM referees r JOIN users u ON r.user_id = u.id
    WHERE r.registration_status = 'Approved'
    ORDER BY u.full_name
")->fetchAll();

$roles = ['Center Referee', 'Assistant Referee 1', 'Assistant Referee 2', 'Fourth Official', 'VAR'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf_token'] ?? '')) {
    $stmt = $pdo->prepare('
        INSERT INTO referee_assignments (match_id, referee_id, role, assigned_by)
        VALUES (?, ?, ?, ?)
    ');
    try {
        $stmt->execute([
            (int) $_POST['match_id'],
            (int) $_POST['referee_id'],
            $_POST['role'],
            $_SESSION['user_id'],
        ]);

        $refStmt = $pdo->prepare('SELECT user_id FROM referees WHERE id = ?');
        $refStmt->execute([(int) $_POST['referee_id']]);
        $userId = $refStmt->fetchColumn();

        createNotification(
            $userId,
            'New Match Assignment',
            'You have been assigned to a match. Please review and respond.',
            'Assignment',
            APP_URL . '/modules/assignments/index.php'
        );

        logActivity('Create', 'Assignments', 'Assigned referee to match');
        setFlash('success', 'Referee assigned successfully.');
    } catch (PDOException $e) {
        setFlash('error', 'This role is already assigned for this match.');
    }
    redirect(APP_URL . '/modules/assignments/index.php');
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h4><i class="bi bi-person-plus me-2"></i>New Assignment</h4>
    <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label">Match *</label>
                        <select name="match_id" class="form-select" required>
                            <option value="">Select match</option>
                            <?php foreach ($matches as $m): ?>
                                <option value="<?= $m['id'] ?>">
                                    <?= sanitize($m['home_team']) ?> vs <?= sanitize($m['away_team']) ?>
                                    (<?= formatDate($m['match_date']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Referee *</label>
                        <select name="referee_id" class="form-select" required>
                            <option value="">Select referee</option>
                            <?php foreach ($referees as $r): ?>
                                <option value="<?= $r['id'] ?>">
                                    <?= sanitize($r['full_name']) ?> (<?= sanitize($r['specialization']) ?> - <?= sanitize($r['category']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role *</label>
                        <select name="role" class="form-select" required>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role ?>"><?= $role ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Assign Referee</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
