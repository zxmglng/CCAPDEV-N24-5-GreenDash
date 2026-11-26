<?php $appBasePath = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/') . '/'; ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <base href="<?= htmlspecialchars($appBasePath, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>GreenDash - Admin Login</title>
        <link rel="stylesheet" href="bootstrap/css/bootstrap-5.3.3.min.css">
        <link rel="stylesheet" href="bootstrap-icons/bootstrap-icons.css">
        <link rel="stylesheet" href="style/centralized.css">
        <link rel="stylesheet" href="style/admin-common.css">
        <link rel="stylesheet" href="style/admin-login.css">
        <link rel="icon" type="image/png" href="assets/main/GreenDash_CIRCLE.png">
    </head>
    <body class="admin-page">
        <main class="admin-login-wrap">
            <form class="admin-login-card" id="adminLoginForm" method="POST">
                <input type="hidden" name="csrfToken" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">

                <img src="assets/main/GreenDash_LOGO.png" alt="GreenDash Logo">

                <h1>Admin Login</h1>
                <p>Sign in with your admin credentials.</p>

                <?php if ($loginError !== ''): ?>
                    <p class="admin-login-error" role="alert"><?= htmlspecialchars($loginError, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                <?php endif; ?>

                <div class="admin-login-field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" autocomplete="username" required autofocus
                        value="<?= htmlspecialchars($oldEmail, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                </div>

                <div class="admin-login-field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" autocomplete="current-password" required>
                </div>

                <button type="submit" class="admin-login-submit">Sign in</button>
                <p class="admin-login-error" id="adminLoginError" role="alert"></p>
                <a href="controller-admin/signup.php" class="admin-login-link">Create an admin account</a>
            </form>
        </main>

        <script src="js/admin-login.js"></script>
    </body>
</html>
