<?php

session_start();
require_once(__DIR__ . "/../app/core/db.php");
require_once(__DIR__ . "/../app/models/stall-model.php");
require_once(__DIR__ . "/../app/models/stall-hours-model.php");
require_once(__DIR__ . "/../app/models/review-model.php");

// user must be logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$stalls = getAllStalls($pdo);
$ratingSummaries = getStallRatingSummaries($pdo);
$stallLocations = [];
$stallTypes = [];
// Build filter options from live records and attach each stall's completed-order review aggregate.
foreach ($stalls as &$stall) {
    $summary = $ratingSummaries[(int) $stall['id']] ?? null;
    $stall['average_rating'] = $summary['averageRating'] ?? null;
    $stall['review_count'] = $summary['reviewCount'] ?? 0;

    $location = trim((string) ($stall['location'] ?? ''));
    if ($location !== '') {
        $stallLocations[mb_strtolower($location, 'UTF-8')] = $location;
    }
    foreach (preg_split('/\s+/', strtolower(trim((string) ($stall['stall_type'] ?? '')))) as $type) {
        if ($type !== '') {
            $stallTypes[$type] = ucfirst($type);
        }
    }
}
unset($stall);
// Sort generated filter choices for predictable, readable controls.
asort($stallLocations, SORT_NATURAL | SORT_FLAG_CASE);
asort($stallTypes, SORT_NATURAL | SORT_FLAG_CASE);
$hoursByStall = get_all_hours_grouped($pdo); // Fetch schedules in one query for the card renderer.
// paths for the shared navigation
$assetBase = '../';
$userPageBase = '../app/views/user/'; // for orders & other pages not handled by controllers yet (points to view path)

require_once(__DIR__ . "/../app/views/user/stalls-directory.php");
?>