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

// get stall id from URL
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

$hours = getStallOperatingHours($pdo, $stallId);

// get available products
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

$categories = getCategoriesByStall($pdo, $stallId); // categories will be integrated later

// calculate today's operating hrs and avail pickup times
$now = new DateTimeImmutable(
    'now',
    new DateTimeZone('Asia/Manila')
);

$pickupTimes = [];
$hoursLabel = 'Closed';

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

// data used by user-stalls.js
$stallData = [
    'stall' => [
        'id' => (int) $stall['id'],
        'name' => $stall['name'],
        'location' => $stall['location'],
        'hours' => $hoursLabel,
        'pickupTimes' => $pickupTimes,
        'logo' => $stall['image_path'] ?? '',
        'banner' => $stall['banner_path'] ?? '',
    ],
    'products' => $products,
];

// paths used by the view and shared navigation
$assetBase = '../';
$userPageBase = '../app/views/user/';

require_once(__DIR__ . "/../app/views/user/stall-info.php");
?>