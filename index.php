<!-- 
    Landing page for the PHP MVC application.
--> 
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>GreenDash - Login</title>

        <!-- website icon -->
        <link rel="icon" type="image/png" href="assets/main/GreenDash_CIRCLE.png">

        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" href="bootstrap-icons/bootstrap-icons.css">
        <link rel="stylesheet" href="style/centralized.css">
        <link rel="stylesheet" href="style/user.css">
    </head>

    <body class="login-page">
        <main class="login-container">
            <img src="assets/main/GreenDash_LOGO.png" alt="Greendash Logo" class="login-logo">
            <div id="googleLoginButton"
                data-login-redirect="app/views/user/stalls-directory.php"
                data-login-endpoint="app/views/user/google-login.php"></div>
            <a href="app/views/vendor/login.php" class="vendor-login-btn">
                Sign in as a food vendor
            </a>
            <a href="controller-admin/login.php" class="vendor-login-btn">
                Sign in as an admin
            </a>
        </main>

        <script src="https://accounts.google.com/gsi/client" async defer></script>
        <script src="js/jquery-4.0.0.min.js"></script>
        <script src="js/user.js"></script>
    </body>
</html>a