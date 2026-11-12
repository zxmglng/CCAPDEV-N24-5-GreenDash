// compared to orders.php, this controller is for returning a single order in JSON format. 
// it is used by the vendor's order details page to fetch the order data via AJAX.
// it is not used for the main orders page, which is handled by orders.php and returns HTML.

<?php
$vendorJsonRequest = true;
require __DIR__ . '/_bootstrap.php';

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
