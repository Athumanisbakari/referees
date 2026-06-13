<?php
require_once __DIR__ . '/../../includes/functions.php';
requireRole(['Admin', 'Assigner', 'Finance']);

$pageTitle = 'Reports & Analytics';
$pdo = getDBConnection();

$stats = [
    'total_referees'   => (int) $pdo->query("SELECT COUNT(*) FROM referees WHERE registration_status = 'Approved'")->fetchColumn(),
    'total_matches'    => (int) $pdo->query('SELECT COUNT(*) FROM matches')->fetchColumn(),
    'completed_matches'=> (int) $pdo->query("SELECT COUNT(*) FROM matches WHERE status = 'Completed'")->fetchColumn(),
    'total_payments'   => (float) $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'Paid'")->fetchColumn(),
    'pending_payments' => (int) $pdo->query("SELECT COUNT(*) FROM payments WHERE status = 'Pending'")->fetchColumn(),
    'avg_assessment'   => (float) $pdo->query('SELECT COALESCE(AVG(overall_score), 0) FROM video_assessments')->fetchColumn(),
    'active_licenses'  => (int) $pdo->query("SELECT COUNT(*) FROM licenses WHERE status = 'Active'")->fetchColumn(),
    'trainings'        => (int) $pdo->query("SELECT COUNT(*) FROM training_programs WHERE status = 'Scheduled'")->fetchColumn(),
];

$matchesByMonth = $pdo->query("
    SELECT DATE_FORMAT(match_date, '%Y-%m') AS month, COUNT(*) AS count
    FROM matches WHERE match_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY month ORDER BY month
")->fetchAll();

$refereesByCategory = $pdo->query("
    SELECT category, COUNT(*) AS count FROM referees
    WHERE registration_status = 'Approved' GROUP BY category
")->fetchAll();

$paymentsByStatus = $pdo->query("
    SELECT status, COUNT(*) AS count, SUM(amount) AS total
    FROM payments GROUP BY status
")->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h4><i class="bi bi-bar-chart me-2"></i>Reports & Analytics</h4>
</div>

<div class="row g-4 mb-4">
    <?php
    $cards = [
        ['label' => 'Active Referees', 'value' => $stats['total_referees'], 'icon' => 'bi-people', 'class' => 'stat-primary'],
        ['label' => 'Total Matches', 'value' => $stats['total_matches'], 'icon' => 'bi-trophy', 'class' => 'stat-success'],
        ['label' => 'Total Paid (TZS)', 'value' => number_format($stats['total_payments'], 0), 'icon' => 'bi-cash', 'class' => 'stat-info'],
        ['label' => 'Avg Assessment', 'value' => number_format($stats['avg_assessment'], 1) . '%', 'icon' => 'bi-star', 'class' => 'stat-warning'],
    ];
    foreach ($cards as $c): ?>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card <?= $c['class'] ?>">
                <div class="stat-icon"><i class="bi <?= $c['icon'] ?>"></i></div>
                <div class="stat-info">
                    <h3><?= $c['value'] ?></h3>
                    <p><?= $c['label'] ?></p>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Matches Over Time</h5></div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="matchesChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Referees by Category</h5></div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Payment Summary</h5></div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead><tr><th>Status</th><th>Count</th><th>Total Amount</th></tr></thead>
                    <tbody>
                        <?php foreach ($paymentsByStatus as $p): ?>
                            <tr>
                                <td><?= getStatusBadge($p['status']) ?></td>
                                <td><?= $p['count'] ?></td>
                                <td><?= formatCurrency($p['total'] ?? 0) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">System Overview</h5></div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Completed Matches</span><strong><?= $stats['completed_matches'] ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Pending Payments</span><strong><?= $stats['pending_payments'] ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Active Licenses</span><strong><?= $stats['active_licenses'] ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Scheduled Trainings</span><strong><?= $stats['trainings'] ?></strong>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
$matchLabels = json_encode(array_column($matchesByMonth, 'month'));
$matchData = json_encode(array_column($matchesByMonth, 'count'));
$catLabels = json_encode(array_column($refereesByCategory, 'category'));
$catData = json_encode(array_column($refereesByCategory, 'count'));

$extraScripts = "<script>
document.addEventListener('DOMContentLoaded', function() {
    new Chart(document.getElementById('matchesChart'), {
        type: 'line',
        data: {
            labels: {$matchLabels},
            datasets: [{ label: 'Matches', data: {$matchData}, borderColor: '#1a5276', backgroundColor: 'rgba(26,82,118,0.1)', fill: true, tension: 0.3 }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
    new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: {
            labels: {$catLabels},
            datasets: [{ data: {$catData}, backgroundColor: ['#1a5276','#2980b9','#27ae60','#f39c12'] }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
});
</script>";

$loadChart = true;
require_once __DIR__ . '/../../includes/footer.php';
?>
