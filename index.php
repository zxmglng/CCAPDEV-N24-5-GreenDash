<!-- 
    This is the LANDING PAGE 
    We'll only use this page to redirect the user to the appropriate page based on their 
    role (admin/vendor/customer)
    For all the other pages for other roles, please add them under the pages directory.
    You can add a subfolder in the pages directory if you think it will help organize 
    the pages better. =)
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
            <div id="googleLoginButton" data-login-redirect="pages/user/stalls-directory.html"></div>
            <a href="pages/vendor/login.html" class="vendor-login-btn">
                Sign in as a food vendor
            </a>
            <a href="pages/admin/login.html" class="vendor-login-btn">
                Sign in as an admin
            </a>
        </main>

        <script src="https://accounts.google.com/gsi/client" async defer></script>
        <script src="js/jquery-4.0.0.min.js"></script>
        <script src="js/user.js"></script>

    </body>
</html>
