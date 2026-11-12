<!DOCTYPE html>
<html lang="en">
<head>
    <title>GreenDash - Select Role</title>

    <link rel="stylesheet" href="<?= $assetBase ?>bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= $assetBase ?>style/centralized.css">
    <link rel="stylesheet" href="<?= $assetBase ?>style/user.css">

    <link rel="icon" type="image/png" href="<?= $assetBase ?>assets/main/GreenDash_CIRCLE.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body data-assets-path="<?= htmlspecialchars($assetBase . 'assets/', ENT_QUOTES, 'UTF-8') ?>">
    <main class="container mt-5">
        <h1>Select your role</h1>
        <p>Please choose your DLSU role to continue.</p>
        <?php if (!empty($error)): ?>
            <p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <form method="post" action="select-role.php">
            <select name="user_role" required>
                <option value="">Select your role</option>
                <option value="student">Student</option>
                <option value="faculty">Faculty</option>
                <option value="staff">Staff</option>
            </select>
            <button type="submit">
                Continue
            </button>
        </form>
    </main>

    <script type="module" src="<?= $assetBase ?>js/centralized.js"></script>
    
</body>
</html>