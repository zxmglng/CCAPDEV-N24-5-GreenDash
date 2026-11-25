<?php

function lockActiveVendorAccount(PDO $pdo, int $vendorId): bool
{
    $stmt = $pdo->prepare(
        "SELECT user_id FROM users WHERE user_id = ? AND user_role = 'vendor' AND acc_status = 'active' FOR UPDATE"
    );
    $stmt->execute([$vendorId]);
    return $stmt->fetchColumn() !== false;
}

/** @return array{DateTimeImmutable, DateTimeImmutable} */
function vendorCancellationWeek(DateTimeImmutable $date): array
{
    $start = $date->setTimezone(new DateTimeZone('Asia/Manila'))->modify('monday this week')->setTime(0, 0, 0);
    return [$start, $start->modify('+7 days')];
}

function getWeeklyVendorCancellationCount(PDO $pdo, int $vendorId, DateTimeImmutable $cancelledAt): int
{
    [$weekStart, $nextWeekStart] = vendorCancellationWeek($cancelledAt);
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM vendor_order_cancellations
         WHERE vendor_id = ? AND reason = 'standard' AND cancelled_at >= ? AND cancelled_at < ?"
    );
    $stmt->execute([$vendorId, $weekStart->format('Y-m-d H:i:s'), $nextWeekStart->format('Y-m-d H:i:s')]);
    return (int) $stmt->fetchColumn();
}

function insertVendorOrderCancellation(PDO $pdo, int $vendorId, int $orderId, string $reason, ?string $details,
    DateTimeImmutable $cancelledAt): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO vendor_order_cancellations (order_id, vendor_id, reason, details, cancelled_at)
         VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([$orderId, $vendorId, $reason, $details, $cancelledAt->format('Y-m-d H:i:s')]);
}

function getVendorOrderCancellations(PDO $pdo, int $vendorId, array $orderIds): array
{
    if (!$orderIds) {
        return [];
    }
    $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
    $stmt = $pdo->prepare(
        "SELECT order_id, reason, details, cancelled_at FROM vendor_order_cancellations
         WHERE vendor_id = ? AND order_id IN ($placeholders)"
    );
    $stmt->execute(array_merge([$vendorId], $orderIds));
    return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), null, 'order_id');
}

function getAdminCancellationVendors(PDO $pdo): array
{
    $stmt = $pdo->query(
        "SELECT u.user_id AS vendor_id, CONCAT(u.first_name, ' ', u.last_name) AS vendor_name
         FROM users u
         WHERE u.user_role = 'vendor'
            OR EXISTS (SELECT 1 FROM vendor_order_cancellations c WHERE c.vendor_id = u.user_id)
         ORDER BY vendor_name, vendor_id"
    );
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAdminVendorCancellationUsage(PDO $pdo, string $weekStart, string $nextWeekStart, ?int $vendorId): array
{
    $sql = "SELECT c.vendor_id, CONCAT(u.first_name, ' ', u.last_name) AS vendor_name,
                   SUM(CASE WHEN c.reason = 'standard' THEN 1 ELSE 0 END) AS standard_count,
                   SUM(CASE WHEN c.reason <> 'standard' THEN 1 ELSE 0 END) AS exception_count
            FROM vendor_order_cancellations c
            JOIN users u ON u.user_id = c.vendor_id
            WHERE c.cancelled_at >= ? AND c.cancelled_at < ?";
    $values = [$weekStart, $nextWeekStart];
    if ($vendorId !== null) {
        $sql .= ' AND c.vendor_id = ?';
        $values[] = $vendorId;
    }
    $stmt = $pdo->prepare($sql . ' GROUP BY c.vendor_id, u.first_name, u.last_name ORDER BY vendor_name, c.vendor_id');
    $stmt->execute($values);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAdminVendorCancellationRecords(
    PDO $pdo,
    ?int $vendorId,
    string $reason,
    ?string $weekStart,
    ?string $nextWeekStart,
    int $limit,
    int $offset
): array {
    $sql = "SELECT o.order_number, o.stall_name, CONCAT(u.first_name, ' ', u.last_name) AS vendor_name,
                   c.reason, c.details, c.cancelled_at
            FROM vendor_order_cancellations c
            JOIN orders o ON o.order_id = c.order_id
            JOIN users u ON u.user_id = c.vendor_id";
    $conditions = [];
    $values = [];
    if ($vendorId !== null) {
        $conditions[] = 'c.vendor_id = ?';
        $values[] = $vendorId;
    }
    if ($reason !== '') {
        $conditions[] = 'c.reason = ?';
        $values[] = $reason;
    }
    if ($weekStart !== null && $nextWeekStart !== null) {
        $conditions[] = 'c.cancelled_at >= ? AND c.cancelled_at < ?';
        $values[] = $weekStart;
        $values[] = $nextWeekStart;
    }
    if ($conditions) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    $stmt = $pdo->prepare($sql . ' ORDER BY c.cancelled_at DESC, c.order_id DESC LIMIT ? OFFSET ?');
    array_push($values, $limit, $offset);
    foreach ($values as $index => $value) {
        $stmt->bindValue($index + 1, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
