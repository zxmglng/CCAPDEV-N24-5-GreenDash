<?php
$vendorJsonRequest = true;
require __DIR__ . '/_bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
try {
    vendorRequireMethod('POST');
    requireVendorCsrfToken($_POST['csrfToken'] ?? null);
    $orderId = vendorPositiveId($_POST['orderId'] ?? null);
    $status = $_POST['status'] ?? null;
    $expectedStatus = $_POST['expectedStatus'] ?? null;
    if (!is_string($status) || !is_string($expectedStatus)) {
        throw new VendorRequestException('Choose a valid order status.', 422);
    }
    $order = changeVendorOrderStatus($pdo, $vendorId, $orderId, $expectedStatus, $status,
        ($_POST['paymentConfirmed'] ?? '') === '1');
    echo json_encode(['success' => true, 'order' => $order], JSON_THROW_ON_ERROR);
} catch (Throwable $error) {
    vendorControllerError($error, true);
}
