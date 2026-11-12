<?php
require __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../app/models/store-request-model.php';
require_once __DIR__ . '/../app/models/stall-hours-model.php';

$error = '';
$submitted = isset($_GET['submitted']) && $_GET['submitted'] === '1';
$csrfToken = vendorCsrfToken();

if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'], true)) {
    header('Allow: GET, POST');
    http_response_code(405);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        requireVendorCsrfToken($_POST['csrfToken'] ?? null);

        $name = trim((string) ($_POST['storeName'] ?? ''));
        $location = trim((string) ($_POST['location'] ?? ''));
        $storeType = trim((string) ($_POST['storeType'] ?? ''));
        $opens = trim((string) ($_POST['opensAt'] ?? ''));
        $closes = trim((string) ($_POST['closesAt'] ?? ''));
        $days = $_POST['days'] ?? [];

        if ($name === '' || mb_strlen($name, 'UTF-8') > 40) {
            throw new VendorRequestException('Store name must be 1–40 characters.', 422);
        }
        if ($location === '' || mb_strlen($location, 'UTF-8') > 100) {
            throw new VendorRequestException('Please choose a location.', 422);
        }
        $allowedTypes = ['Snacks', 'Food stall', 'Restaurant'];
        if (!in_array($storeType, $allowedTypes, true)) {
            throw new VendorRequestException('Please choose a store type.', 422);
        }
        $timePattern = '/^([01]\d|2[0-3]):[0-5]\d$/';
        if (!preg_match($timePattern, $opens) || !preg_match($timePattern, $closes)) {
            throw new VendorRequestException('Please choose opening and closing times.', 422);
        }
        if ($opens >= $closes) {
            throw new VendorRequestException('Opening time must be earlier than closing time.', 422);
        }

        $dayMap = ['Mon' => 1, 'Tue' => 2, 'Wed' => 3, 'Thu' => 4, 'Fri' => 5, 'Sat' => 6, 'Sun' => 0];
        $selectedDays = [];
        if (is_array($days)) {
            foreach ($days as $d) {
                if (isset($dayMap[$d])) {
                    $selectedDays[] = $dayMap[$d];
                }
            }
        }
        if (empty($selectedDays)) {
            throw new VendorRequestException('Please pick at least one operating day.', 422);
        }

        // Logo upload — required, PNG only, up to 2 MB.
        if (!isset($_FILES['storeLogo']) || $_FILES['storeLogo']['error'] !== UPLOAD_ERR_OK) {
            throw new VendorRequestException('Please upload a PNG logo.', 422);
        }
        $file = $_FILES['storeLogo'];
        if ($file['size'] > 2 * 1024 * 1024) {
            throw new VendorRequestException('Logo is too large. Maximum 2 MB.', 422);
        }
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if ($mime !== 'image/png') {
            throw new VendorRequestException('Logo must be a PNG file.', 422);
        }

        $pendingDir = __DIR__ . '/../assets/stalls/pending';
        if (!is_dir($pendingDir)) {
            mkdir($pendingDir, 0775, true);
        }
        $filename = bin2hex(random_bytes(16)) . '.png';
        if (!move_uploaded_file($file['tmp_name'], $pendingDir . '/' . $filename)) {
            throw new VendorRequestException('Unable to save the logo. Please try again.', 500);
        }

        $hoursRows = array_map(
            fn(int $d) => ['day_of_week' => $d, 'opens_at' => $opens . ':00', 'closes_at' => $closes . ':00'],
            $selectedDays
        );
        $hoursText = format_hours_text($hoursRows);

        create_store_request($pdo, $vendorId, $name, $location, $hoursText, [
            'store_type' => $storeType,
            'logo_path' => 'assets/stalls/pending/' . $filename,
        ]);

        header('Location: request-store.php?submitted=1', true, 303);
        exit;

    } catch (VendorRequestException $e) {
        $error = $e->getMessage();
    } catch (Throwable $e) {
        error_log('Store request failed: ' . $e->getMessage());
        $error = 'Unable to submit your request. Please try again.';
    }
}

try {
    $vendorStalls = getVendorStalls($pdo, $vendorId);
    $vendorPage = 'request-store';
    define('GREENDASH_VENDOR_REQUEST_STORE_VIEW', true);
    require __DIR__ . '/../app/views/vendor/request-store.php';
} catch (Throwable $e) {
    vendorControllerError($e);
}