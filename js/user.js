// stall-info filter button
if (window.jQuery) {
    window.jQuery('#filterButton').click(function () {
        window.jQuery('#filterPanel').slideToggle();
    });
}

const stallSearchInput = document.querySelector('#searchInput');

if (stallSearchInput) {
    const stallCards = document.querySelectorAll('.stall-card');
    const noStallsMessage = document.querySelector('#noStallsFound');

    stallSearchInput.addEventListener('input', () => {
        const searchTerm = stallSearchInput.value.trim().toLowerCase();
        let visibleStallCount = 0;

        stallCards.forEach((card) => {
            const searchContent = `${card.dataset.name || ''} ${card.dataset.tags || ''} ${card.textContent}`;
            const matches = searchContent.toLowerCase().includes(searchTerm);
            card.hidden = !matches;
            visibleStallCount += Number(matches);
        });

        if (noStallsMessage) {
            noStallsMessage.hidden = visibleStallCount > 0;
        }
    });
}

// Order data for the user orders page.
const storeDetails = {
    starbucks: {
        name: 'Starbucks',
        location: 'Green Court',
        logo: '../../assets/stalls/starbucks.png'
    },
    potatoCorner: {
        name: 'Potato Corner',
        location: 'Bloemen',
        logo: '../../assets/stalls/potcor.png'
    },
    dairyQueen: {
        name: 'Dairy Queen',
        location: 'Agno',
        logo: '../../assets/stalls/dq.png'
    },
    belgianWaffle: {
        name: 'Belgian Waffle',
        location: 'Gokongwei Hall',
        logo: '../../assets/stalls/belgian.png'
    }
};

