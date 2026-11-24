<?php
require_once(__DIR__ . "/../app/core/checkout.php");
startCheckoutSession();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once(__DIR__ . "/../app/core/db.php");
require_once(__DIR__ . "/../app/models/category-model.php");

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

$stallDetails = getCheckoutStallDetails($pdo, $stall);
$stallData = [
    'stall' => $stallDetails,
    'products' => $products,
    'cartCsrfToken' => checkoutCsrfToken(),
];

// Set view-relative paths before rendering the stall page.
$assetBase = '../';
$userPageBase = '../app/views/user/';

require_once(__DIR__ . "/../app/views/user/stall-info.php");
?>
