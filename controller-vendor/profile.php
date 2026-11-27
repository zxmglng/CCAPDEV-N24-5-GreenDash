<?php
require __DIR__ . '/_bootstrap.php';
try {

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        header('Allow: GET');
        throw new VendorRequestException('Method not allowed.', 405);
    }

    /* Initialize the password change error message */
    $vendorStalls = getVendorStalls($pdo, $vendorId);
    $vendorPage = 'profile';
    define('GREENDASH_VENDOR_PROFILE_VIEW', true);
    require __DIR__ . '/../app/views/vendor/profile.php';
} catch (Throwable $error) {
    vendorControllerError($error);
}