const orders = [
    {
        id: '6767HI', store: 'starbucks', date: 'October 12, 2026',
        status: 'Pending', statusClass: 'pending',
        paymentMethod: 'Online payment', paymentStatus: 'Pending', total: 555,
        items: [
            { name: 'Caff\u00e8 Latte', detail: 'Grande', quantity: 2, total: 480 },
            { name: 'Chocolate Chip Cookie', detail: 'Classic', quantity: 1, total: 75 }
        ]
    },
    {
        id: 'PC7R2Q', store: 'potatoCorner', date: 'October 12, 2026',
        status: 'Ready for pickup', statusClass: 'ready', paymentMethod: 'Online payment',
        paymentStatus: 'Paid', total: 420, items: [
            { name: 'Mega Fries', detail: 'Cheese flavor', quantity: 2, total: 420 }
        ]
    },
    {
        id: '1N2C7T', store: 'dairyQueen', date: 'October 10, 2026',
        status: 'Order collected at 3:43 PM', statusClass: 'completed',
        paymentMethod: 'Cash', paymentStatus: 'Paid', total: 317,
        items: [
            { name: 'Oreo Blizzard', detail: 'Regular', quantity: 1, total: 179 },
            { name: 'Vanilla Cone', detail: 'Single scoop', quantity: 2, total: 138 }
        ]
    },
    {
        id: '8B4K2M', store: 'belgianWaffle', date: 'October 8, 2026',
        status: 'Order cancelled', statusClass: 'cancelled', paymentMethod: 'Cash',
        paymentStatus: 'Not charged', total: 435, items: [
            { name: 'Chocolate Waffle', detail: 'Classic', quantity: 1, total: 145 },
            { name: 'Strawberry Waffle', detail: 'Classic', quantity: 2, total: 290 }
        ]
    },
    {
        id: 'S4K8P2', store: 'starbucks', date: 'October 7, 2026',
        status: 'Order collected at 1:20 PM', statusClass: 'completed',
        paymentMethod: 'Cash', paymentStatus: 'Paid', total: 95,
        items: [{ name: 'Caff\u00e8 Americano', detail: 'Tall', quantity: 1, total: 95 }]
    },
    {
        id: 'S9D3L1', store: 'starbucks', date: 'October 6, 2026',
        status: 'Order collected at 11:05 AM', statusClass: 'completed',
        paymentMethod: 'Online payment', paymentStatus: 'Paid', total: 105,
        items: [{ name: 'Brewed Coffee', detail: 'Grande', quantity: 1, total: 105 }]
    },
    {
        id: 'S2F6M8', store: 'starbucks', date: 'October 5, 2026',
        status: 'Order collected at 4:10 PM', statusClass: 'completed',
        paymentMethod: 'Cash', paymentStatus: 'Paid', total: 110,
        items: [{ name: 'Caff\u00e8 Latte', detail: 'Tall', quantity: 1, total: 110 }]
    },
    {
        id: 'S5J1N4', store: 'starbucks', date: 'October 4, 2026',
        status: 'Order collected at 12:35 PM', statusClass: 'completed',
        paymentMethod: 'Online payment', paymentStatus: 'Paid', total: 120,
        items: [{ name: 'Cappuccino', detail: 'Tall', quantity: 1, total: 120 }]
    },
    {
        id: 'S7Q3B9', store: 'starbucks', date: 'October 3, 2026',
        status: 'Order collected at 9:15 AM', statusClass: 'completed',
        paymentMethod: 'Cash', paymentStatus: 'Paid', total: 85,
        items: [{ name: 'Chocolate Croissant', detail: 'Bakery', quantity: 1, total: 85 }]
    },
    {
        id: 'S1W6C3', store: 'starbucks', date: 'October 2, 2026',
        status: 'Order collected at 2:50 PM', statusClass: 'completed',
        paymentMethod: 'Online payment', paymentStatus: 'Paid', total: 115,
        items: [{ name: 'Cold Brew', detail: 'Tall', quantity: 1, total: 115 }]
    },
    {
        id: 'S8H2T5', store: 'starbucks', date: 'October 1, 2026',
        status: 'Order collected at 10:40 AM', statusClass: 'completed',
        paymentMethod: 'Cash', paymentStatus: 'Paid', total: 125,
        items: [{ name: 'Caramel Macchiato', detail: 'Tall', quantity: 1, total: 125 }]
    },
    {
        id: 'S3A9E7', store: 'starbucks', date: 'September 29, 2026',
        status: 'Order collected at 3:05 PM', statusClass: 'completed',
        paymentMethod: 'Online payment', paymentStatus: 'Paid', total: 100,
        items: [{ name: 'Iced Green Tea', detail: 'Grande', quantity: 1, total: 100 }]
    },
    {
        id: 'S6P4D2', store: 'starbucks', date: 'September 27, 2026',
        status: 'Order collected at 1:45 PM', statusClass: 'completed',
        paymentMethod: 'Cash', paymentStatus: 'Paid', total: 130,
        items: [{ name: 'Java Chip Frappuccino', detail: 'Tall', quantity: 1, total: 130 }]
    },
    {
        id: 'S2V8K6', store: 'starbucks', date: 'September 25, 2026',
        status: 'Order collected at 11:30 AM', statusClass: 'completed',
        paymentMethod: 'Online payment', paymentStatus: 'Paid', total: 90,
        items: [{ name: 'Blueberry Muffin', detail: 'Bakery', quantity: 1, total: 90 }]
    },
    {
        id: 'S9M5R1', store: 'starbucks', date: 'September 23, 2026',
        status: 'Order collected at 4:25 PM', statusClass: 'completed',
        paymentMethod: 'Cash', paymentStatus: 'Paid', total: 110,
        items: [{ name: 'Caff\u00e8 Americano', detail: 'Grande', quantity: 1, total: 110 }]
    },
    {
        id: 'S4C7Y3', store: 'starbucks', date: 'September 21, 2026',
        status: 'Order collected at 10:10 AM', statusClass: 'completed',
        paymentMethod: 'Online payment', paymentStatus: 'Paid', total: 105,
        items: [{ name: 'Butter Croissant', detail: 'Bakery', quantity: 1, total: 105 }]
    },
    {
        id: 'S1L8X5', store: 'starbucks', date: 'September 19, 2026',
        status: 'Order collected at 2:15 PM', statusClass: 'completed',
        paymentMethod: 'Cash', paymentStatus: 'Paid', total: 115,
        items: [{ name: 'Caff\u00e8 Latte', detail: 'Tall', quantity: 1, total: 115 }]
    },
    {
        id: 'S5G2U9', store: 'starbucks', date: 'September 17, 2026',
        status: 'Order collected at 9:55 AM', statusClass: 'completed',
        paymentMethod: 'Online payment', paymentStatus: 'Paid', total: 125,
        items: [{ name: 'Mocha', detail: 'Tall', quantity: 1, total: 125 }]
    },
    {
        id: 'P3D8V1', store: 'potatoCorner', date: 'October 9, 2026',
        status: 'Order collected at 12:40 PM', statusClass: 'completed',
        paymentMethod: 'Cash', paymentStatus: 'Paid', total: 90,
        items: [{ name: 'Regular Fries', detail: 'Cheese flavor', quantity: 1, total: 90 }]
    },
    {
        id: 'P6N2A4', store: 'potatoCorner', date: 'October 6, 2026',
        status: 'Order collected at 2:05 PM', statusClass: 'completed',
        paymentMethod: 'Online payment', paymentStatus: 'Paid', total: 100,
        items: [{ name: 'Regular Fries', detail: 'BBQ flavor', quantity: 1, total: 100 }]
    },
    {
        id: 'P9F5K7', store: 'potatoCorner', date: 'September 28, 2026',
        status: 'Order collected at 10:55 AM', statusClass: 'completed',
        paymentMethod: 'Cash', paymentStatus: 'Paid', total: 110,
        items: [{ name: 'Large Fries', detail: 'Sour cream flavor', quantity: 1, total: 110 }]
    },
    {
        id: 'D2R7M5', store: 'dairyQueen', date: 'October 7, 2026',
        status: 'Order collected at 1:15 PM', statusClass: 'completed',
        paymentMethod: 'Online payment', paymentStatus: 'Paid', total: 80,
        items: [{ name: 'Vanilla Cone', detail: 'Single scoop', quantity: 1, total: 80 }]
    },
    {
        id: 'D5B1Q8', store: 'dairyQueen', date: 'October 2, 2026',
        status: 'Order collected at 3:25 PM', statusClass: 'completed',
        paymentMethod: 'Cash', paymentStatus: 'Paid', total: 90,
        items: [{ name: 'Sundae', detail: 'Hot fudge', quantity: 1, total: 90 }]
    },
    {
        id: 'D8T4C6', store: 'dairyQueen', date: 'September 20, 2026',
        status: 'Order collected at 11:50 AM', statusClass: 'completed',
        paymentMethod: 'Online payment', paymentStatus: 'Paid', total: 100,
        items: [{ name: 'Dilly Bar', detail: 'Chocolate', quantity: 1, total: 100 }]
    },
    {
        id: 'B3K9W2', store: 'belgianWaffle', date: 'September 30, 2026',
        status: 'Order collected at 12:10 PM', statusClass: 'completed',
        paymentMethod: 'Cash', paymentStatus: 'Paid', total: 158,
        items: [{ name: 'Classic Belgian Waffle', detail: 'Chocolate', quantity: 1, total: 158 }]
    }
];
const userOrderStatusStorageKey = 'greendash.user.order-status.v1';
let savedUserOrderStatuses = {};
try {
    const savedStatuses = JSON.parse(localStorage.getItem(userOrderStatusStorageKey) || '{}');
    if (savedStatuses && typeof savedStatuses === 'object' && !Array.isArray(savedStatuses)) {
        savedUserOrderStatuses = savedStatuses;
    }
} catch (error) {
    console.error('Unable to load saved user order statuses.', error);
}

