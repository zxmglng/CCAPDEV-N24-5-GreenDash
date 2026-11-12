<!DOCTYPE html>
<html lang="en">
    <head>
        <title>GreenDash - Stall</title>
        <link rel="stylesheet" href="<?= $assetBase ?>bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" href="<?= $assetBase ?>style/centralized.css">
        <link rel="stylesheet" href="<?= $assetBase ?>style/user.css">
        <link rel="stylesheet" href="<?= $assetBase ?>style/user-stalls.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel="icon" type="image/png" href="<?= $assetBase ?>assets/main/GreenDash_CIRCLE.png">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body data-assets-path="<?= htmlspecialchars($assetBase . 'assets/', ENT_QUOTES, 'UTF-8') ?>">
        <?php include(__DIR__ . "/../nav.php"); ?>
        <main class="stall-info-page">
            <div class="stall-content">
                <div class="stall-main-content">
                    <section class="stall-header">
                        <img class="stall-banner" id="stallBanner" src="<?= htmlspecialchars($stall['banner_path'] ?? '', ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($stall['name']) ?> banner">
                        <img class="stall-info-logo" id="stallLogo" src="<?= htmlspecialchars($stall['image_path'] ?? '', ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($stall['name']) ?>">
                        <div class="stall-header-info">
                            <h1 id="stallName"><?= htmlspecialchars($stall['name']) ?></h1>
                            <p id="stallLocation"><?= htmlspecialchars($stall['location']) ?></p>
                            <p id="stallHours"><?= htmlspecialchars($hoursLabel, ENT_QUOTES, 'UTF-8') ?></p>
                            <button type="button" class="stall-review-button" id="openReviewsButton">
                                <span class="review-stars" aria-hidden="true">★★★★★</span>
                                Reviews
                            </button>
                        </div>
                    </section>

                    <section class="stall-menu-section">
                        <div class="menu-search">
                            <i data-lucide="search"></i>
                            <input type="text" id="menuSearch" placeholder="Search menu">
                            <button type="button" id="filterButton" class="filterButton">
                                <i data-lucide="sliders-horizontal"></i>
                                Filters
                            </button>
                        </div>
                        <div class="filter-panel" id="filterPanel">
                            <h3>Filter Menu</h3>
                            <div class="filter-section">
                                <label for="priceRange">Maximum Price: &#8369; <span id="priceValue">1000</span></label>
                                <input type="range" id="priceRange" min="0" max="1000" value="1000" step="50">
                            </div>
                            <div class="filter-section">
                                <label for="priceSort">Sort by price</label>
                                <select id="priceSort">
                                    <option value="default">Default order</option>
                                    <option value="lowest">Lowest to highest</option>
                                    <option value="highest">Highest to lowest</option>
                                </select>
                            </div>
                            <div class="filter-actions">
                                <button type="button" id="applyFilters" class="apply-filter-button">Apply Filters</button>
                                <button type="button" id="resetFilters" class="reset-filter-button">Reset Filters</button>
                            </div>
                        </div>

                        <!-- foreach.. for each category... -->
                        <div class="menu-categories">
                            <?php foreach ($categories as $index => $category): ?>
                                <button
                                    class="<?= $index === 0 ? 'category-button-active' : 'category-button' ?>"
                                    data-category="<?= htmlspecialchars(strtolower($category), ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <div class="menu-items-wrapper">
                            <button type="button" class="menu-carousel-button menu-carousel-previous"
                                id="previousMenuItem" aria-label="Previous menu item">&#8249;</button>
                            <div class="menu-carousel-window">
                                <div class="container-fluid">
                                    <div class="row" id="menuItems"></div>
                                </div>
                            </div>
                            <p class="no-menu-items-message" id="noMenuItemsMessage" hidden>
                                No item found.
                            </p>
                            <button type="button" class="menu-carousel-button menu-carousel-next"
                                id="nextMenuItem" aria-label="Next menu item">&#8250;</button>
                        </div>
                    </section>
                </div>
                <aside class="order-summary" aria-label="Your order">
                    <div class="order-summary-header">
                        <h2>Your order</h2>
                        <div class="order-header-actions">
                            <div class="mobile-pickup-time">
                                <label for="pickupTimeMobile">Pick-up time</label>
                                <select id="pickupTimeMobile">
                                    <option value="">Select time</option>
                                </select>
                            </div>
                            <button type="button" class="close-order-button" id="closeOrderButton" aria-label="Close your order">&times;</button>
                        </div>
                    </div>
                    <div class="order-items" id="orderItems">
                        <p class="empty-order-message">Your cart is empty!</p>
                    </div>
                    <div class="order-summary-footer">
                        <div class="pickup-time-control">
                            <label for="pickupTime">Pick-up time</label>
                            <select id="pickupTime">
                                <option value="">Select time</option>
                            </select>
                        </div>
                        <button type="button" class="checkout-button" id="checkoutButton">Checkout - &#8369; 0</button>
                    </div>
                </aside>
            </div>
        </main>
        <div class="stall-modal-backdrop" id="itemModal" hidden>
            <section class="item-modal" role="dialog" aria-modal="true" aria-labelledby="modalItemName">
                <button type="button" class="modal-close-button" id="modalCloseButton" aria-label="Close item customization">&times;</button>
                <div class="modal-item-overview">
                    <img id="modalItemImage" alt="">
                    <div>
                        <h2 id="modalItemName"></h2>
                        <p id="modalItemDescription">Blend of delicious ingredients made fresh for you.</p>
                        <strong>From &#8369; <span id="modalBasePrice"></span></strong>
                    </div>
                </div>
                <div id="customizationGroups"></div>
                <div class="modal-actions">
                    <div class="quantity-control">
                        <button type="button" id="decreaseQuantity" aria-label="Decrease quantity">−</button>
                        <output id="itemQuantity">1</output>
                        <button type="button" id="increaseQuantity" aria-label="Increase quantity">+</button>
                    </div>
                    <button type="button" class="add-to-cart-button" id="addToCartButton">Add to cart - &#8369; 0</button>
                </div>
            </section>
        </div>
        <div class="review-modal-backdrop" id="reviewModal" hidden>
            <section class="review-modal" role="dialog" aria-modal="true" aria-labelledby="reviewModalTitle">
                <div class="review-modal-header">
                    <div>
                        <p class="review-eyebrow">REVIEWS</p>
                        <h2 id="reviewModalTitle"></h2>
                    </div>
                    <button type="button" class="review-close-button" id="closeReviewsButton" aria-label="Close reviews">&times;</button>
                </div>
                <div class="review-toolbar">
                    <label for="reviewStarFilter">Filter</label>
                    <select id="reviewStarFilter">
                        <option value="all">All stars</option>
                        <option value="5">5 stars</option>
                        <option value="4">4 stars</option>
                        <option value="3">3 stars</option>
                        <option value="2">2 stars</option>
                        <option value="1">1 star</option>
                    </select>
                    <label for="reviewSort">Sort</label>
                    <select id="reviewSort">
                        <option value="newest">Newest to oldest</option>
                        <option value="oldest">Oldest to newest</option>
                    </select>
                </div>
                <div class="review-list" id="reviewList"></div>
                <p class="review-eligibility-message" id="reviewEligibilityMessage" hidden>
                    You can read reviews, but you need a completed order from this stall to leave one.
                </p>
                <form class="review-form" id="stallReviewForm" method="post" action="reviews.php">
                    <h3>Write a review</h3>
                    <label for="stallReviewOrder">Completed order</label>
                    <select id="stallReviewOrder" name="orderId" required></select>
                    <p id="stallReviewFeedback" role="alert" hidden></p>
                    <div class="review-rating-input" role="radiogroup" aria-label="Your rating">
                        <button type="button" data-rating="1" aria-label="1 star">☆</button>
                        <button type="button" data-rating="2" aria-label="2 stars">☆</button>
                        <button type="button" data-rating="3" aria-label="3 stars">☆</button>
                        <button type="button" data-rating="4" aria-label="4 stars">☆</button>
                        <button type="button" data-rating="5" aria-label="5 stars">☆</button>
                    </div>
                    <textarea id="stallReviewText" name="comment" maxlength="500" placeholder="Share your experience (optional)"></textarea>
                    <button type="button" class="cancel-review-button" id="cancelReviewEdit" hidden>Cancel edit</button>
                    <button type="submit" class="submit-review-button">Post review</button>
                </form>
            </section>
        </div>
        <div class="review-message-backdrop" id="reviewMessage" hidden>
            <section class="review-message" role="alertdialog" aria-labelledby="reviewMessageTitle">
                <h2 id="reviewMessageTitle">A completed order is required</h2>
                <p>You can leave a review after completing an order from this stall.</p>
                <button type="button" class="submit-review-button" id="closeReviewMessage">Okay</button>
            </section>
        </div>
        <div class="empty-cart-backdrop" id="emptyCartMessage" hidden>
            <section class="empty-cart-modal" role="alertdialog" aria-modal="true"
                aria-labelledby="emptyCartTitle">
                <h2 id="emptyCartTitle">Please add items to your cart!</h2>
                <button type="button" class="submit-review-button" id="closeEmptyCartMessage">Okay</button>
            </section>
        </div>
        <script type="application/json" id="stallData"><?= json_encode($stallData,
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) ?></script>
        <script src="<?= $assetBase ?>js/reviews.js"></script>
        <script src="<?= $assetBase ?>js/user-stalls.js"></script>
        <script type="module" src="<?= $assetBase ?>js/centralized.js"></script>
    </body>
</html>
