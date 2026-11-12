<?php
require_once __DIR__ . '/../models/user-model.php';

class VendorRequestException extends RuntimeException {}

function startVendorSession(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    header('Cache-Control: no-store');
}

function vendorPositiveId($value): int
{
    if ((!is_string($value) && !is_int($value))
        || !preg_match('/^[1-9][0-9]*$/D', (string) $value)
        || filter_var($value, FILTER_VALIDATE_INT) === false) {
        throw new VendorRequestException('Invalid order ID.', 422);
    }
    return (int) $value;
}

function requireVendorAccount(PDO $pdo, int $vendorId): array
{
    $vendor = getUserById($pdo, $vendorId);
    if (!$vendor || $vendor['acc_status'] !== 'active' || $vendor['user_role'] !== 'vendor') {
        throw new VendorRequestException('An active vendor account is required. Please contact your administrator.', 403);
    }
    return $vendor;
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