orders.forEach((order) => {
    if (savedUserOrderStatuses[order.id] === 'cancelled' && order.statusClass === 'pending') {
        order.status = 'Order cancelled';
        order.statusClass = 'cancelled';
        order.paymentStatus = 'Not charged';
    }
});

orders.forEach((order) => {
    Object.assign(order, storeDetails[order.store], {
        isOngoing: order.statusClass === 'pending'
            || order.statusClass === 'preparing'
            || order.statusClass === 'ready'
    });
});

const orderRatingsStorageKey = 'greendash.orderRatings';
// Retain comments by order so they can be reopened and edited.
const orderReviewsStorageKey = 'greendash.orderReviews';
const completedOrderIds = new Set(
    orders.filter((order) => order.statusClass === 'completed').map((order) => order.id)
);
let orderRatings = {};
let orderReviews = {};

try {
    const storedRatings = JSON.parse(localStorage.getItem(orderRatingsStorageKey) || '{}');
    if (storedRatings && typeof storedRatings === 'object' && !Array.isArray(storedRatings)) {
        orderRatings = Object.fromEntries(
            Object.entries(storedRatings).filter(([orderId, rating]) =>
                completedOrderIds.has(orderId) && Number.isInteger(rating) && rating >= 1 && rating <= 5
            )
        );
    }
} catch (error) {
    console.error('Unable to load saved order ratings.', error);
}

