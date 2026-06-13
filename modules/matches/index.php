<?php
require_once __DIR__ . '/../../includes/functions.php';
requireRole(['Admin', 'Assigner']);

$pageTitle = 'Match Management';
$pdo = getDBConnection();

$matches = $pdo->query("
    SELECT m.*, v.name AS venue_name
    FROM matches m
    LEFT JOIN venues v ON m.venue_id = v.id
    ORDER BY m.match_date DESC, m.kickoff_time DESC
")->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h4><i class="bi bi-trophy me-2"></i>Match Management</h4>
    <a href="create.php" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Schedule Match</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Match</th>
                        <th>Competition</th>
                        <th>Date & Time</th>
                        <th>Venue</th>
                        <th>Type</th>
                        <th>Score</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($matches)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">No matches scheduled</td></tr>
                    <?php else: ?>
                        <?php foreach ($matches as $match): ?>
                            <tr>
                                <td><strong><?= sanitize($match['home_team']) ?></strong> vs <strong><?= sanitize($match['away_team']) ?></strong></td>
                                <td><?= sanitize($match['competition'] ?? '-') ?></td>
                                <td><?= formatDate($match['match_date']) ?><br>
                                    <small class="text-muted"><?= date('H:i', strtotime($match['kickoff_time'])) ?></small></td>
                                <td><?= sanitize($match['venue_name'] ?? '-') ?></td>
                                <td><?= sanitize($match['match_type']) ?></td>
                                <td><?= $match['home_score'] !== null ? $match['home_score'] . ' - ' . $match['away_score'] : '-' ?></td>
                                <td><?= getStatusBadge($match['status']) ?></td>
                                <td>
                                    <a href="edit.php?id=<?= $match['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
