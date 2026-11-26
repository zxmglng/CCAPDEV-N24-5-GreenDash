<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GreenDash - Select Role</title>

    <link rel="stylesheet" href="<?= htmlspecialchars($assetBase . 'bootstrap/css/bootstrap.min.css', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($assetBase . 'bootstrap-icons/bootstrap-icons.css', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($assetBase . 'style/centralized.css', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($assetBase . 'style/user.css', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">

    <link rel="icon" type="image/png" href="<?= htmlspecialchars($assetBase . 'assets/main/GreenDash_CIRCLE.png', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body class="select-role-page" data-assets-path="<?= htmlspecialchars($assetBase . 'assets/', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
    <main class="select-role-wrapper">
        <section class="select-role-card">
            <img class="select-role-logo" src="<?= htmlspecialchars($assetBase . 'assets/main/GreenDash_LOGO.png', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" alt="GreenDash Logo">
            <h1>Select your role</h1>
            <p class="select-role-subtext">
                Please choose your DLSU role to continue.
            </p>
            <?php if (!empty($error)): ?>
                <p class="select-role-error">
                    <?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </p>
            <?php endif; ?>
            <form method="post" action="select-role.php" class="select-role-form">
                <input type="hidden" name="csrfToken"
                    value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                <div class="role-card-group">
                    <input type="radio" id="roleStudent" name="user_role" value="student" required>
                    <label for="roleStudent" class="role-option-card">
                        <i class="bi bi-mortarboard role-option-icon"></i>
                        <span class="role-option-title">Student</span>
                        <span class="role-option-desc">
                            Order food between classes.
                        </span>
                    </label>
                    <input type="radio" id="roleFaculty" name="user_role" value="faculty" required>
                    <label for="roleFaculty" class="role-option-card">
                        <i class="bi bi-person-video3 role-option-icon"></i>
                        <span class="role-option-title">Faculty</span>
                        <span class="role-option-desc">
                            Enjoy convenient campus dining.
                        </span>
                    </label>
                    <input type="radio" id="roleStaff" name="user_role" value="staff" required>
                    <label for="roleStaff" class="role-option-card">
                        <i class="bi bi-person-badge role-option-icon"></i>
                        <span class="role-option-title">Staff</span>
                        <span class="role-option-desc">
                            Grab meals during your workday.
                        </span>
                    </label>
                </div>

                <button type="submit" class="select-role-button">
                    Continue
                </button>
            </form>
        </section>
    </main>

    <script type="module" src="<?= htmlspecialchars($assetBase . 'js/centralized.js', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"></script>

</body>
</html>