try {
    const storedReviews = JSON.parse(localStorage.getItem(orderReviewsStorageKey) || '{}');
    if (storedReviews && typeof storedReviews === 'object' && !Array.isArray(storedReviews)) {
        orderReviews = Object.fromEntries(
            Object.entries(storedReviews).filter(([orderId, review]) =>
                completedOrderIds.has(orderId) && typeof review === 'string' && review.trim().length > 0 &&
                review.length <= 500
            )
        );
    }
} catch (error) {
    console.error('Unable to load saved order reviews.', error);
}

const orderById = Object.fromEntries(orders.map((order) => [order.id, order]));
const currency = new Intl.NumberFormat('en-PH', { maximumFractionDigits: 0 });
const ongoingOrdersSection = document.querySelector('#ongoing-heading')?.parentElement;
const previousOrdersSection = document.querySelector('#previous-heading')?.parentElement;
const orderCardsById = new Map();

function updateCardRating(orderId, rating) {
    const ratingDisplay = orderCardsById.get(orderId)?.querySelector('.order-card-rating');
    if (!ratingDisplay) {
        return;
    }

    ratingDisplay.replaceChildren();
    ratingDisplay.hidden = !rating;
    if (!rating) {
        ratingDisplay.removeAttribute('aria-label');
        return;
    }

    ratingDisplay.setAttribute('aria-label', `Your rating: ${rating} out of 5 stars`);
    for (let value = 1; value <= 5; value += 1) {
        const star = document.createElement('span');
        star.className = value <= rating ? 'is-filled' : 'is-empty';
        star.textContent = value <= rating ? '\u2605' : '\u2606';
        ratingDisplay.append(star);
    }
}

function createOrderCard(order) {
    const card = document.createElement('article');
    card.className = 'order-card';

    const logo = document.createElement('img');
    logo.className = 'order-stall-logo';
    logo.src = order.logo;
    logo.alt = `${order.name} logo`;

    const details = document.createElement('div');
    details.className = 'order-details';

    const titleLine = document.createElement('div');
    titleLine.className = 'order-topline';
    const title = document.createElement('div');
    title.className = 'order-title-line';

    const storeName = document.createElement('h3');
    storeName.textContent = order.name;
    const ratingDisplay = document.createElement('span');
    ratingDisplay.className = 'order-card-rating';
    ratingDisplay.hidden = true;
    const orderId = document.createElement('span');
    orderId.className = 'order-id';
    orderId.textContent = `ORDER ID: ${order.id}`;
    const status = document.createElement('span');
    status.className = `order-status ${order.statusClass}`;
    status.textContent = order.status;
    title.append(storeName, ratingDisplay, orderId, status);
    titleLine.append(title);

    const location = document.createElement('p');
    location.className = 'order-location';
    location.textContent = `Location: ${order.location}`;
    const date = document.createElement('p');
    date.className = 'order-date';
    date.textContent = order.date;
    const items = document.createElement('p');
    items.className = 'order-items';
    items.textContent = order.items.map((item) => `${item.name} (x${item.quantity})`).join(', ');

    const paymentLine = document.createElement('div');
    paymentLine.className = 'order-bottomline';
    const payment = document.createElement('p');
    payment.className = 'order-payment';
    const paymentLabel = order.statusClass === 'cancelled'
        ? 'TOTAL'
        : order.paymentMethod === 'Online payment' ? 'ONLINE' : 'CASH';
    payment.textContent = `${paymentLabel}: \u20b1${currency.format(order.total)}`;
    paymentLine.append(payment);

    const action = document.createElement('div');
    action.className = 'order-action';
    const viewButton = document.createElement('button');
    viewButton.className = 'view-stall-button';
    viewButton.type = 'button';
    viewButton.dataset.orderId = order.id;
    viewButton.dataset.bsToggle = 'modal';
    viewButton.dataset.bsTarget = '#orderInfoModal';
    viewButton.textContent = 'View order';
    action.append(viewButton);

    details.append(titleLine, location, date, items, paymentLine, action);
    card.append(logo, details);
    orderCardsById.set(order.id, card);
    return card;
}

const sortedOrders = [...orders].sort((first, second) => Date.parse(second.date) - Date.parse(first.date));

