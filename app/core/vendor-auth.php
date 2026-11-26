<?php
require_once __DIR__ . '/../models/user-model.php';
require_once __DIR__ . '/../models/stall-model.php';
require_once __DIR__ . '/session.php';

class VendorRequestException extends RuntimeException {}

function startVendorSession(): void
{
    startGreenDashSession();
    header('Cache-Control: no-store');
}

function vendorPositiveId($value, string $label = 'order ID'): int
{
    if ((!is_string($value) && !is_int($value))
        || !preg_match('/^[1-9][0-9]*$/D', (string) $value)
        || filter_var($value, FILTER_VALIDATE_INT) === false) {
        throw new VendorRequestException('Invalid ' . $label . '.', 422);
    }
    return (int) $value;
}

function requireVendorAccount(PDO $pdo, int $vendorId): array
{
    $vendor = getUserById($pdo, $vendorId);
    if (!$vendor || $vendor['user_role'] !== 'vendor') {
        throw new VendorRequestException('An active vendor account is required. Please contact your administrator.', 403);
    }

    // Pending vendors can browse the vendor area, but cannot submit any form actions.
    if ($vendor['acc_status'] !== 'active') {
        throw new VendorRequestException('Please wait for the admin to activate your account.', 403);
    }
    return $vendor;
}

function requireVendorOwnedStall(PDO $pdo, int $vendorId, int $stallId): void
{
    foreach (getVendorStalls($pdo, $vendorId) as $stall) {
        if ((int) $stall['id'] === $stallId) {
            return;
        }
    }
    throw new VendorRequestException('Stall not found for this vendor.', 404);
}

function vendorCsrfToken(): string
{
    if (!isset($_SESSION['vendor_csrf_token'])) {
        $_SESSION['vendor_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['vendor_csrf_token'];
}

function requireVendorCsrfToken($token): void
{
    if (!is_string($token) || !isset($_SESSION['vendor_csrf_token'])
        || !hash_equals($_SESSION['vendor_csrf_token'], $token)) {
        throw new VendorRequestException('Your session expired. Please reload the page.', 403);
    }
}
