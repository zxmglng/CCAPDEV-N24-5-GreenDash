<?php

require_once __DIR__ . '/../app/core/session.php';
require_once __DIR__ . '/../app/core/user-csrf.php';

function googleLoginFailure(int $status, string $message): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode(['success' => false, 'message' => $message], JSON_THROW_ON_ERROR);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    googleLoginFailure(405, 'Method not allowed.');
}
$contentType = trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '', 2)[0]);
if (strcasecmp($contentType, 'application/json') !== 0) {
    googleLoginFailure(415, 'Send sign-in data as JSON.');
}

startGreenDashSession();
try {
    $data = json_decode(file_get_contents('php://input'), false, 32, JSON_THROW_ON_ERROR);
} catch (JsonException $error) {
    googleLoginFailure(400, 'Invalid sign-in request.');
}
if (!$data instanceof stdClass) {
    googleLoginFailure(400, 'Invalid sign-in request.');
}
if (session_status() !== PHP_SESSION_ACTIVE
    || !isUserCsrfTokenValid($data->csrfToken ?? null, 'google_login_csrf_token')) {
    googleLoginFailure(403, 'Your sign-in session expired. Please try again.');
}
$credential = $data->credential ?? null;
if (!is_string($credential) || trim($credential) === '') {
    googleLoginFailure(400, 'Missing Google credential.');
}

try {
    require_once __DIR__ . '/../vendor/autoload.php';

    $googleClientId = '717424681848-6b9rh0o2u5h7gtkfljmqoinuv66ujr9m.apps.googleusercontent.com';
    $client = new Google\Client(['client_id' => $googleClientId]);
    $googleUser = $client->verifyIdToken($credential);
    if (!$googleUser) {
        googleLoginFailure(401, 'Invalid Google credential.');
    }
    if (($googleUser['aud'] ?? '') !== $googleClientId) {
        googleLoginFailure(401, 'Invalid Google client.');
    }
    if (($googleUser['hd'] ?? '') !== 'dlsu.edu.ph') {
        googleLoginFailure(403, 'Only DLSU accounts are allowed.');
    }

    $email = $googleUser['email'] ?? '';
    $firstName = $googleUser['given_name'] ?? '';
    $lastName = $googleUser['family_name'] ?? '';
    $profileImage = $googleUser['picture'] ?? '';

    if ($email === '' || $firstName === '' || $lastName === '') {
        googleLoginFailure(400, 'Incomplete Google account information.');
    }

    require_once __DIR__ . '/../app/core/db.php';
    require_once __DIR__ . '/../app/models/user-model.php';

    $user = getUserByEmail($pdo, $email);
    if (!$user) {
        $userId = createGoogleUser($pdo, $email, $firstName, $lastName, $profileImage !== '' ? $profileImage : null);
        $user = getUserById($pdo, $userId);
    }
    if (!$user || $user['acc_status'] !== 'active') {
        googleLoginFailure(403, 'This account is inactive.');
    }
    if (!session_regenerate_id(true)) {
        googleLoginFailure(500, 'Unable to establish a secure session.');
    }

    clearGreenDashUserSessionData();
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['user_email'] = $email;
    $_SESSION['user_role'] = $user['user_role'];
    $_SESSION['user_last_activity'] = time();

    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode([
        'success' => true,
        'needs_role' => $user['user_role'] === null,
        'user_id' => $user['user_id'],
        'user_role' => $user['user_role'],
    ], JSON_THROW_ON_ERROR);
} catch (Throwable $error) {
    error_log('Google sign-in failed: ' . get_class($error));
    googleLoginFailure(500, 'Unable to sign in. Please try again.');
}
