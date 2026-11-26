<?php
require_once __DIR__ . '/session.php';

// Roles allowed through the user door. Vendors and admins are rejected.
const USER_ALLOWED_ROLES = ['student', 'faculty', 'staff'];

function isUserLoggedIn(): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return false;
    }
    return isset($_SESSION['user_id']);
}

function isUserRoleAllowed(?string $role): bool
{
    return is_string($role) && in_array($role, USER_ALLOWED_ROLES, true);
}

function requireUserLogin(): void
{
    startGreenDashSession();

    if (!isUserLoggedIn()) {
        header('Location: ../index.php', true, 303);
        exit;
    }

    $role = $_SESSION['user_role'] ?? null;
    if (!isUserRoleAllowed($role)) {
        if ($role === 'vendor') {
            header('Location: ../controller-vendor/orders.php', true, 303);
        } else {
            header('Location: ../index.php', true, 303);
        }
        exit;
    }
}

function requireUserApi(): int
{
    startGreenDashSession();
    header('Content-Type: application/json; charset=utf-8');

    if (!isUserLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Please sign in again.']);
        exit;
    }

    $role = $_SESSION['user_role'] ?? null;
    if (!isUserRoleAllowed($role)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'This account cannot access the user area.']);
        exit;
    }

    return (int) $_SESSION['user_id'];
}