sortedOrders.forEach((order) => {
    const section = order.isOngoing ? ongoingOrdersSection : previousOrdersSection;
    section?.append(createOrderCard(order));
    updateCardRating(order.id, orderRatings[order.id]);
});

// Search and section filtering for the order list.
const orderSearchInput = document.querySelector('#orderSearchInput');

if (orderSearchInput) {
    const orderSections = document.querySelectorAll('.orders-section');
    const noOrdersMessage = document.querySelector('#noOrdersFound');

    orderSearchInput.addEventListener('input', () => {
        const searchTerm = orderSearchInput.value.trim().toLowerCase();
        let visibleOrderCount = 0;

        orderSections.forEach((section) => {
            const orderCards = section.querySelectorAll('.order-card');
            let visibleSectionOrderCount = 0;

            orderCards.forEach((card) => {
                const matches = card.textContent.toLowerCase().includes(searchTerm);
                card.hidden = !matches;
                visibleSectionOrderCount += Number(matches);
            });

            section.hidden = visibleSectionOrderCount === 0;
            visibleOrderCount += visibleSectionOrderCount;
        });

        noOrdersMessage.hidden = visibleOrderCount > 0;
    });
}

// Order detail modal logic and rating interactions.
const orderInfoModal = document.querySelector('#orderInfoModal');
const orderReviewModal = document.querySelector('#orderReviewModal');

