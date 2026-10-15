const stalls = {
    starbucks: {
        name: 'Starbucks',
        location: 'Green Court (Fidel A. Reyes Street)',
        hours: '9:00 AM - 11:00 PM',
        logo: '../../assets/stalls/starbucks.png',
        banner: '../../assets/stalls/starbucks-banner.png',
        menu: [
            ['Java Chip Frappuccino', 210, 'javachip.png'],
            ['Caramel Macchiato', 210, 'caramel.png'],
            ['Pure Matcha Latte', 190, 'matchalatte.png'],
            ['Caffe Americano', 180, 'americano.png']
        ]
    },
    'dairy-queen': {
        name: 'Dairy Queen',
        location: 'Agno',
        hours: '8:00 AM - 5:00 PM',
        logo: '../../assets/stalls/dq.png',
        banner: '../../assets/stalls/starbucks-banner.png',
        menu: [
            ['Classic Blizzard', 179, 'javachip.png'],
            ['Vanilla Cone', 99, 'caramel.png'],
            ['Chocolate Sundae', 129, 'matchalatte.png'],
            ['DQ Freeze', 149, 'americano.png']
        ]
    },
    'belgian-waffles': {
        name: 'The Famous Belgian Waffles',
        location: 'Gokongwei Hall',
        hours: '8:00 AM - 5:00 PM',
        logo: '../../assets/stalls/belgian.png',
        banner: '../../assets/stalls/starbucks-banner.png',
        menu: [
            ['Classic Belgian Waffle', 145, 'javachip.png'],
            ['Chocolate Waffle', 165, 'caramel.png'],
            ['Strawberry Waffle', 175, 'matchalatte.png'],
            ['Cheese Waffle', 155, 'americano.png']
        ]
    },
    'potato-corner': {
        name: 'Potato Corner',
        location: 'Bloemen',
        hours: '8:00 AM - 5:00 PM',
        logo: '../../assets/stalls/potcor.png',
        banner: '../../assets/stalls/starbucks-banner.png',
        menu: [
            ['Regular Fries', 90, 'javachip.png'],
            ['Cheese Fries', 110, 'caramel.png'],
            ['BBQ Fries', 110, 'matchalatte.png'],
            ['Mega Fries', 210, 'americano.png']
        ]
    }
};

const params = new URLSearchParams(window.location.search);
const stall = stalls[params.get('stall')] || stalls.starbucks;
const stallId = params.get('stall') || 'starbucks';

const completedOrderStalls = new Set(['starbucks', 'dairy-queen', 'potato-corner']);
const reviewStorageKey = `greendash-reviews-${stallId}`;
const sampleReviews = [
    { rating: 5, text: 'The drinks were delicious and my order was ready on time.', date: '2026-10-07', author: 'GreenDash user' },
    { rating: 4, text: 'Good food and friendly service.', date: '2026-09-29', author: 'GreenDash user' },
    { rating: 5, text: '', date: '2026-09-18', author: 'GreenDash user' }
];

window.scrollTo(0, 0);

function updateOrderPanel() {
    const header = document.querySelector('header');
    const orderSummary = document.querySelector('.order-summary');
    if (header && orderSummary) {
        const headerBottom = Math.max(0, header.getBoundingClientRect().bottom);
        orderSummary.style.setProperty('--header-height', `${headerBottom}px`);
    }
}

updateOrderPanel();
window.addEventListener('resize', updateOrderPanel);
window.addEventListener('scroll', updateOrderPanel, { passive: true });

document.title = `GreenDash - ${stall.name}`;
document.querySelector('#stallName').textContent = stall.name;
document.querySelector('#stallLocation').textContent = stall.location;
document.querySelector('#stallHours').textContent = stall.hours;

const reviewModal = document.querySelector('#reviewModal');
const reviewMessage = document.querySelector('#reviewMessage');
const reviewList = document.querySelector('#reviewList');
let selectedReviewRating = 0;
let editingReviewId = null;

function getReviewId(review, index) {
    if (review.id) {
        return review.id;
    }

    return `${review.author}-${review.date}-${index}`;
}

