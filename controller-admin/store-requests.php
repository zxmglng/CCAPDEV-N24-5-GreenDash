<?php
require_once __DIR__ . '/../app/core/admin-auth.php';
requireAdminLogin();
require_once __DIR__ . '/../app/core/db.php';
require_once __DIR__ . '/../app/models/store-request-model.php';

$error = '';

$allowedStatuses = ['pending', 'approved', 'denied', ''];
$statusFilter = $_GET['status'] ?? 'pending';
if (!in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = 'pending';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['request_id'] ?? 0);
    $act = $_POST['action'] ?? '';
    try {
        if ($id > 0 && $act === 'approve') {
            $ok = approve_request($id, getAdminUserId());
            if (!$ok) {
                throw new RuntimeException('That request could not be approved. It may have already been resolved.');
            }
        } elseif ($id > 0 && $act === 'deny') {
            $ok = deny_request($id, getAdminUserId());
            if (!$ok) {
                throw new RuntimeException('That request could not be denied. It may have already been resolved.');
            }
        }
        header('Location: store-requests.php?status=' . urlencode($statusFilter));
        exit;
    } catch (Throwable $e) {
        error_log('Request action failed: ' . $e->getMessage());
        $error = $e instanceof RuntimeException
            ? $e->getMessage()
            : 'Unable to complete that action. Please try again.';
    }
}

$requests = get_requests($statusFilter);
$page = 'store-requests';
define('GREENDASH_ADMIN_VIEW', true);
require __DIR__ . '/../app/views/admin/store-requests.php';