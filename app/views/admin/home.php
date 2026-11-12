<!DOCTYPE html>
<html lang="en">
    <head>
        <base href="/GreenDash/">
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>GreenDash - Admin Dashboard</title>
        <link rel="stylesheet" href="bootstrap/css/bootstrap-5.3.3.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="stylesheet" href="style/centralized.css">
        <link rel="stylesheet" href="style/admin-common.css">
        <link rel="icon" type="image/png" href="assets/main/GreenDash_CIRCLE.png">
    </head>
    <body class="admin-page" data-page="<?= htmlspecialchars($page) ?>">
        <?php require __DIR__ . '/nav.php'; ?>
            <h1 class="page-title">Hello, <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Administrator') ?></h1>

            <div class="admin-dashboard">
                <div class="admin-stat-card">
                    <h3>Pending Requests</h3>
                    <p><?= (int) $stats['pendingRequests'] ?></p>
                    <a href="controller-admin/store-requests.php">Review requests →</a>
                </div>
                <div class="admin-stat-card">
                    <h3>Stalls</h3>
                    <p><?= (int) $stats['stalls']['open'] ?> / <?= (int) $stats['stalls']['total'] ?></p>
                    <a href="controller-admin/manage-stores.php">Manage stalls →</a>
                </div>
                <div class="admin-stat-card">
                    <h3>Active Users</h3>
                    <p><?= (int) $stats['activeUsers'] ?></p>
                    <a href="controller-admin/manage-users.php">Manage users →</a>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/centralized.js"></script>
</body>
</html>