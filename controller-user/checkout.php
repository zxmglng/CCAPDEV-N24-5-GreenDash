<?php
require_once __DIR__ . '/../app/core/checkout.php';
startCheckoutSession();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../app/views/user/login.php', true, 302);
    exit;
}

$stallId = filter_var($_GET['stallId'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$stallId) {
    header('Location: stalls-directory.php', true, 302);
    exit;
}

$checkoutError = null;
try {
    require __DIR__ . '/../app/core/db.php';
    $userId = checkoutPositiveInteger($_SESSION['user_id'], 'User ID');
    $user = getCheckoutUser($pdo, $userId);
    if (!$user || $user['acc_status'] !== 'active') {
        header('Location: ../app/views/user/login.php', true, 302);
        exit;
    }
    $stall = getCheckoutStall($pdo, $stallId);
    if (!$stall) {
        header('Location: stalls-directory.php', true, 302);
        exit;
    }
    $cart = getUserCart($pdo, $userId, $stallId);
    if (!$cart['items']) {
        throw new CheckoutValidationException('Your cart is empty.');
    }
    [$items, $total] = priceCheckoutItems($pdo, $stallId, validateCheckoutItems($cart['items']));
    $stallDetails = getCheckoutStallDetails($pdo, $stall);
    $pickupTime = $_GET['pickupTime'] ?? 'now';
    $checkoutData = [
        'stall' => $stallDetails,
        'items' => array_map(static fn(array $item): array => [
            'name' => $item['item_name'],
            'image' => $item['image_path'],
            'quantity' => $item['quantity'],
            'total' => $item['total_price'],
            'options' => array_map(static fn(array $option): array => [
                'groupName' => $option['option_group_name'],
                'optionName' => $option['option_name'],
            ], $item['options']),
        ], $items),
        'total' => checkoutMoney($total),
        'pickupTime' => in_array($pickupTime, $stallDetails['pickupTimes'], true) ? $pickupTime : 'now',
    ];
    $requestId = bin2hex(random_bytes(16));
} catch (CheckoutValidationException $error) {
    http_response_code(422);
    $checkoutError = $error->getMessage();
} catch (Throwable $error) {
    http_response_code(500);
    error_log('Unable to load checkout: ' . $error->getMessage());
    $checkoutError = 'Unable to load checkout. Please try again.';
}

$csrfToken = checkoutCsrfToken();
$assetBase = '../';
$userPageBase = '../app/views/user/';
define('GREENDASH_CHECKOUT_VIEW', true);
require __DIR__ . '/../app/views/user/checkout.php';
