<?php

function startAdminSession(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start([
            'use_strict_mode' => true,
            'cookie_httponly' => true,
            'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'cookie_samesite' => 'Lax',
        ]);
    }

    header('Cache-Control: no-store');
}

function isAdminLoggedIn(): bool
{
    return ($_SESSION['admin_authenticated'] ?? false) === true;
}

function requireAdminLogin(): void
{
    startAdminSession();

    if (!isAdminLoggedIn()) {
        header('Location: login.php', true, 302);
        exit;
    }
}

function adminCsrfToken(): string
{
    if (!isset($_SESSION['admin_csrf_token'])) {
        $_SESSION['admin_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['admin_csrf_token'];
}

function isAdminCsrfTokenValid($token): bool
{
    return is_string($token)
        && isset($_SESSION['admin_csrf_token'])
        && hash_equals($_SESSION['admin_csrf_token'], $token);
}

function isAdminKeyValid(string $key): bool
{
    // Preserve the group's existing key; deployment can override it server-side.
    $configuredKey = getenv('GREENDASH_ADMIN_KEY');
    if ($configuredKey === false || $configuredKey === '') {
        $configuredKey = 'greendashN24';
    }

    return hash_equals($configuredKey, $key);
}

function getAdminDisplayName(): string
{
    $name = getenv('GREENDASH_ADMIN_NAME');
    if (is_string($name) && trim($name) !== '') {
        return trim($name);
    }
    return 'Administrator';
}

function getAdminUserId(): ?int
{
    $id = getenv('GREENDASH_ADMIN_USER_ID');
    if (!is_string($id) || !ctype_digit($id) || (int) $id <= 0) {
        return null;
    }
    return (int) $id;
}