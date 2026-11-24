<?php

require_once __DIR__ . '/../app/core/cart.php';
startCheckoutSession();
header('Content-Type: application/json; charset=utf-8');

function sendCartResponse(int $status, array $data): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    exit;
}

try {
    $method = $_SERVER['REQUEST_METHOD'];
    if (!in_array($method, ['GET', 'POST'], true)) {
        header('Allow: GET, POST');
        sendCartResponse(405, ['success' => false, 'message' => 'Method not allowed.']);
    }
    if (!isset($_SESSION['user_id'])) {
        sendCartResponse(401, ['success' => false, 'message' => 'Please sign in to access your cart.']);
    }
    if ($method === 'POST' && (!is_string($_POST['csrfToken'] ?? null)
        || !isset($_SESSION['checkout_csrf_token'])
        || !hash_equals($_SESSION['checkout_csrf_token'], $_POST['csrfToken']))) {
        sendCartResponse(403, ['success' => false, 'message' => 'Your session expired. Please reload the page.']);
    }

    $userId = checkoutPositiveInteger($_SESSION['user_id'], 'User ID');
    $stallId = checkoutPositiveInteger($_GET['stallId'] ?? null, 'Stall ID');
    require __DIR__ . '/../app/core/db.php';
    $user = getCheckoutUser($pdo, $userId);
    if (!$user || $user['acc_status'] !== 'active') {
        sendCartResponse(403, ['success' => false, 'message' => 'This account cannot access a cart.']);
    }
    if (!getCheckoutStall($pdo, $stallId)) {
        sendCartResponse(404, ['success' => false, 'message' => 'This stall is no longer available.']);
    }
    $cart = getUserCart($pdo, $userId, $stallId);
    if ($method === 'GET') {
        sendCartResponse(200, ['success' => true] + $cart);
    }

    $versionInput = $_POST['version'] ?? null;
    $version = $versionInput === '0' ? 0 : checkoutPositiveInteger($versionInput, 'Cart version');
    if ($version === $cart['version']) {
        $items = decodeCartItems($_POST['items'] ?? null);
        validateCartMenu($pdo, $stallId, $items, $cart['items']);
        if (replaceUserCart($pdo, $userId, $stallId, $version, $items)) {
            sendCartResponse(200, ['success' => true, 'version' => $version + 1, 'items' => $items]);
        }
    }
    sendCartResponse(409, ['success' => false, 'message' => 'Your cart changed. Reload it and try again.']);
} catch (CheckoutValidationException $error) {
    sendCartResponse(422, ['success' => false, 'message' => $error->getMessage()]);
} catch (Throwable $error) {
    error_log('Cart request failed: ' . $error->getMessage());
    sendCartResponse(500, ['success' => false, 'message' => 'Unable to load or save your cart. Please try again.']);
}
