<?php
require_once __DIR__ . '/../app/core/admin-auth.php';
requireAdminLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit;
}

if (!isAdminCsrfTokenValid($_POST['csrfToken'] ?? null)) {
    http_response_code(403);
    exit;
}

// Keep any independent user login in this browser intact.
unset($_SESSION['admin_authenticated'], $_SESSION['admin_csrf_token']);
session_regenerate_id(true);
header('Location: login.php', true, 303);
exit;
