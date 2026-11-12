<?php
require_once __DIR__ . '/../app/core/admin-auth.php';
requireAdminLogin();
require_once __DIR__ . '/../app/core/db.php';
require_once __DIR__ . '/../app/models/user-model.php';

$csrfToken = adminCsrfToken();
define('GREENDASH_ADMIN_VIEW', true);

$error = '';
$flashAction = '';
$error = '';
$flashAction = '';

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
            $contact = trim($_POST['contact_num'] ?? '') ?: null;

            if ($id <= 0 || $fn === '' || $ln === '' || $email === '') {
                throw new InvalidArgumentException('Please fill in all required fields.');
            }
            update_user($id, $fn, $ln, $email, $role, $contact);
            header('Location: manage-users.php');
            exit;

        } elseif ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            $user = getUserById($pdo, $id);
            if (!$user) {
                throw new InvalidArgumentException('That user no longer exists.');
            }
            // Refuse to deactivate the last active admin.
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
