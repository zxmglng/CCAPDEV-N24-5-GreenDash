<?php
// 0 = Sunday ... 6 = Saturday,
$dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <base href="/GreenDash/">
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>GreenDash - Manage Stores</title>
        <link rel="stylesheet" href="bootstrap/css/bootstrap-5.3.3.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="stylesheet" href="style/centralized.css">
        <link rel="stylesheet" href="style/admin-common.css">
        <link rel="stylesheet" href="style/admin-tables.css">
        <link rel="stylesheet" href="style/admin-modals.css">
        <link rel="icon" type="image/png" href="assets/main/GreenDash_CIRCLE.png">
        
    </head>
    <body class="admin-page"
          data-page="<?= htmlspecialchars($page, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
          data-flash="<?= htmlspecialchars($flashAction, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
        <?php require __DIR__ . '/nav.php'; ?>
            <h1 class="page-title">Stalls Directory</h1>

            <?php if ($error !== ''): ?>
                <p class="admin-flash" role="alert"><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
            <?php endif; ?>

            <div class="admin-toolbar">
                <div class="admin-search">
                    <i class="bi bi-search"></i>
                    <input id="searchInput" type="search" placeholder="Search by Stall or Vendor...">
                </div>
                <select class="admin-select" id="filterLocation" aria-label="Filter by location">
                    <option value="">All locations</option>
                </select>
                <select class="admin-select" id="filterStatus" aria-label="Filter by status">
                    <option value="">All statuses</option>
                    <option value="open">Open</option>
                    <option value="closed">Closed</option>
                </select>
                <select class="admin-select" id="sortField" aria-label="Sort by">
                    <option value="stall">Stall name</option>
                    <option value="vendor">Vendor</option>
                    <option value="location">Location</option>
                </select>
                <button class="admin-icon-btn" id="sortBtn" type="button" aria-label="Toggle sort direction">
                    <i class="bi bi-sort-down-alt"></i>
                </button>
                <button class="admin-btn primary" id="addStoreBtn" type="button">Add Stall</button>
                <button class="admin-btn danger" id="removeModeBtn" type="button">Remove Stall</button>
            </div>

            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Stall</th>
                            <th>Vendor</th>
                            <th>Location</th>
                            <th>Operating Hours</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="storesBody">
                        <?php if (empty($stalls)): ?>
                            <tr><td colspan="5" style="text-align:center;">No stalls found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($stalls as $s): ?>
                                <tr class="stall-row"
                                    data-id="<?= (int)$s['id'] ?>"
                                    data-name="<?= htmlspecialchars($s['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                    data-vendor-id="<?= htmlspecialchars($s['vendor_id'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                    data-vendor-name="<?= htmlspecialchars($s['vendor_name'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                    data-hours="<?= htmlspecialchars($s['hours_text'] ?? 'Hours not set', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                    data-hours-json="<?= htmlspecialchars(json_encode($allHours[(int)$s['id']] ?? []), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                                    <td><?= htmlspecialchars($s['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($s['vendor_name'] ?: '—', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($s['location'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                    <td class="hours-cell"><?= htmlspecialchars($s['hours_text'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                    <td>
                                        <span class="status <?= $s['is_open'] ? 'open' : 'closed' ?>">
                                            <?= $s['is_open'] ? 'OPEN' : 'CLOSED' ?>
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

    <!-- Details modal -->
    <div class="admin-modal" id="storeModal" role="dialog" aria-modal="true" aria-labelledby="storeModalTitle">
        <div class="admin-modal-card">
            <button class="close-x" type="button" data-close aria-label="Close"><i class="bi bi-x-lg"></i></button>
            <h2 id="storeModalTitle">Stall</h2>
            <div class="modal-row"><span class="modal-label">Vendor Owner:</span><span class="modal-value" id="mOwner">—</span></div>
            <div class="modal-row"><span class="modal-label">Location:</span><span class="modal-value" id="mLocation">—</span></div>
            <div class="modal-row"><span class="modal-label">Operating Hours:</span><span class="modal-value" id="mHours">—</span></div>
            <div class="modal-actions">
                <button class="admin-btn primary" id="editStoreBtn" type="button">Edit Stall Details</button>
            </div>
        </div>
    </div>

    <!-- Add modal -->
    <div class="admin-modal" id="addStoreModal" role="dialog" aria-modal="true" aria-labelledby="addStoreTitle">
        <div class="admin-modal-card">
            <button class="close-x" type="button" data-close aria-label="Close"><i class="bi bi-x-lg"></i></button>
            <h2 id="addStoreTitle">Add a stall</h2>
            <form id="addStoreForm" method="POST">
                <input type="hidden" name="csrfToken" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                <input type="hidden" name="action" value="add">
                <div class="modal-row">
                    <label class="modal-label" for="newStall">Stall name</label>
                    <input class="form-control" id="newStall" name="name" type="text" required>
                </div>
                <div class="modal-row">
                    <label class="modal-label" for="newVendor">Vendor</label>
                    <select class="form-control" id="newVendor" name="vendor_id">
                        <option value=""> None </option>
                        <?php foreach ($vendors as $v): ?>
                            <option value="<?= (int)$v['user_id'] ?>">
                                <?= htmlspecialchars($v['first_name'] . ' ' . $v['last_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="modal-row">
                    <label class="modal-label" for="newLocation">Location</label>
                    <input class="form-control" id="newLocation" name="location" type="text" required>
                </div>
                <div class="modal-row">
                    <label class="modal-label">Operating hours</label>
                    <?php foreach ($dayNames as $i => $dayName): ?>
                        <div class="day-hours-row">
                            <input type="checkbox" class="form-check-input mt-0"
                                   name="hours[<?= $i ?>][enabled]" value="1" id="add-day-<?= $i ?>">
                            <label for="add-day-<?= $i ?>"><?= $dayName ?></label>
                            <input type="time" class="form-control form-control-sm"
                                   name="hours[<?= $i ?>][opens]" id="add-opens-<?= $i ?>" disabled>
                            <span>to</span>
                            <input type="time" class="form-control form-control-sm"
                                   name="hours[<?= $i ?>][closes]" id="add-closes-<?= $i ?>" disabled>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="modal-row">
                    <label class="modal-label" for="newStatus">Status</label>
                    <select class="form-select" id="newStatus" name="is_open">
                        <option value="1">Open</option>
                        <option value="0">Closed</option>
                    </select>
                </div>
                <p class="field-error" id="addStoreError" role="alert"><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                <div class="modal-actions">
                    <button type="button" class="admin-btn ghost" data-close>Cancel</button>
                    <button type="submit" class="admin-btn primary">Add stall</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit modal -->
    <div class="admin-modal" id="editStoreModal" role="dialog" aria-modal="true" aria-labelledby="editStoreTitle">
        <div class="admin-modal-card">
            <button class="close-x" type="button" data-close aria-label="Close"><i class="bi bi-x-lg"></i></button>
            <h2 id="editStoreTitle">Edit stall</h2>
            <form id="editStoreForm" method="POST">
                <input type="hidden" name="csrfToken" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="editStoreId">

                <div class="modal-row">
                    <label class="modal-label" for="editStall">Stall name</label>
                    <input class="form-control" id="editStall" name="name" type="text" required>
                </div>
                <div class="modal-row">
                    <label class="modal-label" for="editVendor">Vendor</label>
                    <select class="form-control" id="editVendor" name="vendor_id">
                        <option value=""> None </option>
                        <?php foreach ($vendors as $v): ?>
                            <option value="<?= (int)$v['user_id'] ?>">
                                <?= htmlspecialchars($v['first_name'] . ' ' . $v['last_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="modal-row">
                    <label class="modal-label" for="editLocation">Location</label>
                    <input class="form-control" id="editLocation" name="location" type="text" required>
                </div>
                <div class="modal-row">
                    <label class="modal-label">Operating hours</label>
                    <?php foreach ($dayNames as $i => $dayName): ?>
                        <div class="day-hours-row">
                            <input type="checkbox" class="form-check-input mt-0"
                                   name="hours[<?= $i ?>][enabled]" value="1" id="edit-day-<?= $i ?>">
                            <label for="edit-day-<?= $i ?>"><?= $dayName ?></label>
                            <input type="time" class="form-control form-control-sm"
                                   name="hours[<?= $i ?>][opens]" id="edit-opens-<?= $i ?>" disabled>
                            <span>to</span>
                            <input type="time" class="form-control form-control-sm"
                                   name="hours[<?= $i ?>][closes]" id="edit-closes-<?= $i ?>" disabled>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="modal-row">
                    <label class="modal-label" for="editStatus">Status</label>
                    <select class="form-select" id="editStatus" name="is_open">
                        <option value="1">Open</option>
                        <option value="0">Closed</option>
                    </select>
                </div>
                <div class="modal-actions">
                    <button type="button" class="admin-btn ghost" data-close>Cancel</button>
                    <button type="submit" class="admin-btn primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Remove modal -->
    <div class="admin-modal" id="removeStoreModal" role="dialog" aria-modal="true" aria-labelledby="removeStoreTitle">
        <div class="admin-modal-card">
            <button class="close-x" type="button" data-close aria-label="Close"><i class="bi bi-x-lg"></i></button>
            <h2 id="removeStoreTitle">Remove a stall</h2>
            <p class="modal-hint">Pick the stall you want to remove.</p>
            <div class="picker-list" id="removeStoreList" role="radiogroup" aria-label="Stalls">
                <?php if (empty($stalls)): ?>
                    <p class="picker-empty">No stalls to remove.</p>
                <?php else: ?>
                    <?php foreach ($stalls as $s): ?>
                        <label class="picker-item">
                            <input type="radio" name="removeStorePick" value="<?= (int)$s['id'] ?>">
                            <span><?= htmlspecialchars($s['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> — <?= htmlspecialchars($s['location'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                        </label>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="modal-actions">
                <button type="button" class="admin-btn ghost" data-close>Cancel</button>
                <button type="button" class="admin-btn danger" id="confirmRemoveStoreBtn" disabled>Remove stall</button>
            </div>
        </div>
    </div>

    <form method="POST" id="deleteStoreForm" style="display:none;">
        <input type="hidden" name="csrfToken" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" id="deleteStoreId">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/centralized.js"></script>
    <script src="js/admin-stores.js"></script>
</body>
</html>