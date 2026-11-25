<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function is_admin_logged_in(): bool
{
    ensure_session();
    return !empty($_SESSION['admin_id']) && !empty($_SESSION['admin_username']);
}

function current_admin_username(): string
{
    ensure_session();
    return (string)($_SESSION['admin_username'] ?? 'admin');
}

function require_admin(): void
{
    if (!is_admin_logged_in()) {
        redirect_to('/admin/login.php');
    }
}

function attempt_admin_login(string $username, string $password): bool
{
    ensure_session();
    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT id, username, password_hash, display_name FROM admins WHERE username = ? LIMIT 1');
    $stmt->execute([trim($username)]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($password, (string)$admin['password_hash'])) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int)$admin['id'];
    $_SESSION['admin_username'] = (string)$admin['username'];
    $_SESSION['admin_display_name'] = (string)$admin['display_name'];
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return true;
}

function logout_admin(): void
{
    ensure_session();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            (bool)$params['secure'],
            (bool)$params['httponly']
        );
    }
    session_destroy();
}
