<?php
require_once __DIR__ . '/reviews.php';
require_once __DIR__ . '/../models/order-model.php';

function loadUserOrders(PDO $pdo, int $userId): array
{
    $optionsByItem = [];
    foreach (getUserOrderOptions($pdo, $userId) as $option) {
        $optionsByItem[(int) $option['order_item_id']][] = $option['option_name'];
    }
    $itemsByOrder = [];
    foreach (getUserOrderItems($pdo, $userId) as $item) {
        $details = $optionsByItem[(int) $item['order_item_id']] ?? [];
        if ($item['special_instructions'] !== null && $item['special_instructions'] !== '') {
            $details[] = $item['special_instructions'];
        }
        $itemsByOrder[(int) $item['order_id']][] = [
            'name' => $item['item_name'], 'detail' => implode(' · ', $details),
            'quantity' => (int) $item['quantity'], 'total' => (float) $item['total_price'],
        ];
    }
    $statusLabels = ['pending' => 'Pending', 'preparing' => 'Preparing', 'ready' => 'Ready for pickup',
                     'completed' => 'Order collected', 'cancelled' => 'Order cancelled'];
    $paymentLabels = ['pending' => 'Pending', 'paid' => 'Paid', 'not_charged' => 'Not charged'];
    $orders = [];
    foreach (getUserOrderRecords($pdo, $userId) as $row) {
        $review = $row['review_id'] === null ? null : [
            'id' => (int) $row['review_id'], 'orderId' => (int) $row['order_id'],
            'rating' => (int) $row['review_rating'], 'text' => $row['review_comment'] ?? '',
            'createdAt' => $row['review_created_at'], 'updatedAt' => $row['review_updated_at'],
            'isOwner' => true,
        ];
        $orders[] = [
            'id' => $row['order_number'], 'orderId' => (int) $row['order_id'],
            'name' => $row['stall_name'], 'location' => $row['stall_location'],
            'logo' => $row['stall_logo'] ?? '', 'date' => $row['created_at'],
            'status' => $statusLabels[$row['status']] ?? $row['status'], 'statusClass' => $row['status'],
            'isOngoing' => in_array($row['status'], ['pending', 'preparing', 'ready'], true),
            'paymentMethod' => $row['payment_method'] === 'online' ? 'Online payment' : 'Cash',
            'paymentStatus' => $paymentLabels[$row['payment_status']] ?? $row['payment_status'],
            'total' => (float) $row['total_amount'], 'items' => $itemsByOrder[(int) $row['order_id']] ?? [],
            'review' => $review,
        ];
    }
    return $orders;
}
