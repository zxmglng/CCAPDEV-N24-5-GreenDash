<?php
if (!defined('GREENDASH_VENDOR_MANAGE_MENU_VIEW')) {
    header('Location: ../../../controller-vendor/manage-menu.php', true, 302);
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
        <title>GreenDash - Manage Store</title>
        <link rel="stylesheet" href="../bootstrap/css/bootstrap-5.3.3.min.css">
        <link rel="stylesheet" href="../bootstrap-icons/bootstrap-icons.css">
        <link rel="stylesheet" href="../style/centralized.css">
        <link rel="stylesheet" href="../style/vendors-common.css">
        <link rel="stylesheet" href="../style/vendors-manage-menu.css">
        <link rel="icon" type="image/png" href="../assets/main/GreenDash_CIRCLE.png">
    </head>
    <body class="vendor-page" data-page="manage-menu">
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
                    <a href="profile.php">Profile</a>
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
                    <li><a href="profile.php">Your account</a></li>
                    <li><a href="manage-menu.php" class="active" aria-current="page">Manage store</a></li>
                    <li><a href="request-store.php">Request to add a new store</a></li>
                </ul>
            </aside>

            <main class="vendor-content">
                <h1 class="page-title">Manage store</h1>

                <div class="store-grid">
                    <?php if (empty($vendorStalls)): ?>
                        <p class="empty-note">
                            No stores are assigned to your account yet.
                            <a href="request-store.php">Request to add a store</a>.
                        </p>
                    <?php else: ?>
                        <?php foreach ($vendorStalls as $s): ?>
                            <a class="store-card" href="manage-store.php?stall=<?= (int) $s['id'] ?>">
                                <?php if (!empty($s['image_path'])): ?>
                                    <img class="store-card-logo" src="<?= htmlspecialchars($s['image_path']) ?>" alt="">
                                <?php else: ?>
                                    <img class="store-card-logo" src="../assets/main/defaultAvatar.png" alt="">
                                <?php endif; ?>
                                <span class="store-card-name"><?= htmlspecialchars($s['name']) ?></span>
                                <span class="store-card-location"><?= htmlspecialchars($s['location'] ?? '') ?></span>
                                <span class="status <?= $s['is_open'] ? 'open' : 'closed' ?>">
                                    <?= $s['is_open'] ? 'OPEN' : 'CLOSED' ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </main>
        </div>

        <script type="module" src="../js/centralized.js"></script>
    </body>
</html>