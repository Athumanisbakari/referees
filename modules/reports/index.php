<?php
require_once __DIR__ . '/../../includes/functions.php';
requireRole(['Admin', 'Assigner', 'Referee', 'Assessor']);

$pageTitle = 'Match Reports';
$pdo = getDBConnection();

$reports = $pdo->query("
    SELECT mr.*, m.home_team, m.away_team, m.match_date, u.full_name AS referee_name
    FROM match_reports mr
    JOIN matches m ON mr.match_id = m.id
    JOIN referees r ON mr.referee_id = r.id
    JOIN users u ON r.user_id = u.id
    ORDER BY mr.submitted_at DESC
")->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h4><i class="bi bi-file-text me-2"></i>Match Reports</h4>
    <?php if (in_array($_SESSION['role_name'], ['Referee'], true)): ?>
        <a href="create.php" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Submit Report</a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Match</th>
                        <th>Referee</th>
                        <th>Type</th>
                        <th>Cards</th>
                        <th>Pitch</th>
                        <th>Status</th>
                        <th>Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reports)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No reports submitted</td></tr>
                    <?php else: ?>
                        <?php foreach ($reports as $r): ?>
                            <tr>
                                <td>
                                    <strong><?= sanitize($r['home_team']) ?></strong> vs <?= sanitize($r['away_team']) ?><br>
                                    <small class="text-muted"><?= formatDate($r['match_date']) ?></small>
                                </td>
                                <td><?= sanitize($r['referee_name']) ?></td>
                                <td><?= sanitize($r['report_type']) ?></td>
                                <td><span class="badge bg-warning text-dark"><?= $r['cards_yellow'] ?>Y</span>
                                    <span class="badge bg-danger"><?= $r['cards_red'] ?>R</span></td>
                                <td><?= sanitize($r['pitch_condition']) ?></td>
                                <td><?= getStatusBadge($r['status']) ?></td>
                                <td><?= formatDateTime($r['submitted_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
