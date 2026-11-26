<?php

require_once __DIR__ . '/../app/core/session.php';
require_once __DIR__ . '/../app/core/user-csrf.php';
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit;
}

startGreenDashSession();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

if (!isUserCsrfTokenValid($_POST['csrfToken'] ?? null)) {
    http_response_code(403);
    exit;
}

destroyGreenDashSession();
header('Location: ../index.php', true, 303);
exit;
