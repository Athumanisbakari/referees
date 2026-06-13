<?php
/**
 * Helper Functions
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/assets.php';
require_once __DIR__ . '/lang.php';

initLanguage();

function sanitize(string $input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        redirect(APP_URL . '/login.php');
    }
}

function requireRole(array $roles): void
{
    requireLogin();
    if (!in_array($_SESSION['role_name'] ?? '', $roles, true)) {
        $_SESSION['flash_error'] = __('error.no_permission');
        redirect(APP_URL . '/dashboard.php');
    }
}

function getCurrentUser(): ?array
{
    if (!isLoggedIn()) {
        return null;
    }

    $pdo = getDBConnection();
    $stmt = $pdo->prepare('
        SELECT u.*, r.name AS role_name
        FROM users u
        JOIN roles r ON u.role_id = r.id
        WHERE u.id = ? AND u.is_active = 1
    ');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function logActivity(string $action, string $module, string $description = ''): void
{
    if (!isLoggedIn()) {
        return;
    }

    $pdo = getDBConnection();
    $stmt = $pdo->prepare('
        INSERT INTO activity_logs (user_id, action, module, description, ip_address)
        VALUES (?, ?, ?, ?, ?)
    ');
    $stmt->execute([
        $_SESSION['user_id'],
        $action,
        $module,
        $description,
        $_SERVER['REMOTE_ADDR'] ?? null,
    ]);
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash_' . $type] = $message;
}

function getFlash(string $type): ?string
{
    if (isset($_SESSION['flash_' . $type])) {
        $message = $_SESSION['flash_' . $type];
        unset($_SESSION['flash_' . $type]);
        return $message;
    }
    return null;
}

function formatDate(?string $date, string $format = 'd M Y'): string
{
    if (!$date) {
        return '-';
    }
    return date($format, strtotime($date));
}

function formatDateTime(?string $datetime, string $format = 'd M Y H:i'): string
{
    if (!$datetime) {
        return '-';
    }
    return date($format, strtotime($datetime));
}

function formatCurrency(float $amount, string $currency = 'TZS'): string
{
    return number_format($amount, 0, '.', ',') . ' ' . $currency;
}

function getUnreadNotificationCount(int $userId): int
{
    $pdo = getDBConnection();
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
    $stmt->execute([$userId]);
    return (int) $stmt->fetchColumn();
}

function createNotification(int $userId, string $title, string $message, string $type = 'System', ?string $link = null): void
{
    $pdo = getDBConnection();
    $stmt = $pdo->prepare('
        INSERT INTO notifications (user_id, title, message, type, link)
        VALUES (?, ?, ?, ?, ?)
    ');
    $stmt->execute([$userId, $title, $message, $type, $link]);
}

function getStatusBadge(string $status): string
{
    $classes = [
        'Pending'     => 'warning',
        'Approved'    => 'success',
        'Accepted'    => 'success',
        'Confirmed'   => 'success',
        'Active'      => 'success',
        'Paid'        => 'success',
        'Verified'    => 'info',
        'Submitted'   => 'info',
        'Scheduled'   => 'primary',
        'Completed'   => 'secondary',
        'Rejected'    => 'danger',
        'Declined'    => 'danger',
        'Cancelled'   => 'danger',
        'Suspended'   => 'danger',
        'Expired'     => 'dark',
        'Late'        => 'danger',
        'On Time'     => 'success',
        'Early'       => 'info',
        'In Progress' => 'warning',
    ];

    $labels = [
        'Pending'     => 'status.pending',
        'Approved'    => 'status.approved',
        'Accepted'    => 'status.accepted',
        'Confirmed'   => 'status.confirmed',
        'Active'      => 'status.active',
        'Paid'        => 'status.paid',
        'Verified'    => 'status.verified',
        'Submitted'   => 'status.submitted',
        'Scheduled'   => 'status.scheduled',
        'Completed'   => 'status.completed',
        'Rejected'    => 'status.rejected',
        'Declined'    => 'status.declined',
        'Cancelled'   => 'status.cancelled',
        'Suspended'   => 'status.suspended',
        'Expired'     => 'status.expired',
        'Late'        => 'status.late',
        'On Time'     => 'status.on_time',
        'Early'       => 'status.early',
        'In Progress' => 'status.in_progress',
    ];

    $class = $classes[$status] ?? 'secondary';
    $label = isset($labels[$status]) ? __($labels[$status]) : $status;

    return '<span class="badge bg-' . $class . '">' . sanitize($label) . '</span>';
}

function paginate(int $total, int $perPage, int $currentPage): array
{
    $totalPages = max(1, (int) ceil($total / $perPage));
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;

    return [
        'total'        => $total,
        'per_page'     => $perPage,
        'current_page' => $currentPage,
        'total_pages'  => $totalPages,
        'offset'       => $offset,
    ];
}

function renderPagination(array $pagination, string $baseUrl): string
{
    if ($pagination['total_pages'] <= 1) {
        return '';
    }

    $html = '<nav><ul class="pagination justify-content-center">';
    $page = $pagination['current_page'];
    $total = $pagination['total_pages'];
    $sep = str_contains($baseUrl, '?') ? '&' : '?';

    if ($page > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . $sep . 'page=' . ($page - 1) . '">&laquo;</a></li>';
    }

    for ($i = max(1, $page - 2); $i <= min($total, $page + 2); $i++) {
        $active = $i === $page ? ' active' : '';
        $html .= '<li class="page-item' . $active . '"><a class="page-link" href="' . $baseUrl . $sep . 'page=' . $i . '">' . $i . '</a></li>';
    }

    if ($page < $total) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . $sep . 'page=' . ($page + 1) . '">&raquo;</a></li>';
    }

    $html .= '</ul></nav>';
    return $html;
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(?string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

function getMobileNavItems(string $role): array
{
    $items = [
        [
            'icon' => 'bi-house',
            'label_key' => 'mobile.home',
            'url' => '/dashboard.php',
            'match' => '/dashboard.php',
        ],
        [
            'icon' => 'bi-bell',
            'label_key' => 'mobile.alerts',
            'url' => '/modules/notifications/index.php',
            'match' => '/modules/notifications/',
        ],
    ];

    if ($role === 'Referee') {
        $items[] = [
            'icon' => 'bi-pin-map',
            'label_key' => 'menu.arrival',
            'url' => '/modules/arrival/index.php',
            'match' => '/modules/arrival/',
        ];
    } elseif (in_array($role, ['Admin', 'Assigner'], true)) {
        $items[] = [
            'icon' => 'bi-trophy',
            'label_key' => 'menu.matches',
            'url' => '/modules/matches/index.php',
            'match' => '/modules/matches/',
        ];
    } elseif ($role === 'Finance') {
        $items[] = [
            'icon' => 'bi-cash-coin',
            'label_key' => 'menu.payments',
            'url' => '/modules/payments/index.php',
            'match' => '/modules/payments/',
        ];
    } else {
        $items[] = [
            'icon' => 'bi-file-text',
            'label_key' => 'menu.reports',
            'url' => '/modules/reports/index.php',
            'match' => '/modules/reports/',
        ];
    }

    $items[] = [
        'icon' => 'bi-list',
        'label_key' => 'mobile.menu',
        'action' => 'menu',
    ];
    $items[] = [
        'icon' => 'bi-person',
        'label_key' => 'nav.profile',
        'url' => '/profile.php',
        'match' => '/profile.php',
    ];

    return $items;
}

function isNavItemActive(array $item): bool
{
    if (isset($item['action'])) {
        return false;
    }

    $uri = $_SERVER['REQUEST_URI'] ?? '';

    if (($item['match'] ?? '') === '/dashboard.php') {
        return basename($_SERVER['PHP_SELF'] ?? '') === 'dashboard.php';
    }

    return str_contains($uri, $item['match'] ?? '');
}

function deleteUser(int $userId): array
{
    if ($userId === (int) ($_SESSION['user_id'] ?? 0)) {
        return ['success' => false, 'message' => 'You cannot delete your own account.'];
    }

    $pdo = getDBConnection();

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare('SELECT id FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        if (!$stmt->fetchColumn()) {
            $pdo->rollBack();
            return ['success' => false, 'message' => 'User not found.'];
        }

        $stmt = $pdo->prepare('SELECT id FROM referees WHERE user_id = ?');
        $stmt->execute([$userId]);
        $refereeId = $stmt->fetchColumn();

        $pdo->prepare('
            DELETE va FROM video_assessments va
            INNER JOIN match_videos mv ON va.video_id = mv.id
            WHERE mv.uploaded_by = ?
        ')->execute([$userId]);
        $pdo->prepare('DELETE FROM video_assessments WHERE assessor_id = ?')->execute([$userId]);
        $pdo->prepare('DELETE FROM match_videos WHERE uploaded_by = ?')->execute([$userId]);

        if ($refereeId) {
            $pdo->prepare('DELETE FROM video_assessments WHERE referee_id = ?')->execute([$refereeId]);
            $pdo->prepare('DELETE FROM arrival_confirmations WHERE referee_id = ?')->execute([$refereeId]);
            $pdo->prepare('DELETE FROM match_reports WHERE referee_id = ?')->execute([$refereeId]);
            $pdo->prepare('DELETE FROM payments WHERE referee_id = ?')->execute([$refereeId]);
            $pdo->prepare('DELETE FROM training_attendance WHERE referee_id = ?')->execute([$refereeId]);
            $pdo->prepare('DELETE FROM licenses WHERE referee_id = ?')->execute([$refereeId]);
            $pdo->prepare('DELETE FROM referee_assignments WHERE referee_id = ?')->execute([$refereeId]);
            $pdo->prepare('DELETE FROM referees WHERE id = ?')->execute([$refereeId]);
        }

        $pdo->prepare('UPDATE activity_logs SET user_id = NULL WHERE user_id = ?')->execute([$userId]);
        $pdo->prepare('UPDATE matches SET created_by = NULL WHERE created_by = ?')->execute([$userId]);
        $pdo->prepare('UPDATE referee_assignments SET assigned_by = NULL WHERE assigned_by = ?')->execute([$userId]);
        $pdo->prepare('UPDATE payments SET verified_by = NULL WHERE verified_by = ?')->execute([$userId]);
        $pdo->prepare('UPDATE training_programs SET trainer_id = NULL WHERE trainer_id = ?')->execute([$userId]);

        $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$userId]);

        $pdo->commit();

        return ['success' => true, 'message' => 'User deleted successfully.'];
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return ['success' => false, 'message' => 'Could not delete user. They may still be linked to system records.'];
    }
}
