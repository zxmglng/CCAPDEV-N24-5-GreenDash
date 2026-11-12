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
    $vendorPage = 'manage-menu';
    define('GREENDASH_VENDOR_MANAGE_MENU_VIEW', true);
    require __DIR__ . '/../app/views/vendor/manage-menu.php';
} catch (Throwable $error) {
    vendorControllerError($error);
}