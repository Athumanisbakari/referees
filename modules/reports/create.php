<?php
require_once __DIR__ . '/../../includes/functions.php';
requireRole(['Referee']);

$pageTitle = 'Submit Match Report';
$pdo = getDBConnection();

$refStmt = $pdo->prepare('SELECT id FROM referees WHERE user_id = ?');
$refStmt->execute([$_SESSION['user_id']]);
$refereeId = $refStmt->fetchColumn();

$matches = $pdo->prepare("
    SELECT m.id, m.home_team, m.away_team, m.match_date
    FROM matches m
    JOIN referee_assignments ra ON ra.match_id = m.id
    WHERE ra.referee_id = ? AND m.status IN ('Completed', 'In Progress', 'Scheduled')
    ORDER BY m.match_date DESC
");
$matches->execute([$refereeId]);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf_token'] ?? '')) {
    $stmt = $pdo->prepare('
        INSERT INTO match_reports (match_id, referee_id, report_type, summary, incidents,
        cards_yellow, cards_red, weather_conditions, pitch_condition)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([
        (int) $_POST['match_id'], $refereeId, $_POST['report_type'],
        $_POST['summary'], $_POST['incidents'] ?? null,
        (int) ($_POST['cards_yellow'] ?? 0), (int) ($_POST['cards_red'] ?? 0),
        $_POST['weather_conditions'] ?? null, $_POST['pitch_condition'],
    ]);

    logActivity('Create', 'Reports', 'Submitted match report');
    setFlash('success', 'Report submitted successfully.');
    redirect(APP_URL . '/modules/reports/index.php');
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h4><i class="bi bi-file-earmark-plus me-2"></i>Submit Match Report</h4>
    <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <?= csrfField() ?>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Match *</label>
                            <select name="match_id" class="form-select" required>
                                <option value="">Select match</option>
                                <?php while ($m = $matches->fetch()): ?>
                                    <option value="<?= $m['id'] ?>">
                                        <?= sanitize($m['home_team']) ?> vs <?= sanitize($m['away_team']) ?>
                                        (<?= formatDate($m['match_date']) ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Report Type</label>
                            <select name="report_type" class="form-select">
                                <option value="Post-Match">Post-Match</option>
                                <option value="Pre-Match">Pre-Match</option>
                                <option value="Incident">Incident</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Summary *</label>
                            <textarea name="summary" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Incidents</label>
                            <textarea name="incidents" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Yellow Cards</label>
                            <input type="number" name="cards_yellow" class="form-control" min="0" value="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Red Cards</label>
                            <input type="number" name="cards_red" class="form-control" min="0" value="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Weather</label>
                            <input type="text" name="weather_conditions" class="form-control" placeholder="Clear, Rainy...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Pitch Condition</label>
                            <select name="pitch_condition" class="form-select">
                                <?php foreach (['Excellent','Good','Fair','Poor'] as $p): ?>
                                    <option value="<?= $p ?>"><?= $p ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>Submit Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
