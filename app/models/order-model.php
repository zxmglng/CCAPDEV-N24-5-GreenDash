<?php

function insertCheckoutOrder(PDO $pdo, array $order): int
{
    $stmt = $pdo->prepare(
        'INSERT INTO orders (order_number, user_id, stall_id, stall_name, stall_location,
         status, payment_method, payment_status, payment_reference, pickup_type, pickup_at, total_amount)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $order['order_number'], $order['user_id'], $order['stall_id'], $order['stall_name'],
        $order['stall_location'], 'pending', $order['payment_method'], 'pending',
        $order['payment_reference'], $order['pickup_type'], $order['pickup_at'], $order['total_amount'],
    ]);
    return (int) $pdo->lastInsertId();
}

function insertCheckoutItem(PDO $pdo, int $orderId, array $item): int
{
    $stmt = $pdo->prepare(
        'INSERT INTO order_items (order_id, menu_item_id, item_name, category_name,
         special_instructions, quantity, base_price, unit_price, total_price)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $orderId, $item['menu_item_id'], $item['item_name'], $item['category_name'],
        $item['special_instructions'], $item['quantity'], $item['base_price'],
        $item['unit_price'], $item['total_price'],
    ]);
    return (int) $pdo->lastInsertId();
}

function insertCheckoutItemOption(PDO $pdo, int $orderItemId, array $option): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO order_item_options (order_item_id, option_id, option_group_name,
         option_name, quantity, extra_price) VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $orderItemId, null, $option['option_group_name'], $option['option_name'],
        1, $option['extra_price'],
    ]);
}

function getUserOrderRecords(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare(
        'SELECT o.*, s.image_path AS stall_logo, r.review_id, r.rating AS review_rating,
         r.`comment` AS review_comment, r.created_at AS review_created_at, r.updated_at AS review_updated_at
         FROM orders o LEFT JOIN stalls s ON s.id = o.stall_id
         LEFT JOIN reviews r ON r.order_id = o.order_id
         WHERE o.user_id = ? ORDER BY o.created_at DESC, o.order_id DESC'
    );
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUserOrderItems(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare('SELECT i.* FROM order_items i JOIN orders o ON o.order_id = i.order_id
                          WHERE o.user_id = ? ORDER BY i.order_item_id');
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUserOrderOptions(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare(
        'SELECT p.* FROM order_item_options p JOIN order_items i ON i.order_item_id = p.order_item_id
         JOIN orders o ON o.order_id = i.order_id WHERE o.user_id = ? ORDER BY p.order_item_option_id'
    );
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function cancelPendingUserOrder(PDO $pdo, int $userId, int $orderId): bool
{
    $stmt = $pdo->prepare(
        "UPDATE orders SET status = 'cancelled',
         payment_status = CASE WHEN payment_status = 'paid' THEN 'paid' ELSE 'not_charged' END
         WHERE order_id = ? AND user_id = ? AND status = 'pending'"
    );
    $stmt->execute([$orderId, $userId]);
    return $stmt->rowCount() === 1;
}
