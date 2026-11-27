<?php

require_once __DIR__ . '/../app/core/session.php';
require_once __DIR__ . '/../app/core/user-csrf.php';

startGreenDashSession();
header('Cache-Control: no-store');

$error = '';
$emailValue = '';

const VENDOR_LOGIN_MAX_ATTEMPTS = 5;
const VENDOR_LOGIN_LOCKOUT_SEC = 300;

/* vendor login lockout and attempt tracking */
function vendorLoginLockoutRemaining(): int
{
    $lockedUntil = (int) ($_SESSION['vendor_login_locked_until'] ?? 0);
    $remaining = $lockedUntil - time();
    return $remaining > 0 ? $remaining : 0;
}

/*
 * Record a failed vendor login attempt and lock the account if the maximum attempts are reached.
 */
function vendorLoginRecordFailure(): void
{
    $attempts = (int) ($_SESSION['vendor_login_attempts'] ?? 0) + 1;
    $_SESSION['vendor_login_attempts'] = $attempts;
    if ($attempts >= VENDOR_LOGIN_MAX_ATTEMPTS) {
        $_SESSION['vendor_login_locked_until'] = time() + VENDOR_LOGIN_LOCKOUT_SEC;
    }
}

/*
 * Reset the vendor login attempts and lockout status.
 */
function vendorLoginResetAttempts(): void
{
    unset($_SESSION['vendor_login_attempts'], $_SESSION['vendor_login_locked_until']);
}

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

        /* check if the vendor account is currently locked due to too many failed login attempts */
        if (vendorLoginLockoutRemaining() > 0) {
            $minutes = (int) ceil(vendorLoginLockoutRemaining() / 60);
            http_response_code(429);
            $error = 'Too many failed attempts. Try again in '
                . $minutes . ' minute' . ($minutes === 1 ? '' : 's') . '.';
        } else {
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

            if ($user['acc_status'] === 'inactive') {
                throw new InvalidArgumentException('This vendor account is inactive. Please contact your administrator.');
            }
            if (!in_array($user['acc_status'], ['active', 'pending'], true)) {
                throw new InvalidArgumentException('This account cannot sign in.');
            }

            /* reset the vendor login attempts and lockout status upon successful login */
            vendorLoginResetAttempts();

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
                vendorLoginRecordFailure();
                $attempts = (int) ($_SESSION['vendor_login_attempts'] ?? 0);
                $remainingAttempts = VENDOR_LOGIN_MAX_ATTEMPTS - $attempts;
                $error = $exception->getMessage();
                if ($remainingAttempts > 0) {
                    $error .= ' ' . $remainingAttempts . ' attempt'
                        . ($remainingAttempts === 1 ? '' : 's')
                        . ' remaining before a temporary lockout.';
                } else {
                    $error = 'Too many failed attempts. Try again in 5 minutes.';
                    http_response_code(429);
                }
                if ($attempts < VENDOR_LOGIN_MAX_ATTEMPTS) {
                    http_response_code(422);
                }
            }
        }
    }
}

$csrfToken = userCsrfToken('vendor_login_csrf_token');
define('GREENDASH_VENDOR_LOGIN_VIEW', true);
require __DIR__ . '/../app/views/vendor/login.php';
