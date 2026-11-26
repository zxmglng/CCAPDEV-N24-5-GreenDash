<?php

require_once __DIR__ . '/../app/core/session.php';
require_once __DIR__ . '/../app/core/user-csrf.php';
startGreenDashSession();
header('Cache-Control: no-store');

function sendProfileResponse(int $status, array $body): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($body, JSON_THROW_ON_ERROR);
    exit;
}

$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
if (!isset($_SESSION['user_id'])) {
    if ($isPost) {
        sendProfileResponse(401, ['success' => false, 'message' => 'Sign in again to update your profile.']);
    }
    header('Location: ../index.php');
    exit;
}

if ($isPost && !isUserCsrfTokenValid($_POST['csrfToken'] ?? null)) {
    sendProfileResponse(403, ['success' => false, 'message' => 'Reload your profile page and try again.']);
}

require_once __DIR__ . '/../app/core/db.php';
require_once __DIR__ . '/../app/models/user-model.php';
require_once __DIR__ . '/../app/models/order-model.php';

$userId = (int) $_SESSION['user_id'];
$user = getUserById($pdo, $userId);
if (!$user || $user['acc_status'] !== 'active') {
    if ($isPost) {
        sendProfileResponse(403, ['success' => false, 'message' => 'This account cannot update its profile.']);
    }
    header('Location: ../index.php');
    exit;
}

if ($isPost) {
    require_once __DIR__ . '/../app/core/image-upload.php';

    if (($_POST['profile_image'] ?? '') !== '' || isset($_POST['profileImage'])) {
        sendProfileResponse(422, ['success' => false, 'message' => 'Choose a profile image file to upload.']);
    }

    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $contactNum = $_POST['contact_num'] ?? '';
    if (!is_string($firstName) || !is_string($lastName) || !is_string($contactNum)) {
        sendProfileResponse(422, ['success' => false, 'message' => 'Enter valid profile fields.']);
    }

    $storedPath = null;
    try {
        $file = $_FILES['profileImage'] ?? null;
        if ($file !== null && (!is_array($file) || ($file['error'] ?? null) !== UPLOAD_ERR_NO_FILE)) {
            $storedPath = storeUploadedRasterImage(
                $file,
                RASTER_UPLOAD_EXTENSIONS,
                2 * 1024 * 1024,
                'assets/uploads/profiles',
                2048,
                4_000_000
            );
        }

        $profileImage = $storedPath === null ? null : '../' . $storedPath;
        $contactNum = trim($contactNum);
        if (!updateUserProfile(
            $pdo,
            $userId,
            trim($firstName),
            trim($lastName),
            $contactNum !== '' ? $contactNum : null,
            $profileImage
        )) {
            throw new RuntimeException('Profile update returned false.');
        }
    } catch (ImageUploadValidationException $error) {
        sendProfileResponse(422, ['success' => false, 'message' => $error->getMessage()]);
    } catch (Throwable $error) {
        if ($storedPath !== null && !@unlink(dirname(__DIR__) . '/' . $storedPath)) {
            error_log('Failed to remove a profile image after its save failed.');
        }
        error_log('Profile update failed: ' . $error->getMessage());
        sendProfileResponse(500, ['success' => false, 'message' => 'Unable to save your profile. Try again.']);
    }

    sendProfileResponse(200, ['success' => true, 'profileImage' => $profileImage]);
}

$totalOrders = getUserCompletedOrderCount($pdo, $userId);
$totalSpent = getUserTotalSpent($pdo, $userId);
$favoriteStall = getUserFavoriteStall($pdo, $userId);
$monthlyOrders = getUserMonthlyOrderCounts($pdo, $userId);
$csrfToken = userCsrfToken();
$assetBase = '../';
$userPageBase = '../app/views/user/';

require __DIR__ . '/../app/views/user/profile.php';
