<?php
// auth.php
// Small auth helper (sessions + guards)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in(): bool {
    return isset($_SESSION['user']);
}

function current_user() {
    return $_SESSION['user'] ?? null;
}



function login_user(array $user): void {
    // Store only safe fields in session
    unset($user['password']);
    $_SESSION['user'] = $user;
}

function logout_user(): void {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

function require_login(): void {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function require_admin(): void {
    require_login();
    $u = current_user();
    if (!$u || ($u['user_type'] ?? '') !== 'admin') {
        http_response_code(403);
        echo "<h2>403 - Forbidden</h2><p>Admin access required.</p>";
        exit;
    }
}
