<?php
require_once __DIR__ . '/../app/core/admin-auth.php';
startAdminSession();

if (isAdminLoggedIn()) {
    header('Location: home.php', true, 303);
    exit;
}

$loginError = '';
$requestMethod = $_SERVER['REQUEST_METHOD'];

if ($requestMethod === 'POST') {
    $adminKey = is_string($_POST['adminKey'] ?? null) ? trim($_POST['adminKey']) : '';

    if (!isAdminCsrfTokenValid($_POST['csrfToken'] ?? null)) {
        http_response_code(403);
        $loginError = 'Your session expired. Please try again.';
    } elseif ($adminKey === '') {
        $loginError = 'Please enter the admin key.';
    } elseif (!isAdminKeyValid($adminKey)) {
        $loginError = 'Incorrect admin key. Please try again.';
    } else {
        session_regenerate_id(true);
        $_SESSION['admin_authenticated'] = true;
        unset($_SESSION['admin_csrf_token']);
        header('Location: home.php', true, 303);
        exit;
    }
} elseif ($requestMethod !== 'GET') {
    header('Allow: GET, POST');
    http_response_code(405);
    exit;
}

$csrfToken = adminCsrfToken();
define('GREENDASH_ADMIN_VIEW', true);
require __DIR__ . '/../app/views/admin/login.php';
