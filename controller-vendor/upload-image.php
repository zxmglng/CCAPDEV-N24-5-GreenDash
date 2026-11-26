<?php
$vendorJsonRequest = true;
require __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../app/core/image-upload.php';

header('Content-Type: application/json; charset=utf-8');

function uploadRespond(int $status, array $body): void
{
    http_response_code($status);
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    exit;
}

try {
    vendorRequireMethod('POST');
    requireVendorCsrfToken($_POST['csrfToken'] ?? null);

    $stallId = vendorPositiveId($_POST['stallId'] ?? null, 'stall ID');
    requireVendorOwnedStall($pdo, $vendorId, $stallId);
    $path = storeUploadedRasterImage(
        $_FILES['image'] ?? null,
        RASTER_UPLOAD_EXTENSIONS,
        5 * 1024 * 1024,
        'assets/uploads/products/' . $stallId
    );

    uploadRespond(200, ['success' => true, 'path' => $path]);
} catch (ImageUploadValidationException $error) {
    uploadRespond(422, ['success' => false, 'message' => $error->getMessage()]);
} catch (VendorRequestException $error) {
    uploadRespond($error->getCode() ?: 400, ['success' => false, 'message' => $error->getMessage()]);
} catch (Throwable $error) {
    error_log('Image upload failed: ' . $error->getMessage());
    uploadRespond(500, ['success' => false, 'message' => 'Unable to save the image. Please try again.']);
}
