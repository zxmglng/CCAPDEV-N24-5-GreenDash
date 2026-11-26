<?php
require_once __DIR__ . '/../app/core/orders.php';
startReviewSession();
require_once __DIR__ . '/../app/core/user-auth.php';

header('Content-Type: application/json; charset=utf-8');

// Cancellation is restricted to the signed-in user and orders still pending or preparing.
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Allow: POST');
        throw new ReviewRequestException('Method not allowed.', 405);
    }
    
    requireUserApi();

    if (!is_string($_POST['csrfToken'] ?? null) || !isset($_SESSION['review_csrf_token'])
        || !hash_equals($_SESSION['review_csrf_token'], $_POST['csrfToken'])) {
        throw new ReviewRequestException('Your session expired. Please reload the page.', 403);
    }
    // Validate IDs before querying; the model also checks ownership and order status.
    $userId = reviewPositiveId($_SESSION['user_id'], 'user');
    $orderId = reviewPositiveId($_POST['orderId'] ?? null, 'order');
    require __DIR__ . '/../app/core/db.php';
    $user = getCheckoutUser($pdo, $userId);
    if (!$user || $user['acc_status'] !== 'active') {
        throw new ReviewRequestException('Your account cannot cancel orders.', 403);
    }

    // Retrieve the current order status while verifying that it belongs to the signed-in user
    $orderStatus = getUserOrderStatus($pdo, $userId, $orderId);
    if ($orderStatus === null) {
        throw new ReviewRequestException('This order cannot be cancelled. Please refresh your orders.', 409);
    }

    // Pending orders = can cancel, preparing orders = weekly limit
    if ($orderStatus === 'pending') {
        if (!cancelPendingUserOrder($pdo, $userId, $orderId)) {
            throw new ReviewRequestException('This order cannot be cancelled.', 409);
        }
    } elseif ($orderStatus === 'preparing') {
        $result = cancelPreparingUserOrder($pdo, $userId, $orderId);

        if ($result === 'limit_reached') {
            throw new ReviewRequestException('You have reached your limit of order cancellations this week.', 409);
        }
        if ($result !== 'success') {
            throw new ReviewRequestException('This order can no longer be cancelled.', 409);
        }
    } else {
        throw new ReviewRequestException('Only pending or preparing orders can be cancelled.', 409);
    }

    // Return JSON for the orders page to handle without a full-page form submission
    echo json_encode(['success' => true]);
} catch (ReviewRequestException $error) {
    http_response_code($error->getCode());
    echo json_encode(['success' => false, 'message' => $error->getMessage()]);
} catch (Throwable $error) {
    error_log('Order cancellation failed: ' . $error->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to cancel this order. Please try again.']);
}