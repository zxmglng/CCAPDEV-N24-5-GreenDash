<!-- Admin Login -->
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>GreenDash - Admin Login</title>

        <link rel="stylesheet" href="../../bootstrap/css/bootstrap-5.3.3.min.css">
        <link rel="stylesheet" href="../../bootstrap-icons/bootstrap-icons.css">
        <link rel="stylesheet" href="../../style/centralized.css">
        <link rel="stylesheet" href="../../style/admin-common.css">
        <link rel="stylesheet" href="../../style/admin-login.css">

        <link rel="icon" type="image/png" href="../../assets/main/GreenDash_CIRCLE.png">

    </head>
    <body class="admin-page">
        <main class="admin-login-wrap">
            <form class="admin-login-card" id="adminLoginForm" novalidate>
                <img src="../../assets/main/GreenDash_LOGO.png" alt="GreenDash Logo">

                <h1>Admin Login</h1>
                <p>Enter the admin key to continue.</p>

                <div class="admin-login-field">
                    <label for="adminKey">Admin key</label>
                    <input
                        type="password"
                        id="adminKey"
                        name="adminKey"
                        autocomplete="off"
                        autofocus
                        required
                    >
                </div>

                <button type="submit" class="admin-login-submit">Sign in</button>

                <p class="admin-login-error" id="adminLoginError" role="alert"></p>
            </form>
        </main>

        <script src="../../js/admin-login.js"></script>
    </body>
</html>