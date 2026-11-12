<?php
require_once __DIR__ . '/../app/core/vendor-auth.php';
startVendorSession();
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'csrfToken' => vendorCsrfToken()]);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: GET, POST');
    http_response_code(405);
    exit;
}
try {
    requireVendorCsrfToken($_POST['csrfToken'] ?? null);
    $_SESSION = [];
    session_destroy();
    header('Location: ../app/views/vendor/login.php', true, 303);
} catch (VendorRequestException $error) {
    http_response_code($error->getCode());
    echo $error->getMessage();
}
