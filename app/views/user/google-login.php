<?php
session_start();
header('Content-Type: application/json');

require __DIR__ . '/../../core/db.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$credential = $data['credential'] ?? '';

if ($credential === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing Google credential.']);
    exit;
}
$googleClientId = '480778889925-f2akgk8hkv2b6rtoqhc9laag22l21irc.apps.googleusercontent.com';

/* verifies the Google ID token using the Google PHP Client */
$client = new Google\Client(['client_id' => $googleClientId]);
$googleUser = $client->verifyIdToken($credential);

if (!$googleUser) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid Google credential.']);
    exit;
}

/* verifies that this token belongs to Google app */
if (($googleUser['aud'] ?? '') !== $googleClientId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid Google client.']);
    exit;
}

/* verifies that it's a DLSU Google account */
if (($googleUser['hd'] ?? '') !== 'dlsu.edu.ph') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Only DLSU accounts are allowed.']);
    exit;
}

$email = $googleUser['email'] ?? '';
$firstName = $googleUser['given_name'] ?? '';
$lastName = $googleUser['family_name'] ?? '';
$profileImage = $googleUser['picture'] ?? '';

if ($email === '' || $firstName === '' || $lastName === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Incomplete Google account information.']);
    exit;
}

try {
    /* check if the Google account alr has a user */
    $stmt = $pdo->prepare(
        'SELECT user_id, user_role, acc_status
         FROM users
         WHERE email_add = ?'
    );
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    /* first time logging in create the user automatically */
    if (!$user) {
        $stmt = $pdo->prepare(
            'INSERT INTO users (email_add, first_name, last_name, profile_image)
             VALUES (?, ?, ?, ?)'
        );

        $stmt->execute([$email, $firstName, $lastName, $profileImage !== '' ? $profileImage : null]);
        $userId = $pdo->lastInsertId();
        $userRole = 'student';
    }
    else {
        /* this stops inactive users from logging in */
        if ($user['acc_status'] === 'inactive') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'This account is inactive.']);
            exit;
        }
        $userId = $user['user_id'];
        $userRole = $user['user_role'];
    }

    /* identifies the logged in user using PHP session */
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_role'] = $userRole;

    echo json_encode(['success' => true, 'user_id' => $userId, 'user_role' => $userRole]);
}
catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}
?>