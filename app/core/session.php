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