function getReviews() {
    const savedReviews = localStorage.getItem(reviewStorageKey);

    if (!savedReviews) {
        return sampleReviews;
    }

    try {
        const reviews = JSON.parse(savedReviews);

        if (Array.isArray(reviews)) {
            return reviews;
        }

        return sampleReviews;
    } catch (error) {
        console.error('Unable to load stall reviews.', error);
        return sampleReviews;
    }
}

function renderReviews() {
    const filter = document.querySelector('#reviewStarFilter').value;
    const sort = document.querySelector('#reviewSort').value;
    const reviews = getReviews()
        .filter((review) => filter === 'all' || review.rating === Number(filter))
        .sort((a, b) => {
            const difference = new Date(a.date) - new Date(b.date);

            if (sort === 'oldest') {
                return difference;
            }

            return -difference;
        });

    if (reviews.length === 0) {
        reviewList.innerHTML = '<p class="review-empty">No reviews match this filter.</p>';
        return;
    }

    reviewList.innerHTML = reviews.map((review, index) => {
        let reviewText = '<p class="review-no-text">No written review.</p>';
        let reviewActions = '';

        if (review.text) {
            reviewText = `<p>${review.text}</p>`;
        }

        if (review.author === 'You') {
            reviewActions = `
                <div class="review-actions">
                    <button type="button" class="edit-review-button" data-review-id="${getReviewId(review, index)}">Edit</button>
                    <button type="button" class="delete-review-button" data-review-id="${getReviewId(review, index)}">Delete</button>
                </div>`;
        }

        return `
            <article class="review-card" data-review-id="${getReviewId(review, index)}">
                <div class="review-card-heading">
                    <strong>${review.author}</strong>
                    <time datetime="${review.date}">${new Date(`${review.date}T00:00:00`).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' })}</time>
                </div>
                <div class="review-card-stars" aria-label="${review.rating} out of 5 stars">${'★'.repeat(review.rating)}${'☆'.repeat(5 - review.rating)}</div>
                ${reviewText}
                ${reviewActions}
            </article>`;
    }).join('');
}

function openReviews() {
    document.querySelector('#reviewModalTitle').textContent = `${stall.name} reviews`;
    renderReviews();
    const canReview = completedOrderStalls.has(stallId);
    document.querySelector('#stallReviewForm').hidden = !canReview;
    document.querySelector('#reviewEligibilityMessage').hidden = canReview;
    reviewModal.hidden = false;
}

document.querySelector('#openReviewsButton').addEventListener('click', openReviews);
document.querySelector('#closeReviewsButton').addEventListener('click', () => {
    reviewModal.hidden = true;
});
document.querySelector('#closeReviewMessage').addEventListener('click', () => {
    reviewMessage.hidden = true;
});
reviewModal.addEventListener('click', (event) => {
    if (event.target === reviewModal) reviewModal.hidden = true;
});
reviewMessage.addEventListener('click', (event) => {
    if (event.target === reviewMessage) reviewMessage.hidden = true;
});
reviewList.addEventListener('click', (event) => {
    const reviewId = event.target.dataset.reviewId;

    if (!reviewId) {
        return;
    }

    const reviews = getReviews();
    const reviewIndex = reviews.findIndex((review, index) => getReviewId(review, index) === reviewId);

    if (reviewIndex === -1 || reviews[reviewIndex].author !== 'You') {
        return;
    }

    if (event.target.classList.contains('edit-review-button')) {
        const review = reviews[reviewIndex];
        editingReviewId = reviewId;
        selectedReviewRating = review.rating;
        document.querySelector('#stallReviewText').value = review.text;
        document.querySelector('#cancelReviewEdit').hidden = false;
        document.querySelector('#stallReviewForm .submit-review-button').textContent = 'Save changes';
        document.querySelectorAll('.review-rating-input button').forEach((star) => {
            star.textContent = '☆';

            if (Number(star.dataset.rating) <= selectedReviewRating) {
                star.textContent = '★';
            }
        });
        return;
    }

    if (event.target.classList.contains('delete-review-button')) {
        const shouldDelete = window.confirm('Delete your review?');

        if (!shouldDelete) {
            return;
        }

        reviews.splice(reviewIndex, 1);
        try {
            localStorage.setItem(reviewStorageKey, JSON.stringify(reviews));
        } catch (error) {
            console.error('Unable to delete the stall review.', error);
            return;
        }
        renderReviews();
    }
});
document.querySelector('#reviewStarFilter').addEventListener('change', renderReviews);
document.querySelector('#reviewSort').addEventListener('change', renderReviews);

