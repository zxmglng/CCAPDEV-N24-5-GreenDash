<?php
require_once __DIR__ . '/../app/core/admin-auth.php';
require_once __DIR__ . '/../app/core/db.php';
require_once __DIR__ . '/../app/models/user-model.php';

startAdminSession();

if (isAdminLoggedIn()) {
    header('Location: home.php', true, 303);
    exit;
}

$signupError = '';
$old = ['email' => '', 'first_name' => '', 'last_name' => ''];
$requestMethod = $_SERVER['REQUEST_METHOD'];

if ($requestMethod === 'POST') {
    $firstName = is_string($_POST['first_name'] ?? null) ? trim($_POST['first_name']) : '';
    $lastName = is_string($_POST['last_name'] ?? null) ? trim($_POST['last_name']) : '';
    $email = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    $confirm = is_string($_POST['confirmPassword'] ?? null) ? $_POST['confirmPassword'] : '';
    $adminKey = is_string($_POST['adminKey'] ?? null) ? trim($_POST['adminKey']) : '';

    $old['email'] = $email;
    $old['first_name'] = $firstName;
    $old['last_name'] = $lastName;

    if (!isAdminCsrfTokenValid($_POST['csrfToken'] ?? null)) {
        http_response_code(403);
        $signupError = 'Your session expired. Please try again.';
    } elseif ($firstName === '' || $lastName === '') {
        $signupError = 'Please enter your first and last name.';
    } elseif ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $signupError = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $signupError = 'Password must be at least 8 characters long.';
    } elseif (!hash_equals($password, $confirm)) {
        $signupError = 'Passwords do not match.';
    } elseif ($adminKey === '') {
        $signupError = 'Please enter the admin key.';
    } elseif (!isAdminKeyValid($adminKey)) {
        $signupError = 'Incorrect admin key. Please try again.';
    } elseif (find_user_by_email($email) !== null) {
        $signupError = 'An account with that email already exists.';
    } else {
        $userId = createAdminUser($pdo, $firstName, $lastName, $email, $password);

        session_regenerate_id(true);
        $_SESSION['admin_authenticated'] = true;
        $_SESSION['admin_last_activity'] = time();
        $_SESSION['admin_name'] = $firstName . ' ' . $lastName;
        $_SESSION['admin_user_id'] = $userId;
        unset($_SESSION['admin_csrf_token']);
        header('Location: home.php', true, 303);
        exit;
    } 
} elseif ($requestMethod === 'GET') {
    header('Allow: GEET, POST');
    http_response_code(405);
    exit;
}

$csrfToken = adminCsrfToken();
define('GREENDASH_ADMIN_VIEW', true);
require __DIR__ . '/../app/views/admin/signup.php';