<?php
require __DIR__ . '/_bootstrap.php';
try {
    vendorRequireMethod('GET');
    $vendorOrdersData = ['orders' => [], 'csrfToken' => vendorCsrfToken()];
    $vendorStalls = getVendorStalls($pdo, $vendorId);
    $vendorPage = 'pickup';
    define('GREENDASH_VENDOR_ORDERS_VIEW', true);
    require __DIR__ . '/../app/views/vendor/pickup.php';
} catch (Throwable $error) {
    vendorControllerError($error);
}
