<?php
require_once __DIR__ . '/../app/core/checkout.php';
startCheckoutSession();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../app/views/user/login.php', true, 302);
    exit;
}

$csrfToken = checkoutCsrfToken();
$assetBase = '../';
$userPageBase = '../app/views/user/';
define('GREENDASH_CHECKOUT_VIEW', true);
require __DIR__ . '/../app/views/user/checkout.php';