if (orderInfoModal) {
    const modalCurrency = new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2
    });
    const ratingControl = orderInfoModal.querySelector('#orderRatingControl');
    const ratingSection = ratingControl.closest('.order-modal-rating');
    const reviewButton = orderInfoModal.querySelector('#reviewOrderButton');
    const reviewForm = orderReviewModal?.querySelector('#orderReviewForm');
    const reviewComment = orderReviewModal?.querySelector('#orderReviewComment');
    const reviewCount = orderReviewModal?.querySelector('#orderReviewCount');
    const reviewContext = orderReviewModal?.querySelector('#orderReviewContext');
    const reviewFeedback = orderReviewModal?.querySelector('#orderReviewFeedback');
    const cancelUserOrderButton = orderInfoModal.querySelector('#cancelUserOrderButton');
    const cancelUserOrderModal = document.querySelector('#cancelUserOrderModal');
    let cancelConfirmationPending = false;
    let selectedOrderId = null;
    let returnToOrderDetails = false;
    let pendingReview = false;

    function updateReviewCount() {
        reviewCount.textContent = `${reviewComment.value.length}/500`;
    }

    // Avoid stacking the review form on top of the order details modal.
    function closeOrderModalForReview() {
        let isHidingOrderModal = false;
        const handleOrderModalHide = () => {
            isHidingOrderModal = true;
            returnToOrderDetails = true;
            orderInfoModal.addEventListener('hidden.bs.modal', () => {
                bootstrap.Modal.getOrCreateInstance(orderReviewModal).show();
            }, { once: true });
        };

        orderInfoModal.addEventListener('hide.bs.modal', handleOrderModalHide, { once: true });
        bootstrap.Modal.getOrCreateInstance(orderInfoModal).hide();
        if (!isHidingOrderModal) {
            orderInfoModal.removeEventListener('hide.bs.modal', handleOrderModalHide);
            orderInfoModal.addEventListener('shown.bs.modal', closeOrderModalForReview, { once: true });
        }
    }

    reviewButton.addEventListener('click', () => {
        const order = orderById[selectedOrderId];
        if (!order || order.statusClass !== 'completed' || pendingReview) {
            return;
        }

        pendingReview = true;
        reviewContext.textContent = `${order.name} - Order ${order.id}`;
        reviewComment.value = orderReviews[order.id] || '';
        reviewComment.setCustomValidity('');
        reviewFeedback.hidden = true;
        updateReviewCount();
        closeOrderModalForReview();
    });

    reviewComment.addEventListener('input', () => {
        reviewComment.setCustomValidity('');
        reviewFeedback.hidden = true;
        updateReviewCount();
    });

    reviewForm.addEventListener('submit', (event) => {
        event.preventDefault();

        const order = orderById[selectedOrderId];
        const review = reviewComment.value.trim();
        reviewComment.setCustomValidity(review ? '' : 'Enter a comment before saving your review.');
        if (!order || order.statusClass !== 'completed' || !reviewForm.reportValidity()) {
            return;
        }

        const updatedReviews = { ...orderReviews, [order.id]: review };
        try {
            localStorage.setItem(orderReviewsStorageKey, JSON.stringify(updatedReviews));
        } catch (error) {
            console.error('Unable to save the order review in this browser.', error);
            reviewFeedback.textContent = 'Unable to save your review. Please try again.';
            reviewFeedback.hidden = false;
            return;
        }

        orderReviews = updatedReviews;
        reviewButton.textContent = 'Edit review';
        bootstrap.Modal.getOrCreateInstance(orderReviewModal).hide();
    });

    orderReviewModal.addEventListener('hidden.bs.modal', () => {
        if (returnToOrderDetails && selectedOrderId) {
            returnToOrderDetails = false;
            pendingReview = false;
            bootstrap.Modal.getOrCreateInstance(orderInfoModal).show();
        }
    });

    function renderRatingStars(rating, previewRating = rating) {
        ratingControl.querySelectorAll('.order-rating-star').forEach((star) => {
            const starRating = Number(star.dataset.rating);
            const isFilled = starRating <= previewRating;
            star.textContent = isFilled ? '\u2605' : '\u2606';
            star.classList.toggle('is-active', isFilled);
            star.setAttribute('aria-pressed', String(starRating === rating));
        });
    }

    ratingControl.addEventListener('pointerover', (event) => {
        const star = event.target.closest('.order-rating-star');
        if (star && orderById[selectedOrderId]?.statusClass === 'completed') {
            renderRatingStars(orderRatings[selectedOrderId], Number(star.dataset.rating));
        }
    });

    ratingControl.addEventListener('pointerleave', () => {
        renderRatingStars(orderRatings[selectedOrderId]);
    });

    ratingControl.addEventListener('focusin', (event) => {
        const star = event.target.closest('.order-rating-star');
        if (star && orderById[selectedOrderId]?.statusClass === 'completed') {
            renderRatingStars(orderRatings[selectedOrderId], Number(star.dataset.rating));
        }
    });

    ratingControl.addEventListener('focusout', (event) => {
        if (!ratingControl.contains(event.relatedTarget)) {
            renderRatingStars(orderRatings[selectedOrderId]);
        }
    });

    ratingControl.addEventListener('click', (event) => {
        const star = event.target.closest('.order-rating-star');
        if (!star || !selectedOrderId || orderById[selectedOrderId]?.statusClass !== 'completed') {
            return;
        }

        const rating = Number(star.dataset.rating);
        orderRatings[selectedOrderId] = rating;
        try {
            localStorage.setItem(orderRatingsStorageKey, JSON.stringify(orderRatings));
        } catch (error) {
            console.error('Unable to save the order rating in this browser.', error);
        }

        renderRatingStars(rating);
        updateCardRating(selectedOrderId, rating);
    });

    orderInfoModal.addEventListener('show.bs.modal', (event) => {
        const orderId = event.relatedTarget?.dataset.orderId || selectedOrderId;
        const order = orderById[orderId];

        if (!order) {
            console.error('Unable to show order details: the selected order was not found.');
            event.preventDefault();
            return;
        }

        selectedOrderId = order.id;
        const canRateOrder = order.statusClass === 'completed';
        ratingSection.hidden = !canRateOrder;
        ratingControl.querySelectorAll('.order-rating-star').forEach((star) => {
            star.disabled = !canRateOrder;
        });
        if (canRateOrder) {
            renderRatingStars(orderRatings[selectedOrderId]);
        }

        const orderStatus = orderInfoModal.querySelector('#orderModalStatus');
        const itemList = orderInfoModal.querySelector('#orderModalItems');
        const orderLogo = orderInfoModal.querySelector('#orderStoreLogo');
        orderLogo.src = order.logo;
        orderLogo.alt = `${order.name} logo`;
        orderInfoModal.querySelector('#orderStoreName').textContent = order.name;
        orderInfoModal.querySelector('#orderStoreLocation').textContent = order.location;
        orderInfoModal.querySelector('#orderModalId').textContent = `ORDER ID: ${order.id}`;
        orderStatus.textContent = order.status;
        orderStatus.className = `order-status ${order.statusClass}`;
        orderInfoModal.querySelector('#orderModalDate').textContent = order.date;
        orderInfoModal.querySelector('#orderSubtotal').textContent = modalCurrency.format(order.total);
        orderInfoModal.querySelector('#orderTotal').textContent = modalCurrency.format(order.total);
        orderInfoModal.querySelector('#orderPaymentMethod').textContent = order.paymentMethod;
        const paymentStatus = orderInfoModal.querySelector('#orderPaymentStatus');
        paymentStatus.textContent = order.paymentStatus;
        paymentStatus.className = `order-payment-status ${order.paymentStatus.toLowerCase().replace(/\s+/g, '-')}`;
        reviewButton.hidden = order.statusClass !== 'completed';
        reviewButton.textContent = orderReviews[order.id] ? 'Edit review' : 'Leave a review';
        cancelUserOrderButton.hidden = order.statusClass !== 'pending';
        itemList.replaceChildren();

        order.items.forEach((item) => {
            const row = document.createElement('div');
            row.className = 'order-modal-item';

            const description = document.createElement('div');
            description.className = 'order-modal-item-description';

            const name = document.createElement('strong');
            name.textContent = item.name;

            const detail = document.createElement('span');
            detail.textContent = item.detail;

            const quantity = document.createElement('span');
            quantity.className = 'order-modal-item-quantity';
            quantity.textContent = `Quantity: ${item.quantity}`;

            const price = document.createElement('strong');
            price.className = 'order-modal-item-price';
            price.textContent = modalCurrency.format(item.total);

            description.append(name, detail, quantity);
            row.append(description, price);
            itemList.append(row);
        });
    });

    // Show confirmation only after the details modal closes to avoid stacked backdrops.
    cancelUserOrderButton.addEventListener('click', () => {
        const order = orderById[selectedOrderId];
        if (!order || order.statusClass !== 'pending') {
            return;
        }

        cancelUserOrderModal.querySelector('#cancelUserOrderMessage').textContent =
            `Cancel order ${order.id} from ${order.name}?`;
        orderInfoModal.addEventListener('hidden.bs.modal', () => {
            cancelConfirmationPending = true;
            bootstrap.Modal.getOrCreateInstance(cancelUserOrderModal).show();
        }, { once: true });
        bootstrap.Modal.getOrCreateInstance(orderInfoModal).hide();
    });

    cancelUserOrderModal.querySelector('#confirmCancelUserOrderButton')
        .addEventListener('click', () => {
            const order = orderById[selectedOrderId];
            if (!order || order.statusClass !== 'pending') {
                return;
            }

            order.status = 'Order cancelled';
            order.statusClass = 'cancelled';
            order.paymentStatus = 'Not charged';
            order.isOngoing = false;

            const card = orderCardsById.get(order.id);
            const status = card?.querySelector('.order-status');
            if (status) {
                status.textContent = order.status;
                status.className = `order-status ${order.statusClass}`;
                previousOrdersSection?.append(card);
            }
            cancelUserOrderButton.hidden = true;

            try {
                savedUserOrderStatuses[order.id] = 'cancelled';
                localStorage.setItem(userOrderStatusStorageKey, JSON.stringify(savedUserOrderStatuses));
            } catch (error) {
                console.error('Unable to save the cancelled order status.', error);
            }

            bootstrap.Modal.getOrCreateInstance(cancelUserOrderModal).hide();
        });

    cancelUserOrderModal.addEventListener('hidden.bs.modal', () => {
        if (cancelConfirmationPending) {
            cancelConfirmationPending = false;
            bootstrap.Modal.getOrCreateInstance(orderInfoModal).show();
        }
    });

}


