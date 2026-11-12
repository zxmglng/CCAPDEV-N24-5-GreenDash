<?php
require __DIR__ . '/_bootstrap.php';
try {
    vendorRequireMethod('GET');
    $vendorOrdersData = [
        'orders' => loadVendorOrders($pdo, $vendorId, ['pending', 'preparing', 'ready']),
        'csrfToken' => vendorCsrfToken(),
    ];
    $vendorStalls = getVendorStalls($pdo, $vendorId);
    $vendorPage = 'orders';
    define('GREENDASH_VENDOR_ORDERS_VIEW', true);
    require __DIR__ . '/../app/views/vendor/orders.php';
} catch (Throwable $error) {
    vendorControllerError($error);
}
