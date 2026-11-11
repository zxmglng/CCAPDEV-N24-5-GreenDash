<!DOCTYPE html>
<html lang="en">
    <head>
        <base href="/GreenDash/">
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
                <input type="hidden" name="csrfToken" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                
                <img src="assets/main/GreenDash_LOGO.png" alt="GreenDash Logo">

                <h1>Admin Login</h1>
                <p>Enter your admin key.</p>

                <?php if ($loginError !== ''): ?>
                    <p class="admin-login-error" role="alert"><?= htmlspecialchars($loginError) ?></p>
                <?php endif; ?>

                <div class="admin-login-field">
                    <label for="adminKey">Admin Key</label>
                    <input type="password" id="adminKey" name="adminKey" autocomplete="off" required autofocus>
                </div>
                
                <button type="submit" class="admin-login-submit">Sign in</button>
                <p class="admin-login-error" id="adminLoginError" role="alert"></p>
            </form>
        </main>

        <script src="js/admin-login.js"></script>
    </body>
</html>