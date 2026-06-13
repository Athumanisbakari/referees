<?php
require_once __DIR__ . '/../../includes/functions.php';
requireRole(['Admin', 'Assigner', 'Referee']);

$pageTitle = 'Arrival Confirmation';
$pdo = getDBConnection();
$user = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf_token'] ?? '')) {
    $assignmentId = (int) $_POST['assignment_id'];
    $refereeId = (int) $_POST['referee_id'];
    $matchId = (int) $_POST['match_id'];

    $stmt = $pdo->prepare('
        INSERT INTO arrival_confirmations (assignment_id, referee_id, match_id, latitude, longitude, status)
        VALUES (?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([
        $assignmentId, $refereeId, $matchId,
        $_POST['latitude'] ?? null, $_POST['longitude'] ?? null,
        $_POST['status'] ?? 'On Time',
    ]);

    $pdo->prepare("UPDATE referee_assignments SET assignment_status = 'Confirmed' WHERE id = ?")
        ->execute([$assignmentId]);

    logActivity('Confirm', 'Arrival', 'Arrival confirmed for match #' . $matchId);
    setFlash('success', 'Arrival confirmed successfully.');
    redirect(APP_URL . '/modules/arrival/index.php');
}

if ($_SESSION['role_name'] === 'Referee') {
    $refStmt = $pdo->prepare('SELECT id FROM referees WHERE user_id = ?');
    $refStmt->execute([$user['id']]);
    $refereeId = $refStmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT ra.*, m.home_team, m.away_team, m.match_date, m.kickoff_time,
               v.name AS venue_name, v.latitude AS venue_lat, v.longitude AS venue_lng,
               ac.id AS confirmed
        FROM referee_assignments ra
        JOIN matches m ON ra.match_id = m.id
        LEFT JOIN venues v ON m.venue_id = v.id
        LEFT JOIN arrival_confirmations ac ON ac.assignment_id = ra.id
        WHERE ra.referee_id = ? AND m.match_date >= CURDATE()
        ORDER BY m.match_date, m.kickoff_time
    ");
    $stmt->execute([$refereeId]);
} else {
    $stmt = $pdo->query("
        SELECT ra.*, m.home_team, m.away_team, m.match_date, m.kickoff_time,
               u.full_name AS referee_name, v.name AS venue_name,
               ac.arrival_time, ac.status AS arrival_status
        FROM referee_assignments ra
        JOIN matches m ON ra.match_id = m.id
        JOIN referees r ON ra.referee_id = r.id
        JOIN users u ON r.user_id = u.id
        LEFT JOIN venues v ON m.venue_id = v.id
        LEFT JOIN arrival_confirmations ac ON ac.assignment_id = ra.id
        WHERE m.match_date >= CURDATE()
        ORDER BY m.match_date, m.kickoff_time
    ");
}

$assignments = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h4><i class="bi bi-pin-map me-2"></i>Arrival Confirmation</h4>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Match</th>
                        <th>Date & Time</th>
                        <th>Venue</th>
                        <?php if ($_SESSION['role_name'] !== 'Referee'): ?>
                            <th>Referee</th>
                        <?php endif; ?>
                        <th>Arrival Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($assignments)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No upcoming assignments</td></tr>
                    <?php else: ?>
                        <?php foreach ($assignments as $a): ?>
                            <tr>
                                <td><strong><?= sanitize($a['home_team']) ?></strong> vs <?= sanitize($a['away_team']) ?></td>
                                <td><?= formatDate($a['match_date']) ?> <?= date('H:i', strtotime($a['kickoff_time'])) ?></td>
                                <td><?= sanitize($a['venue_name'] ?? '-') ?></td>
                                <?php if ($_SESSION['role_name'] !== 'Referee'): ?>
                                    <td><?= sanitize($a['referee_name']) ?></td>
                                <?php endif; ?>
                                <td>
                                    <?php if (!empty($a['confirmed']) || !empty($a['arrival_time'])): ?>
                                        <?= getStatusBadge($a['arrival_status'] ?? 'Confirmed') ?>
                                        <?php if (!empty($a['arrival_time'])): ?>
                                            <br><small><?= formatDateTime($a['arrival_time']) ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Not Confirmed</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($_SESSION['role_name'] === 'Referee' && empty($a['confirmed'])): ?>
                                        <form method="POST" id="arrivalForm">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="assignment_id" value="<?= $a['id'] ?>">
                                            <input type="hidden" name="referee_id" value="<?= $refereeId ?>">
                                            <input type="hidden" name="match_id" value="<?= $a['match_id'] ?>">
                                            <input type="hidden" name="latitude" id="latitude">
                                            <input type="hidden" name="longitude" id="longitude">
                                            <button type="button" id="confirmArrival" class="btn btn-sm btn-success">
                                                <i class="bi bi-pin-map me-1"></i>Confirm Arrival
                                            </button>
                                        </form>
                                    <?php elseif (!empty($a['venue_lat'])): ?>
                                        <a href="https://www.google.com/maps/dir/?api=1&destination=<?= $a['venue_lat'] ?>,<?= $a['venue_lng'] ?>"
                                           target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-signpost-2"></i> Navigate
                                        </a>
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

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
