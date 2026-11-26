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
        <link rel="stylesheet" href="../style/vendors-modal.css">
        <link rel="stylesheet" href="../style/vendors-profile.css">
        <link rel="icon" type="image/png" href="../assets/main/GreenDash_CIRCLE.png">
    </head>
    <body class="vendor-page" data-page="profile" data-assets-path="../assets/">
        <header>
            <div class="main-header">
                <img id="logo" src="../assets/main/GreenDash_LOGO.png" alt="GreenDash Logo">
                <div class="location">
                    <strong><?= htmlspecialchars($vendorHeaderName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong>
                    <span><?= htmlspecialchars($vendorHeaderLocation, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
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
                <p class="vendor-greeting">Hello, <?= htmlspecialchars($vendor['first_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>!</p>
                <ul class="sidebar-links">
                    <li><a href="profile.php" class="active" aria-current="page">Your account</a></li>
                    <li><a href="manage-menu.php">Manage store</a></li>
                    <li><a href="request-store.php">Request to add a new store</a></li>
                </ul>
            </aside>

            <main class="vendor-content">
                <!-- Display a notice while vendor activation is pending. -->
                <?php if ($vendor['acc_status'] === 'pending'): ?>
                <div class="inactive-notice">
                    <p class="field-error" role="status">NOTICE: Please wait for the admin to activate your account. You can browse the vendor menu, but store requests are unavailable until your account is active.</p>
                </div>
                <?php endif; ?>
                
                <h1 class="page-title">Account Details</h1>

                <dl class="account-details">
                    <div class="account-row">
                        <dt>Name:</dt>
                        <dd><?= htmlspecialchars(trim($vendor['first_name'] . ' ' . $vendor['last_name']),
                            ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>
                    </div>
                    <div class="account-row">
                        <dt>Registered email:</dt>
                        <dd><?= htmlspecialchars($vendor['email_add'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>
                    </div>
                    <div class="account-row">
                        <dt>Account type:</dt>
                        <dd><?= htmlspecialchars(ucfirst($vendor['user_role']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>
                    </div>
                    <div class="account-row">
                        <dt>Account status:</dt>
                        <dd><?= htmlspecialchars(ucfirst($vendor['acc_status']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>
                    </div>
                </dl>

                <hr> 
                <!-- change password section -->
                <section class="password-section" aria-labelledby="passwordSectionTitle">
                    <h1 class="page-title" id="passwordSectionTitle">Change password</h2>
                    <?php if (!empty($_GET['password_changed'])): ?>    <!-- Password changed successfully -->
                        <p class="password-feedback success" role="status">Password changed successfully.</p>
                    <?php endif; ?>
                    <button class="vendor-btn secondary" type="button" id="openPasswordModal">Change password</button>
                </section>

                <form method="post" action="logout.php">
                    <input type="hidden" name="csrfToken"
                        value="<?= htmlspecialchars(vendorCsrfToken(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                    <button class="vendor-btn danger" type="submit">Sign out</button>
                </form>
            </main>
        </div>

        <!-- password change modal -->
        <div class="vmodal<?= $passwordChangeError !== '' ? ' open' : '' ?>" id="passwordModal" role="dialog" aria-modal="true" aria-labelledby="passwordModalTitle">
            <button type="button" class="vmodal-backdrop" data-close aria-label="Close"></button>
            <form method="post" action="profile.php" class="vmodal-box password-form">
                <div class="vmodal-head">
                    <h2 class="page-title" id="passwordModalTitle">Change password</h2>
                    <button type="button" class="vmodal-close" data-close aria-label="Close"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
                </div>
                <div class="vmodal-body">
                    <?php if ($passwordChangeError !== ''): ?>
                        <p class="password-feedback error" role="alert"><?= htmlspecialchars($passwordChangeError, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                    <?php endif; ?>
                    <input type="hidden" name="csrfToken" value="<?= htmlspecialchars(vendorCsrfToken(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                    <label class="form-label" for="current_password">Current password</label>
                    <input class="form-control" id="current_password" name="current_password" type="password" autocomplete="current-password" required maxlength="128">
                    <label class="form-label" for="new_password">New password</label>
                    <input class="form-control" id="new_password" name="new_password" type="password" autocomplete="new-password" required minlength="8" maxlength="128">
                    <label class="form-label" for="confirm_password">Confirm new password</label>
                    <input class="form-control" id="confirm_password" name="confirm_password" type="password" autocomplete="new-password" required minlength="8" maxlength="128">
                </div>
                <div class="vmodal-foot">
                    <button class="vendor-btn secondary" type="button" data-close>Cancel</button>
                    <button class="vendor-btn primary" type="submit">Update password</button>
                </div>
            </form>
        </div>

        <script type="module" src="../js/centralized.js"></script>
        <script src="../js/vendor-profile.js" defer></script>
    </body>
</html>
