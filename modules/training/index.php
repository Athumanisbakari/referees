<?php
require_once __DIR__ . '/../../includes/functions.php';
requireRole(['Admin', 'Assigner', 'Referee']);

$pageTitle = 'Training Management';
$pdo = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';

    if ($action === 'create' && $_SESSION['role_name'] !== 'Referee') {
        $stmt = $pdo->prepare('
            INSERT INTO training_programs (title, description, trainer_id, venue_id, start_date, end_date,
            start_time, end_time, max_participants, training_type)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            $_POST['title'], $_POST['description'] ?? null, $_SESSION['user_id'],
            $_POST['venue_id'] ?: null, $_POST['start_date'], $_POST['end_date'],
            $_POST['start_time'] ?? null, $_POST['end_time'] ?? null,
            (int) ($_POST['max_participants'] ?? 30), $_POST['training_type'],
        ]);
        setFlash('success', 'Training program created.');
    } elseif ($action === 'register' && $_SESSION['role_name'] === 'Referee') {
        $refStmt = $pdo->prepare('SELECT id FROM referees WHERE user_id = ?');
        $refStmt->execute([$_SESSION['user_id']]);
        $refereeId = $refStmt->fetchColumn();

        $stmt = $pdo->prepare('INSERT INTO training_attendance (training_id, referee_id) VALUES (?, ?)');
        try {
            $stmt->execute([(int) $_POST['training_id'], $refereeId]);
            setFlash('success', 'Registered for training.');
        } catch (PDOException $e) {
            setFlash('error', 'Already registered for this training.');
        }
    }
    redirect(APP_URL . '/modules/training/index.php');
}

$trainings = $pdo->query("
    SELECT tp.*, v.name AS venue_name, u.full_name AS trainer_name,
           (SELECT COUNT(*) FROM training_attendance ta WHERE ta.training_id = tp.id) AS participants
    FROM training_programs tp
    LEFT JOIN venues v ON tp.venue_id = v.id
    LEFT JOIN users u ON tp.trainer_id = u.id
    ORDER BY tp.start_date DESC
")->fetchAll();

$venues = $pdo->query('SELECT id, name FROM venues WHERE is_active = 1')->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h4><i class="bi bi-mortarboard me-2"></i>Training Management</h4>
    <?php if ($_SESSION['role_name'] !== 'Referee'): ?>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTrainingModal">
            <i class="bi bi-plus-lg me-1"></i>New Training
        </button>
    <?php endif; ?>
</div>

<div class="row g-4">
    <?php if (empty($trainings)): ?>
        <div class="col-12 text-center text-muted py-5">No training programs scheduled</div>
    <?php else: ?>
        <?php foreach ($trainings as $t): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="badge bg-primary"><?= sanitize($t['training_type']) ?></span>
                            <?= getStatusBadge($t['status']) ?>
                        </div>
                        <h5 class="card-title"><?= sanitize($t['title']) ?></h5>
                        <p class="card-text text-muted small"><?= sanitize($t['description'] ?? '') ?></p>
                        <ul class="list-unstyled small">
                            <li><i class="bi bi-calendar me-1"></i><?= formatDate($t['start_date']) ?> - <?= formatDate($t['end_date']) ?></li>
                            <li><i class="bi bi-geo-alt me-1"></i><?= sanitize($t['venue_name'] ?? 'TBA') ?></li>
                            <li><i class="bi bi-people me-1"></i><?= $t['participants'] ?>/<?= $t['max_participants'] ?> participants</li>
                        </ul>
                    </div>
                    <?php if ($_SESSION['role_name'] === 'Referee' && $t['status'] === 'Scheduled'): ?>
                        <div class="card-footer">
                            <form method="POST">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="register">
                                <input type="hidden" name="training_id" value="<?= $t['id'] ?>">
                                <button class="btn btn-sm btn-outline-primary w-100">Register</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php if ($_SESSION['role_name'] !== 'Referee'): ?>
<div class="modal fade" id="createTrainingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="create">
                <div class="modal-header"><h5 class="modal-title">New Training Program</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Training Type</label>
                            <select name="training_type" class="form-select">
                                <option value="Rules">Rules</option>
                                <option value="Fitness">Fitness</option>
                                <option value="VAR">VAR</option>
                                <option value="Physical">Physical</option>
                                <option value="Workshop">Workshop</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Venue</label>
                            <select name="venue_id" class="form-select">
                                <option value="">Select venue</option>
                                <?php foreach ($venues as $v): ?>
                                    <option value="<?= $v['id'] ?>"><?= sanitize($v['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
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
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
