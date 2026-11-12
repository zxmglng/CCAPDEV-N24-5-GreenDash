<?php
require __DIR__ . '/_bootstrap.php';
try {
    vendorRequireMethod('GET');
    $vendorOrdersData = [
        'orders' => loadVendorOrders($pdo, $vendorId, ['completed', 'cancelled']),
        'csrfToken' => vendorCsrfToken(),
    ];
    $vendorStalls = getVendorStalls($pdo, $vendorId);
    $vendorPage = 'history';
    define('GREENDASH_VENDOR_ORDERS_VIEW', true);
    require __DIR__ . '/../app/views/vendor/transaction-history.php';
} catch (Throwable $error) {
    vendorControllerError($error);
}
