<?php
if (!defined('GREENDASH_ADMIN_VIEW')) {
    header('Location: ../../../controller-admin/activate-vendors.php', true, 302);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <base href="<?= htmlspecialchars($appBasePath, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>GreenDash - Activate Vendors</title>
        <link rel="stylesheet" href="bootstrap/css/bootstrap-5.3.3.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="stylesheet" href="style/centralized.css">
        <link rel="stylesheet" href="style/admin-common.css">
        <link rel="stylesheet" href="style/admin-tables.css">
        <link rel="icon" type="image/png" href="assets/main/GreenDash_CIRCLE.png">
    </head>
    <body class="admin-page" data-page="<?= htmlspecialchars($page, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
        <?php require __DIR__ . '/nav.php'; ?>

            <h1 class="page-title">Pending Vendor Accounts</h1>

            <?php if ($activated): ?>
                <p class="admin-flash" role="status">Vendor account activated.</p>
            <?php endif; ?>
            <?php if ($error !== ''): ?>
                <p class="admin-flash" role="alert"><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
            <?php endif; ?>

            <?php if (empty($pendingVendors)): ?>
                <p>No vendor accounts are waiting for activation.</p>
            <?php else: ?>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr><th>Name</th><th>Email</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingVendors as $vendor): ?>
                                <tr>
                                    <td><?= htmlspecialchars($vendor['first_name'] . ' ' . $vendor['last_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($vendor['email_add'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                    <td>
                                        <form method="post" action="controller-admin/activate-vendors.php">
                                            <input type="hidden" name="csrfToken" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                                            <input type="hidden" name="action" value="activate-vendor">
                                            <input type="hidden" name="id" value="<?= (int) $vendor['user_id'] ?>">
                                            <button type="submit" class="admin-btn primary">Activate</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/centralized.js"></script>
</body>
</html>