document.querySelectorAll('.review-rating-input button').forEach((button) => {
    button.addEventListener('click', () => {
        selectedReviewRating = Number(button.dataset.rating);
        document.querySelectorAll('.review-rating-input button').forEach((star) => {
            star.textContent = '☆';

            if (Number(star.dataset.rating) <= selectedReviewRating) {
                star.textContent = '★';
            }
        });
    });
});

document.querySelector('#stallReviewForm').addEventListener('submit', (event) => {
    event.preventDefault();
    if (!selectedReviewRating) return;
    const review = {
        id: `review-${Date.now()}`,
        rating: selectedReviewRating,
        text: document.querySelector('#stallReviewText').value.trim(),
        date: new Date().toISOString().slice(0, 10),
        author: 'You'
    };
    const reviews = getReviews();

    if (editingReviewId) {
        const reviewIndex = reviews.findIndex((savedReview, index) => getReviewId(savedReview, index) === editingReviewId);

        if (reviewIndex !== -1) {
            review.id = editingReviewId;
            review.date = reviews[reviewIndex].date;
            reviews[reviewIndex] = review;
        }
    } else {
        reviews.push(review);
    }

    try {
        localStorage.setItem(reviewStorageKey, JSON.stringify(reviews));
    } catch (error) {
        console.error('Unable to save stall review.', error);
        return;
    }
    document.querySelector('#stallReviewText').value = '';
    selectedReviewRating = 0;
    editingReviewId = null;
    document.querySelector('#cancelReviewEdit').hidden = true;
    document.querySelector('#stallReviewForm .submit-review-button').textContent = 'Post review';
    document.querySelectorAll('.review-rating-input button').forEach((star) => { star.textContent = '☆'; });
    renderReviews();
});

document.querySelector('#cancelReviewEdit').addEventListener('click', () => {
    editingReviewId = null;
    selectedReviewRating = 0;
    document.querySelector('#stallReviewText').value = '';
    document.querySelector('#cancelReviewEdit').hidden = true;
    document.querySelector('#stallReviewForm .submit-review-button').textContent = 'Post review';
    document.querySelectorAll('.review-rating-input button').forEach((star) => { star.textContent = '☆'; });
});

const banner = document.querySelector('#stallBanner');
banner.src = stall.banner;
banner.alt = `Banner for ${stall.name}`;

const logo = document.querySelector('#stallLogo');
logo.src = stall.logo;
logo.alt = `Logo of ${stall.name}`;

const menuItems = document.querySelector('#menuItems');
const cart = [];
let selectedItem = null;
let selectedQuantity = 1;

const modal = document.querySelector('#itemModal');
const modalPrice = document.querySelector('#modalBasePrice');
const addToCartButton = document.querySelector('#addToCartButton');

stall.menu.forEach(([name, price, image]) => {
    const item = document.createElement('div');
    item.className = 'col-12 col-sm-6 col-md-4 col-lg-3 menu-column';
    item.dataset.name = name.toLowerCase();
    item.dataset.price = String(price);
    item.innerHTML = `
        <div class="menu-item">
            <div class="menu-image-container">
                <img src="../../assets/starbucksMenu/${image}" alt="${name}">
                <button type="button" class="add-item-button" aria-label="Add ${name}" data-menu-name="${name}" data-menu-price="${price}" data-menu-image="${image}">+</button>
            </div>
            <h3>${name}</h3>
            <p>from &#8369; ${price}</p>
        </div>`;
    menuItems.appendChild(item);
});

function getCustomizationPrice() {
    const milk = document.querySelector('input[name="milk"]:checked');
    const size = document.querySelector('input[name="size"]:checked');
    return Number(selectedItem.price) + Number(milk?.dataset.price || 0) + Number(size?.dataset.price || 0);
}

