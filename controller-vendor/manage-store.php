<?php
require __DIR__ . '/_bootstrap.php';
try {
    vendorRequireMethod('GET');
    $vendorStalls = getVendorStalls($pdo, $vendorId);
    foreach ($vendorStalls as &$s) {
        $details = getCheckoutStall($pdo, (int) $s['id']);
        $s['image_path'] = $details['image_path'] ?? null;
        $s['is_open'] = (bool) ($details['is_open'] ?? false);
    }
    unset($s);
    $vendorPage = 'manage-store';
    define('GREENDASH_VENDOR_MANAGE_STORE_VIEW', true);
    require __DIR__ . '/../app/views/vendor/manage-store.php';
} catch (Throwable $error) {
    vendorControllerError($error);
}