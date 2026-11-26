<?php

require_once __DIR__ . '/../app/core/session.php';
require_once __DIR__ . '/../app/core/user-csrf.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.'], JSON_THROW_ON_ERROR);
    exit;
}

startGreenDashSession();
if (session_status() !== PHP_SESSION_ACTIVE) {
    startGreenDashSession();
}
header('Cache-Control: no-store');

if (session_status() !== PHP_SESSION_ACTIVE) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to start a sign-in session.'], JSON_THROW_ON_ERROR);
    exit;
}

echo json_encode([
    'success' => true,
    'csrfToken' => userCsrfToken('google_login_csrf_token'),
], JSON_THROW_ON_ERROR);
