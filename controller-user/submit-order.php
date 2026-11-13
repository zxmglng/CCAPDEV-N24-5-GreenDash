<?php
/*
db: order_item_options -- customization options for each order item
+----------------------+------------------+------+-----+---------+----------------+
| Field                | Type             | Null | Key | Default | Extra          |
+----------------------+------------------+------+-----+---------+----------------+
| order_item_option_id | int(11)          | NO   | PRI | NULL    | auto_increment |
| order_item_id        | int(11)          | NO   | MUL | NULL    |                |
| option_id            | int(11)          | YES  |     | NULL    |                |
| option_group_name    | varchar(100)     | NO   |     | NULL    |                |
| option_name          | varchar(100)     | NO   |     | NULL    |                |
| quantity             | int(10) unsigned | NO   |     | 1       |                |
| extra_price          | decimal(10,2)    | NO   |     | 0.00    |                |
+----------------------+------------------+------+-----+---------+----------------+

db: order_items -- items ordered by the user
+----------------------+------------------+------+-----+---------+----------------+
| Field                | Type             | Null | Key | Default | Extra          |
+----------------------+------------------+------+-----+---------+----------------+
| order_item_id        | int(11)          | NO   | PRI | NULL    | auto_increment |
| order_id             | int(11)          | NO   | MUL | NULL    |                |
| menu_item_id         | int(11)          | YES  |     | NULL    |                |
| item_name            | varchar(150)     | NO   |     | NULL    |                |
| category_name        | varchar(100)     | YES  |     | NULL    |                |
| special_instructions | varchar(255)     | YES  |     | NULL    |                |
| quantity             | int(10) unsigned | NO   |     | 1       |                |
| base_price           | decimal(10,2)    | NO   |     | NULL    |                |
| unit_price           | decimal(10,2)    | NO   |     | NULL    |                |
| total_price          | decimal(10,2)    | NO   |     | NULL    |                |
+----------------------+------------------+------+-----+---------+----------------+

db: products -- menu items available for ordering
+--------------+---------------+------+-----+---------+----------------+
| Field        | Type          | Null | Key | Default | Extra          |
+--------------+---------------+------+-----+---------+----------------+
| id           | int(11)       | NO   | PRI | NULL    | auto_increment |
| stall_id     | int(11)       | NO   | MUL | NULL    |                |
| category_id  | int(11)       | NO   | MUL | NULL    |                |
| name         | varchar(150)  | NO   |     | NULL    |                |
| description  | text          | YES  |     | NULL    |                |
| price        | decimal(10,2) | NO   |     | NULL    |                |
| image_path   | varchar(255)  | YES  |     | NULL    |                |
| is_available | tinyint(1)    | NO   |     | 1       |                |
| banner_path  | varchar(255)  | YES  |     | NULL    |                |
+--------------+---------------+------+-----+---------+----------------+
*/ 

require_once __DIR__ . '/../app/core/checkout.php';
startCheckoutSession();
header('Content-Type: application/json; charset=utf-8');

// Send a consistent JSON response and stop processing the request.
function checkoutResponse(int $status, array $result): void
{
    http_response_code($status);
    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    exit;
}

// Reject unsupported methods before reading form data.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    checkoutResponse(405, ['success' => false, 'message' => 'Method not allowed.']);
}

// Require an authenticated session for order placement.
if (!isset($_SESSION['user_id'])) {
    checkoutResponse(401, ['success' => false, 'message' => 'Please sign in before placing an order.']);
}

// Reject submissions that did not originate from this user's checkout session.
if (!is_string($_POST['csrfToken'] ?? null) || !isset($_SESSION['checkout_csrf_token'])
    || !hash_equals($_SESSION['checkout_csrf_token'], $_POST['csrfToken'])) {
    checkoutResponse(403, ['success' => false, 'message' => 'Your checkout session expired. Please reload the page.']);
}

// Validate the request, prevent duplicate submissions, and persist the order.
try {
    $userId = checkoutPositiveInteger($_SESSION['user_id'], 'User ID');
    // Decode the serialized cart with size/depth limits before validating its structure and contents.
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
        // Return the prior receipt for an exact retry, but reject reusing an ID for changed order data.
        if (!hash_equals($previousReceipt['fingerprint'], $fingerprint)) {
            checkoutResponse(409, ['success' => false, 'message' => 'This checkout has already been submitted. Please start a new order.']);
        }
        checkoutResponse(200, $previousReceipt['result']);
    }

    require __DIR__ . '/../app/core/db.php';
    // Persist the validated order and cache its receipt so a network retry cannot create a duplicate.
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
