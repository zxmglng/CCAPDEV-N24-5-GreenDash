<?php
if (!defined('GREENDASH_VENDOR_ORDERS_VIEW')) {
    http_response_code(404);
    exit;
}
?>
    <script type="application/json" id="vendorOrdersData"><?= json_encode($vendorOrdersData,
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) ?></script>
    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../js/vendor-orders.js"></script>
    <script type="module" src="../js/centralized.js"></script>
</body>
</html>
