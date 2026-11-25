<?php
require_once __DIR__ . '/vendor-auth.php';
require_once __DIR__ . '/../models/order-model.php';
require_once __DIR__ . '/../models/stall-model.php';
require_once __DIR__ . '/../models/vendor-cancellation-model.php';

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
    $cancelledOrders = array_filter($records, static fn(array $row): bool => $row['status'] === 'cancelled');
    $cancellations = getVendorOrderCancellations($pdo, $vendorId, array_column($cancelledOrders, 'order_id'));
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
    return array_map(static function (array $row) use ($itemsByOrder, $cancellations): array {
        $cancellation = $cancellations[(int) $row['order_id']] ?? null;
        return [
            'orderId' => (int) $row['order_id'], 'orderNumber' => $row['order_number'],
            'customer' => trim($row['first_name'] . ' ' . $row['last_name']),
            'stallId' => (int) $row['stall_id'], 'stallName' => $row['stall_name'],
            'stallLocation' => $row['stall_location'], 'status' => $row['status'],
            'paymentMethod' => $row['payment_method'], 'paymentStatus' => $row['payment_status'],
            'paymentReference' => $row['payment_reference'], 'pickupType' => $row['pickup_type'],
            'pickupAt' => vendorOrderDate($row['pickup_at']), 'createdAt' => vendorOrderDate($row['created_at']),
            'total' => (float) $row['total_amount'], 'items' => $itemsByOrder[(int) $row['order_id']] ?? [],
            'cancellationReason' => $cancellation['reason'] ?? null,
            'cancellationDetails' => $cancellation['details'] ?? null,
            'cancelledAt' => vendorOrderDate($cancellation['cancelled_at'] ?? null),
            'review' => $row['review_rating'] === null ? null : [
                'rating' => (int) $row['review_rating'], 'comment' => $row['review_comment'] ?? '',
            ],
        ];
    }, $records);
}

function validateVendorCancellation(mixed $reason, mixed $details): array
{
    if (!in_array($reason, ['standard', 'out_of_stock', 'utility_outage', 'emergency_closure'], true)) {
        throw new VendorRequestException('Choose a valid cancellation reason.', 422);
    }
    $details = $details ?? '';
    if (!is_string($details) || !mb_check_encoding($details, 'UTF-8') || mb_strlen($details, 'UTF-8') > 255) {
        throw new VendorRequestException('Cancellation details must be valid text of up to 255 characters.', 422);
    }
    $details = preg_replace('/\A\s+|\s+\z/u', '', $details);
    if ($reason !== 'standard' && $details === '') {
        throw new VendorRequestException('Enter details explaining the cancellation exception.', 422);
    }
    return [$reason, $details !== '' ? $details : null];
}

function changeVendorOrderStatus(PDO $pdo, int $vendorId, int $orderId, string $expectedStatus,
    string $status, bool $paymentConfirmed, mixed $cancellationReason = null, mixed $cancellationDetails = null): array
{
    $cancellation = $status === 'cancelled' ? validateVendorCancellation($cancellationReason, $cancellationDetails) : null;
    $pdo->beginTransaction();
    try {
        if ($cancellation !== null) {
            // Serialize cancellations across this vendor's stalls before any transaction snapshot reads.
            if (!lockActiveVendorAccount($pdo, $vendorId)) {
                throw new VendorRequestException('An active vendor account is required. Please contact your administrator.', 403);
            }
        } else {
            requireVendorAccount($pdo, $vendorId);
        }
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
        if ($cancellation !== null) {
            [$reason, $details] = $cancellation;
            $cancelledAt = new DateTimeImmutable('now', new DateTimeZone('Asia/Manila'));
            if ($reason === 'standard' && getWeeklyVendorCancellationCount($pdo, $vendorId, $cancelledAt) >= 3) {
                throw new VendorRequestException('You have reached the limit of 3 standard cancellations this week.', 409);
            }
            insertVendorOrderCancellation($pdo, $vendorId, $orderId, $reason, $details, $cancelledAt);
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
