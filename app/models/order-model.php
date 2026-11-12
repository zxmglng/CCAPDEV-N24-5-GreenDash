<?php

// Order write helpers used by checkout; callers provide validated snapshot data.
function insertCheckoutOrder(PDO $pdo, array $order): int
{
    // Save an order-level snapshot so later stall/menu edits do not rewrite the receipt.
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
        $orderItemId, $option['option_id'], $option['option_group_name'], $option['option_name'],
        1, $option['extra_price'],
    ]);
}

// User order queries are scoped to the owner so one account cannot read another's history.
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
    // Load child rows separately so the order list can group each item's options in the core layer.
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
    // The status predicate makes cancellation atomic and rejects already-progressed orders.
    $stmt = $pdo->prepare(
        "UPDATE orders SET status = 'cancelled',
         payment_status = CASE WHEN payment_status = 'paid' THEN 'paid' ELSE 'not_charged' END
         WHERE order_id = ? AND user_id = ? AND status = 'pending'"
    );
    $stmt->execute([$orderId, $userId]);
    return $stmt->rowCount() === 1;
}

function getUserCompletedOrderCount(PDO $pdo, int $userId): int
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND status = 'completed'");
    $stmt->execute([$userId]);
    return (int) $stmt->fetchColumn();
}

function getUserTotalSpent(PDO $pdo, int $userId): float
{
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE user_id = ? AND status = 'completed'");
    $stmt->execute([$userId]);
    return (float) $stmt->fetchColumn();
}

function getUserFavoriteStall(PDO $pdo, int $userId): ?string
{
    $stmt = $pdo->prepare(
        "SELECT stall_name FROM orders WHERE user_id = ?
        AND status = 'completed' GROUP BY stall_name
        ORDER BY COUNT(*) DESC LIMIT 1"
        );
    $stmt->execute([$userId]);
    $stall = $stmt->fetchColumn();
    return $stall !== false ? $stall : null;
}

function getUserMonthlyOrderCounts(PDO $pdo, int $userId): array
{
    // Return a fixed 12-month array, including zeroes, for the profile chart.
    $stmt = $pdo->prepare(
        "SELECT MONTH(created_at) AS order_month, COUNT(*) AS total_orders
        FROM orders WHERE user_id = ? AND status = 'completed' AND YEAR(created_at) = YEAR(CURDATE())
        GROUP BY MONTH(created_at) ORDER BY MONTH(created_at)"
        );

    $stmt->execute([$userId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $monthlyOrders = array_fill(0, 12, 0);

    foreach ($rows as $row) {
        $monthIndex = (int) $row['order_month'] - 1;
        $monthlyOrders[$monthIndex] = (int) $row['total_orders'];
    }
    return $monthlyOrders;
}

function getVendorOrderRecords(PDO $pdo, int $vendorId, array $statuses = [], ?int $orderId = null,
    ?string $orderNumber = null): array
{
    // Restrict vendor results through owned stalls; optional filters are parameterized below.
    $sql = "SELECT o.*, u.first_name, u.last_name, r.rating AS review_rating, r.`comment` AS review_comment
            FROM orders o JOIN stalls s ON s.id = o.stall_id
            JOIN users u ON u.user_id = o.user_id
            LEFT JOIN reviews r ON r.order_id = o.order_id AND o.status = 'completed'
            WHERE s.vendor_id = ?";
    $values = [$vendorId];
    if ($statuses) {
        $sql .= ' AND o.status IN (' . implode(',', array_fill(0, count($statuses), '?')) . ')';
        $values = array_merge($values, $statuses);
    }
    if ($orderId !== null) {
        $sql .= ' AND o.order_id = ?';
        $values[] = $orderId;
    }
    if ($orderNumber !== null) {
        $sql .= ' AND o.order_number = ?';
        $values[] = $orderNumber;
    }
    $stmt = $pdo->prepare($sql . ' ORDER BY o.created_at DESC, o.order_id DESC');
    $stmt->execute($values);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getVendorOrderItems(PDO $pdo, int $vendorId, array $orderIds): array
{
    if (!$orderIds) return [];
    // Scope item details to stalls owned by this vendor as well as the requested order IDs.
    $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
    $stmt = $pdo->prepare(
        "SELECT i.* FROM order_items i JOIN orders o ON o.order_id = i.order_id
         JOIN stalls s ON s.id = o.stall_id WHERE s.vendor_id = ?
         AND o.order_id IN ($placeholders) ORDER BY i.order_item_id"
    );
    $stmt->execute(array_merge([$vendorId], $orderIds));
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getVendorOrderOptions(PDO $pdo, int $vendorId, array $orderIds): array
{
    if (!$orderIds) return [];
    $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
    $stmt = $pdo->prepare(
        "SELECT p.* FROM order_item_options p JOIN order_items i ON i.order_item_id = p.order_item_id
         JOIN orders o ON o.order_id = i.order_id JOIN stalls s ON s.id = o.stall_id
         WHERE s.vendor_id = ? AND o.order_id IN ($placeholders) ORDER BY p.order_item_option_id"
    );
    $stmt->execute(array_merge([$vendorId], $orderIds));
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getVendorOrderForUpdate(PDO $pdo, int $vendorId, int $orderId): ?array
{
    $stmt = $pdo->prepare('SELECT o.* FROM orders o JOIN stalls s ON s.id = o.stall_id
                          WHERE o.order_id = ? AND s.vendor_id = ? FOR UPDATE');
    $stmt->execute([$orderId, $vendorId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function updateVendorOrderStatus(PDO $pdo, int $vendorId, int $orderId, string $expectedStatus, string $status): bool
{
    // Update only from the expected current state, and only for an order at this vendor's stall.
    $stmt = $pdo->prepare(
        "UPDATE orders SET status = ?, payment_status = CASE
         WHEN ? = 'completed' THEN 'paid'
         WHEN ? = 'cancelled' AND payment_status <> 'paid' THEN 'not_charged'
         ELSE payment_status END
         WHERE order_id = ? AND status = ? AND stall_id IN (SELECT id FROM stalls WHERE vendor_id = ?)"
    );
    $stmt->execute([$status, $status, $status, $orderId, $expectedStatus, $vendorId]);
    return $stmt->rowCount() === 1;
}
