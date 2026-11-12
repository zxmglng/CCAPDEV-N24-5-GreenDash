<?php
if (!defined('GREENDASH_VENDOR_ORDERS_VIEW')) {
    http_response_code(404);
    exit;
}
$vendorPageTitle = ['orders' => 'Order Requests', 'history' => 'Transaction History', 'pickup' => 'Order Pickup'][$vendorPage];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenDash - <?= htmlspecialchars($vendorPageTitle) ?></title>
    <link rel="stylesheet" href="../bootstrap/css/bootstrap-5.3.3.min.css">
    <link rel="stylesheet" href="../style/centralized.css">
    <link rel="stylesheet" href="../style/vendor.css">
    <link rel="stylesheet" href="../style/vendor-orders.css">
    <link rel="icon" type="image/png" href="../assets/main/GreenDash_CIRCLE.png">
</head>
<body class="vendor-page vendor-orders-page" data-assets-path="../assets/" data-vendor-page="<?= $vendorPage ?>">
    <header>
        <div class="main-header">
            <img id="logo" src="../assets/main/GreenDash_LOGO.png" alt="GreenDash Logo">
            <div class="location">
                <strong><?= count($vendorStalls) === 1 ? htmlspecialchars($vendorStalls[0]['name']) : 'Your stores' ?></strong>
                <span><?= count($vendorStalls) === 1 ? htmlspecialchars($vendorStalls[0]['location'] ?? '')
                    : count($vendorStalls) . ' assigned stalls' ?></span>
            </div>
            <button class="icon-button" id="eclipse" type="button" aria-label="Toggle dark mode">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"></circle><path d="M12 2a7 7 0 1 0 10 10"></path>
                </svg>
            </button>
        </div>
        <nav aria-label="Vendor navigation">
            <div class="nav-links">
                <a href="profile.php">Profile</a>
                <a href="orders.php" <?= $vendorPage === 'orders' ? 'aria-current="page"' : '' ?>>Order Requests</a>
                <a href="pickup.php" <?= $vendorPage === 'pickup' ? 'aria-current="page"' : '' ?>>Pickup</a>
                <a href="transaction-history.php" <?= $vendorPage === 'history' ? 'aria-current="page"' : '' ?>>Transaction History</a>
            </div>
        </nav>
    </header>
    <div class="vendor-account-bar">
        <span>Signed in as <?= htmlspecialchars(trim($vendor['first_name'] . ' ' . $vendor['last_name'])) ?></span>
        <form method="post" action="logout.php" id="vendorSessionLogoutForm">
            <input type="hidden" name="csrfToken" value="<?= htmlspecialchars($vendorOrdersData['csrfToken']) ?>">
            <button type="submit" class="vendor-action secondary">Sign out</button>
        </form>
    </div>
    <?php if (!$vendorStalls): ?>
        <p class="vendor-page-feedback">No stalls are assigned to your account yet. Ask your administrator to assign your stall.</p>
    <?php endif; ?>