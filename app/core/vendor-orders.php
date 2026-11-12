<?php
require_once __DIR__ . '/vendor-auth.php';
require_once __DIR__ . '/../models/order-model.php';
require_once __DIR__ . '/../models/stall-model.php';

function vendorOrderDate(?string $value): ?string
{
    return $value === null ? null
        : (new DateTimeImmutable($value, new DateTimeZone('Asia/Manila')))->format(DateTimeInterface::ATOM);
}

function loadVendorOrders(PDO $pdo, int $vendorId, array $statuses = [], ?int $orderId = null,
    ?string $orderNumber = null): array
{
    $records = getVendorOrderRecords($pdo, $vendorId, $statuses, $orderId, $orderNumber);
    $orderIds = array_column($records, 'order_id');
    $optionsByItem = [];
    foreach (getVendorOrderOptions($pdo, $vendorId, $orderIds) as $option) {
        $optionsByItem[(int) $option['order_item_id']][] = $option['option_group_name'] . ': '
            . $option['option_name'] . ((int) $option['quantity'] > 1 ? ' × ' . $option['quantity'] : '');
    }
    $itemsByOrder = [];
    foreach (getVendorOrderItems($pdo, $vendorId, $orderIds) as $item) {
        $details = $optionsByItem[(int) $item['order_item_id']] ?? [];
        if ($item['special_instructions'] !== null && $item['special_instructions'] !== '') {
            $details[] = 'Note: ' . $item['special_instructions'];
        }
        $itemsByOrder[(int) $item['order_id']][] = [
            'name' => $item['item_name'], 'detail' => implode(' · ', $details),
            'quantity' => (int) $item['quantity'], 'unitPrice' => (float) $item['unit_price'],
            'total' => (float) $item['total_price'],
        ];
    }
    return array_map(static function (array $row) use ($itemsByOrder): array {
        return [
            'orderId' => (int) $row['order_id'], 'orderNumber' => $row['order_number'],
            'customer' => trim($row['first_name'] . ' ' . $row['last_name']),
            'stallId' => (int) $row['stall_id'], 'stallName' => $row['stall_name'],
            'stallLocation' => $row['stall_location'], 'status' => $row['status'],
            'paymentMethod' => $row['payment_method'], 'paymentStatus' => $row['payment_status'],
            'paymentReference' => $row['payment_reference'], 'pickupType' => $row['pickup_type'],
            'pickupAt' => vendorOrderDate($row['pickup_at']), 'createdAt' => vendorOrderDate($row['created_at']),
            'total' => (float) $row['total_amount'], 'items' => $itemsByOrder[(int) $row['order_id']] ?? [],
            'review' => $row['review_rating'] === null ? null : [
                'rating' => (int) $row['review_rating'], 'comment' => $row['review_comment'] ?? '',
            ],
        ];
    }, $records);
}

function changeVendorOrderStatus(PDO $pdo, int $vendorId, int $orderId, string $expectedStatus,
    string $status, bool $paymentConfirmed): array
{
    $pdo->beginTransaction();
    try {
        requireVendorAccount($pdo, $vendorId);
        $order = getVendorOrderForUpdate($pdo, $vendorId, $orderId);
        if (!$order) {
            throw new VendorRequestException('Order not found for your stores.', 404);
        }
        if ($order['status'] !== $expectedStatus) {
            throw new VendorRequestException('This order has changed. Please refresh it before continuing.', 409);
        }
        $nextStatus = ['pending' => 'preparing', 'preparing' => 'ready', 'ready' => 'completed'];
        $canCancel = in_array($order['status'], ['pending', 'preparing', 'ready'], true)
            && $order['payment_status'] === 'pending';
        if (($nextStatus[$order['status']] ?? null) !== $status && !($status === 'cancelled' && $canCancel)) {
            throw new VendorRequestException('This status change is not allowed.', 409);
        }
        if ($status === 'completed' && !$paymentConfirmed) {
            throw new VendorRequestException('Confirm that payment has been received or verified before collection.', 422);
        }
        if (!updateVendorOrderStatus($pdo, $vendorId, $orderId, $expectedStatus, $status)) {
            throw new VendorRequestException('This order has changed. Please refresh it before continuing.', 409);
        }
        $updated = loadVendorOrders($pdo, $vendorId, [], $orderId)[0];
        $pdo->commit();
        return $updated;
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $error;
    }
}
