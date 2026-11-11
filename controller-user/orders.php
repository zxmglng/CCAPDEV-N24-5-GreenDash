<?php
require_once __DIR__ . '/../app/core/orders.php';
startReviewSession();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../app/views/user/login.php', true, 302);
    exit;
}

try {
    $userId = reviewPositiveId($_SESSION['user_id'], 'user');
    require __DIR__ . '/../app/core/db.php';
    $user = getCheckoutUser($pdo, $userId);
    if (!$user || $user['acc_status'] !== 'active') {
        header('Location: ../app/views/user/login.php', true, 302);
        exit;
    }
    $ordersData = ['orders' => loadUserOrders($pdo, $userId), 'csrfToken' => reviewCsrfToken()];
    $assetBase = '../';
    $userPageBase = '../app/views/user/';
    define('GREENDASH_ORDERS_VIEW', true);
    require __DIR__ . '/../app/views/user/orders.php';
} catch (Throwable $error) {
    error_log('Orders page failed: ' . $error->getMessage());
    http_response_code(500);
    echo 'Unable to load your orders. Please try again.';
}
