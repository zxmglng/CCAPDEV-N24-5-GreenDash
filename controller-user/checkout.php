<?php
require_once __DIR__ . '/../app/core/checkout.php';
startCheckoutSession();

// Checkout requires an authenticated user and a session-bound CSRF token.
if (!isset($_SESSION['user_id'])) {
    header('Location: ../app/views/user/login.php', true, 302);
    exit;
}

$csrfToken = checkoutCsrfToken();
$assetBase = '../';
$userPageBase = '../app/views/user/';
// Give the template its CSRF token and path bases, then render it through this guarded entry point.
define('GREENDASH_CHECKOUT_VIEW', true);
require __DIR__ . '/../app/views/user/checkout.php';
