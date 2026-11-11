<?php
if (!defined('GREENDASH_CHECKOUT_VIEW')) {
    header('Location: ../../../controller-user/checkout.php', true, 307);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title>GreenDash - Checkout</title>
        <link rel="stylesheet" href="../style/centralized.css">
        <link rel="stylesheet" href="../style/user.css">
        <link rel="stylesheet" href="../style/checkout.css">
        <link rel="icon" type="image/png" href="../assets/main/GreenDash_CIRCLE.png">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body data-assets-path="../assets/">
        <?php include __DIR__ . "/../nav.php"; ?>

        <main class="checkout-page">
            <section class="checkout-main">
                <h1>Checkout</h1>
                <div class="checkout-stall">
                    <img id="checkoutStallLogo" alt="">
                    <div>
                        <div class="checkout-stall-name">
                            <h2 id="checkoutStallName"></h2>
                            <strong class="checkout-order-id" id="checkoutOrderNumber">New order</strong>
                        </div>
                        <p id="checkoutStallLocation"></p>
                        <p id="checkoutStallHours"></p>
                    </div>
                </div>
                <form id="checkoutForm" class="payment-form" method="post" action="submit-order.php"
                    data-orders-url="../app/views/user/orders.php"
                    data-directory-url="../app/views/user/stalls-directory.php" novalidate>
                    <input type="hidden" name="csrfToken" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="stallId" id="checkoutStallId">
                    <input type="hidden" name="orderItems" id="checkoutOrderItems">
                    <input type="hidden" name="requestId" id="checkoutRequestId">
                    <h2>Mode of payment</h2>
                    <label><input type="radio" name="paymentMethod" value="cash" checked> Cash</label>
                    <label class="online-payment-option">
                        <input type="radio" name="paymentMethod" value="online">
                        Online payment (QR Code)
                        <img class="qr-placeholder" src="https://via.placeholder.com/240x240.png?text=QR+Code"
                            alt="QR code placeholder" hidden>
                    </label>
                    <div class="online-payment-fields" id="onlinePaymentFields" hidden>
                        <div class="reference-number-field">
                            <label for="referenceNumber">Reference number</label>
                            <input id="referenceNumber" name="referenceNumber" type="text" maxlength="40"
                                placeholder="Enter reference number">
                        </div>
                    </div>
                    <button type="submit" class="submit-order-button">Submit order</button>
                </form>
            </section>
            <aside class="checkout-order">
                <h2>Your order</h2>
                <div id="checkoutItems"></div>
                <div class="checkout-pickup">
                    <strong>Pick-up time</strong>
                    <select id="checkoutPickupTime" name="pickupTime" form="checkoutForm" aria-label="Pick-up time"></select>
                </div>
                <div class="checkout-total">
                    <strong>Total</strong>
                    <strong id="checkoutTotal"></strong>
                </div>
            </aside>
        </main>
        <div class="checkout-message-backdrop" id="checkoutMessage" hidden>
            <section class="checkout-message" role="alertdialog" aria-modal="true"
                aria-labelledby="checkoutMessageTitle">
                <h2 id="checkoutMessageTitle"></h2>
                <button type="button" class="submit-order-button" id="closeCheckoutMessage">Okay</button>
            </section>
        </div>
        <script src="../js/checkout.js"></script>
        <script type="module" src="../js/centralized.js"></script>
    </body>
</html>