function updateModalPrice() {
    const total = getCustomizationPrice() * selectedQuantity;
    modalPrice.textContent = selectedItem.price;
    addToCartButton.innerHTML = `Add to cart - &#8369; ${total}`;
    document.querySelector('#itemQuantity').textContent = selectedQuantity;
}

function openItemModal(item) {
    selectedItem = item;
    selectedQuantity = 1;
    document.querySelector('#modalItemName').textContent = item.name;
    document.querySelector('#modalItemDescription').textContent = `${item.name} prepared fresh and customized to your preference.`;
    const image = document.querySelector('#modalItemImage');
    image.src = `../../assets/starbucksMenu/${item.image}`;
    image.alt = item.name;
    modal.hidden = false;
    updateModalPrice();
}

function renderOrder() {
    const orderItems = document.querySelector('#orderItems');
    if (!cart.length) {
        orderItems.innerHTML = '<p class="empty-order-message">Your cart is empty!</p>';
    } else {
        orderItems.innerHTML = cart.map((item) => `
            <div class="order-item">
                <img src="../../assets/starbucksMenu/${item.image}" alt="${item.name}">
                <div><h3>${item.name}</h3><p>${item.size} · ${item.quantity}</p></div>
                <strong>&#8369; ${item.total}</strong>
            </div>`).join('');
    }
    const total = cart.reduce((sum, item) => sum + item.total, 0);
    document.querySelector('#checkoutButton').innerHTML = `Checkout - &#8369; ${total}`;
}

document.querySelectorAll('.add-item-button').forEach((button) => {
    button.addEventListener('click', () => openItemModal({
        name: button.dataset.menuName,
        price: Number(button.dataset.menuPrice),
        image: button.dataset.menuImage
    }));
});

document.querySelectorAll('input[name="milk"], input[name="size"]').forEach((input) => {
    input.addEventListener('change', updateModalPrice);
});

document.querySelector('#increaseQuantity').addEventListener('click', () => {
    selectedQuantity += 1;
    updateModalPrice();
});

document.querySelector('#decreaseQuantity').addEventListener('click', () => {
    selectedQuantity = Math.max(1, selectedQuantity - 1);
    updateModalPrice();
});

addToCartButton.addEventListener('click', () => {
    const size = document.querySelector('input[name="size"]:checked').value;
    cart.push({
        ...selectedItem,
        size,
        quantity: selectedQuantity,
        total: getCustomizationPrice() * selectedQuantity
    });
    modal.hidden = true;
    renderOrder();
    document.body.classList.add('cart-open');
});

document.querySelector('#modalCloseButton').addEventListener('click', () => {
    modal.hidden = true;
});

modal.addEventListener('click', (event) => {
    if (event.target === modal) modal.hidden = true;
});

document.querySelector('#cart').addEventListener('click', () => {
    updateOrderPanel();
    document.body.classList.toggle('cart-open');
});

document.querySelector('#closeOrderButton').addEventListener('click', () => {
    document.body.classList.remove('cart-open');
});

document.querySelector('#filterButton').addEventListener('click', () => {
    const panel = document.querySelector('#filterPanel');

    if (panel.style.display === 'block') {
        panel.style.display = 'none';
    } else {
        panel.style.display = 'block';
    }
});

document.querySelector('#menuSearch').addEventListener('input', (event) => {
    const search = event.target.value.trim().toLowerCase();
    document.querySelectorAll('.menu-column').forEach((item) => {
        item.hidden = !item.dataset.name.includes(search);
    });
});

document.querySelector('#priceRange').addEventListener('input', (event) => {
    document.querySelector('#priceValue').textContent = event.target.value;
    document.querySelectorAll('.menu-column').forEach((item) => {
        item.hidden = Number(item.dataset.price) > Number(event.target.value);
    });
});

document.querySelector('#resetFilters').addEventListener('click', () => {
    document.querySelector('#priceRange').value = 1000;
    document.querySelector('#priceValue').textContent = '1000';
    document.querySelector('#menuSearch').value = '';
    document.querySelectorAll('.menu-column').forEach((item) => {
        item.hidden = false;
    });
});
