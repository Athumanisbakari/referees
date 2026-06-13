<?php
require_once __DIR__ . '/../../includes/functions.php';
requireRole(['Admin', 'Assigner']);

$pageTitle = 'Edit Match';
$pdo = getDBConnection();
$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM matches WHERE id = ?');
$stmt->execute([$id]);
$match = $stmt->fetch();

if (!$match) {
    setFlash('error', 'Match not found.');
    redirect(APP_URL . '/modules/matches/index.php');
}

$venues = $pdo->query('SELECT id, name FROM venues WHERE is_active = 1 ORDER BY name')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf_token'] ?? '')) {
    $stmt = $pdo->prepare('
        UPDATE matches SET home_team=?, away_team=?, venue_id=?, match_date=?, kickoff_time=?,
        competition=?, season=?, match_type=?, status=?, home_score=?, away_score=?, notes=?
        WHERE id=?
    ');
    $stmt->execute([
        $_POST['home_team'], $_POST['away_team'], $_POST['venue_id'] ?: null,
        $_POST['match_date'], $_POST['kickoff_time'], $_POST['competition'] ?? null,
        $_POST['season'] ?? null, $_POST['match_type'], $_POST['status'],
        $_POST['home_score'] !== '' ? $_POST['home_score'] : null,
        $_POST['away_score'] !== '' ? $_POST['away_score'] : null,
        $_POST['notes'] ?? null, $id,
    ]);
    setFlash('success', 'Match updated.');
    redirect(APP_URL . '/modules/matches/index.php');
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h4><i class="bi bi-pencil me-2"></i>Edit Match</h4>
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
                            <label class="form-label">Home Team</label>
                            <input type="text" name="home_team" class="form-control" required value="<?= sanitize($match['home_team']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Away Team</label>
                            <input type="text" name="away_team" class="form-control" required value="<?= sanitize($match['away_team']) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date</label>
                            <input type="date" name="match_date" class="form-control" required value="<?= $match['match_date'] ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Time</label>
                            <input type="time" name="kickoff_time" class="form-control" required value="<?= $match['kickoff_time'] ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <?php foreach (['Scheduled','In Progress','Completed','Cancelled','Postponed'] as $s): ?>
                                    <option value="<?= $s ?>" <?= $match['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Venue</label>
                            <select name="venue_id" class="form-select">
                                <option value="">Select venue</option>
                                <?php foreach ($venues as $v): ?>
                                    <option value="<?= $v['id'] ?>" <?= $match['venue_id'] == $v['id'] ? 'selected' : '' ?>><?= sanitize($v['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Match Type</label>
                            <select name="match_type" class="form-select">
                                <?php foreach (['League','Cup','Friendly','Training'] as $t): ?>
                                    <option value="<?= $t ?>" <?= $match['match_type'] === $t ? 'selected' : '' ?>><?= $t ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Home Score</label>
                            <input type="number" name="home_score" class="form-control" min="0" value="<?= $match['home_score'] ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Away Score</label>
                            <input type="number" name="away_score" class="form-control" min="0" value="<?= $match['away_score'] ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Competition</label>
                            <input type="text" name="competition" class="form-control" value="<?= sanitize($match['competition'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2"><?= sanitize($match['notes'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Update Match</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
