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
require_once __DIR__ . '/../app/models/order-model.php';

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

if ($isPost) {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $contactNum = trim($_POST['contact_num'] ?? '');
    $profileImage = trim($_POST['profile_image'] ?? '');

    updateUserProfile(
        $pdo,
        $userId,
        $firstName,
        $lastName,
        $contactNum !== '' ? $contactNum : null,
        $profileImage !== '' ? $profileImage : $user['profile_image']
    );
    exit;
}

$totalOrders = getUserCompletedOrderCount($pdo, $userId);
$totalSpent = getUserTotalSpent($pdo, $userId);
$favoriteStall = getUserFavoriteStall($pdo, $userId);
$monthlyOrders = getUserMonthlyOrderCounts($pdo, $userId);
$csrfToken = userCsrfToken();
$assetBase = '../';
$userPageBase = '../app/views/user/';

require __DIR__ . '/../app/views/user/profile.php';
