<?php
require_once __DIR__ . '/../app/core/vendor-orders.php';
startVendorSession();

function vendorControllerError(Throwable $error, bool $json = false): void
{
    if ($error instanceof VendorRequestException) {
        http_response_code($error->getCode());
        $message = $error->getMessage();
    } else {
        error_log('Vendor orders failed: ' . $error->getMessage());
        http_response_code(500);
        $message = 'Unable to load or update orders. Please try again.';
    }
    if ($json) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => $message]);
    } else {
        header('Content-Type: text/plain; charset=utf-8');
        echo $message;
    }
    exit;
}

function vendorRequireMethod(string $method): void
{
    if ($_SERVER['REQUEST_METHOD'] !== $method) {
        header('Allow: ' . $method);
        throw new VendorRequestException('Method not allowed.', 405);
    }
}

try {
    if (!isset($_SESSION['user_id'])) {
        if (!($vendorJsonRequest ?? false)) {   /* Redirect to login page if not a JSON request */
            $target = 'login.php?redirect='
                . rawurlencode('../controller-vendor/' . basename($_SERVER['SCRIPT_NAME']));
            header('Location: ' . $target, true, 302);
            exit;
        }
        throw new VendorRequestException('Please sign in again.', 401);
    }
    $vendorId = vendorPositiveId($_SESSION['user_id']);
    require __DIR__ . '/../app/core/db.php';
    $vendor = getUserById($pdo, $vendorId);
    if (!$vendor || $vendor['user_role'] !== 'vendor') {
        throw new VendorRequestException('An active vendor account is required. Please contact your administrator.', 403);
    }
    if ($vendor['acc_status'] === 'inactive') {
        throw new VendorRequestException('This vendor account is inactive. Please contact your administrator.', 403);
    }
    /* Pending vendors can browse the vendor area and change their password, but cannot submit store actions. */
    
    /* Check if the current request is a password change request */
    /* basename(...) strips off the directory path and leaves the filename */
    $isPasswordChange = basename($_SERVER['SCRIPT_NAME'] ?? '') === 'profile.php' /* check if this is the profile.php page */ 
        && $_SERVER['REQUEST_METHOD'] === 'POST';   /* check if this is a POST request */

    /* If the vendor account is pending, only allow GET requests and password change requests */
    if ($vendor['acc_status'] === 'pending' && $_SERVER['REQUEST_METHOD'] !== 'GET' && !$isPasswordChange) {
        throw new VendorRequestException('Please wait for the admin to activate your account.', 403);
    }
    /* Check if the vendor account has a valid status */
    if (($vendor['acc_status'] !== 'active' && $vendor['acc_status'] !== 'pending' || $vendor['acc_status'] !== 'inactive')) {
        throw new VendorRequestException('An active vendor account is required. Please contact your administrator.', 403);
    }
} catch (Throwable $error) {
    vendorControllerError($error, $vendorJsonRequest ?? false);
}
