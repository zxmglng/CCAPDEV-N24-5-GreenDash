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

// Session-based Login limits
const ADMIN_LOGIN_MAX_ATTEMPTS = 5;      // failures before lockout
const ADMIN_LOGIN_LOCKOUT_SEC  = 900;    // 15-minute lockout

function adminLoginLockoutRemaining(): int
{
    $lockedUntil = (int) ($_SESSION['admin_login_locked_until'] ?? 0);
    $remaining = $lockedUntil - time();
    return $remaining > 0 ? $remaining : 0;
}

function adminLoginRecordFailure(): void
{
    $count = (int) ($_SESSION['admin_login_attempts'] ?? 0) + 1;
    $_SESSION['admin_login_attempts'] = $count;
    if ($count >= ADMIN_LOGIN_MAX_ATTEMPTS) {
        $_SESSION['admin_login_locked_until'] = time() + ADMIN_LOGIN_LOCKOUT_SEC;
    }
}

function adminLoginResetAttempts(): void
{
    unset($_SESSION['admin_login_attempts'], $_SESSION['admin_login_locked_until']);
}

if ($requestMethod === 'POST') {
    $email = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    $oldEmail = $email;

    if (!isAdminCsrfTokenValid($_POST['csrfToken'] ?? null)) {
        http_response_code(403);
        $loginError = 'Your session expired. Please try again.';
    } elseif ($email === '' || $password === '') {
        $loginError = 'Please enter your email and password.';
    } elseif (adminLoginLockoutRemaining() > 0) {
        // Refuse before touching the password hash.
        $minutes = (int) ceil(adminLoginLockoutRemaining() / 60);
        http_response_code(429);
        $loginError = 'Too many failed attempts. Try again in '
            . $minutes . ' minute' . ($minutes === 1 ? '' : 's') . '.';
    } else {
        $user = getUserByEmail($pdo, $email);
        $valid = $user !== null
            && $user['user_role'] === 'admin'
            && $user['acc_status'] === 'active'
            && is_string($user['password_hash'] ?? null)
            && $user['password_hash'] !== ''
            && password_verify($password, $user['password_hash']);

        if (!$valid) {
            adminLoginRecordFailure();
            $remaining = ADMIN_LOGIN_MAX_ATTEMPTS - (int) ($_SESSION['admin_login_attempts'] ?? 0);
            $loginError = 'Invalid email or password.';
            if ($remaining > 0) {
                $loginError .= ' ' . $remaining . ' attempt'
                    . ($remaining === 1 ? '' : 's') . ' remaining before a temporary lockout.';
            } else {
                $loginError = 'Too many failed attempts. Try again in 15 minutes.';
            }
        } else {
            adminLoginResetAttempts();
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