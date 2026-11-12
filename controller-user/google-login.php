<?php
session_start();
header('Content-Type: application/json');

require_once(__DIR__ . "/../app/core/db.php");
require_once(__DIR__ . "/../app/models/user-model.php");
require_once(__DIR__ . "/../vendor/autoload.php");

// only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed.'
    ]);
    exit;
}

// get google credential from request body
$data = json_decode(file_get_contents('php://input'), true);
$credential = $data['credential'] ?? '';

if ($credential === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Missing Google credential.'
    ]);
    exit;
}

$googleClientId = '480778889925-f2akgk8hkv2b6rtoqhc9laag22l21irc.apps.googleusercontent.com';

// verify google ID token
$client = new Google\Client([
    'client_id' => $googleClientId
]);

$googleUser = $client->verifyIdToken($credential);

if (!$googleUser) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid Google credential.'
    ]);
    exit;
}

// verify that the token belongs to correct google app
if (($googleUser['aud'] ?? '') !== $googleClientId) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid Google client.'
    ]);
    exit;
}

// only allows DLSU google acc
if (($googleUser['hd'] ?? '') !== 'dlsu.edu.ph') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Only DLSU accounts are allowed.'
    ]);
    exit;
}

$email = $googleUser['email'] ?? '';
$firstName = $googleUser['given_name'] ?? '';
$lastName = $googleUser['family_name'] ?? '';
$profileImage = $googleUser['picture'] ?? '';

if ($email === '' || $firstName === '' || $lastName === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Incomplete Google account information.'
    ]);
    exit;
}

try {
    $user = getUserByEmail($pdo, $email); // already an existing user?
    if (!$user) {
        $userId = createGoogleUser($pdo, $email, $firstName, $lastName, $profileImage !== '' ? $profileImage : null);
        // get the newly created user including user_role
        $user = getUserById($pdo, $userId);
    }

    // stop inactive users from logging in
    if ($user['acc_status'] === 'inactive') {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'This account is inactive.'
        ]);
        exit;
    }

    $userId = $user['user_id'];
    $userRole = $user['user_role'];

    // identify the logged in user using PHP session
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_role'] = $userRole;

    // user still needs to choose student, faculty, or staff
    if ($userRole === null) {
        echo json_encode([
            'success' => true,
            'needs_role' => true,
            'user_id' => $userId,
            'user_role' => null
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'needs_role' => false,
        'user_id' => $userId,
        'user_role' => $userRole
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error. '
    ]);
}
?>