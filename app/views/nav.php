<?php session_start(); 
$currentPage = basename($_SERVER['PHP_SELF']); ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>GreenDash - Profile</title>
        <link rel="stylesheet" href="../../../bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" href="../../../bootstrap-icons/bootstrap-icons.css">
        <link rel="stylesheet" href="../../../style/centralized.css">
        <link rel="stylesheet" href="../../../style/user.css">
        
        <!-- bootstrap icons -->
         <link rel="stylesheet" href="../../../bootstrap-icons/bootstrap-icons.css">

        <!-- website icon -->
        <link rel="icon" type="image/png" href="../../../assets/main/GreenDash_CIRCLE.png">

        <meta name="viewport" content="width=device-width, initial-scale=1.0">

    </head>
    <body class="profile-list-page">
        <!-- INCLUDE EVERYTHING FOR THE MAIN HEADER FOR ALL PAGES FROM HERE-->
        <header>
            <div class="main-header">
                <a href="stalls-directory.php" aria-label="Go to stalls directory">
                    <img id="logo" src="../../../assets/main/GreenDash_LOGO.png" alt="GreenDash Logo">
                </a>
                <div class="location">
                    <span>Your Location:</span>
                    <select class="location-select" aria-label="Choose your location">
                        <option>Gokongwei</option>
                        <option>La Salle Hall</option>
                        <option>Bloemen</option>
                        <option>Razon</option>
                        <option>Andrew</option>
                        <option>Yuchengco</option>
                    </select>
                </div>
                <button class="icon-button" id="eclipse" aria-label="Toggle dark mode">
                    <i data-lucide="eclipse"></i>
                </button>
            </div>

            <!-- CHANGE THE NAVIGATION LINKS IF NEEDED ACCDG TO USER ROLE HERE-->
            <nav>
                <div class="nav-links">
                    <a href="profile.php" aria-current="page">Profile</a>
                    <a href="stalls-directory.php">Stalls directory</a>
                    <a href="orders.php">Orders</a>
                </div>

                <button class="icon-button" id="cart" aria-label="View cart">
                    <i data-lucide="shopping-cart"></i>
                </button>
            </nav>
        </header>
        <!-- TO HERE!-->
    </body>
    </html>