<?php

const RASTER_UPLOAD_EXTENSIONS = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
    'image/gif' => 'gif',
];

class ImageUploadValidationException extends InvalidArgumentException {}

function storeUploadedRasterImage(
    mixed $file,
    array $mimeExtensions,
    int $maxBytes,
    string $relativeDirectory,
    int $maxEdge = 4096,
    int $maxPixels = 8000000
): string {
    $error = is_array($file) ? ($file['error'] ?? null) : null;
    $tooLarge = 'Image is too large. Maximum ' . ($maxBytes / 1048576) . ' MB.';
    if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
        throw new ImageUploadValidationException($tooLarge);
    }

    $temporaryPath = is_array($file) ? ($file['tmp_name'] ?? null) : null;
    if ($error !== UPLOAD_ERR_OK || !is_string($temporaryPath) || !is_uploaded_file($temporaryPath)) {
        throw new ImageUploadValidationException('Please upload an image file.');
    }

    $bytes = file_get_contents($temporaryPath, false, null, 0, $maxBytes + 1);
    if ($bytes === false) {
        throw new RuntimeException('Could not read uploaded image.');
    }
    if (strlen($bytes) > $maxBytes) {
        throw new ImageUploadValidationException($tooLarge);
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->buffer($bytes);
    if (!isset($mimeExtensions[$mime])) {
        $types = implode(', ', array_map('strtoupper', $mimeExtensions));
        throw new ImageUploadValidationException('Allowed image types: ' . $types . '.');
    }

    // Check the declared dimensions before allocating memory to decode the raster.
    $dimensions = @getimagesizefromstring($bytes);
    if ($dimensions === false || ($dimensions['mime'] ?? '') !== $mime || $dimensions[0] < 1 || $dimensions[1] < 1) {
        throw new ImageUploadValidationException('Could not read the image. Choose a valid image file.');
    }
    if ($dimensions[0] > $maxEdge || $dimensions[1] > $maxEdge || $dimensions[0] * $dimensions[1] > $maxPixels) {
        throw new ImageUploadValidationException(
            'Image dimensions must be at most ' . $maxEdge . ' pixels per side and '
            . number_format($maxPixels) . ' total pixels.'
        );
    }

    if (!function_exists('imagecreatefromstring')) {
        throw new RuntimeException('The PHP GD extension is required for image uploads.');
    }
    // GD can return an image alongside a warning for a damaged upload.
    $decodeWarning = false;
    set_error_handler(static function () use (&$decodeWarning): bool {
        $decodeWarning = true;
        return true;
    }, E_WARNING | E_NOTICE);
    try {
        $decoded = imagecreatefromstring($bytes);
    } finally {
        restore_error_handler();
    }
    if ($decoded === false || $decodeWarning) {
        throw new ImageUploadValidationException('Could not decode the image. Choose a valid image file.');
    }
    $width = imagesx($decoded);
    $height = imagesy($decoded);
    if ($width > $maxEdge || $height > $maxEdge || $width * $height > $maxPixels) {
        throw new ImageUploadValidationException('The decoded image exceeds the allowed dimensions.');
    }
    unset($decoded, $bytes);

    $directory = dirname(__DIR__, 2) . '/' . $relativeDirectory;
    if (!is_dir($directory) && !@mkdir($directory, 0775, true) && !is_dir($directory)) {
        throw new RuntimeException('Could not create the upload directory.');
    }
    $filename = bin2hex(random_bytes(16)) . '.' . $mimeExtensions[$mime];
    if (!@move_uploaded_file($temporaryPath, $directory . '/' . $filename)) {
        throw new RuntimeException('Could not save uploaded image.');
    }
    return $relativeDirectory . '/' . $filename;
}
