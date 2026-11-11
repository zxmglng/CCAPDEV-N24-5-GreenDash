<?php
require_once __DIR__ . '/../app/core/admin-auth.php';
requireAdminLogin();
require_once __DIR__ . '/../app/core/db.php';
require_once __DIR__ . '/../app/models/user-model.php';

$csrfToken = adminCsrfToken();
define('GREENDASH_ADMIN_VIEW', true);

$error = '';
$flashAction = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $fn = trim($_POST['first_name'] ?? '');
        $ln = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'student';
        
        if (find_user_by_email($email)) {
            $error = 'A user with that email already exists.';
            $flashAction = 'add';
        } else {
            add_user($fn, $ln, $email, $role);
            header('Location: manage-users.php');
            exit;
        }
    } elseif ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $fn = trim($_POST['first_name'] ?? '');
        $ln = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'student';
        update_user($id, $fn, $ln, $email, $role);
        header('Location: manage-users.php');
        exit;
    } elseif ($action === 'delete') {
        delete_user((int)($_POST['id'] ?? 0));
        header('Location: manage-users.php');
        exit;
    }
}

$users = get_users();
$page = 'manage-users';
require __DIR__ . '/../app/views/admin/manage-users.php';
