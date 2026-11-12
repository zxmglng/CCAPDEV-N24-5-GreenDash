<?php
require __DIR__ . '/_bootstrap.php';
try {
    vendorRequireMethod('GET');
    $stallId = isset($_GET['stall']) ? (int) $_GET['stall'] : 0;
    if ($stallId <= 0) {
        header('Location: manage-menu.php', true, 302);
        exit;
    }
    $stall = getCheckoutStall($pdo, $stallId);
    if (!$stall || (int) $stall['vendor_id'] !== $vendorId) {
        header('Location: manage-menu.php', true, 302);
        exit;
    }
    $vendorStalls = getVendorStalls($pdo, $vendorId);
    $stallHoursText = get_hours_text($stallId);
    $vendorPage = 'manage-store';
    define('GREENDASH_VENDOR_MANAGE_STORE_VIEW', true);
    require __DIR__ . '/../app/views/vendor/manage-store.php';
} catch (Throwable $error) {
    vendorControllerError($error);
}