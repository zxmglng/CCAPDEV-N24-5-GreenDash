<?php
session_start();

require_once(__DIR__ . "/../app/core/db.php");
require_once(__DIR__ . "/../app/core/checkout.php");
require_once(__DIR__ . "/../app/models/stall-model.php");
require_once(__DIR__ . "/../app/models/stall-hours-model.php");
require_once(__DIR__ . "/../app/models/product-model.php");
require_once(__DIR__ . "/../app/models/category-model.php");

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

// Accept only a positive integer stall ID from the query string.
$stallId = filter_var(
    $_GET['stall'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

// get stall
$stall = $stallId ? getCheckoutStall($pdo, $stallId) : null;
if (!$stall) {
    header('Location: stalls-directory.php');
    exit;
}

// Load the selected stall's schedule and menu data for the page and cart script.
$hours = getStallOperatingHours($pdo, $stallId);

// Only available products are exposed to the ordering UI.
$products = array_values(
    array_filter(
        getStallProducts($pdo, $stallId),
        static fn(array $product): bool => (bool) $product['is_available']
    )
);
$productOptionGroups = getProductOptionGroups(
    $pdo,
    array_column($products, 'id')
);
foreach ($products as &$product) {
    $product['optionGroups'] = $productOptionGroups[(int) $product['id']] ?? [];
}
unset($product);

$categories = getCategoriesByStall($pdo, $stallId);

// Build today's display hours and remaining pickup windows in local time.
$now = new DateTimeImmutable(
    'now',
    new DateTimeZone('Asia/Manila')
);

$pickupTimes = [];
$hoursLabel = 'Closed';

// Convert today's opening intervals into a display label and future 15-minute pickup slots.
foreach (checkoutOpeningIntervals($hours, $now) as [$opens, $closes]) {
    if ($opens->format('Y-m-d') === $now->format('Y-m-d') || ($now >= $opens && $now < $closes)) {
        $hoursLabel = $opens->format('g:i A')
            . ' - '
            . $closes->format('g:i A');
    }

    for ($start = $opens;
        $start->modify('+15 minutes') <= $closes;
        $start = $start->modify('+15 minutes')
    ) {
        if (
            $start >= $now
            && $start->format('Y-m-d') === $now->format('Y-m-d')
        ) {
            $pickupTimes[] =
                $start->format('g:i A')
                . ' - '
                . $start->modify('+15 minutes')->format('g:i A');
        }
    }
}

// Embed a compact payload for user-stalls.js; checkout uses the same stall and cart details.
$stallData = [
    'stall' => [
        'id' => (int) $stall['id'],
        'name' => $stall['name'],
        'location' => $stall['location'],
        'hours' => $hoursLabel,
        'pickupTimes' => $pickupTimes,
        'logo' => $stall['image_path'] ?? '',
        'banner' => $stall['banner_path'] ?? '',
        'qrCodePath' => $stall['qr_code_path'] ?? '',
    ],
    'products' => $products,
];

// Set view-relative paths before rendering the stall page.
$assetBase = '../';
$userPageBase = '../app/views/user/';

require_once(__DIR__ . "/../app/views/user/stall-info.php");
?>