<!DOCTYPE html>
<html lang="en">
    <head>
        <base href="/GreenDash/">
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>GreenDash - Manage Users</title>
        <link rel="stylesheet" href="bootstrap/css/bootstrap-5.3.3.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="stylesheet" href="style/centralized.css">
        <link rel="stylesheet" href="style/admin-common.css">
        <link rel="stylesheet" href="style/admin-tables.css">
        <link rel="stylesheet" href="style/admin-modals.css">
        <link rel="stylesheet" href="style/admin-users.css">
        <link rel="icon" type="image/png" href="assets/main/GreenDash_CIRCLE.png">
    </head>
    <body class="admin-page"
          data-page="<?= htmlspecialchars($page) ?>"
          data-flash="<?= htmlspecialchars($flashAction) ?>">
        <?php require __DIR__ . '/nav.php'; ?>
            <h1 class="page-title">Users Directory</h1>

            <?php if ($error !== ''): ?>
                <p class="admin-flash" role="alert"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <div class="admin-toolbar">
                <div class="admin-search">
                    <i class="bi bi-search"></i>
                    <input id="searchInput" type="search" placeholder="Search by Name or Email...">
                </div>
                <select class="admin-select" id="filterRole" aria-label="Filter by role">
                    <option value="">All roles</option>
                    <option value="student">Student</option>
                    <option value="faculty">Faculty</option>
                    <option value="staff">Staff</option>
                    <option value="vendor">Vendor</option>
                    <option value="admin">Admin</option>
                </select>
                <select class="admin-select" id="sortField" aria-label="Sort by">
                    <option value="name">Name</option>
                    <option value="email">Email</option>
                    <option value="role">Role</option>
                </select>
                <button class="admin-icon-btn" id="sortBtn" type="button" aria-label="Toggle sort direction">
                    <i class="bi bi-sort-down-alt"></i>
                </button>
                <button class="admin-btn primary" id="addUserBtn" type="button">Add User</button>
                <button class="admin-btn danger" id="removeModeBtn" type="button">Remove User</button>
            </div>

            <div class="admin-table-wrap">
                <table class="admin-table users-table">
                    <thead>
                        <tr><th>Name</th><th>Email</th><th>Contact</th><th>Role</th></tr>
                    </thead>
                    <tbody id="usersBody">
                        <?php if (empty($users)): ?>
                            <tr><td colspan="4" style="text-align:center;">No users found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($users as $u): ?>
                                <tr class="user-row"
                                    data-id="<?= (int)$u['user_id'] ?>"
                                    data-name="<?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?>"
                                    data-email="<?= htmlspecialchars($u['email_add']) ?>"
                                    data-contact="<?= htmlspecialchars($u['contact_num'] ?? '') ?>"
                                    data-role="<?= strtolower($u['user_role']) ?>">
                                    <td><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></td>
                                    <td><?= htmlspecialchars($u['email_add']) ?></td>
                                    <td><?= htmlspecialchars($u['contact_num'] ?? '—') ?></td>
                                    <td><?= ucfirst($u['user_role']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Details modal -->
    <div class="admin-modal" id="userModal" role="dialog" aria-modal="true" aria-labelledby="userModalTitle">
        <div class="admin-modal-card">
            <button class="close-x" type="button" data-close aria-label="Close"><i class="bi bi-x-lg"></i></button>
            <h2 id="userModalTitle">User</h2>
            <div class="modal-row"><span class="modal-label">Email:</span><span class="modal-value" id="mEmail"></span></div>
            <div class="modal-row"><span class="modal-label">Contact:</span><span class="modal-value" id="mContact">—</span></div>
            <div class="modal-row"><span class="modal-label">Role:</span><span class="modal-value" id="mRole"></span></div>
            <div class="modal-row"><span class="modal-label">Status:</span><span class="modal-value" id="mStatus"></span></div>
            <div class="modal-actions">
                <button class="admin-btn primary" id="editUserBtn" type="button">Edit User</button>
            </div>
        </div>
    </div>

    <!-- Add modal -->
    <div class="admin-modal" id="addUserModal" role="dialog" aria-modal="true" aria-labelledby="addUserTitle">
        <div class="admin-modal-card">
            <button class="close-x" type="button" data-close aria-label="Close"><i class="bi bi-x-lg"></i></button>
            <h2 id="addUserTitle">Add a user</h2>
            <form id="addUserForm" method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-row">
                    <label class="modal-label" for="newName">First Name</label>
                    <input class="form-control" id="newName" name="first_name" type="text" required>
                </div>
                <div class="modal-row">
                    <label class="modal-label" for="newLastName">Last Name</label>
                    <input class="form-control" id="newLastName" name="last_name" type="text" required>
                </div>
                <div class="modal-row">
                    <label class="modal-label" for="newEmail">Email</label>
                    <input class="form-control" id="newEmail" name="email" type="email" required>
                </div>
                <div class="modal-row">
                    <label class="modal-label" for="newContact">Contact Number</label>
                    <input class="form-control" id="newContact" name="contact_num" type="tel">
                </div>
                <div class="modal-row">
                    <label class="modal-label" for="newRole">Role</label>
                    <select class="form-select" id="newRole" name="role">
                        <option value="student">Student</option>
                        <option value="faculty">Faculty</option>
                        <option value="staff">Staff</option>
                        <option value="vendor">Vendor</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <p class="field-error" id="addUserError" role="alert"><?= $flashAction === 'add' ? htmlspecialchars($error) : '' ?></p>
                <div class="modal-actions">
                    <button type="button" class="admin-btn ghost" data-close>Cancel</button>
                    <button type="submit" class="admin-btn primary">Add user</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit modal -->
    <div class="admin-modal" id="editUserModal" role="dialog" aria-modal="true" aria-labelledby="editUserTitle">
        <div class="admin-modal-card">
            <button class="close-x" type="button" data-close aria-label="Close"><i class="bi bi-x-lg"></i></button>
            <h2 id="editUserTitle">Edit user</h2>
            <form id="editUserForm" method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="editId">
                <div class="modal-row"><label class="modal-label" for="editFirstName">First Name</label>
                    <input class="form-control" id="editFirstName" name="first_name" type="text" required></div>
                <div class="modal-row"><label class="modal-label" for="editLastName">Last Name</label>
                    <input class="form-control" id="editLastName" name="last_name" type="text" required></div>
                <div class="modal-row"><label class="modal-label" for="editEmail">Email</label>
                    <input class="form-control" id="editEmail" name="email" type="email" required></div>
                <div class="modal-row"><label class="modal-label" for="editContact">Contact Number</label>
                    <input class="form-control" id="editContact" name="contact_num" type="tel"></div>

                <div class="modal-row"><label class="modal-label" for="editRole">Role</label>
                    <select class="form-select" id="editRole" name="role">
                        <option value="student">Student</option>
                        <option value="faculty">Faculty</option>
                        <option value="staff">Staff</option>
                        <option value="vendor">Vendor</option>
                        <option value="admin">Admin</option>
                    </select></div>
                <div class="modal-actions">
                    <button type="button" class="admin-btn ghost" data-close>Cancel</button>
                    <button type="submit" class="admin-btn primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Remove modal -->
    <div class="admin-modal" id="removeUserModal" role="dialog" aria-modal="true" aria-labelledby="removeUserTitle">
        <div class="admin-modal-card">
            <button class="close-x" type="button" data-close aria-label="Close"><i class="bi bi-x-lg"></i></button>
            <h2 id="removeUserTitle">Remove a user</h2>
            <p class="modal-hint">Pick the account you want to remove.</p>
            <div class="picker-list" id="removeUserList" role="radiogroup" aria-label="Users">
                <?php if (empty($users)): ?>
                    <p class="picker-empty">No users to remove.</p>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <label class="picker-item">
                            <input type="radio" name="removeUserPick" value="<?= (int)$u['user_id'] ?>">
                            <span><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?> — <?= htmlspecialchars($u['email_add']) ?></span>
                        </label>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="modal-actions">
                <button type="button" class="admin-btn ghost" data-close>Cancel</button>
                <button type="button" class="admin-btn danger" id="confirmRemoveUserBtn" disabled>Remove user</button>
            </div>
        </div>
    </div>

    <form method="POST" id="deleteForm" style="display:none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" id="deleteId">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/centralized.js"></script>
    <script src="js/admin-users.js"></script>
</body>
</html>