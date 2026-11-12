<!-- Home would immediately be the Stalls Directory section -->
<?php
// $stalls and $hoursByStall are provided by controller-user/stalls-directory.php
?>
<head>
    <title>GreenDash - Stalls</title>
        <link rel="stylesheet" href="<?= $assetBase ?>bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" href="<?= $assetBase ?>bootstrap-icons/bootstrap-icons.css">
        <link rel="stylesheet" href="<?= $assetBase ?>style/centralized.css">
        <link rel="stylesheet" href="<?= $assetBase ?>style/user.css">

        <!-- website icon -->
        <link rel="icon" type="image/png" href="<?= $assetBase ?>assets/main/GreenDash_CIRCLE.png">

        <meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>
<body data-assets-path="<?= htmlspecialchars($assetBase . 'assets/', ENT_QUOTES, 'UTF-8') ?>">
    <?php include(__DIR__ . '/../nav.php'); ?>
        <main class="stalls-directory-page">
            <!-- SEARCH BAR -->
             <div class="search-container">
                <input type="text" id="searchInput" placeholder="What are you in the mood for?">
                <button type="button" id="stallFilterButton" class="filterButton">
                    <i data-lucide="sliders-horizontal"></i>
                    Filters
                </button>
             </div>

             <div class="filter-panel" id="stallFilterPanel">
                <h3>Filter Stalls</h3>
                <div class="filter-section">
                    <input type="checkbox" id="openOnly">
                    <label for="openOnly">Open Stalls Only</label>
                </div>

                <div class="filter-section">
                    <p>Type</p>
                    <input type="checkbox" class="stall-type-filter" id="foodFilter" value="food">
                    <label for="foodFilter">Food</label>
                    <input type="checkbox" class="stall-type-filter" id="drinksFilter" value="drinks">
                    <label for="drinksFilter">Drinks</label>
                    <input type="checkbox" class="stall-type-filter" id="dessertFilter" value="dessert">
                    <label for="dessertFilter">Dessert</label>
                </div>

                <div class="filter-section">
                    <label for="locationFilter">Location</label>
                    <select id="locationFilter">
                        <option value="all">All Locations</option>
                        <option value="agno">Agno</option>
                        <option value="bloemen">Bloemen</option>
                        <option value="green court">Green Court</option>
                        <option value="gokongwei hall">Gokongwei Hall</option>
                    </select>
                </div>
                <button type="button" id="resetStallFilters" class="reset-filter-button">
                    Reset Filters
                </button>
             </div>

             <!-- PAGE TITLE -->
             <h2 class="section-headers"> Stalls Directory </h2>

             <!-- LIST OF THE STALLS -->
              <div class="stall-list">
                <!-- php NESTED foreach loop to display stalls from the database -->
                <!-- i figured it would be better to store the operating hours in a separate table and then join them 
                     with the stalls table to avoid redundancy and make it easier to update the hours if needed -->
                <?php foreach ($stalls as $stall): ?>
                    <div class="stall-card" data-stall-id="<?= (int) $stall['id'] ?>" data-location="<?= htmlspecialchars(strtolower($stall['location'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        <img

                            src="<?= htmlspecialchars($stall['image_path'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            alt="<?= htmlspecialchars($stall['name'], ENT_QUOTES, 'UTF-8') ?> Logo"
                            class="stall-logo">

                        <div class="stall-info">
                            <h3 class="stall-name">
                                <?= htmlspecialchars($stall['name'], ENT_QUOTES, 'UTF-8') ?>
                            </h3>
                            <p class="stall-description">
                                <?= htmlspecialchars($stall['description'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                            </p>
                            <p class="stall-location">
                                Location: <?= htmlspecialchars($stall['location'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                            </p>

                            <?php
                            // i lowkey just realized kasi na stalls have different operating hours per day, 
                            // so i was thinking of refining this 
                            $days = [0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'];
                            $stallHours = $hoursByStall[(int) $stall['id']] ?? [];
                            ?>

                            <div class="stall-hours">
                                <strong>Operating hours:</strong>
                                <?php if ($stallHours): ?>
                                    <?php foreach ($stallHours as $hours): ?>
                                        <p>
                                            <?= $days[(int) $hours['day_of_week']] ?? 'Unknown day' ?>:
                                            <?= date('g:i A', strtotime($hours['opens_at'])) ?>
                                            -
                                            <?= date('g:i A', strtotime($hours['closes_at'])) ?>
                                        </p>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p>Hours not available</p>
                                <?php endif; ?>
                            </div>
                            <a class="view-stall-button" href="../app/views/user/stall-info.php?stall=<?= (int) $stall['id'] ?>">
                            View Stall
                            </a>
                        </div>            
                    </div>
                <?php endforeach; ?>
              </div>
            </main>

        <!-- SCRIPTS --> 
        <div class="closed-stall-backdrop" id="closedStallMessage" hidden>
            <section class="closed-stall-modal" role="alertdialog" aria-modal="true"
                aria-labelledby="closedStallTitle">
                <h2 id="closedStallTitle">This stall is currently closed.</h2>
                <p>Please check back during the stall's operating hours.</p>
                <button type="button" class="view-stall-button" id="closeClosedStallMessage">Okay</button>
            </section>
        </div>
        <script src="<?= $assetBase ?>js/jquery-4.0.0.min.js"></script>
        <script src="<?= $assetBase ?>js/user.js"></script>
        <script src="<?= $assetBase ?>js/user-stalls-directory.js"></script>
        <script type="module" src="<?= $assetBase ?>js/centralized.js"></script>
    </body>
</html>