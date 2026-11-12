<?php

session_start();
require_once(__DIR__ . "/../app/core/db.php");
require_once(__DIR__ . "/../app/models/user-model.php");

// user must be logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$user = getUserById($pdo, $_SESSION['user_id']);

if (!$user) {
    header('Location: ../index.php');
    exit;
}

// if role is already set, no need to choose again!!
if ($user['user_role'] !== null) {
    header('Location: stalls-directory.php');
    exit;
}

// save selected role
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['user_role'] ?? '';

    $allowedRoles = ['student', 'faculty', 'staff'];

    if (!in_array($role, $allowedRoles, true)) {
        $error = 'Please select a valid role.';
    } else {
        updateUserRole(
            $pdo,
            $_SESSION['user_id'],
            $role
        );
        $_SESSION['user_role'] = $role;
        header('Location: stalls-directory.php');
        exit;
    }
}

// paths used by the view
$assetBase = '../';

require_once(__DIR__ . "/../app/views/user/select-role.php");
?>