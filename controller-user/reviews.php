<?php
require_once __DIR__ . '/../app/core/reviews.php';
require_once __DIR__ . '/../app/core/user-auth.php';
startReviewSession();
require_once __DIR__ . '/../app/core/user-auth.php';

header('Content-Type: application/json; charset=utf-8');

function sendReviewResponse(int $status, array $data): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    exit;
}

try {
    $method = $_SERVER['REQUEST_METHOD'];
    if (!in_array($method, ['GET', 'POST'], true)) {
        header('Allow: GET, POST');
        throw new ReviewRequestException('Method not allowed.', 405);
    }
    // GET is public. Only expose owner-specific data to a user-role session;
    // vendors/admins/guests see reviews as anonymous readers.
    $userId = (isset($_SESSION['user_id']) && isUserRoleAllowed($_SESSION['user_role'] ?? null))
        ? reviewPositiveId($_SESSION['user_id'], 'user')
        : null;
    if ($method === 'GET') {
        $stallId = reviewPositiveId($_GET['stallId'] ?? null, 'stall');
        require __DIR__ . '/../app/core/db.php';
        $result = loadStallReviews($pdo, $stallId, $userId);
        $result['csrfToken'] = $userId !== null ? reviewCsrfToken() : null;
        sendReviewResponse(200, $result);
    }
    // POST: require an authenticated user-role session.
    $userId = requireUserApi();
    if (!is_string($_POST['csrfToken'] ?? null) || !isset($_SESSION['review_csrf_token'])
        || !hash_equals($_SESSION['review_csrf_token'], $_POST['csrfToken'])) {
        throw new ReviewRequestException('Your session expired. Please reload the page.', 403);
    }
    $action = $_POST['action'] ?? '';
    if (!in_array($action, ['save', 'delete'], true)) {
        throw new ReviewRequestException('Invalid review action.', 422);
    }
    $orderId = reviewPositiveId($_POST['orderId'] ?? null, 'order');
    require __DIR__ . '/../app/core/db.php';
    $result = $action === 'save'
        ? saveUserReview($pdo, $userId, $orderId, $_POST['rating'] ?? null, $_POST['comment'] ?? '')
        : mutateUserReview($pdo, $userId, $orderId, null, null);
    sendReviewResponse(200, $result);
} catch (ReviewRequestException $error) {
    sendReviewResponse($error->getCode(), ['success' => false, 'message' => $error->getMessage()]);
} catch (Throwable $error) {
    error_log('Reviews request failed: ' . $error->getMessage());
    sendReviewResponse(500, ['success' => false, 'message' => 'Reviews are currently unavailable. Please try again.']);
}