<?php
if (!defined('GREENDASH_VENDOR_PROFILE_VIEW')) {
    header('Location: ../../../controller-vendor/profile.php', true, 302);
    exit;
}
$vendorHeaderName = count($vendorStalls) === 1 ? $vendorStalls[0]['name'] : 'Your stores';
$vendorHeaderLocation = count($vendorStalls) === 1
    ? ($vendorStalls[0]['location'] ?? '')
    : count($vendorStalls) . ' assigned stalls';
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>GreenDash - Vendor Profile</title>
        <link rel="stylesheet" href="../bootstrap/css/bootstrap-5.3.3.min.css">
        <link rel="stylesheet" href="../bootstrap-icons/bootstrap-icons.css">
        <link rel="stylesheet" href="../style/centralized.css">
        <link rel="stylesheet" href="../style/vendors-common.css">
        <link rel="stylesheet" href="../style/vendors-profile.css">
        <link rel="icon" type="image/png" href="../assets/main/GreenDash_CIRCLE.png">
    </head>
    <body class="vendor-page" data-page="profile">
        <header>
            <div class="main-header">
                <img id="logo" src="../assets/main/GreenDash_LOGO.png" alt="GreenDash Logo">
                <div class="location">
                    <strong><?= htmlspecialchars($vendorHeaderName) ?></strong>
                    <span><?= htmlspecialchars($vendorHeaderLocation) ?></span>
                </div>
                <button class="icon-button" id="eclipse" type="button" aria-label="Toggle dark mode">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M12 2a7 7 0 1 0 10 10"></path>
                    </svg>
                </button>
            </div>
            <nav>
                <div class="nav-links">
                    <a href="profile.php" class="active" aria-current="page">Profile</a>
                    <a href="orders.php">Order Requests</a>
                    <a href="transaction-history.php">Transaction History</a>
                </div>
            </nav>
        </header>

        <div class="vendor-layout">
            <aside class="vendor-sidebar" aria-label="Profile sections">
                <h2>Profile</h2>
                <p class="vendor-greeting">Hello, <?= htmlspecialchars($vendor['first_name']) ?>!</p>
                <ul class="sidebar-links">
                    <li><a href="profile.php" class="active" aria-current="page">Your account</a></li>
                    <li><a href="manage-menu.php">Manage store</a></li>
                    <li><a href="request-store.php">Request to add a new store</a></li>
                </ul>
            </aside>

            <main class="vendor-content">
                <h1 class="page-title">Account Details</h1>

                <dl class="account-details">
                    <div class="account-row">
                        <dt>Name:</dt>
                        <dd><?= htmlspecialchars(trim($vendor['first_name'] . ' ' . $vendor['last_name'])) ?></dd>
                    </div>
                    <div class="account-row">
                        <dt>Registered email:</dt>
                        <dd><?= htmlspecialchars($vendor['email_add']) ?></dd>
                    </div>
                    <div class="account-row">
                        <dt>Account type:</dt>
                        <dd><?= ucfirst($vendor['user_role']) ?></dd>
                    </div>
                </dl>

                <form method="post" action="logout.php">
                    <input type="hidden" name="csrfToken" value="<?= htmlspecialchars(vendorCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                    <button class="vendor-btn danger" type="submit">Sign out</button>
                </form>
            </main>
        </div>

        <script type="module" src="../js/centralized.js"></script>
    </body>
</html>