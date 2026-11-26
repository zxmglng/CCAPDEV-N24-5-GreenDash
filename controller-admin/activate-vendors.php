<?php
require_once __DIR__ . '/../app/core/admin-auth.php';
requireAdminLogin();
require_once __DIR__ . '/../app/core/db.php';
require_once __DIR__ . '/../app/models/user-model.php';
define('GREENDASH_ADMIN_VIEW', true);

$error = '';
$activated = isset($_GET['activated']) && $_GET['activated'] === '1';
$csrfToken = adminCsrfToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isAdminCsrfTokenValid($_POST['csrfToken'] ?? null)) {
        http_response_code(403);
        exit('Your session expired. Please reload the page.');
    }

    try {
        if (($_POST['action'] ?? '') !== 'activate-vendor') {
            throw new InvalidArgumentException('Invalid vendor activation action.');
        }
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        $vendor = $id !== false && $id > 0 ? getUserById($pdo, (int) $id) : null;
        if (!$vendor || $vendor['user_role'] !== 'vendor' || $vendor['acc_status'] !== 'inactive') {
            throw new InvalidArgumentException('That pending vendor account no longer exists.');
        }
        if (!activateVendor($pdo, (int) $id)) {
            throw new RuntimeException('Unable to activate that vendor account. Please try again.');
        }
        header('Location: activate-vendors.php?activated=1', true, 303);
        exit;
    } catch (Throwable $exception) {
        error_log('Admin vendor activation failed: ' . $exception->getMessage());
        $error = ($exception instanceof InvalidArgumentException || $exception instanceof RuntimeException)
            ? $exception->getMessage()
            : 'Unable to activate that vendor account. Please try again.';
    }
}

$pendingVendors = getPendingVendors($pdo);
$page = 'activate-vendors';
$appBasePath = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/') . '/';
require __DIR__ . '/../app/views/admin/activate-vendors.php';
