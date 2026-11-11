<?php
require_once __DIR__ . '/../app/core/orders.php';
startReviewSession();
header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Allow: POST');
        throw new ReviewRequestException('Method not allowed.', 405);
    }
    if (!isset($_SESSION['user_id'])) {
        throw new ReviewRequestException('Please sign in again.', 401);
    }
    if (!is_string($_POST['csrfToken'] ?? null) || !isset($_SESSION['review_csrf_token'])
        || !hash_equals($_SESSION['review_csrf_token'], $_POST['csrfToken'])) {
        throw new ReviewRequestException('Your session expired. Please reload the page.', 403);
    }
    $userId = reviewPositiveId($_SESSION['user_id'], 'user');
    $orderId = reviewPositiveId($_POST['orderId'] ?? null, 'order');
    require __DIR__ . '/../app/core/db.php';
    $user = getCheckoutUser($pdo, $userId);
    if (!$user || $user['acc_status'] !== 'active') {
        throw new ReviewRequestException('Your account cannot cancel orders.', 403);
    }
    if (!cancelPendingUserOrder($pdo, $userId, $orderId)) {
        throw new ReviewRequestException('This order cannot be cancelled. Please refresh your orders.', 409);
    }
    echo json_encode(['success' => true]);
} catch (ReviewRequestException $error) {
    http_response_code($error->getCode());
    echo json_encode(['success' => false, 'message' => $error->getMessage()]);
} catch (Throwable $error) {
    error_log('Order cancellation failed: ' . $error->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to cancel this order. Please try again.']);
}
