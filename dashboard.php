<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$pageTitle = __('dashboard.title');
$pdo = getDBConnection();
$role = $_SESSION['role_name'];

$stats = [
    'referees'    => (int) $pdo->query("SELECT COUNT(*) FROM referees WHERE registration_status = 'Approved'")->fetchColumn(),
    'matches'     => (int) $pdo->query("SELECT COUNT(*) FROM matches WHERE status = 'Scheduled' AND match_date >= CURDATE()")->fetchColumn(),
    'assignments' => (int) $pdo->query("SELECT COUNT(*) FROM referee_assignments WHERE assignment_status = 'Pending'")->fetchColumn(),
    'payments'    => (int) $pdo->query("SELECT COUNT(*) FROM payments WHERE status = 'Pending'")->fetchColumn(),
];

$recentMatches = $pdo->query("
    SELECT m.*, v.name AS venue_name
    FROM matches m
    LEFT JOIN venues v ON m.venue_id = v.id
    ORDER BY m.match_date DESC, m.kickoff_time DESC
    LIMIT 5
")->fetchAll();

$upcomingAssignments = $pdo->query("
    SELECT ra.*, m.home_team, m.away_team, m.match_date, m.kickoff_time,
           u.full_name AS referee_name, v.name AS venue_name
    FROM referee_assignments ra
    JOIN matches m ON ra.match_id = m.id
    JOIN referees r ON ra.referee_id = r.id
    JOIN users u ON r.user_id = u.id
    LEFT JOIN venues v ON m.venue_id = v.id
    WHERE m.match_date >= CURDATE()
    ORDER BY m.match_date ASC, m.kickoff_time ASC
    LIMIT 5
")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-primary">
            <div class="stat-icon"><i class="bi bi-people"></i></div>
            <div class="stat-info">
                <h3><?= $stats['referees'] ?></h3>
                <p><?= __('dashboard.active_referees') ?></p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-success">
            <div class="stat-icon"><i class="bi bi-calendar-event"></i></div>
            <div class="stat-info">
                <h3><?= $stats['matches'] ?></h3>
                <p><?= __('dashboard.upcoming_matches') ?></p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-warning">
            <div class="stat-icon"><i class="bi bi-person-check"></i></div>
            <div class="stat-info">
                <h3><?= $stats['assignments'] ?></h3>
                <p><?= __('dashboard.pending_assignments') ?></p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-info">
            <div class="stat-icon"><i class="bi bi-cash-coin"></i></div>
            <div class="stat-info">
                <h3><?= $stats['payments'] ?></h3>
                <p><?= __('dashboard.pending_payments') ?></p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-calendar3 me-2"></i><?= __('dashboard.recent_matches') ?></h5>
                <a href="<?= APP_URL ?>/modules/matches/index.php" class="btn btn-sm btn-outline-primary"><?= __('dashboard.view_all') ?></a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th><?= __('dashboard.match') ?></th>
                                <th><?= __('dashboard.date') ?></th>
                                <th><?= __('dashboard.venue') ?></th>
                                <th><?= __('dashboard.status') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentMatches)): ?>
                                <tr><td colspan="4" class="text-center text-muted py-4"><?= __('dashboard.no_matches') ?></td></tr>
                            <?php else: ?>
                                <?php foreach ($recentMatches as $match): ?>
                                    <tr>
                                        <td><strong><?= sanitize($match['home_team']) ?></strong> vs <?= sanitize($match['away_team']) ?></td>
                                        <td><?= formatDate($match['match_date']) ?></td>
                                        <td><?= sanitize($match['venue_name'] ?? '-') ?></td>
                                        <td><?= getStatusBadge($match['status']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-person-check me-2"></i><?= __('dashboard.upcoming_assignments') ?></h5>
                <a href="<?= APP_URL ?>/modules/assignments/index.php" class="btn btn-sm btn-outline-primary"><?= __('dashboard.view_all') ?></a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php if (empty($upcomingAssignments)): ?>
                        <div class="text-center text-muted py-4"><?= __('dashboard.no_assignments') ?></div>
                    <?php else: ?>
                        <?php foreach ($upcomingAssignments as $a): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <strong><?= sanitize($a['home_team']) ?> vs <?= sanitize($a['away_team']) ?></strong>
                                    <?= getStatusBadge($a['assignment_status']) ?>
                                </div>
                                <small class="text-muted">
                                    <?= sanitize($a['referee_name']) ?> &middot; <?= sanitize($a['role']) ?><br>
                                    <?= formatDate($a['match_date']) ?> at <?= date('H:i', strtotime($a['kickoff_time'])) ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-lightning me-2"></i><?= __('dashboard.quick_actions') ?></h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <?php if (in_array($role, ['Admin', 'Assigner'], true)): ?>
                        <a href="<?= APP_URL ?>/modules/matches/create.php" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-plus-circle me-1"></i> <?= __('dashboard.schedule_match') ?>
                        </a>
                        <a href="<?= APP_URL ?>/modules/assignments/create.php" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-person-plus me-1"></i> <?= __('dashboard.assign_referee') ?>
                        </a>
                    <?php endif; ?>
                    <?php if ($role === 'Referee'): ?>
                        <a href="<?= APP_URL ?>/modules/arrival/index.php" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-pin-map me-1"></i> <?= __('dashboard.confirm_arrival') ?>
                        </a>
                        <a href="<?= APP_URL ?>/modules/reports/create.php" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-file-text me-1"></i> <?= __('dashboard.submit_report') ?>
                        </a>
                    <?php endif; ?>
                    <?php if (in_array($role, ['Admin', 'Finance'], true)): ?>
                        <a href="<?= APP_URL ?>/modules/payments/index.php" class="btn btn-outline-warning btn-sm">
                            <i class="bi bi-cash me-1"></i> <?= __('dashboard.verify_payments') ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
