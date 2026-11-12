<?php

session_start();
require_once(__DIR__ . "/../app/core/db.php");
require_once(__DIR__ . "/../app/models/stall-model.php");
require_once(__DIR__ . "/../app/models/stall-hours-model.php");

// user must be logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$stalls = getAllStalls($pdo);
$hoursByStall = get_all_hours_grouped($pdo); // get operating hours for all stalls
// paths for the shared navigation
$assetBase = '../';
$userPageBase = '../app/views/user/'; // for orders & other pages not handled by controllers yet (points to view path)

require_once(__DIR__ . "/../app/views/user/stalls-directory.php");
?>