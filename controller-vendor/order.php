<?php
$vendorJsonRequest = true;
require __DIR__ . '/_bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
try {
    vendorRequireMethod('GET');
    $orderId = isset($_GET['orderId']) ? vendorPositiveId($_GET['orderId']) : null;
    $orderNumber = $_GET['orderNumber'] ?? null;
    if ($orderId === null) {
        if (!is_string($orderNumber) || trim($orderNumber) === '' || strlen(trim($orderNumber)) > 20) {
            throw new VendorRequestException('Enter a valid order ID from the customer’s Orders page.', 422);
        }
        $orderNumber = trim($orderNumber);
    } else {
        $orderNumber = null;
    }
    $orders = loadVendorOrders($pdo, $vendorId, [], $orderId, $orderNumber);
    if (!$orders) {
        throw new VendorRequestException('Order not found for your stores. Check the order ID.', 404);
    }
    echo json_encode(['success' => true, 'order' => $orders[0]], JSON_THROW_ON_ERROR);
} catch (Throwable $error) {
    vendorControllerError($error, true);
}
