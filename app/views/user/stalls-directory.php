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
                <?php foreach ($stalls as $stall): ?>
                    
                    <?php $stallHours = $hoursByStall[(int) $stall['id']] ?? [];
                    $today = (int) date('w');
                    $todayHours = null;

                    foreach ($stallHours as $hours) {
                        if ((int) $hours['day_of_week'] === $today) {
                            $todayHours = $hours;
                            break;
                        }
                    }
                    if ($todayHours) {
                        $hoursText =
                            date('g:i A', strtotime($todayHours['opens_at']))
                            . ' - ' .
                            date('g:i A', strtotime($todayHours['closes_at']));
                    } else {
                        $hoursText = 'Hours not available';
                    }
                    $statusText = ((int) $stall['is_open'] === 1) ? 'OPEN' : 'CLOSED';
                    $statusClass = ((int) $stall['is_open'] === 1) ? 'open' : 'closed';
                    ?>

                    <div class="stall-card"
                        data-stall-id="<?= (int) $stall['id'] ?>"
                        data-name="<?= htmlspecialchars(strtolower($stall['name']), ENT_QUOTES, 'UTF-8') ?>"
                        data-tags="<?= htmlspecialchars(strtolower($stall['tags'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                        data-location="<?= htmlspecialchars(strtolower($stall['location'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                        data-type="<?= htmlspecialchars(strtolower($stall['stall_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                        data-status="<?= $statusClass ?>">

                        <img
                            class="stall-logo"
                            src="<?= htmlspecialchars($stall['image_path'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            alt="<?= htmlspecialchars($stall['name'], ENT_QUOTES, 'UTF-8') ?> Logo">

                        <div class="stall-info">
                            <div class="stall-title-row">
                                <h3><?= htmlspecialchars($stall['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                                <?php if (!empty($stall['tags'])): ?>
                                    <div class="stall-tags">
                                        <?php foreach (explode(' ', $stall['tags']) as $tag): ?>
                                            <span>#<?= htmlspecialchars($tag, ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="stall-details">
                                <p>Location: <?= htmlspecialchars($stall['location'] ?? 'Not available', ENT_QUOTES, 'UTF-8') ?></p>
                                <p><?= htmlspecialchars($hoursText, ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                            <p class="stall-description"><?= htmlspecialchars($stall['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                            <div class="stall-bottom">
                                <div class="rating">
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                </div>
                                <span class="status <?= $statusClass ?>"><?= $statusText ?></span>
                            </div>

                            <a class="view-stall-button"
                            href="stall-info.php?stall=<?= (int) $stall['id'] ?>">
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