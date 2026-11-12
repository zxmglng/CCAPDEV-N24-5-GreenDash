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
        if (!($vendorJsonRequest ?? false)) {
            $target = '../app/views/vendor/login.php?redirect='
                . rawurlencode('../../../controller-vendor/' . basename($_SERVER['SCRIPT_NAME']));
            header('Location: ' . $target, true, 302);
            exit;
        }
        throw new VendorRequestException('Please sign in again.', 401);
    }
    $vendorId = vendorPositiveId($_SESSION['user_id']);
    require __DIR__ . '/../app/core/db.php';
    $vendor = requireVendorAccount($pdo, $vendorId);
} catch (Throwable $error) {
    vendorControllerError($error, $vendorJsonRequest ?? false);
}
