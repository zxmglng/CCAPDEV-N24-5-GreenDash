<?php
$vendorJsonRequest = true;
require __DIR__ . '/_bootstrap.php';

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

    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        uploadRespond(422, ['success' => false, 'message' => 'No file was uploaded.']);
    }
    $file = $_FILES['image'];
    if ($file['size'] > 5 * 1024 * 1024) {
        uploadRespond(422, ['success' => false, 'message' => 'File is too large. Maximum 5 MB.']);
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];
    if (!isset($allowed[$mime])) {
        uploadRespond(422, ['success' => false, 'message' => 'Only JPG, PNG, WEBP, or GIF images are allowed.']);
    }

    $ext = $allowed[$mime];
    $dir = __DIR__ . '/../assets/uploads/products';
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $dest = $dir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        uploadRespond(500, ['success' => false, 'message' => 'Unable to save the image.']);
    }

    uploadRespond(200, ['success' => true, 'path' => 'assets/uploads/products/' . $filename]);

} catch (VendorRequestException $error) {
    uploadRespond($error->getCode() ?: 400, ['success' => false, 'message' => $error->getMessage()]);
} catch (Throwable $error) {
    error_log('Image upload failed: ' . $error->getMessage());
    uploadRespond(500, ['success' => false, 'message' => 'Unable to save the image. Please try again.']);
}