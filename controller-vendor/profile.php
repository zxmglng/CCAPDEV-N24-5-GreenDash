<?php
require __DIR__ . '/_bootstrap.php';
try {
    vendorRequireMethod('GET');
    $vendorStalls = getVendorStalls($pdo, $vendorId);
    $vendorPage = 'profile';
    define('GREENDASH_VENDOR_PROFILE_VIEW', true);
    require __DIR__ . '/../app/views/vendor/profile.php';
} catch (Throwable $error) {
    vendorControllerError($error);
}