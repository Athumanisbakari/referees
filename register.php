<?php
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    redirect(APP_URL . '/dashboard.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/includes/auth.php';

    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request.';
    } else {
        $required = ['username', 'email', 'password', 'confirm_password', 'full_name', 'phone'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                $error = 'Please fill in all required fields.';
                break;
            }
        }

        if (!$error && $_POST['password'] !== $_POST['confirm_password']) {
            $error = 'Passwords do not match.';
        }

        if (!$error && strlen($_POST['password']) < 6) {
            $error = 'Password must be at least 6 characters.';
        }

        if (!$error) {
            $userId = registerUser([
                'username'   => $_POST['username'],
                'email'      => $_POST['email'],
                'password'   => $_POST['password'],
                'full_name'  => $_POST['full_name'],
                'phone'      => $_POST['phone'],
                'role_id'    => 2,
            ]);

            if ($userId) {
                $pdo = getDBConnection();
                $stmt = $pdo->prepare('
                    INSERT INTO referees (user_id, category, specialization, registration_status)
                    VALUES (?, ?, ?, ?)
                ');
                $stmt->execute([
                    $userId,
                    $_POST['category'] ?? 'Local',
                    $_POST['specialization'] ?? 'Center',
                    'Pending',
                ]);

                $success = 'Registration successful! Your account is pending approval.';
            } else {
                $error = 'Username or email already exists.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php renderPwaHead(); ?>
    <title>Referee Registration - <?= APP_SHORT_NAME ?></title>
    <?php renderVendorStyles(); ?>
</head>
<body class="login-page">
    <div class="login-container" style="max-width: 600px;">
        <div class="login-card">
            <div class="text-center mb-4">
                <div class="login-logo"><i class="bi bi-person-badge"></i></div>
                <h3 class="fw-bold">Referee Registration</h3>
                <p class="text-muted">Join the Ilala Referee Management System</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= sanitize($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= sanitize($success) ?></div>
                <div class="text-center"><a href="<?= APP_URL ?>/login.php" class="btn btn-primary">Go to Login</a></div>
            <?php else: ?>
                <form method="POST">
                    <?= csrfField() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="full_name" class="form-control" required
                                   value="<?= sanitize($_POST['full_name'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone *</label>
                            <input type="tel" name="phone" class="form-control" required
                                   value="<?= sanitize($_POST['phone'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Username *</label>
                            <input type="text" name="username" class="form-control" required
                                   value="<?= sanitize($_POST['username'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" required
                                   value="<?= sanitize($_POST['email'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                <option value="Local">Local</option>
                                <option value="Regional">Regional</option>
                                <option value="National">National</option>
                                <option value="FIFA">FIFA</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Specialization</label>
                            <select name="specialization" class="form-select">
                                <option value="Center">Center Referee</option>
                                <option value="Assistant">Assistant Referee</option>
                                <option value="Fourth Official">Fourth Official</option>
                                <option value="VAR">VAR</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password *</label>
                            <input type="password" name="password" class="form-control" required minlength="6">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm Password *</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mt-4">
                        <i class="bi bi-person-plus me-2"></i>Register
                    </button>
                </form>
                <div class="text-center mt-3">
                    <small class="text-muted">Already registered? <a href="<?= APP_URL ?>/login.php">Sign in</a></small>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php renderVendorScripts(); ?>
    <?php renderPwaScripts(); ?>
</body>
</html>
