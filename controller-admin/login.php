<?php
require_once __DIR__ . '/../app/core/admin-auth.php';
require_once __DIR__ . '/../app/core/db.php';
require_once __DIR__ . '/../app/models/user-model.php';

startAdminSession();

if (isAdminLoggedIn()) {
    header('Location: home.php', true, 303);
    exit;
}

$loginError = '';
$oldEmail = '';
$requestMethod = $_SERVER['REQUEST_METHOD'];

if ($requestMethod === 'POST') {
    $email = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    $oldEmail = $email;

    if (!isAdminCsrfTokenValid($_POST['csrfToken'] ?? null)) {
        http_response_code(403);
        $loginError = 'Your session expired. Please try again.';
    } elseif ($email === '' || $password === '') {
        $loginError = 'Please enter your email and password.';
    } else {
        $user = getUserByEmail($pdo, $email);

        $valid = $user !== null
            && $user['user_role'] === 'admin'
            && $user['acc_status'] === 'active'
            && is_string($user['password_hash'] ?? null)
            && $user['password_hash'] !== ''
            && password_verify($password, $user['password_hash']);

        if (!$valid) {
            $loginError = 'Invalid email or password.';
        } else {
            session_regenerate_id(true);
            $_SESSION['admin_authenticated'] = true;
            $_SESSION['admin_last_activity'] = time();
            $_SESSION['admin_name'] = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: 'Administrator';
            $_SESSION['admin_user_id'] = (int) $user['user_id'];
            unset($_SESSION['admin_csrf_token']);
            header('Location: home.php', true, 303);
            exit;
        }
    }
} elseif ($requestMethod !== 'GET') {
    header('Allow: GET, POST');
    http_response_code(405);
    exit;
}

$csrfToken = adminCsrfToken();
define('GREENDASH_ADMIN_VIEW', true);
require __DIR__ . '/../app/views/admin/login.php';