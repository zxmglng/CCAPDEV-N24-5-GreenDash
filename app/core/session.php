<?php

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
    if (session_status() !== PHP_SESSION_ACTIVE) {
        $isHttps = !empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off';
        session_start(['use_strict_mode' => 1, 'use_only_cookies' => 1, 'use_trans_sid' => 0,
            'cookie_lifetime' => 0, 'cookie_httponly' => 1, 'cookie_secure' => $isHttps, 'cookie_samesite' => 'Lax',]);
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