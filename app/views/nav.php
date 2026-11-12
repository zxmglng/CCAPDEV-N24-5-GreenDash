<?php
// Browser-relative paths can be supplied by controllers rendering this shared fragment.
$currentPage = basename($_SERVER['SCRIPT_NAME'] ?? '');
$assetBase = $assetBase ?? '../../../';
$userPageBase = $userPageBase ?? '';

$isUserController = str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/controller-user/');
// profile and stalls directory alr use controllers
$profilePage = $isUserController
    ? 'profile.php'
    : '../../../controller-user/profile.php';
$stallsDirectoryPage = $isUserController
    ? 'stalls-directory.php'
    : '../../../controller-user/stalls-directory.php';
?>
        <header>
            <div class="main-header">
                <a href="<?= htmlspecialchars($stallsDirectoryPage, ENT_QUOTES, 'UTF-8') ?>" aria-label="Go to stalls directory">
                    <img id="logo" src="<?= htmlspecialchars($assetBase . 'assets/main/GreenDash_LOGO.png', ENT_QUOTES, 'UTF-8') ?>" alt="GreenDash Logo">
                </a>
                <div class="location">
                    <span>Your Location:</span>
                    <select class="location-select" aria-label="Choose your location">
                        <option>Gokongwei</option>
                        <option>La Salle Hall</option>
                        <option>Bloemen</option>
                        <option>Razon</option>
                        <option>Andrew</option>
                        <option>Yuchengco</option>
                    </select>
                </div>
                <button class="icon-button" id="eclipse" aria-label="Toggle dark mode">
                    <i data-lucide="eclipse"></i>
                </button>
            </div>

            <!-- CHANGE THE NAVIGATION LINKS IF NEEDED ACCDG TO USER ROLE HERE-->
            <nav>
                <div class="nav-links">
                    <a href="<?= htmlspecialchars($profilePage, ENT_QUOTES, 'UTF-8') ?>"<?= $currentPage === 'profile.php' ? ' aria-current="page"' : '' ?>>Profile</a>
                    <a href="<?= htmlspecialchars($stallsDirectoryPage, ENT_QUOTES, 'UTF-8') ?>"<?= $currentPage === 'stalls-directory.php' ? ' aria-current="page"' : '' ?>>Stalls Directory</a>
                    <a href="<?= htmlspecialchars($userPageBase . 'orders.php', ENT_QUOTES, 'UTF-8') ?>">Orders</a>
                </div>

                <button class="icon-button" id="cart" aria-label="View cart">
                    <i data-lucide="shopping-cart"></i>
                </button>
            </nav>
        </header>
