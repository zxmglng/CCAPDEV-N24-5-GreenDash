<?php
// this controller handsle session_start(), logout, POST, updating profile, and loads profile view

session_start();
require_once(__DIR__ . "/../app/core/db.php");
require_once(__DIR__ . "/../app/models/user-model.php");

//log out current user
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ../index.php');
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

// get info of the logged in user
$user = getUserById($pdo, $_SESSION['user_id']);
if (!$user) {
    header('Location: ../index.php');
    exit;
}

// update profile info
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $contactNum = trim($_POST['contact_num'] ?? '');
    $profileImage = trim($_POST['profile_image'] ?? '');

    updateUserProfile(
        $pdo,
        $_SESSION['user_id'],
        $firstName,
        $lastName,
        $contactNum !== '' ? $contactNum : null,
        $profileImage !== '' ? $profileImage : $user['profile_image']
    );
    exit;
}

// paths used by the shared navigation
$assetBase = '../';
$userPageBase = '../app/views/user/';

require_once(__DIR__ . "/../app/views/user/profile.php");
?>