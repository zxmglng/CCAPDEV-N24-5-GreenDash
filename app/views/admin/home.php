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
            <h1 class="page-title">Hello, <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></h1>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/centralized.js"></script>
</body>
</html>