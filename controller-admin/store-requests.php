<?php
require_once __DIR__ . '/../app/core/admin-auth.php';
requireAdminLogin();
require_once __DIR__ . '/../app/core/db.php';
require_once __DIR__ . '/../app/models/store-request-model.php';

$csrfToken = adminCsrfToken();
define('GREENDASH_ADMIN_VIEW', true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['request_id'] ?? 0);
    $act = $_POST['action'] ?? '';
    if ($id > 0 && $act === 'approve') {
        approve_request($id, 1); // Passing 1 since admin_id is no longer a DB row
    } elseif ($id > 0 && $act === 'deny') {
        deny_request($id, 1);
    }
    header('Location: store-requests.php');
    exit;
}

$requests = get_requests('pending');
$page = 'store-requests';
require __DIR__ . '/../app/views/admin/store-requests.php';
