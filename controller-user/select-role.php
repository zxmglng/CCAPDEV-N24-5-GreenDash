<?php

require_once __DIR__ . '/../app/core/session.php';
require_once __DIR__ . '/../app/core/user-csrf.php';
startGreenDashSession();
header('Cache-Control: no-store');

$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
if (!isset($_SESSION['user_id'])) {
    if ($isPost) {
        http_response_code(401);
    } else {
        header('Location: ../index.php');
    }
    exit;
}

if ($isPost && !isUserCsrfTokenValid($_POST['csrfToken'] ?? null)) {
    http_response_code(403);
    exit;
}

require_once __DIR__ . '/../app/core/db.php';
require_once __DIR__ . '/../app/models/user-model.php';

$userId = (int) $_SESSION['user_id'];
$user = getUserById($pdo, $userId);
if (!$user || $user['acc_status'] !== 'active') {
    if ($isPost) {
        http_response_code(403);
    } else {
        header('Location: ../index.php');
    }
    exit;
}

if ($user['user_role'] !== null) {
    header('Location: stalls-directory.php');
    exit;
}

if ($isPost) {
    $role = $_POST['user_role'] ?? '';
    $allowedRoles = ['student', 'faculty', 'staff'];

    if (!in_array($role, $allowedRoles, true)) {
        $error = 'Please select a valid role.';
    } else {
        updateUserRole($pdo, $userId, $role);
        $_SESSION['user_role'] = $role;
        header('Location: stalls-directory.php', true, 303);
        exit;
    }
}

$csrfToken = userCsrfToken();
$assetBase = '../';

require __DIR__ . '/../app/views/user/select-role.php';
