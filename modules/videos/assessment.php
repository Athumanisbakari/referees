<?php
require_once __DIR__ . '/../../includes/functions.php';
requireRole(['Admin', 'Assessor']);

$pageTitle = 'Video Assessment';
$pdo = getDBConnection();

$videos = $pdo->query("
    SELECT mv.*, m.home_team, m.away_team
    FROM match_videos mv JOIN matches m ON mv.match_id = m.id
    WHERE mv.upload_status = 'Ready'
    ORDER BY mv.uploaded_at DESC
")->fetchAll();

$referees = $pdo->query("
    SELECT r.id, u.full_name FROM referees r JOIN users u ON r.user_id = u.id
    WHERE r.registration_status = 'Approved' ORDER BY u.full_name
")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf_token'] ?? '')) {
    $scores = [
        (float) $_POST['decision_accuracy'],
        (float) $_POST['positioning_score'],
        (float) $_POST['communication_score'],
        (float) $_POST['fitness_score'],
    ];
    $overall = array_sum($scores) / count($scores);

    $stmt = $pdo->prepare('
        INSERT INTO video_assessments (video_id, assessor_id, referee_id, decision_accuracy,
        positioning_score, communication_score, fitness_score, overall_score, feedback, key_moments)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([
        (int) $_POST['video_id'], $_SESSION['user_id'], (int) $_POST['referee_id'] ?: null,
        $scores[0], $scores[1], $scores[2], $scores[3], $overall,
        $_POST['feedback'] ?? null, $_POST['key_moments'] ?? null,
    ]);

    logActivity('Create', 'Assessment', 'Video assessment submitted');
    setFlash('success', 'Assessment submitted successfully.');
    redirect(APP_URL . '/modules/videos/assessment.php');
}

$assessments = $pdo->query("
    SELECT va.*, mv.title AS video_title, u.full_name AS assessor_name,
           ru.full_name AS referee_name
    FROM video_assessments va
    JOIN match_videos mv ON va.video_id = mv.id
    JOIN users u ON va.assessor_id = u.id
    LEFT JOIN referees r ON va.referee_id = r.id
    LEFT JOIN users ru ON r.user_id = ru.id
    ORDER BY va.assessed_at DESC LIMIT 20
")->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h4><i class="bi bi-star me-2"></i>Video Assessment</h4>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">New Assessment</h5></div>
            <div class="card-body">
                <form method="POST">
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label">Video *</label>
                        <select name="video_id" class="form-select" required>
                            <option value="">Select video</option>
                            <?php foreach ($videos as $v): ?>
                                <option value="<?= $v['id'] ?>"><?= sanitize($v['title']) ?> (<?= sanitize($v['home_team']) ?> vs <?= sanitize($v['away_team']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Referee</label>
                        <select name="referee_id" class="form-select">
                            <option value="">Select referee</option>
                            <?php foreach ($referees as $r): ?>
                                <option value="<?= $r['id'] ?>"><?= sanitize($r['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php
                    $criteria = ['decision_accuracy' => 'Decision Accuracy', 'positioning_score' => 'Positioning',
                                 'communication_score' => 'Communication', 'fitness_score' => 'Fitness'];
                    foreach ($criteria as $name => $label): ?>
                        <div class="mb-3">
                            <label class="form-label"><?= $label ?> (0-100)</label>
                            <input type="range" name="<?= $name ?>" class="form-range" min="0" max="100" value="70"
                                   oninput="this.nextElementSibling.textContent = this.value + '%'">
                            <span class="text-primary fw-bold">70%</span>
                        </div>
                    <?php endforeach; ?>
                    <div class="mb-3">
                        <label class="form-label">Key Moments</label>
                        <textarea name="key_moments" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Feedback</label>
                        <textarea name="feedback" class="form-control" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check-lg me-1"></i>Submit Assessment</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Recent Assessments</h5></div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead><tr><th>Video</th><th>Referee</th><th>Overall</th><th>Assessor</th><th>Date</th></tr></thead>
                    <tbody>
                        <?php if (empty($assessments)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No assessments yet</td></tr>
                        <?php else: ?>
                            <?php foreach ($assessments as $a): ?>
                                <tr>
                                    <td><?= sanitize($a['video_title']) ?></td>
                                    <td><?= sanitize($a['referee_name'] ?? '-') ?></td>
                                    <td><span class="badge bg-<?= $a['overall_score'] >= 70 ? 'success' : 'warning' ?>"><?= number_format($a['overall_score'], 1) ?>%</span></td>
                                    <td><?= sanitize($a['assessor_name']) ?></td>
                                    <td><?= formatDate($a['assessed_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
