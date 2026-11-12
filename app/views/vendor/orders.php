<?php
if (!defined('GREENDASH_VENDOR_ORDERS_VIEW')) {
    $queryString = $_SERVER['QUERY_STRING'] ?? '';
    header('Location: ../../../controller-vendor/orders.php' . ($queryString !== '' ? '?' . $queryString : ''), true, 302);
    exit;
}
require __DIR__ . '/order-header.php';
?>
<main class="vendor-orders-layout">
    <section class="vendor-orders-list" aria-labelledby="ongoingOrdersHeading">
        <label class="vendor-search-container">
            <span class="visually-hidden">Search orders</span>
            <input type="search" id="vendorOrderSearchInput" aria-label="Search orders by order ID, customer, or item"
                placeholder="Search orders by order ID, name, or item">
        </label>
        <div class="vendor-orders-heading">
            <h1 id="ongoingOrdersHeading">Active Orders</h1>
            <div class="vendor-order-controls">
                <label class="vendor-sort-control">Sort by
                    <select id="vendorOrderSort">
                        <option value="pickup">Pickup time (Now first)</option>
                        <option value="status">Status</option>
                    </select>
                </label>
                <label class="vendor-sort-control">Status
                    <select id="vendorOrderStatusFilter">
                        <option value="all">All active</option>
                        <option value="pending">Pending</option>
                        <option value="preparing">Preparing</option>
                        <option value="ready">Ready for pickup</option>
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
                <button type="button" class="vendor-action secondary" id="vendorRefreshOrders">Refresh</button>
            </div>
        </div>
        <p id="vendorPageFeedback" role="status" aria-live="polite"></p>
        <div class="vendor-order-grid" id="vendorOrderGrid"></div>
        <p class="vendor-orders-empty" id="vendorOrdersEmpty" hidden>No active orders match your search.</p>
        <noscript>Enable JavaScript to view and manage your orders.</noscript>
    </section>
</main>
<?php require __DIR__ . '/order-modal.php'; ?>
<?php require __DIR__ . '/order-footer.php'; ?>
