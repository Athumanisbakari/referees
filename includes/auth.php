<?php
/**
 * Authentication Handler
 */

require_once __DIR__ . '/functions.php';

function login(string $username, string $password): bool
{
    $pdo = getDBConnection();
    $stmt = $pdo->prepare('
        SELECT u.*, r.name AS role_name
        FROM users u
        JOIN roles r ON u.role_id = r.id
        WHERE (u.username = ? OR u.email = ?) AND u.is_active = 1
    ');
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role_name'] = $user['role_name'];
        $_SESSION['role_id'] = $user['role_id'];

        $pdo->prepare('UPDATE users SET last_login = NOW() WHERE id = ?')->execute([$user['id']]);
        logActivity('Login', 'Auth', 'User logged in');
        return true;
    }

    return false;
}

function logout(): void
{
    logActivity('Logout', 'Auth', 'User logged out');
    session_destroy();
    redirect(APP_URL . '/login.php');
}

function registerUser(array $data): int|false
{
    $pdo = getDBConnection();

    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
    $stmt->execute([$data['username'], $data['email']]);
    if ($stmt->fetch()) {
        return false;
    }

    $stmt = $pdo->prepare('
        INSERT INTO users (role_id, username, email, password_hash, full_name, phone)
        VALUES (?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([
        $data['role_id'] ?? 2,
        $data['username'],
        $data['email'],
        password_hash($data['password'], PASSWORD_DEFAULT),
        $data['full_name'],
        $data['phone'] ?? null,
    ]);

    return (int) $pdo->lastInsertId();
}
