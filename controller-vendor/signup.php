<?php

require_once __DIR__ . '/../app/core/session.php';
require_once __DIR__ . '/../app/core/user-csrf.php';

startGreenDashSession();
header('Cache-Control: no-store');

$error = '';
$firstNameValue = '';
$lastNameValue = '';
$emailValue = '';

if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'], true)) {
    header('Allow: GET, POST');
    http_response_code(405);    // Method Not Allowed
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isUserCsrfTokenValid($_POST['csrfToken'] ?? null, 'vendor_signup_csrf_token')) {
        http_response_code(403);    // Forbidden
        $error = 'Your session expired. Please reload the page.';
    } else {
        $firstNameValue = trim((string) ($_POST['first_name'] ?? ''));
        $lastNameValue = trim((string) ($_POST['last_name'] ?? ''));
        $emailValue = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

        // try to create a new vendor account
        try {
            if ($firstNameValue === '' || mb_strlen($firstNameValue, 'UTF-8') > 100
                || $lastNameValue === '' || mb_strlen($lastNameValue, 'UTF-8') > 100) {
                throw new InvalidArgumentException('Enter a first and last name, each no longer than 100 characters.');
            }
            if (strlen($emailValue) > 150 || !filter_var($emailValue, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException('Enter a valid email address (maximum 150 characters).');
            }
            if (strlen($password) < 8 || strlen($password) > 128) {
                throw new InvalidArgumentException('Your password must be between 8 and 128 characters.');
            }
            if ($password !== $confirmPassword) {
                throw new InvalidArgumentException('The passwords do not match.');
            }

            require_once __DIR__ . '/../app/core/db.php';
            require_once __DIR__ . '/../app/models/user-model.php';

            // if an account with the same email already exists, do not create a new account
            if (getUserByEmail($pdo, $emailValue)) {
                throw new InvalidArgumentException('An account with that email already exists. Please sign in instead.');
            }

            // create a new vendor account if no email is found in the database
            createVendorAccount($pdo, $emailValue, $firstNameValue, $lastNameValue, $password);
            header('Location: login.php?registered=1', true, 303);
            exit;
            
        } catch (InvalidArgumentException $exception) {
            http_response_code(422);
            $error = $exception->getMessage();
        
            // generic error message
        } catch (Throwable $exception) {
            error_log('Vendor signup failed: ' . $exception->getMessage());
            http_response_code(500);
            $error = 'Unable to create your account. Please try again.';
        }
    }
}

$csrfToken = userCsrfToken('vendor_signup_csrf_token');
define('GREENDASH_VENDOR_SIGNUP_VIEW', true);
require __DIR__ . '/../app/views/vendor/signup.php';
