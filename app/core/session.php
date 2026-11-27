<?php

require_once __DIR__ . '/security-headers.php';

// start a secure session w protected cookie settings
// use_strict_mode = prevents uninitialized session IDs from being accepted
// use_only_cookies = allow session IDs to be sent only thru cookies
// use_trans_sid = prevent session IDs from being added to URLs
// cookie_lifetime = expire the session cookie when the browser close
// cookie_httponly = prevent JS from accessing the session cookie
// cookie_secure = send the cookie only over HTTPS
// cookie_samesite = restrict cross-site cookie requests to help prevent CSRF
function startGreenDashSession(): void
{
    sendGreenDashCspReportOnly();
    if (session_status() !== PHP_SESSION_ACTIVE) {
        $isHttps = !empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off';
        session_start(['use_strict_mode' => 1, 'use_only_cookies' => 1, 'use_trans_sid' => 0,
            'cookie_lifetime' => 0, 'cookie_httponly' => 1, 'cookie_secure' => $isHttps, 'cookie_samesite' => 'Lax',]);
    }
    enforceGreenDashSessionTimeout(); // enforce the role-based inactivity timeout
}

function clearGreenDashUserSessionData(): void
{
    unset($_SESSION['user_id'], $_SESSION['user_email'], $_SESSION['user_role'], $_SESSION['user_last_activity'],
        $_SESSION['vendor_csrf_token'], $_SESSION['checkout_csrf_token'], $_SESSION['checkout_receipts'],
        $_SESSION['review_csrf_token'], $_SESSION['user_csrf_token'], $_SESSION['google_login_csrf_token'],
        $_SESSION['vendor_password_authenticated_at']); // clear the timestamp of the last successful vendor password authentication
}

// enforce role-based session inactivity timeouts
function enforceGreenDashSessionTimeout(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return;
    }
    $currentTime = time();
    $sessionExpired = false;

    // admin inactivity timeout = 30 min
    if (!empty($_SESSION['admin_authenticated'])) {
        $lastActivity = (int) ($_SESSION['admin_last_activity'] ?? $currentTime);

        // FOR TESTING ONLY = uncomment
        // if (($currentTime - $lastActivity) >= 10) {
        if (($currentTime - $lastActivity) >= 1800) {
            unset($_SESSION['admin_authenticated'], $_SESSION['admin_name'], $_SESSION['admin_user_id'],
                $_SESSION['admin_csrf_token'], $_SESSION['admin_last_activity']);
            $sessionExpired = true;
        } else {
            $_SESSION['admin_last_activity'] = $currentTime;
        }
    }
    // userand vendor inactivity timeout
    if (isset($_SESSION['user_id'])) {
        // user and vendor timeout = 15 min
        // FOR TESTING ONLY = uncomment
        // $timeout = 10;
        $timeout = 900;
        $lastActivity = (int) ($_SESSION['user_last_activity'] ?? $currentTime);
        if (($currentTime - $lastActivity) >= $timeout) {
            clearGreenDashUserSessionData();
            $sessionExpired = true;
        } else {
            $_SESSION['user_last_activity'] = $currentTime;
        }
    }

    // handle expired authentication sesh
    if ($sessionExpired) {
        $hasAdminSession = !empty($_SESSION['admin_authenticated']);
        $hasUserSession = isset($_SESSION['user_id']);
        
        if (!$hasAdminSession && !$hasUserSession) {
            destroyGreenDashSession();
            // start a new session after expiration
            startGreenDashSession();
            return;
        }
        // regenerate session ID after removing expired authentication data
        if (!session_regenerate_id(true)) {
            destroyGreenDashSession();
        }
    }
}


// destroy the current session and remove its cookie
function destroyGreenDashSession(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        startGreenDashSession();
    }

    // clear all session variables
    $_SESSION = [];
    // get the current session cookie settings
    $params = session_get_cookie_params();
    // expire the session cookie in the browser.
    setcookie(session_name(), '', ['expires' => time() - 42000, 'path' => $params['path'], 'domain' => $params['domain'],
        'secure' => $params['secure'], 'httponly' => $params['httponly'], 'samesite' => $params['samesite'] ?? 'Lax'
    ]);

    // Destroy the server-side session.
    session_destroy();
}

// clear selected session variables while preserving other active sessions
function clearGreenDashSessionData(array $keys): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        startGreenDashSession();
    }

    // remove only the specified session variables
    foreach ($keys as $key) {
        if (is_string($key) && $key !== '') {
            unset($_SESSION[$key]);
        }
    }
    // generate new session ID to invalidate the previous one
    return session_regenerate_id(true);
}
