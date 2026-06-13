<?php
require_once __DIR__ . '/../../includes/functions.php';
requireRole(['Admin', 'Assigner']);

$pageTitle = 'Schedule Match';
$pdo = getDBConnection();
$venues = $pdo->query('SELECT id, name FROM venues WHERE is_active = 1 ORDER BY name')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf_token'] ?? '')) {
    $stmt = $pdo->prepare('
        INSERT INTO matches (home_team, away_team, venue_id, match_date, kickoff_time, competition, season, match_type, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([
        $_POST['home_team'],
        $_POST['away_team'],
        $_POST['venue_id'] ?: null,
        $_POST['match_date'],
        $_POST['kickoff_time'],
        $_POST['competition'] ?? null,
        $_POST['season'] ?? null,
        $_POST['match_type'],
        $_SESSION['user_id'],
    ]);

    logActivity('Create', 'Matches', 'Scheduled: ' . $_POST['home_team'] . ' vs ' . $_POST['away_team']);
    setFlash('success', 'Match scheduled successfully.');
    redirect(APP_URL . '/modules/matches/index.php');
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h4><i class="bi bi-plus-circle me-2"></i>Schedule Match</h4>
    <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <?= csrfField() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Home Team *</label>
                            <input type="text" name="home_team" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Away Team *</label>
                            <input type="text" name="away_team" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Match Date *</label>
                            <input type="date" name="match_date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kickoff Time *</label>
                            <input type="time" name="kickoff_time" class="form-control" required>
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
                        <div class="col-md-6">
                            <label class="form-label">Match Type</label>
                            <select name="match_type" class="form-select">
                                <option value="League">League</option>
                                <option value="Cup">Cup</option>
                                <option value="Friendly">Friendly</option>
                                <option value="Training">Training</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Competition</label>
                            <input type="text" name="competition" class="form-control" placeholder="e.g. Premier League">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Season</label>
                            <input type="text" name="season" class="form-control" placeholder="e.g. 2025/2026">
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Schedule Match</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
