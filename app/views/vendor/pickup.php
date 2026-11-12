<?php
if (!defined('GREENDASH_VENDOR_ORDERS_VIEW')) {
    $queryString = $_SERVER['QUERY_STRING'] ?? '';
    header('Location: ../../../controller-vendor/pickup.php' . ($queryString !== '' ? '?' . $queryString : ''), true, 302);
    exit;
}
require __DIR__ . '/order-header.php';
?>
<main class="vendor-pickup-layout" aria-labelledby="vendorPickupHeading">
    <h1 id="vendorPickupHeading">Order Pickup</h1>
    <p>Ask the customer for the order ID shown on their Orders page.</p>
    <form id="vendorPickupForm" class="vendor-pickup-form" method="get" action="pickup.php">
        <label for="vendorPickupOrderNumber">Order ID</label>
        <div class="vendor-pickup-search">
            <input type="text" id="vendorPickupOrderNumber" name="orderNumber" maxlength="20" required
                autocomplete="off" placeholder="Enter order ID">
            <button type="submit" class="vendor-action">Verify order</button>
        </div>
    </form>
    <p id="vendorPickupFeedback" class="vendor-page-feedback" role="status" aria-live="polite"></p>
    <section id="vendorPickupDetails" class="vendor-pickup-details" aria-label="Verified order" hidden>
        <?php require __DIR__ . '/order-details.php'; ?>
        <form id="vendorCollectionForm" class="vendor-collection-form" hidden>
            <label class="vendor-payment-confirmation">
                <input type="checkbox" id="vendorPaymentConfirmed" required>
                I have received or verified payment of <strong id="vendorPickupTotal"></strong>.
            </label>
            <button type="submit" class="vendor-action">Mark order as collected</button>
        </form>
    </section>
    <noscript>Enable JavaScript to verify and collect an order.</noscript>
</main>
<?php require __DIR__ . '/order-footer.php'; ?>
