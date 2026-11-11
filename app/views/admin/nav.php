<?php
// Shared header + sidebar for authenticated admin pages.
// Expects: $page = 'home' | 'manage-stores' | 'store-requests' | 'manage-users'
$page       = $page ?? 'home';
$stallsOpen = in_array($page, ['manage-stores', 'store-requests']);
$usersOpen  = $page === 'manage-users';
?>
<header>
    <div class="main-header">
        <img id="logo" src="assets/main/GreenDash_LOGO.png" alt="GreenDash Logo">
        <button class="icon-button" id="eclipse" type="button" aria-label="Toggle dark mode">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M12 2a7 7 0 1 0 10 10"></path>
            </svg>
        </button>
    </div>
</header>

<div class="admin-layout">
    <aside class="admin-sidebar" aria-label="Admin dashboard">
        <span>Dashboard</span>

        <button class="sidebar-link" type="button"
                data-bs-toggle="collapse" data-bs-target="#stallsMenu"
                aria-expanded="<?= $stallsOpen ? 'true' : 'false' ?>" aria-controls="stallsMenu">
            <span>Stalls</span>
            <span class="caret"><i class="bi bi-arrow-down"></i></span>
        </button>
        <div class="collapse <?= $stallsOpen ? 'show' : '' ?>" id="stallsMenu">
            <div class="submenu">
                <a href="controller-admin/manage-stores.php"
                   class="submenu-link <?= $page === 'manage-stores' ? 'active' : '' ?>"
                   <?= $page === 'manage-stores' ? 'aria-current="page"' : '' ?>>Manage</a>
                <a href="controller-admin/store-requests.php"
                   class="submenu-link <?= $page === 'store-requests' ? 'active' : '' ?>"
                   <?= $page === 'store-requests' ? 'aria-current="page"' : '' ?>>Requests</a>
            </div>
        </div>

        <button class="sidebar-link" type="button"
                data-bs-toggle="collapse" data-bs-target="#usersMenu"
                aria-expanded="<?= $usersOpen ? 'true' : 'false' ?>" aria-controls="usersMenu">
            <span>Users</span>
            <span class="caret"><i class="bi bi-arrow-down"></i></span>
        </button>
        <div class="collapse <?= $usersOpen ? 'show' : '' ?>" id="usersMenu">
            <div class="submenu">
                <a href="controller-admin/manage-users.php"
                   class="submenu-link <?= $page === 'manage-users' ? 'active' : '' ?>"
                   <?= $page === 'manage-users' ? 'aria-current="page"' : '' ?>>Manage</a>
            </div>
        </div>

        <hr>
        <form action="controller-admin/logout.php" method="POST" style="margin: 0;">
            <input type="hidden" name="csrfToken" value="<?= htmlspecialchars(adminCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
            <button class="sidebar-signout" type="submit" style="width: 100%; border: none; background: transparent; cursor: pointer; text-align: left;">
                <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                <span>Sign out</span>
            </button>
        </form>
    </aside>

    <main class="admin-content">