<?php
require_once __DIR__ . '/../models/review-model.php';
require_once __DIR__ . '/../models/user-model.php';

class ReviewRequestException extends RuntimeException {}

function startReviewSession(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    header('Cache-Control: no-store');
}

function reviewCsrfToken(): string
{
    if (!isset($_SESSION['review_csrf_token'])) {
        $_SESSION['review_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['review_csrf_token'];
}

function reviewPositiveId($value, string $label): int
{
    if ((!is_int($value) && !is_string($value))
        || !preg_match('/^[1-9][0-9]*$/D', (string) $value)
        || filter_var($value, FILTER_VALIDATE_INT) === false) {
        throw new ReviewRequestException('Invalid ' . $label . '.', 422);
    }
    return (int) $value;
}

function reviewDto(array $row, ?int $userId): array
{
    $isOwner = $userId !== null && (int) $row['user_id'] === $userId;
    $author = trim($row['first_name'] . ' ' . mb_substr($row['last_name'], 0, 1, 'UTF-8'));
    return [
        'id' => (int) $row['review_id'], 'orderId' => $isOwner ? (int) $row['order_id'] : null,
        'rating' => (int) $row['rating'], 'text' => $row['comment'] ?? '',
        'date' => substr($row['created_at'], 0, 10), 'createdAt' => $row['created_at'],
        'updatedAt' => $row['updated_at'], 'isOwner' => $isOwner,
        'author' => $isOwner ? 'You' : ($author !== '' ? $author . '.' : 'GreenDash user'),
    ];
}

function loadStallReviews(PDO $pdo, int $stallId, ?int $userId): array
{
    $reviews = array_map(static fn(array $row): array => reviewDto($row, $userId), getStallReviews($pdo, $stallId));
    $orders = $userId === null ? [] : getCompletedReviewOrders($pdo, $userId, $stallId);
    return [
        'success' => true, 'reviews' => $reviews,
        'eligibleOrders' => array_map(static fn(array $order): array => [
            'orderId' => (int) $order['order_id'], 'orderNumber' => $order['order_number'],
            'reviewId' => $order['review_id'] === null ? null : (int) $order['review_id'],
        ], $orders),
        'count' => count($reviews),
        'averageRating' => $reviews ? round(array_sum(array_column($reviews, 'rating')) / count($reviews), 2) : null,
    ];
}

function saveUserReview(PDO $pdo, int $userId, int $orderId, $rating, $comment): array
{
    if ((!is_int($rating) && !is_string($rating)) || !preg_match('/^[1-5]$/D', (string) $rating)) {
        throw new ReviewRequestException('Choose a rating from 1 to 5.', 422);
    }
    if (!is_string($comment) || !preg_match('//u', $comment) || mb_strlen($comment, 'UTF-8') > 500) {
        throw new ReviewRequestException('Your comment cannot exceed 500 characters.', 422);
    }
    return mutateUserReview($pdo, $userId, $orderId, (int) $rating, trim($comment));
}

function mutateUserReview(PDO $pdo, int $userId, int $orderId, ?int $rating, ?string $comment): array
{
    $pdo->beginTransaction();
    try {
        $user = getCheckoutUser($pdo, $userId);
        if (!$user || $user['acc_status'] !== 'active') {
            throw new ReviewRequestException('Your account cannot submit reviews. Please sign in again.', 403);
        }
        // Lock the parent order so concurrent saves still produce one review per order.
        $order = getReviewOrderForUpdate($pdo, $userId, $orderId);
        if (!$order) {
            throw new ReviewRequestException('Order not found.', 404);
        }
        if ($order['status'] !== 'completed') {
            throw new ReviewRequestException('You can only review completed orders.', 409);
        }
        $existing = getOrderReview($pdo, $orderId);
        if ($rating === null) {
            deleteOrderReview($pdo, $orderId);
            $result = ['success' => true, 'orderId' => $orderId];
        } else {
            $comment = $comment !== '' ? $comment : null;
            if ($existing) {
                updateOrderReview($pdo, $orderId, $rating, $comment);
            } else {
                insertOrderReview($pdo, $orderId, $rating, $comment);
            }
            $result = ['success' => true, 'review' => reviewDto(getOrderReview($pdo, $orderId), $userId)];
        }
        $pdo->commit();
        return $result;
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}
