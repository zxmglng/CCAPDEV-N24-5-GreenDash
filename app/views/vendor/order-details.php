<?php
if (!defined('GREENDASH_VENDOR_ORDERS_VIEW')) {
    http_response_code(404);
    exit;
}
?>
<div data-order-details>
    <div class="vendor-modal-items" data-order-items></div>
    <div class="vendor-order-summary">
        <p class="vendor-order-id" data-order-field="orderNumber"></p>
        <dl>
            <div><dt>Customer:</dt><dd data-order-field="customer"></dd></div>
            <div><dt>Stall:</dt><dd data-order-field="stallName"></dd></div>
            <div><dt>Location:</dt><dd data-order-field="stallLocation"></dd></div>
            <div><dt>Status:</dt><dd data-order-field="status"></dd></div>
            <div><dt>Pickup:</dt><dd data-order-field="pickup"></dd></div>
            <div><dt>Ordered:</dt><dd data-order-field="createdAt"></dd></div>
            <div><dt>Payment method:</dt><dd data-order-field="paymentMethod"></dd></div>
            <div><dt>Payment status:</dt><dd data-order-field="paymentStatus"></dd></div>
            <div data-order-reference hidden><dt>Reference:</dt><dd data-order-field="paymentReference"></dd></div>
            <div><dt>Total:</dt><dd data-order-field="total"></dd></div>
        </dl>
        <div class="vendor-order-review" data-order-review hidden></div>
    </div>
    <div class="vendor-live-actions" data-order-actions hidden>
        <button type="button" class="vendor-action" data-order-advance hidden></button>
        <a class="vendor-action" data-order-pickup hidden>Verify pickup</a>
        <button type="button" class="vendor-action danger" data-order-cancel hidden>Cancel order</button>
    </div>
</div>
