<?php

require_once __DIR__ . '/../app/core/session.php';
require_once __DIR__ . '/../app/core/user-csrf.php';

startGreenDashSession();
header('Cache-Control: no-store');

$error = '';
$emailValue = '';

// check if the request method is either GET or POST
if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'], true)) {
    header('Allow: GET, POST');
    http_response_code(405);
    exit;
}

// if the request method is POST, process the login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // validate the CSRF token to prevent cross-site request forgery
    if (!isUserCsrfTokenValid($_POST['csrfToken'] ?? null, 'vendor_login_csrf_token')) {
        http_response_code(403);
        $error = 'Your session expired. Please reload the page.';
    } else {

    // retrieve and sanitize the email and password from the POST data
        $emailValue = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');

        try {
            require_once __DIR__ . '/../app/core/db.php';
            require_once __DIR__ . '/../app/models/user-model.php';
            
            // validate the email and password inputs
            $user = filter_var($emailValue, FILTER_VALIDATE_EMAIL)
                ? getUserByEmail($pdo, $emailValue)
                : null;
            $passwordHash = $user['password_hash'] ?? null;

            // check if the user is in the database
            if (!$user) {
                throw new InvalidArgumentException('Invalid email or password. Please make sure you have an account.');
            }

            // check if the user exists, has the correct role, and if the password is valid
            if (!$user || $user['user_role'] !== 'vendor'
                || !is_string($passwordHash) || $passwordHash === ''
                || !password_verify($password, $passwordHash)) {
                throw new InvalidArgumentException('Invalid email or password.');
            }

            // check if the account status is either active or inactive
            // if the account status is neither active nor inactive, throw an exception;
            // user can still log in if the account status is inactive!
            if (!in_array($user['acc_status'], ['active', 'inactive'], true)) {
                throw new InvalidArgumentException('This account cannot sign in.');
            }

            // regenerate the session ID to prevent session fixation attacks
            if (!session_regenerate_id(true)) {
                throw new RuntimeException('Unable to establish a secure session.');
            }

            // clear any existing session data and set the user session variables
            clearGreenDashUserSessionData();
            unset($_SESSION['vendor_login_csrf_token'], $_SESSION['vendor_signup_csrf_token']);
            $_SESSION['user_id'] = (int) $user['user_id'];
            $_SESSION['user_email'] = $user['email_add'];
            $_SESSION['user_role'] = 'vendor';
            $_SESSION['user_last_activity'] = time();

            $destination = 'profile.php';

            // redirect to profile if account status is active, otherwise redirect to home.
            if ($user['acc_status'] === 'active') {
                $destination = 'orders.php';
                $requestedRedirect = $_GET['redirect'] ?? '';

                // validate the requested redirect to ensure it is a valid vendor page
                if (is_string($requestedRedirect)
                    && preg_match('~^\.\./controller-vendor/([a-z0-9-]+)\.php$~i', $requestedRedirect, $matches)
                    && $matches[1] !== 'login'
                    && is_file(__DIR__ . '/' . $matches[1] . '.php')) {
                    $destination = $matches[1] . '.php';
                }
            }
            header('Location: ' . $destination, true, 303);     // use 303 to ensure the redirect is a GET request
            exit;
        
        // catch any exceptions thrown during the login process and handle them appropriately
        } catch (InvalidArgumentException $exception) {
            http_response_code(422);
            $error = $exception->getMessage();

        }
    }
}

$csrfToken = userCsrfToken('vendor_login_csrf_token');
define('GREENDASH_VENDOR_LOGIN_VIEW', true);
require __DIR__ . '/../app/views/vendor/login.php';
