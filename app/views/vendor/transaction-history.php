<?php
if (!defined('GREENDASH_VENDOR_ORDERS_VIEW')) {
    header('Location: ../../../controller-vendor/transaction-history.php', true, 302);
    exit;
}
require __DIR__ . '/order-header.php';
?>
<main class="vendor-history-layout" aria-labelledby="vendorHistoryHeading">
    <div class="vendor-history-heading">
        <div>
            <h1 id="vendorHistoryHeading">Transaction History</h1>
            <p>Completed and cancelled orders</p>
        </div>
        <div class="vendor-history-controls">
            <label class="vendor-sort-control">Status
                <select id="vendorHistoryFilter">
                    <option value="all">All past orders</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </label>
            <label class="vendor-sort-control">Stall
                <select id="vendorStallFilter">
                    <option value="all">All your stalls</option>
                    <?php foreach ($vendorStalls as $stall): ?>
                        <option value="<?= (int) $stall['id'] ?>"><?= htmlspecialchars($stall['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="vendor-history-search">
                <span class="visually-hidden">Search transaction history</span>
                <input id="vendorHistorySearch" type="search" placeholder="Search by order ID, name, or item">
            </label>
            <button type="button" class="vendor-action secondary" id="vendorRefreshOrders">Refresh</button>
        </div>
    </div>
    <p id="vendorPageFeedback" role="status" aria-live="polite"></p>
    <div class="vendor-history-table-wrap">
        <table class="vendor-history-table">
            <thead><tr>
                <th scope="col">Customer</th><th scope="col">Order ID</th><th scope="col">Stall</th>
                <th scope="col">Status</th><th scope="col">Ordered</th><th scope="col">Items</th>
                <th scope="col">Total</th><th scope="col">Rating</th>
            </tr></thead>
            <tbody id="vendorHistoryBody"></tbody>
        </table>
    </div>
    <p class="vendor-orders-empty" id="vendorHistoryEmpty" hidden>No past orders match your search.</p>
    <noscript>Enable JavaScript to view transaction details.</noscript>
</main>
<?php require __DIR__ . '/order-modal.php'; ?>
<?php require __DIR__ . '/order-footer.php'; ?>
