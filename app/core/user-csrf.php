<?php

function userCsrfToken(string $sessionKey = 'user_csrf_token'): string
{
    return $_SESSION[$sessionKey] ??= bin2hex(random_bytes(32));
}

function isUserCsrfTokenValid(mixed $submitted, string $sessionKey = 'user_csrf_token'): bool
{
    $expected = $_SESSION[$sessionKey] ?? null;
    return is_string($expected) && is_string($submitted) && hash_equals($expected, $submitted);
}
