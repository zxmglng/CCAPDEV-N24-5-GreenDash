<?php
if (!defined('GREENDASH_ADMIN_VIEW')) {
    header('Location: ../../../controller-admin/vendor-cancellations.php', true, 302);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <base href="../">
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>GreenDash - Vendor Cancellations</title>
        <link rel="stylesheet" href="bootstrap/css/bootstrap-5.3.3.min.css">
        <link rel="stylesheet" href="bootstrap-icons/bootstrap-icons.css">
        <link rel="stylesheet" href="style/centralized.css">
        <link rel="stylesheet" href="style/admin-common.css">
        <link rel="stylesheet" href="style/admin-tables.css">
        <link rel="icon" type="image/png" href="assets/main/GreenDash_CIRCLE.png">
    </head>
    <body class="admin-page">
        <?php require __DIR__ . '/nav.php'; ?>
            <h1 class="page-title">Vendor cancellations</h1>

            <?php if ($error !== ''): ?>
                <p class="admin-flash" role="alert"><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
            <?php else: ?>
                <form class="admin-toolbar" method="get" action="controller-admin/vendor-cancellations.php">
                    <select class="admin-select" name="vendor" aria-label="Vendor">
                        <option value="">All vendors</option>
                        <?php foreach ($vendors as $vendor): ?>
                            <?php $id = (int) $vendor['vendor_id']; ?>
                            <option value="<?= $id ?>"<?= $vendorId === $id ? ' selected' : '' ?>>
                                <?= htmlspecialchars($vendor['vendor_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select class="admin-select" name="reason" aria-label="Cancellation reason">
                        <option value="">All reasons</option>
                        <?php foreach ($reasons as $code => $label): ?>
                            <option value="<?= htmlspecialchars($code, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                <?= $reasonFilter === $code ? 'selected' : '' ?>>
                                <?= htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select class="admin-select" name="period" aria-label="History period">
                        <option value="week"<?= $period === 'week' ? ' selected' : '' ?>>Current week</option>
                        <option value="all"<?= $period === 'all' ? ' selected' : '' ?>>All time</option>
                    </select>
                    <button class="admin-btn primary" type="submit">Apply filters</button>
                </form>

                <section aria-labelledby="weeklyCancellationHeading">
                    <h2 id="weeklyCancellationHeading" class="h4 mt-4">Weekly cancellation usage</h2>
                    <p><?= htmlspecialchars($weekLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> (Manila time)</p>
                    <div class="admin-table-wrap overflow-auto">
                        <table id="weeklyCancellationUsage" class="admin-table read-only">
                            <thead>
                                <tr>
                                    <th scope="col">Vendor</th>
                                    <th scope="col">Standard cancellations</th>
                                    <th scope="col">Exception cancellations</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$weekUsage): ?>
                                    <tr class="empty-row"><td colspan="3">No cancellations recorded this week.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($weekUsage as $usage): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($usage['vendor_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                            <td><?= (int) $usage['standard_count'] ?> / 3</td>
                                            <td><?= (int) $usage['exception_count'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section aria-labelledby="vendorCancellationHistoryHeading">
                    <h2 id="vendorCancellationHistoryHeading" class="h4 mt-4">Cancellation history</h2>
                    <div class="admin-table-wrap overflow-auto">
                        <table id="vendorCancellationHistory" class="admin-table read-only">
                            <thead>
                                <tr>
                                    <th scope="col">Cancelled at (Manila time)</th>
                                    <th scope="col">Order</th>
                                    <th scope="col">Vendor</th>
                                    <th scope="col">Stall</th>
                                    <th scope="col">Reason</th>
                                    <th scope="col">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$cancellations): ?>
                                    <tr class="empty-row"><td colspan="6">No cancellations match these filters.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($cancellations as $cancellation): ?>
                                        <tr>
                                            <td class="text-nowrap"><?= htmlspecialchars($cancellation['cancelled_at'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($cancellation['order_number'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($cancellation['vendor_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($cancellation['stall_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($reasons[$cancellation['reason']] ?? $cancellation['reason'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                            <td class="text-break"><?= nl2br(htmlspecialchars($cancellation['details'] ?? '—', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if ($previousPageUrl !== null || $nextPageUrl !== null): ?>
                        <nav class="admin-toolbar mt-3" aria-label="Cancellation history pages">
                            <?php if ($previousPageUrl !== null): ?>
                                <a class="admin-btn ghost" rel="prev"
                                   href="<?= htmlspecialchars($previousPageUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">Previous page</a>
                            <?php endif; ?>
                            <?php if ($nextPageUrl !== null): ?>
                                <a class="admin-btn ghost" rel="next"
                                   href="<?= htmlspecialchars($nextPageUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">Next page</a>
                            <?php endif; ?>
                        </nav>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/centralized.js"></script>
</body>
</html>
