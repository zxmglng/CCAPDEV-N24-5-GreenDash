<?php
if (!defined('GREENDASH_VENDOR_SIGNUP_VIEW')) {
    header('Location: ../../../controller-vendor/signup.php', true, 302);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>GreenDash - Vendor Sign Up</title>
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
            <h1 class="page-title">Create a Vendor Account</h1>
            <p>Register with your email. An administrator must activate your account before you can manage stores.</p>

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="post" class="vendor-auth-form">
                <input type="hidden" name="csrfToken"
                    value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                <div class="mb-3 text-start">
                    <label for="firstName" class="form-label">First name</label>
                    <input class="form-control" id="firstName" name="first_name" type="text"
                        maxlength="100" autocomplete="given-name" required
                        value="<?= htmlspecialchars($firstNameValue, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                </div>
                <div class="mb-3 text-start">
                    <label for="lastName" class="form-label">Last name</label>
                    <input class="form-control" id="lastName" name="last_name" type="text"
                        maxlength="100" autocomplete="family-name" required
                        value="<?= htmlspecialchars($lastNameValue, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                </div>
                <div class="mb-3 text-start">
                    <label for="email" class="form-label">Email</label>
                    <input class="form-control" id="email" name="email" type="email"
                        maxlength="150" autocomplete="email" required
                        value="<?= htmlspecialchars($emailValue, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                </div>
                <div class="mb-3 text-start">
                    <label for="password" class="form-label">Password</label>
                    <input class="form-control" id="password" name="password" type="password"
                        minlength="8" maxlength="128" autocomplete="new-password" required>
                </div>
                <div class="mb-3 text-start">
                    <label for="confirmPassword" class="form-label">Confirm password</label>
                    <input class="form-control" id="confirmPassword" name="confirm_password" type="password"
                        minlength="8" maxlength="128" autocomplete="new-password" required>
                </div>
                <button type="submit" class="btn btn-success w-100">Create account</button>
            </form>

            <a class="back-link" href="login.php">Already have an account? Sign in</a>
        </main>
    </body>
</html>
