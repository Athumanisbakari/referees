<?php
require_once __DIR__ . '/../../includes/functions.php';
requireRole(['Admin']);

$pageTitle = 'User Management';
$pdo = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        require_once __DIR__ . '/../../includes/auth.php';
        $userId = registerUser([
            'username'  => $_POST['username'],
            'email'     => $_POST['email'],
            'password'  => $_POST['password'],
            'full_name' => $_POST['full_name'],
            'phone'     => $_POST['phone'] ?? null,
            'role_id'   => (int) $_POST['role_id'],
        ]);
        if ($userId) {
            logActivity('Create', 'Users', 'Created user: ' . $_POST['username']);
            setFlash('success', 'User created successfully.');
        } else {
            setFlash('error', 'Username or email already exists.');
        }
    } elseif ($action === 'toggle') {
        $stmt = $pdo->prepare('UPDATE users SET is_active = NOT is_active WHERE id = ? AND id != ?');
        $stmt->execute([(int) $_POST['user_id'], $_SESSION['user_id']]);
        setFlash('success', 'User status updated.');
    } elseif ($action === 'delete') {
        $result = deleteUser((int) $_POST['user_id']);
        setFlash($result['success'] ? 'success' : 'error', $result['message']);
        if ($result['success']) {
            logActivity('Delete', 'Users', 'Deleted user ID: ' . (int) $_POST['user_id']);
        }
    }
    redirect(APP_URL . '/modules/users/index.php');
}

$users = $pdo->query('
    SELECT u.*, r.name AS role_name
    FROM users u JOIN roles r ON u.role_id = r.id
    ORDER BY u.created_at DESC
')->fetchAll();

$roles = $pdo->query('SELECT * FROM roles ORDER BY name')->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h4><i class="bi bi-people me-2"></i>User Management</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
        <i class="bi bi-plus-lg me-1"></i>Add User
    </button>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><strong><?= sanitize($user['full_name']) ?></strong></td>
                            <td><?= sanitize($user['username']) ?></td>
                            <td><?= sanitize($user['email']) ?></td>
                            <td><span class="badge bg-primary"><?= sanitize($user['role_name']) ?></span></td>
                            <td><?= $user['is_active'] ? getStatusBadge('Active') : getStatusBadge('Suspended') ?></td>
                            <td><?= formatDateTime($user['last_login']) ?></td>
                            <td>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <form method="POST" class="d-inline">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Toggle Status">
                                            <i class="bi bi-toggle-on"></i>
                                        </button>
                                    </form>
                                    <form method="POST" class="d-inline">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                data-confirm="Delete this user?" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="create">
                <div class="modal-header">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role_id" class="form-select" required>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['id'] ?>"><?= sanitize($role['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
