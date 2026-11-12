<?php
require __DIR__ . '/_bootstrap.php';
try {
    vendorRequireMethod('GET');
    $vendorStalls = getVendorStalls($pdo, $vendorId);
    $vendorPage = 'request-store';
    define('GREENDASH_VENDOR_REQUEST_STORE_VIEW', true);
    require __DIR__ . '/../app/views/vendor/request-store.php';
} catch (Throwable $error) {
    vendorControllerError($error);
}