/*
MOCK LOGIN / LOGIN WITH GOOGLE :>
Sources: 
https://support.google.com/cloud/answer/15549257?hl=en
https://www.youtube.com/watch?v=tgO_ADSvY1I
*/
function handleGoogleLogin(response) {
    var payload = JSON.parse(
        atob(response.credential.spli('.')[1])
    );

    if (payload.email.endsWith("@dlsu.edu.ph")) {
        window.location.href = "stalls-directory.html";
    }
}

window.onload = function () {
    google.accounts.id.initialize({
        client_id: "724785346741-nn8e5lr9aga88afsfa9bsa79s9ofj6k3.apps.googleusercontent.com",
        callback: handleGoogleLogin,
        hd: 'dlsu.edu.ph'
    });

    google.accounts.id.renderButton(
        document.getElementById("googleLoginButton"),
        {
            theme: "outline",
            size: "large",
            text: "continue_with",
            width: 300
        }
    );
};

// login handler
function handleGoogleLogin(response) {
    var payload = JSON.parse(
        atob(response.credential.split('.')[1])
    );
    if (payload.email.endsWith('dlsu.edu.ph')) {
        localStorage.setItem('userEmail', payload.email);
        localStorage.setItem('userFirstName', payload.given_name);
        localStorage.setItem('userLastName', payload.family_name);
        localStorage.setItem('userFullName', payload.name);
        localStorage.setItem('isLoggedIn', true);

        if (payload.picture) {
            localStorage.setItem('userPicture', payload.picture);
        }
        window.location.href = 'stalls-directory.html';
    }
}

