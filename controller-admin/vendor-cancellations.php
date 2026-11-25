<?php
require_once __DIR__ . '/../app/core/admin-auth.php';
requireAdminLogin();
require_once __DIR__ . '/../app/models/vendor-cancellation-model.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    http_response_code(405);
    exit;
}

$reasons = [
    'standard' => 'Standard',
    'out_of_stock' => 'Out of stock',
    'utility_outage' => 'Utility outage',
    'emergency_closure' => 'Emergency closure',
];
$vendorId = filter_var($_GET['vendor'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
$reasonFilter = $_GET['reason'] ?? '';
if (!is_string($reasonFilter) || !isset($reasons[$reasonFilter])) {
    $reasonFilter = '';
}
$period = ($_GET['period'] ?? 'week') === 'all' ? 'all' : 'week';
$pageNumber = filter_var($_GET['page'] ?? null, FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1, 'max_range' => 1000000]]) ?: 1;
$pageSize = 25;

[$weekStart, $nextWeekStart] = vendorCancellationWeek(new DateTimeImmutable());
$weekLabel = $weekStart->format('M j') . ' – ' . $nextWeekStart->modify('-1 day')->format('M j, Y');
$startsAt = $weekStart->format('Y-m-d H:i:s');
$endsBefore = $nextWeekStart->format('Y-m-d H:i:s');
$previousPageUrl = $nextPageUrl = null;
$vendors = $weekUsage = $cancellations = [];
$error = '';

try {
    require __DIR__ . '/../app/core/db.php';
    $vendors = getAdminCancellationVendors($pdo);
    $weekUsage = getAdminVendorCancellationUsage($pdo, $startsAt, $endsBefore, $vendorId);
    $cancellations = getAdminVendorCancellationRecords(
        $pdo,
        $vendorId,
        $reasonFilter,
        $period === 'week' ? $startsAt : null,
        $period === 'week' ? $endsBefore : null,
        $pageSize + 1,
        ($pageNumber - 1) * $pageSize
    );
    $filters = ['vendor' => $vendorId, 'reason' => $reasonFilter, 'period' => $period];
    $pageUrl = static fn(int $number): string =>
        'controller-admin/vendor-cancellations.php?' . http_build_query($filters + ['page' => $number]);
    $previousPageUrl = $pageNumber > 1 ? $pageUrl($pageNumber - 1) : null;
    if (count($cancellations) > $pageSize) {
        array_pop($cancellations);
        $nextPageUrl = $pageUrl($pageNumber + 1);
    }
} catch (Throwable $exception) {
    http_response_code(500);
    error_log('Unable to load vendor cancellation records: ' . $exception->getMessage());
    $error = 'Unable to load vendor cancellations. Please try again.';
}

$page = 'vendor-cancellations';
define('GREENDASH_ADMIN_VIEW', true);
require __DIR__ . '/../app/views/admin/vendor-cancellations.php';
