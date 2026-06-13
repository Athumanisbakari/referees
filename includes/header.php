<?php
if (!isset($pageTitle)) {
    $pageTitle = __('dashboard.title');
}

$currentUser = getCurrentUser();
$unreadCount = $currentUser ? getUnreadNotificationCount($currentUser['id']) : 0;

$menuItems = [
    ['icon' => 'bi-speedometer2', 'label_key' => 'menu.dashboard', 'url' => '/dashboard.php', 'roles' => ['Admin', 'Referee', 'Assigner', 'Assessor', 'Finance']],
    ['icon' => 'bi-people', 'label_key' => 'menu.users', 'url' => '/modules/users/index.php', 'roles' => ['Admin']],
    ['icon' => 'bi-person-badge', 'label_key' => 'menu.referees', 'url' => '/modules/referees/index.php', 'roles' => ['Admin', 'Assigner']],
    ['icon' => 'bi-trophy', 'label_key' => 'menu.matches', 'url' => '/modules/matches/index.php', 'roles' => ['Admin', 'Assigner']],
    ['icon' => 'bi-person-check', 'label_key' => 'menu.assignments', 'url' => '/modules/assignments/index.php', 'roles' => ['Admin', 'Assigner']],
    ['icon' => 'bi-geo-alt', 'label_key' => 'menu.venues', 'url' => '/modules/venues/index.php', 'roles' => ['Admin', 'Assigner', 'Referee']],
    ['icon' => 'bi-pin-map', 'label_key' => 'menu.arrival', 'url' => '/modules/arrival/index.php', 'roles' => ['Admin', 'Assigner', 'Referee']],
    ['icon' => 'bi-file-text', 'label_key' => 'menu.reports', 'url' => '/modules/reports/index.php', 'roles' => ['Admin', 'Assigner', 'Referee', 'Assessor']],
    ['icon' => 'bi-camera-video', 'label_key' => 'menu.video_upload', 'url' => '/modules/videos/upload.php', 'roles' => ['Admin', 'Assessor', 'Referee']],
    ['icon' => 'bi-star', 'label_key' => 'menu.video_assessment', 'url' => '/modules/videos/assessment.php', 'roles' => ['Admin', 'Assessor']],
    ['icon' => 'bi-cash-coin', 'label_key' => 'menu.payments', 'url' => '/modules/payments/index.php', 'roles' => ['Admin', 'Finance']],
    ['icon' => 'bi-wallet2', 'label_key' => 'menu.allowances', 'url' => '/modules/allowances/index.php', 'roles' => ['Admin', 'Finance']],
    ['icon' => 'bi-mortarboard', 'label_key' => 'menu.training', 'url' => '/modules/training/index.php', 'roles' => ['Admin', 'Assigner', 'Referee']],
    ['icon' => 'bi-card-checklist', 'label_key' => 'menu.licenses', 'url' => '/modules/licenses/index.php', 'roles' => ['Admin', 'Assigner']],
    ['icon' => 'bi-bell', 'label_key' => 'menu.notifications', 'url' => '/modules/notifications/index.php', 'roles' => ['Admin', 'Referee', 'Assigner', 'Assessor', 'Finance']],
    ['icon' => 'bi-bar-chart', 'label_key' => 'menu.analytics', 'url' => '/modules/analytics/index.php', 'roles' => ['Admin', 'Assigner', 'Finance']],
];

$userRole = $_SESSION['role_name'] ?? '';
$currentLang = getCurrentLang();
?>
<!DOCTYPE html>
<html lang="<?= sanitize($currentLang) ?>">
<head>
    <meta charset="UTF-8">
    <?php renderPwaHead(); ?>
    <title><?= sanitize($pageTitle) ?> - <?= APP_SHORT_NAME ?></title>
    <?php renderVendorStyles(true); ?>
</head>
<body class="app-shell">
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
    <div class="wrapper">
        <nav id="sidebar" class="sidebar">
            <div class="sidebar-header">
                <div class="logo-icon"><i class="bi bi-whistle"></i></div>
                <div>
                    <h5 class="mb-0">Ilala Referees</h5>
                    <small class="text-muted"><?= __('app.tagline') ?></small>
                </div>
            </div>
            <ul class="sidebar-nav">
                <?php foreach ($menuItems as $item): ?>
                    <?php if (in_array($userRole, $item['roles'], true)): ?>
                        <?php
                        $isActive = str_contains($_SERVER['REQUEST_URI'], $item['url']) ||
                                    ($item['url'] === '/dashboard.php' && basename($_SERVER['PHP_SELF']) === 'dashboard.php');
                        ?>
                        <li class="nav-item">
                            <a href="<?= APP_URL . $item['url'] ?>" class="nav-link<?= $isActive ? ' active' : '' ?>">
                                <i class="bi <?= $item['icon'] ?>"></i>
                                <span><?= __($item['label_key']) ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
            <div class="sidebar-footer">
                <a href="<?= APP_URL ?>/logout.php" class="nav-link text-danger">
                    <i class="bi bi-box-arrow-left"></i>
                    <span><?= __('nav.logout') ?></span>
                </a>
            </div>
        </nav>

        <div class="main-content">
            <header class="top-navbar">
                <button class="btn btn-link sidebar-toggle" id="sidebarToggle">
                    <i class="bi bi-list fs-4"></i>
                </button>
                <div class="navbar-title">
                    <h6 class="mb-0"><?= sanitize($pageTitle) ?></h6>
                </div>
                <div class="navbar-actions ms-auto d-flex align-items-center gap-3">
                    <?php require __DIR__ . '/language_switcher.php'; ?>
                    <a href="<?= APP_URL ?>/modules/notifications/index.php" class="btn btn-link position-relative">
                        <i class="bi bi-bell fs-5"></i>
                        <?php if ($unreadCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= $unreadCount ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-link dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                            <div class="avatar-circle">
                                <?= strtoupper(substr($currentUser['full_name'] ?? 'U', 0, 1)) ?>
                            </div>
                            <span class="d-none d-md-inline"><?= sanitize($currentUser['full_name'] ?? '') ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text text-muted small"><?= sanitize($userRole) ?></span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= APP_URL ?>/profile.php"><i class="bi bi-person me-2"></i><?= __('nav.profile') ?></a></li>
                            <li><a class="dropdown-item text-danger" href="<?= APP_URL ?>/logout.php"><i class="bi bi-box-arrow-left me-2"></i><?= __('nav.logout') ?></a></li>
                        </ul>
                    </div>
                </div>
            </header>

            <main class="content-area">
                <?php if ($success = getFlash('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= sanitize($success) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if ($error = getFlash('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= sanitize($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
