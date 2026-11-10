<!-- User Profile Page -->
 <?php
 session_start();
 require __DIR__ . '/../db.php';

 if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
 }

 $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
 $stmt->execute([$_SESSION['user_id']]);
 $user = $stmt->fetch(PDO::FETCH_ASSOC);

 if (!$user) {
    // user not found, redirect to login page
    header('Location: login.php');
    exit;
 }
 ?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title>GreenDash - Profile</title>
        <link rel="stylesheet" href="../../bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" href="../../bootstrap-icons/bootstrap-icons.css">
        <link rel="stylesheet" href="../../style/centralized.css">
        <link rel="stylesheet" href="../../style/user.css">
        
        <!-- bootstrap icons -->
         <link rel="stylesheet" href="../../bootstrap-icons/bootstrap-icons.css">

        <!-- website icon -->
        <link rel="icon" type="image/png" href="../../assets/main/GreenDash_CIRCLE.png">

        <meta name="viewport" content="width=device-width, initial-scale=1.0">

    </head>
    <body class="profile-list-page">
        <!-- INCLUDE EVERYTHING FOR THE MAIN HEADER FOR ALL PAGES FROM HERE-->
        <?php include __DIR__ . "/../nav.php"; ?>
        <!-- TO HERE!-->

        <!-- USER PROFILE PAGE -->
         <main class="profile-page">
            <h1 class="section-headers">My Profile</h1>

            <div class="profile-layout">
                <!-- LEFT CARD (profile stuff)-->
                <div class="row">
                    <div class="col-lg-5 d-flex">
                        <section class="profile-card">
                            <div class="profile-top">
                                <img src="<?= htmlspecialchars($user['profile_image'] ?: '../../assets/main/defaultAvatar.png') ?>" alt="User Avatar" id="profileAvatar" class="profile-avatar">
                                <input type="file" id="profilePictureInput" accept="image/*" hidden>
                                <!-- REPLACED THE HARDCODED VALUE WITH DB VALS!! -->
                                <h2 id="profileName">
                                    <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                                </h2>
                                <p id="profileEmail" class="profile-email">
                                    <?= htmlspecialchars($user['email_add']) ?>
                                </p>
                                <span class="profile-role-badge">
                                    <?= htmlspecialchars(ucfirst($user['user_role'])) ?>
                                </span>
                            </div>

                            <hr class="profile-divider">

                            <div class="profile-information">
                                <h3>Personal Information</h3>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="firstName" class="profile-label">First Name</label>
                                        <input type="text" id="firstName" class="profile-input" value="<?= htmlspecialchars($user['first_name']) ?>" readonly>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="lastName" class="profile-label">Last Name</label>
                                        <input type="text" id="lastName" class="profile-input" value="<?= htmlspecialchars($user['last_name']) ?>" readonly>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="emailAddress" class="profile-label">Email Address</label>
                                        <input type="email" id="emailAddress" class="profile-input" value="<?= htmlspecialchars($user['email_add']) ?>" readonly>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="contactNumber" class="profile-label">Contact Number</label>
                                        <input type="tel" id="contactNumber" class="profile-input" value="<?= htmlspecialchars($user['contact_num']) ?>" readonly>
                                    </div>
                                </div>

                                <div class="profile-button-group">
                                    <button type="button" id="editProfileBtn" class="edit-profile-btn">Edit Profile</button>
                                    <button type="button" id="logoutBtn" class="logout-btn">Logout</button>
                                </div>
                            </div>
                        </section>
                    </div>

                    <!-- RIGHT CARD (order summary/graph etc)-->
                    <div class="col-lg-7 d-flex">
                        <section class="activity-card w-100 h-100">
                            <h2>My Order Activity</h2>
                            <p class="activity-description">A summary of your all-time orders</p>

                            <div class="activity-stats">
                                <div class="stat-box">
                                    <div class="stat-icon-circle">
                                        <i class="bi bi-bag"></i>
                                    </div>
                                    <div class="stat-text">
                                        <p class="stat-label">Total Orders</p>
                                        <h3>25</h3>
                                    </div>
                                </div>

                                <div class="stat-box">
                                    <div class="stat-icon-circle">
                                        <i class="bi bi-star"></i>
                                    </div>
                                    <div class="stat-text">
                                        <p class="stat-label">Favorite Stall</p>
                                        <h3>Starbucks</h3>
                                    </div>
                                </div>

                                <div class="stat-box">
                                    <div class="stat-icon-circle">
                                        <i class="bi bi-receipt"></i>
                                    </div>
                                    <div class="stat-text">
                                        <p class="stat-label">Total Spent</p>
                                        <h3>&#8369;3,550</h3>
                                    </div>
                                </div>
                            </div>

                            <div class="chart-section">
                                <h3>Order Frequency</h3>
                                <p class="activity-description">Your number of orders per month</p>

                                <div class="chart-container">
                                    <canvas id="orderFrequencyChart"></canvas>
                                </div>
                            </div>
                        </section>
                    </div>  
                </div>
            </div>
        </main>

        <!-- CHART JS -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script src="../../js/jquery-4.0.0.min.js"></script>
        <script src="../../js/user.js"></script>
        <script type="module" src="../../js/centralized.js"></script>

    </body>     
</html>