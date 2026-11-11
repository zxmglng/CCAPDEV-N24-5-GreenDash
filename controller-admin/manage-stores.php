<?php
require_once __DIR__ . '/../app/core/admin-auth.php';
requireAdminLogin();
require_once __DIR__ . '/../app/core/db.php';
require_once __DIR__ . '/../app/models/stall-model.php';
require_once __DIR__ . '/../app/models/user-model.php';

$csrfToken = adminCsrfToken();
define('GREENDASH_ADMIN_VIEW', true);

$error = '';
$flashAction = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $vendorId = ($_POST['vendor_id'] ?? '') !== '' ? (int)$_POST['vendor_id'] : null;
        $location = trim($_POST['location'] ?? '');
        $hours = trim($_POST['operating_hours'] ?? '');
        $isOpen = ($_POST['is_open'] ?? '1') === '1';

        if ($name === '' || $location === '' || $hours === '') {
            $error = 'Please fill in all fields.';
            $flashAction = 'add';
        } else {
            add_stall($name, $vendorId, $location, $isOpen, $hours);
            header('Location: manage-stores.php');
            exit;
        }
    } elseif ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $vendorId = ($_POST['vendor_id'] ?? '') !== '' ? (int)$_POST['vendor_id'] : null;
        $location = trim($_POST['location'] ?? '');
        $hours = trim($_POST['operating_hours'] ?? '');
        $isOpen = ($_POST['is_open'] ?? '1') === '1';
        if ($id > 0 && $name !== '' && $location !== '') {
            update_stall($id, $name, $vendorId, $location, $isOpen, $hours);
            header('Location: manage-stores.php');
            exit;
        }
    } elseif ($action === 'delete') {
        delete_stall((int)($_POST['id'] ?? 0));
        header('Location: manage-stores.php');
        exit;
    }
}

$stalls = get_stalls();
$vendors = get_vendors();
$page = 'manage-stores';
require __DIR__ . '/../app/views/admin/manage-stores.php';
