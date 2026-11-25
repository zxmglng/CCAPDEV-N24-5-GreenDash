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

// clear admin session data while preserving any independent user login
if (!clearGreenDashSessionData(['admin_authenticated', 'admin_name', 'admin_user_id', 'admin_csrf_token'])) {
    http_response_code(500);
    exit;
}
header('Location: ../index.php', true, 303);
exit;