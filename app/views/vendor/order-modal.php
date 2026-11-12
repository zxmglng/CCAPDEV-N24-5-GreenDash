<?php
if (!defined('GREENDASH_VENDOR_ORDERS_VIEW')) {
    http_response_code(404);
    exit;
}
?>
<div class="modal fade vendor-order-modal vendor-right-modal" id="vendorOrderModal" tabindex="-1"
    aria-labelledby="vendorOrderModalHeading" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content vendor-order-detail vendor-right-panel">
            <div class="vendor-order-modal-header">
                <h2 id="vendorOrderModalHeading">Order details</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close order details"></button>
            </div>
            <div class="vendor-order-modal-body">
                <p id="vendorModalFeedback" role="status" aria-live="polite"></p>
                <?php require __DIR__ . '/order-details.php'; ?>
            </div>
        </div>
    </div>
</div>
