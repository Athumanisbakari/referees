<?php
require_once __DIR__ . '/../../includes/functions.php';
requireRole(['Admin', 'Assigner']);

$pageTitle = 'Referee Details';
$pdo = getDBConnection();
$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('
    SELECT r.*, u.full_name, u.email, u.phone, u.username
    FROM referees r JOIN users u ON r.user_id = u.id WHERE r.id = ?
');
$stmt->execute([$id]);
$referee = $stmt->fetch();

if (!$referee) {
    setFlash('error', 'Referee not found.');
    redirect(APP_URL . '/modules/referees/index.php');
}

$assignments = $pdo->prepare('
    SELECT ra.*, m.home_team, m.away_team, m.match_date
    FROM referee_assignments ra
    JOIN matches m ON ra.match_id = m.id
    WHERE ra.referee_id = ?
    ORDER BY m.match_date DESC LIMIT 10
');
$assignments->execute([$id]);

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h4><i class="bi bi-person-badge me-2"></i><?= sanitize($referee['full_name']) ?></h4>
    <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="avatar-circle mx-auto mb-3" style="width:80px;height:80px;font-size:2rem;">
                    <?= strtoupper(substr($referee['full_name'], 0, 1)) ?>
                </div>
                <h5><?= sanitize($referee['full_name']) ?></h5>
                <p class="text-muted"><?= sanitize($referee['username']) ?></p>
                <?= getStatusBadge($referee['registration_status']) ?>
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between">
                    <span>Category</span><strong><?= sanitize($referee['category']) ?></strong>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span>Level</span><strong><?= sanitize($referee['level']) ?></strong>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span>Specialization</span><strong><?= sanitize($referee['specialization']) ?></strong>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span>Experience</span><strong><?= (int) $referee['years_experience'] ?> years</strong>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span>Email</span><strong><?= sanitize($referee['email']) ?></strong>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span>Phone</span><strong><?= sanitize($referee['phone'] ?? '-') ?></strong>
                </li>
            </ul>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Recent Assignments</h5></div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead><tr><th>Match</th><th>Role</th><th>Date</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php while ($a = $assignments->fetch()): ?>
                            <tr>
                                <td><?= sanitize($a['home_team']) ?> vs <?= sanitize($a['away_team']) ?></td>
                                <td><?= sanitize($a['role']) ?></td>
                                <td><?= formatDate($a['match_date']) ?></td>
                                <td><?= getStatusBadge($a['assignment_status']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
