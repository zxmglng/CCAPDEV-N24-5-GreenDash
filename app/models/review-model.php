<?php

// Aggregate only reviews tied to completed orders for the directory's stall cards.
function getStallRatingSummaries(PDO $pdo): array
{
    $stmt = $pdo->query(
        "SELECT o.stall_id, AVG(r.rating) AS average_rating, COUNT(*) AS review_count
         FROM reviews r
         JOIN orders o ON o.order_id = r.order_id
         WHERE o.status = 'completed'
         GROUP BY o.stall_id"
    );
    $summaries = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $summaries[(int) $row['stall_id']] = [
            'averageRating' => (float) $row['average_rating'],
            'reviewCount' => (int) $row['review_count'],
        ];
    }
    return $summaries;
}

function getStallReviews(PDO $pdo, int $stallId): array
{
    // Include author details for public review display, scoped to one stall.
    $stmt = $pdo->prepare(
        "SELECT r.*, o.user_id, u.first_name, u.last_name
         FROM reviews r JOIN orders o ON o.order_id = r.order_id
         JOIN users u ON u.user_id = o.user_id
         WHERE o.stall_id = ? AND o.status = 'completed'
         ORDER BY r.created_at DESC, r.review_id DESC"
    );
    $stmt->execute([$stallId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCompletedReviewOrders(PDO $pdo, int $userId, int $stallId): array
{
    // These are the only orders eligible for review; existing review IDs support editing.
    $stmt = $pdo->prepare(
        "SELECT o.order_id, o.order_number, o.created_at, r.review_id
         FROM orders o LEFT JOIN reviews r ON r.order_id = o.order_id
         WHERE o.user_id = ? AND o.stall_id = ? AND o.status = 'completed'
         ORDER BY o.created_at DESC, o.order_id DESC"
    );
    $stmt->execute([$userId, $stallId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getReviewOrderForUpdate(PDO $pdo, int $userId, int $orderId): ?array
{
    // Lock the owned order while saving/deleting its review to prevent concurrent changes.
    $stmt = $pdo->prepare('SELECT order_id, user_id, stall_id, status FROM orders
                          WHERE order_id = ? AND user_id = ? FOR UPDATE');
    $stmt->execute([$orderId, $userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function getOrderReview(PDO $pdo, int $orderId): ?array
{
    $stmt = $pdo->prepare(
        'SELECT r.*, o.user_id, u.first_name, u.last_name
         FROM reviews r JOIN orders o ON o.order_id = r.order_id
         JOIN users u ON u.user_id = o.user_id WHERE r.order_id = ?'
    );
    $stmt->execute([$orderId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function insertOrderReview(PDO $pdo, int $orderId, int $rating, ?string $comment): void
{
    // Each completed order can have one review, enforced by the order_id key.
    $stmt = $pdo->prepare('INSERT INTO reviews (order_id, rating, `comment`) VALUES (?, ?, ?)');
    $stmt->execute([$orderId, $rating, $comment]);
}

function updateOrderReview(PDO $pdo, int $orderId, int $rating, ?string $comment): void
{
    // Refresh updated_at so the UI can distinguish a later edit from the original post.
    $stmt = $pdo->prepare('UPDATE reviews SET rating = ?, `comment` = ?, updated_at = CURRENT_TIMESTAMP WHERE order_id = ?');
    $stmt->execute([$rating, $comment, $orderId]);
}

function deleteOrderReview(PDO $pdo, int $orderId): void
{
    // Removing the order's review also removes it from the stall's calculated average.
    $stmt = $pdo->prepare('DELETE FROM reviews WHERE order_id = ?');
    $stmt->execute([$orderId]);
}
