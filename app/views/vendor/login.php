<?php
if (!defined('GREENDASH_VENDOR_LOGIN_VIEW')) {
    header('Location: ../../../controller-vendor/login.php', true, 302);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>GreenDash - Vendor Sign In</title>
        <link rel="stylesheet" href="../bootstrap/css/bootstrap-5.3.3.min.css">
        <link rel="stylesheet" href="../bootstrap-icons/bootstrap-icons.css">
        <link rel="stylesheet" href="../style/centralized.css">
        <link rel="stylesheet" href="../style/vendors-common.css">
        <link rel="stylesheet" href="../style/vendors-login.css">
        <link rel="icon" type="image/png" href="../assets/main/GreenDash_CIRCLE.png">
    </head>
    <body class="vendor-page">
        <main class="vendor-login-wrap">
            <img src="../assets/main/GreenDash_LOGO.png" alt="GreenDash Logo">
            <h1 class="page-title">Vendor Sign In</h1>
            <p>Sign in to your vendor account. New accounts need administrator activation.</p>

            <?php if (isset($_GET['registered']) && $_GET['registered'] === '1'): ?>
                <div class="alert alert-success" role="status">
                    Your account was created. Please sign in. You will need admin activation before managing stores.
                </div>
            <?php endif; ?>
            <?php if ($error !== ''): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="post" class="vendor-auth-form">
                <input type="hidden" name="csrfToken"
                    value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                <div class="mb-3 text-start">
                    <label for="email" class="form-label">Email</label>
                    <input class="form-control" id="email" name="email" type="email"
                        autocomplete="username" required
                        value="<?= htmlspecialchars($emailValue, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                </div>
                <div class="mb-3 text-start">
                    <label for="password" class="form-label">Password</label>
                    <input class="form-control" id="password" name="password" type="password"
                        autocomplete="current-password" required>
                </div>
                <button type="submit" class="btn btn-success w-100">Sign in</button>
            </form>

            <div class="vendor-login-divider" aria-hidden="true"><span>or</span></div>
            <div id="googleLoginButton" data-login-redirect="../controller-vendor/profile.php" class="google-btn"></div>
            <a class="back-link" href="signup.php">Create a vendor account</a>
            <a class="back-link" href="../index.php">
                <i class="bi bi-chevron-left" aria-hidden="true"></i>
                Back to user login
            </a>
        </main>

        <script src="https://accounts.google.com/gsi/client" async defer></script>
        <script src="../js/vendor-auth.js"></script>
    </body>
</html>