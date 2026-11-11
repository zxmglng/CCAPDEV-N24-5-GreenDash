<?php
require_once __DIR__ . '/../app/core/checkout.php';
startCheckoutSession();
header('Content-Type: application/json; charset=utf-8');

function checkoutResponse(int $status, array $result): void
{
    http_response_code($status);
    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    checkoutResponse(405, ['success' => false, 'message' => 'Method not allowed.']);
}
if (!isset($_SESSION['user_id'])) {
    checkoutResponse(401, ['success' => false, 'message' => 'Please sign in before placing an order.']);
}
if (!is_string($_POST['csrfToken'] ?? null) || !isset($_SESSION['checkout_csrf_token'])
    || !hash_equals($_SESSION['checkout_csrf_token'], $_POST['csrfToken'])) {
    checkoutResponse(403, ['success' => false, 'message' => 'Your checkout session expired. Please reload the page.']);
}

try {
    $userId = checkoutPositiveInteger($_SESSION['user_id'], 'User ID');
    $orderItems = $_POST['orderItems'] ?? '';
    if (!is_string($orderItems) || strlen($orderItems) > 100000) {
        throw new CheckoutValidationException('Invalid order items.');
    }
    try {
        $orderItems = json_decode($orderItems, true, 32, JSON_THROW_ON_ERROR);
    } catch (JsonException $error) {
        throw new CheckoutValidationException('Invalid order items.');
    }
    $request = validateCheckoutRequest([
        'stallId' => $_POST['stallId'] ?? null, 'orderItems' => $orderItems,
        'pickupTime' => $_POST['pickupTime'] ?? null,
        'paymentMethod' => $_POST['paymentMethod'] ?? null,
        'referenceNumber' => $_POST['referenceNumber'] ?? '',
    ]);
    $requestId = $_POST['requestId'] ?? '';
    if (!is_string($requestId) || !preg_match('/^[a-f0-9]{32}$/D', $requestId)) {
        throw new CheckoutValidationException('Invalid checkout request. Please reload the page.');
    }
    $receiptKey = $userId . ':' . $requestId;
    $fingerprint = hash('sha256', json_encode($request, JSON_THROW_ON_ERROR));
    $previousReceipt = $_SESSION['checkout_receipts'][$receiptKey] ?? null;
    if ($previousReceipt) {
        if (!hash_equals($previousReceipt['fingerprint'], $fingerprint)) {
            checkoutResponse(409, ['success' => false, 'message' => 'This checkout has already been submitted. Please start a new order.']);
        }
        checkoutResponse(200, $previousReceipt['result']);
    }

    require __DIR__ . '/../app/core/db.php';
    $result = placeCheckoutOrder($pdo, $userId, $request);
    // Repeating the same submission in this session returns its saved receipt.
    $_SESSION['checkout_receipts'][$receiptKey] = ['fingerprint' => $fingerprint, 'result' => $result];
    while (count($_SESSION['checkout_receipts']) > 20) {
        array_shift($_SESSION['checkout_receipts']);
    }
    checkoutResponse(201, $result);
} catch (CheckoutValidationException $error) {
    checkoutResponse(422, ['success' => false, 'message' => $error->getMessage()]);
} catch (Throwable $error) {
    error_log('Checkout failed: ' . $error->getMessage());
    checkoutResponse(500, ['success' => false, 'message' => 'Your order could not be saved. Please try again.']);
}
