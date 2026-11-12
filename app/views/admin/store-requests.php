<!DOCTYPE html>
<html lang="en">
    <head>
        <base href="/GreenDash/">
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>GreenDash - Store Requests</title>
        <link rel="stylesheet" href="bootstrap/css/bootstrap-5.3.3.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="stylesheet" href="style/centralized.css">
        <link rel="stylesheet" href="style/admin-common.css">
        <link rel="stylesheet" href="style/admin-tables.css">
        <link rel="stylesheet" href="style/admin-modals.css">
        <link rel="stylesheet" href="style/admin-requests.css">
        <link rel="icon" type="image/png" href="assets/main/GreenDash_CIRCLE.png">
    </head>
    <body class="admin-page" data-page="<?= htmlspecialchars($page) ?>">
        <?php require __DIR__ . '/nav.php'; ?>
            <h1 class="page-title">Requests</h1>
            <?php if ($error !== ''): ?>
                <p class="admin-flash" role="alert"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <?php
            $tabs = ['pending' => 'Pending', 'approved' => 'Approved', 'denied' => 'Denied', '' => 'All'];
            ?>

            <div class="admin-toolbar">
                <div class="admin-search">
                    <i class="bi bi-search"></i>
                    <input id="searchInput" type="search" placeholder="Search by Stall or Vendor...">
                </div>
                <input class="admin-input" id="filterMonth" type="month" aria-label="Filter by month">
                <select class="admin-select" id="filterType" aria-label="Filter by type">
                    <option value="">All types</option>
                    <option value="Add Stall">Add Stall</option>
                    <option value="Remove Stall">Remove Stall</option>
                </select>
                <select class="admin-select" id="sortField" aria-label="Sort by">
                    <option value="stall">Stall name</option>
                    <option value="vendor">Vendor</option>
                    <option value="date">Date</option>
                </select>
                <button class="admin-icon-btn" id="sortBtn" type="button" aria-label="Toggle sort direction">
                    <i class="bi bi-sort-down-alt"></i>
                </button>
            </div>

            <div class="admin-table-wrap">
                <table class="admin-table requests-table">
                    <thead>
                        <tr>
                            <th>Stall Name</th>
                            <th>Vendor</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="requestsBody">
                        <?php if (empty($requests)): ?>
                            <tr><td colspan="5" class="empty-row" style="text-align:center;">No requests found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($requests as $r): ?>
                                <tr class="request-row"
                                    data-id="<?= (int)$r['id'] ?>"
                                    data-stall="<?= htmlspecialchars($r['proposed_name'] ?? $r['stall_name'] ?? '—') ?>"
                                    data-vendor-name="<?= htmlspecialchars($r['vendor_name']) ?>"
                                    data-vendor-email="<?= htmlspecialchars($r['vendor_email']) ?>"
                                    data-location="<?= htmlspecialchars($r['proposed_location'] ?? $r['stall_location'] ?? '—') ?>"
                                    data-date="<?= htmlspecialchars(substr($r['created_at'], 0, 10)) ?>"
                                    data-type="<?= $r['type'] === 'add' ? 'Add Stall' : 'Remove Stall' ?>"
                                    data-status="<?= htmlspecialchars($r['status']) ?>">
                                    <td><?= htmlspecialchars($r['proposed_name'] ?? $r['stall_name'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($r['vendor_name']) ?></td>
                                    <td><?= date('M j, Y', strtotime($r['created_at'])) ?></td>
                                    <td><?= $r['type'] === 'add' ? 'Add Stall' : 'Remove Stall' ?></td>
                                    <td>
                                        <span class="status <?= $r['status'] ?>">
                                            <?= strtoupper($r['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <div class="admin-modal" id="requestModal" role="dialog" aria-modal="true" aria-labelledby="requestModalTitle">
        <div class="admin-modal-card">
            <button class="close-x" type="button" data-close aria-label="Close"><i class="bi bi-x-lg"></i></button>
            <h2 id="requestModalTitle">Stall Name</h2>
            <div class="modal-row"><span class="modal-label">Vendor Owner:</span><span class="modal-value" id="mOwner">-</span></div>
            <div class="modal-row"><span class="modal-label">Contact:</span><span class="modal-value" id="mContact">-</span></div>
            <div class="modal-row"><span class="modal-label">Location:</span><span class="modal-value" id="mLocation">-</span></div>
            <div class="modal-row"><span class="modal-label">Operating Hours:</span><span class="modal-value" id="mHours">-</span></div>
            <div class="modal-actions">
                <button class="admin-btn primary" id="approveBtn" type="button">Approve</button>
                <button class="admin-btn danger" id="denyBtn" type="button">Deny</button>
            </div>
        </div>
    </div>

    <form method="POST" id="actionForm" style="display:none;">
        <input type="hidden" name="request_id" id="actionRequestId">
        <input type="hidden" name="action" id="actionAction">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/centralized.js"></script>
    <script src="js/admin-requests.js"></script>
</body>
</html>