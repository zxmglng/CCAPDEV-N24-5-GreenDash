<!DOCTYPE html>
<html lang="en">
    <head>
        <base href="/GreenDash/">
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>GreenDash - Admin Signup</title>
        <link rel="stylesheet" href="bootstrap/css/bootstrap-5.3.3.min.css">
        <link rel="stylesheet" href="bootstrap-icons/bootstrap-icons.css">
        <link rel="stylesheet" href="style/centralized.css">
        <link rel="stylesheet" href="style/admin-common.css">
        <link rel="stylesheet" href="style/admin-login.css">
        <link rel="icon" type="image/png" href="assets/main/GreenDash_CIRCLE.png">
    </head>
    <body class="admin-page">
        <main class="admin-login-wrap">
            <form class="admin-login-card" id="adminSignupForm" method="POST">
                <input type="hidden" name="csrfToken" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">

                <img src="assets/main/GreenDash_LOGO.png" alt="GreenDash Logo">

                <h1>Create Admin Account</h1>
                <p>Enter your details, then the admin key.</p>

                <?php if ($signupError !== ''): ?>
                    <p class="admin-login-error" role="alert"><?= htmlspecialchars($signupError, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                <?php endif; ?>

                <div class="admin-login-field">
                    <label for="firstName">First Name</label>
                    <input type="text" id="firstName" name="firstName" autocomplete="given-name" required
                           value="<?= htmlspecialchars($old['firstName'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                </div>

                <div class="admin-login-field">
                    <label for="lastName">Last Name</label>
                    <input type="text" id="lastName" name="lastName" autocomplete="family-name" required
                           value="<?= htmlspecialchars($old['lastName'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                </div>

                <div class="admin-login-field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" autocomplete="email" required
                           value="<?= htmlspecialchars($old['email'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                </div>

                <div class="admin-login-field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" autocomplete="new-password" required minlength="8">
                </div>

                <div class="admin-login-field">
                    <label for="confirmPassword">Confirm Password</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" autocomplete="new-password" required minlength="8">
                </div>

                <div class="admin-login-field">
                    <label for="adminKey">Admin Key</label>
                    <input type="password" id="adminKey" name="adminKey" autocomplete="off" required>
                </div>

                <button type="submit" class="admin-login-submit">Create account</button>
                <p class="admin-login-error" id="adminSignupError" role="alert"></p>
                <a href="controller-admin/login.php" class="admin-login-link">Back to admin login</a>
            </form>
        </main>

        <script src="js/admin-signup.js"></script>
    </body>
</html>