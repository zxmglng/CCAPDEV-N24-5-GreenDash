<?php
require_once __DIR__ . '/../app/core/admin-auth.php';
requireAdminLogin();
require_once __DIR__ . '/../app/core/db.php';
require_once __DIR__ . '/../app/models/user-model.php';

define('GREENDASH_ADMIN_VIEW', true);

$error = '';
$flashAction = '';
$csrfToken = adminCsrfToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isAdminCsrfTokenValid($_POST['csrfToken'] ?? null)) {
        http_response_code(403);
        exit('Your session expired. Please reload the page.');
    }

    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'add') {
            $fn = trim($_POST['first_name'] ?? '');
            $ln = trim($_POST['last_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $role = $_POST['role'] ?? 'student';
            $contact = trim($_POST['contact_num'] ?? '') ?: null;

            if ($fn === '' || $ln === '' || $email === '') {
                throw new InvalidArgumentException('Please fill in all fields.');
            }
            if (find_user_by_email($email)) {
                throw new InvalidArgumentException('A user with that email already exists.');
            }

            add_user($fn, $ln, $email, $role, $contact);
            header('Location: manage-users.php');
            exit;

        } elseif ($action === 'edit') {
            $id = (int) ($_POST['id'] ?? 0);
            $fn = trim($_POST['first_name'] ?? '');
            $ln = trim($_POST['last_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $role = $_POST['role'] ?? 'student';
            $status = $_POST['status'] ?? 'active';
            $contact = trim($_POST['contact_num'] ?? '') ?: null;

            if ($id <= 0 || $fn === '' || $ln === '' || $email === '') {
                throw new InvalidArgumentException('Please fill in all required fields.');
            }
            if (!in_array($role, ['student', 'faculty', 'staff', 'vendor', 'admin'], true)
                || !in_array($status, ['active', 'inactive'], true)) {
                throw new InvalidArgumentException('Choose a valid role and account status.');
            }

            $existing = find_user_by_email($email);
            if ($existing && (int) $existing['user_id'] !== $id) {
                throw new InvalidArgumentException('A user with that email already exists.');
            }

            $current = getUserById($pdo, $id);
            if (!$current) {
                throw new InvalidArgumentException('That user no longer exists.');
            }
            $willDeactivateLastAdmin = $current['user_role'] === 'admin'
                && $current['acc_status'] === 'active'
                && ($role !== 'admin' || $status !== 'active');
            if ($willDeactivateLastAdmin) {
                $countStmt = $pdo->prepare(
                    "SELECT COUNT(*) FROM users WHERE user_role = 'admin' AND acc_status = 'active' AND user_id <> ?"
                );
                $countStmt->execute([$id]);
                if ((int) $countStmt->fetchColumn() === 0) {
                    throw new InvalidArgumentException('You cannot deactivate or change the role of the last active admin.');
                }
            }

            update_user($id, $fn, $ln, $email, $role, $contact, $status);
            header('Location: manage-users.php');
            exit;

        } elseif ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            $user = getUserById($pdo, $id);
            if (!$user) {
                throw new InvalidArgumentException('That user no longer exists.');
            }
            if ($user['user_role'] === 'admin' && $user['acc_status'] === 'active') {
                $countStmt = $pdo->prepare(
                    "SELECT COUNT(*) FROM users WHERE user_role = 'admin' AND acc_status = 'active' AND user_id <> ?"
                );
                $countStmt->execute([$id]);
                if ((int) $countStmt->fetchColumn() === 0) {
                    throw new InvalidArgumentException('You cannot deactivate the last active admin.');
                }
            }
            delete_user($id);
            header('Location: manage-users.php');
            exit;

        }
    } catch (Throwable $e) {
        error_log('Admin user action failed: ' . $e->getMessage());
        $error = ($e instanceof InvalidArgumentException || $e instanceof RuntimeException)
            ? $e->getMessage()
            : 'Unable to complete that action. Please try again.';
        $flashAction = $action;
    }
}

$users = get_users();
$page = 'manage-users';
require __DIR__ . '/../app/views/admin/manage-users.php';
