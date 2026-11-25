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
                <form class="vendor-cancellation-form" id="vendorCancellationForm"
                    aria-labelledby="vendorCancellationHeading" hidden>
                    <h3 id="vendorCancellationHeading">Cancel order</h3>
                    <p>Up to 3 standard cancellations per vendor across all stalls each Monday–Sunday (Manila time).
                        Exceptions require details and are exempt from the limit.</p>
                    <fieldset id="vendorCancellationFields">
                        <label for="vendorCancellationReason">Reason</label>
                        <select id="vendorCancellationReason" name="cancellationReason" required>
                            <option value="" disabled selected>Choose a reason</option>
                            <option value="standard">Standard</option>
                            <option value="out_of_stock">Out of stock</option>
                            <option value="utility_outage">Utility outage</option>
                            <option value="emergency_closure">Emergency closure</option>
                        </select>
                        <label for="vendorCancellationDetails" id="vendorCancellationDetailsLabel">Details (optional)</label>
                        <textarea id="vendorCancellationDetails" name="cancellationDetails" maxlength="255" rows="3"></textarea>
                        <p id="vendorCancellationFeedback" role="status"></p>
                        <div class="vendor-live-actions">
                            <button type="button" class="vendor-action secondary" id="vendorKeepOrder">Keep order</button>
                            <button type="submit" class="vendor-action danger">Confirm cancellation</button>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>
</div>
