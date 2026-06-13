<?php
require_once __DIR__ . '/../../includes/functions.php';
requireRole(['Admin', 'Assigner']);

$pageTitle = 'Referee Assignment';
$pdo = getDBConnection();

$assignments = $pdo->query("
    SELECT ra.*, m.home_team, m.away_team, m.match_date, m.kickoff_time,
           u.full_name AS referee_name, v.name AS venue_name
    FROM referee_assignments ra
    JOIN matches m ON ra.match_id = m.id
    JOIN referees r ON ra.referee_id = r.id
    JOIN users u ON r.user_id = u.id
    LEFT JOIN venues v ON m.venue_id = v.id
    ORDER BY m.match_date DESC, m.kickoff_time DESC
")->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h4><i class="bi bi-person-check me-2"></i>Referee Assignment</h4>
    <a href="create.php" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>New Assignment</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Match</th>
                        <th>Date</th>
                        <th>Venue</th>
                        <th>Referee</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Assigned</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($assignments)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No assignments yet</td></tr>
                    <?php else: ?>
                        <?php foreach ($assignments as $a): ?>
                            <tr>
                                <td><strong><?= sanitize($a['home_team']) ?></strong> vs <?= sanitize($a['away_team']) ?></td>
                                <td><?= formatDate($a['match_date']) ?><br>
                                    <small><?= date('H:i', strtotime($a['kickoff_time'])) ?></small></td>
                                <td><?= sanitize($a['venue_name'] ?? '-') ?></td>
                                <td><?= sanitize($a['referee_name']) ?></td>
                                <td><?= sanitize($a['role']) ?></td>
                                <td><?= getStatusBadge($a['assignment_status']) ?></td>
                                <td><?= formatDateTime($a['assigned_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
