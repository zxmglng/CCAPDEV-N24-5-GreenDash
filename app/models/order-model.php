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