// replace login
$(document).ready(function () {
    var firstName = localStorage.getItem('userFirstName');
    var lastName = localStorage.getItem('userLastName');
    var fullName = localStorage.getItem('userFullName');
    var email = localStorage.getItem('userEmail');
    var picture = localStorage.getItem('userPicture');

    var newProfilePic = null;
    

    if(fullName) {
        $('#profileName').text(fullName);
    }
    if (email) {
        $('#profileEmail').text(email);
        $('#emailAddress').val(email);
    }
    if (firstName) {
        $('#firstName').val(firstName);
    }
    if (lastName) {
        $('#lastName').val(lastName);
    }
    if (picture) {
        $('#profileAvatar').attr('src', picture);
    }

    // EDIT PROFILE BUTTON
    $('#editProfileBtn').click(function () {
        if ($(this).text().trim() == 'Edit Profile') {
            $('#firstName').prop('readonly', false);
            $('#lastName').prop('readonly', false);
            $('#contactNumber').prop('readonly', false);
            $('#profileAvatar').addClass('editable-avatar');

            $(this).text('Save Changes');
        }
        else {
            var newFirstName = $('#firstName').val().trim();
            var newLastName = $('#lastName').val().trim();
            var newContactNumber = $('#contactNumber').val().trim();

            localStorage.setItem('userFirstName', newFirstName);
            localStorage.setItem('userLastName', newLastName);
            localStorage.setItem('userFullName', newFirstName + ' ' + newLastName);
            localStorage.setItem('userContactNumber', newContactNumber);

            if (newProfilePic) {
                 localStorage.setItem('userPicture', newProfilePic);
                 newProfilePic = null;
            }

            $('#profileName').text(newFirstName + ' ' + newLastName);
            $('#firstName').prop('readonly', true);
            $('#lastName').prop('readonly', true);
            $('#contactNumber').prop('readonly', true);

            $('#profileAvatar').removeClass('editable-avatar');

            $(this).text('Edit Profile');
            alert('Profile updated successfully!');
        }
    });

    // PROFILE PIC
    $('#profileAvatar').click(function () {
        if ($('#editProfileBtn').text().trim() == 'Save Changes') {
            $('#profilePictureInput').click();
        }
    });

    $('#profilePictureInput').change(function () {
        var file = this.files[0];

        if (file) {
            var reader = new FileReader();
            reader.onload = function (e) {
                newProfilePic = e.target.result;

                // preview only
                $('#profileAvatar').attr('src', newProfilePic);
            };
            reader.readAsDataURL(file);
        }
    });

    // LOGOUT BUTTON
    $('#logoutBtn').click(function () {
        localStorage.removeItem('userFirstName');
        localStorage.removeItem('userLastName');
        localStorage.removeItem('userFullName');
        localStorage.removeItem('userEmail');
        localStorage.removeItem('userPicture');
        localStorage.removeItem('userContactNumber');
        localStorage.removeItem('isLoggedIn');

        window.location.href = 'login.html';
    });
});

// Profile page - order frequency chart
$(document).ready(function () {
    var chartCanvas = $('#orderFrequencyChart');
    if (chartCanvas.length) {
        var ctx = chartCanvas[0].getContext('2d');
        var orderFrequencyChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Number of Orders',
                    data: [0, 1, 4, 6, 3, 6, 7, 3, 10, 2, 12, 0],
                    backgroundColor: '#006937',
                    borderColor: '#006937',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {stepSize: 2}
                    }
                }
            }
        });
    }
});