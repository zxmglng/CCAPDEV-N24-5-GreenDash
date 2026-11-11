const stallAssetPath = window.location.pathname.includes('/app/views/')
    ? '../../../assets/'
    : '../../assets/';

const stalls = {
    starbucks: {
        name: 'Starbucks',
        location: 'Green Court (Fidel A. Reyes Street)',
        hours: '9:00 AM - 11:00 PM',
        logo: `${stallAssetPath}stalls/starbucks.png`,
        banner: `${stallAssetPath}stalls/starbucks-banner.png`,
        menu: [
            ['Java Chip Frappuccino', 210, 'javachip.png'],
            ['Caramel Macchiato', 210, 'caramel.png'],
            ['Pure Matcha Latte', 190, 'matchalatte.png'],
            ['Caffe Americano', 180, 'americano.png']
        ]
    }
};

const params = new URLSearchParams(window.location.search);
const stallDataElement = document.querySelector('#stallData');
const serverStallData = stallDataElement ? JSON.parse(stallDataElement.textContent) : null;
const stall = serverStallData?.stall || stalls[params.get('stall')] || stalls.starbucks;
const stallId = String(stall.id || params.get('stall') || 'starbucks');
const stallApplicationUrl = new URL('../', document.currentScript.src);

function stallImageUrl(path) {
    if (!path) return '';
    const marker = path.indexOf('assets/');
    if (marker !== -1) return new URL(path.slice(marker), stallApplicationUrl).href;
    if (/^(https?:|data:|\/)/i.test(path)) return new URL(path, stallApplicationUrl).href;
    return new URL(`assets/starbucksMenu/${path}`, stallApplicationUrl).href;
}

function escapeStallHtml(value) {
    return String(value).replace(/[&<>"']/g, character => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    })[character]);
}

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

function convertTimeToMinutes(time) {
    const parts = time.match(/(\d+):(\d+)\s*(AM|PM)/i);
    let hour = Number(parts[1]);
    const minute = Number(parts[2]);

    if (parts[3].toUpperCase() === 'PM' && hour !== 12) {
        hour += 12;
    }

    if (parts[3].toUpperCase() === 'AM' && hour === 12) {
        hour = 0;
    }

    return hour * 60 + minute;
}

function formatMinutesAsTime(minutes) {
    let hour = Math.floor(minutes / 60);
    const minute = minutes % 60;
    let period = 'AM';

    if (hour >= 12) {
        period = 'PM';
    }

    if (hour > 12) {
        hour -= 12;
    }

    if (hour === 0) {
        hour = 12;
    }

    return `${hour}:${String(minute).padStart(2, '0')} ${period}`;
}

function populatePickupTimes() {
    if (Array.isArray(stall.pickupTimes)) {
        [document.querySelector('#pickupTimeMobile'), document.querySelector('#pickupTime')].forEach(select => {
            for (const value of ['now', ...stall.pickupTimes]) {
                const option = document.createElement('option');
                option.value = value;
                option.textContent = value === 'now' ? 'Pick up now' : value;
                select.appendChild(option);
            }
        });
        return;
    }
    const hours = stall.hours.match(/(\d+):(\d+)\s*(AM|PM)/gi);

    if (!hours || hours.length !== 2) {
        return;
    }

    const openingMinutes = convertTimeToMinutes(hours[0]);
    const closingMinutes = convertTimeToMinutes(hours[1]);
    const pickupSelects = [
        document.querySelector('#pickupTimeMobile'),
        document.querySelector('#pickupTime')
    ];

    pickupSelects.forEach((select) => {
        const pickupNowOption = document.createElement('option');
        pickupNowOption.value = 'now';
        pickupNowOption.textContent = 'Pick up now';
        select.appendChild(pickupNowOption);

        for (let start = openingMinutes; start + 15 <= closingMinutes; start += 15) {
            const option = document.createElement('option');
            option.value = `${formatMinutesAsTime(start)} - ${formatMinutesAsTime(start + 15)}`;
            option.textContent = option.value;
            select.appendChild(option);
        }
    });
}

populatePickupTimes();

document.querySelector('#pickupTimeMobile').addEventListener('change', (event) => {
    document.querySelector('#pickupTime').value = event.target.value;
});

document.querySelector('#pickupTime').addEventListener('change', (event) => {
    document.querySelector('#pickupTimeMobile').value = event.target.value;
});

const reviewModal = document.querySelector('#reviewModal');
const reviewMessage = document.querySelector('#reviewMessage');
const reviewList = document.querySelector('#reviewList');
const stallReviewForm = document.querySelector('#stallReviewForm');
const stallReviewOrder = document.querySelector('#stallReviewOrder');
const stallReviewFeedback = document.querySelector('#stallReviewFeedback');
let selectedReviewRating = 0;
let stallReviews = [];
let eligibleReviewOrders = [];
let reviewCsrfToken = null;
let stallReviewBusy = false;
let stallReviewsLoading = false;
stallReviewForm.hidden = true;

function stallReviewElement(tag, className, text) {
    const element = document.createElement(tag);
    if (className) element.className = className;
    if (text !== undefined) element.textContent = text;
    return element;
}

function showStallReviewError(error) {
    if (!stallReviewFeedback) return;
    stallReviewFeedback.textContent = error.message || 'Your review could not be saved. Please try again.';
    stallReviewFeedback.hidden = false;
}

function updateStallReviewStars() {
    document.querySelectorAll('.review-rating-input button').forEach(star => {
        const rating = Number(star.dataset.rating);
        star.textContent = rating <= selectedReviewRating ? '★' : '☆';
        star.setAttribute('aria-pressed', String(rating === selectedReviewRating));
    });
}

function fillStallReviewForm(orderId) {
    const review = stallReviews.find(item => item.isOwner && item.orderId === Number(orderId));
    selectedReviewRating = review?.rating || 0;
    document.querySelector('#stallReviewText').value = review?.text || '';
    document.querySelector('#cancelReviewEdit').hidden = !review;
    stallReviewForm.querySelector('.submit-review-button').textContent = review ? 'Save changes' : 'Post review';
    if (stallReviewFeedback) stallReviewFeedback.hidden = true;
    updateStallReviewStars();
}

function updateReviewFormVisibility(preferredOrderId) {
    const canReview = eligibleReviewOrders.length > 0 && Boolean(reviewCsrfToken) && Boolean(stallReviewOrder);
    stallReviewForm.hidden = !canReview;
    document.querySelector('#reviewEligibilityMessage').hidden = canReview;
    if (!canReview) return;
    stallReviewOrder.replaceChildren();
    eligibleReviewOrders.forEach(order => {
        const option = document.createElement('option');
        option.value = order.orderId;
        option.textContent = `Order ${order.orderNumber}${order.reviewId ? ' (reviewed)' : ''}`;
        stallReviewOrder.appendChild(option);
    });
    const selected = eligibleReviewOrders.find(order => order.orderId === Number(preferredOrderId))
        || eligibleReviewOrders.find(order => !order.reviewId) || eligibleReviewOrders[0];
    stallReviewOrder.value = selected.orderId;
    fillStallReviewForm(selected.orderId);
}

function renderReviews() {
    const filter = document.querySelector('#reviewStarFilter').value;
    const sort = document.querySelector('#reviewSort').value;
    const reviews = stallReviews.filter(review => filter === 'all' || review.rating === Number(filter))
        .sort((first, second) => {
            const difference = new Date(first.createdAt.replace(' ', 'T')) - new Date(second.createdAt.replace(' ', 'T'))
                || first.id - second.id;
            return sort === 'oldest' ? difference : -difference;
        });
    reviewList.replaceChildren();
    if (!reviews.length) {
        reviewList.appendChild(stallReviewElement('p', 'review-empty', stallReviews.length ? 'No reviews match this filter.' : 'No reviews yet.'));
        return;
    }
    reviews.forEach(review => {
        const card = stallReviewElement('article', 'review-card');
        const heading = stallReviewElement('div', 'review-card-heading');
        const date = stallReviewElement('time', '', new Date(`${review.date}T00:00:00`).toLocaleDateString('en-PH', {
            year: 'numeric', month: 'short', day: 'numeric',
        }));
        date.dateTime = review.date;
        heading.append(stallReviewElement('strong', '', review.author), date);
        const stars = stallReviewElement('div', 'review-card-stars', '★'.repeat(review.rating) + '☆'.repeat(5 - review.rating));
        stars.setAttribute('aria-label', `${review.rating} out of 5 stars`);
        card.append(heading, stars, stallReviewElement('p', review.text ? '' : 'review-no-text', review.text || 'No written review.'));
        if (review.isOwner) {
            const actions = stallReviewElement('div', 'review-actions');
            for (const [action, text] of [['edit', 'Edit'], ['delete', 'Delete']]) {
                const button = stallReviewElement('button', `${action}-review-button`, text);
                button.type = 'button';
                button.dataset.reviewId = review.id;
                button.dataset.action = action;
                button.disabled = stallReviewBusy;
                actions.append(button);
            }
            card.append(actions);
        }
        reviewList.appendChild(card);
    });
}

async function refreshStallReviews(preferredOrderId) {
    if (!window.GreenDashReviews || !serverStallData) throw new Error('Reviews are currently unavailable. Please try again later.');
    const result = await window.GreenDashReviews.load(stallId);
    stallReviews = result.reviews;
    eligibleReviewOrders = result.eligibleOrders;
    reviewCsrfToken = result.csrfToken;
    document.querySelector('#reviewModalTitle').textContent = result.count
        ? `${stall.name} reviews — ${result.averageRating}/5 (${result.count})` : `${stall.name} reviews`;
    const summary = document.querySelector('.review-stars');
    if (summary) {
        const rating = Math.round(result.averageRating || 0);
        summary.textContent = '★'.repeat(rating) + '☆'.repeat(5 - rating);
    }
    renderReviews();
    updateReviewFormVisibility(preferredOrderId);
}

async function openReviews() {
    reviewModal.hidden = false;
    if (stallReviewsLoading) return;
    stallReviewsLoading = true;
    stallReviewForm.hidden = true;
    document.querySelector('#reviewModalTitle').textContent = `${stall.name} reviews`;
    reviewList.textContent = 'Loading reviews…';
    try {
        await refreshStallReviews();
    } catch (error) {
        reviewList.textContent = error.message;
        document.querySelector('#reviewEligibilityMessage').hidden = true;
    } finally { stallReviewsLoading = false; }
}

function setStallReviewBusy(busy) {
    stallReviewBusy = busy;
    stallReviewForm.querySelectorAll('button, select, textarea').forEach(control => {control.disabled = busy;});
    reviewList.querySelectorAll('button').forEach(button => {button.disabled = busy;});
}

document.querySelector('#openReviewsButton').addEventListener('click', openReviews);
document.querySelector('#closeReviewsButton').addEventListener('click', () => {reviewModal.hidden = true;});
document.querySelector('#closeReviewMessage').addEventListener('click', () => {reviewMessage.hidden = true;});
reviewModal.addEventListener('click', event => {if (event.target === reviewModal) reviewModal.hidden = true;});
reviewMessage.addEventListener('click', event => {if (event.target === reviewMessage) reviewMessage.hidden = true;});
document.querySelector('#reviewStarFilter').addEventListener('change', renderReviews);
document.querySelector('#reviewSort').addEventListener('change', renderReviews);
stallReviewOrder?.addEventListener('change', () => fillStallReviewForm(stallReviewOrder.value));

document.querySelectorAll('.review-rating-input button').forEach(button => {
    button.addEventListener('click', () => {
        selectedReviewRating = Number(button.dataset.rating);
        updateStallReviewStars();
    });
});

reviewList.addEventListener('click', async event => {
    const button = event.target.closest('button[data-review-id]');
    if (!button || stallReviewBusy) return;
    const review = stallReviews.find(item => item.id === Number(button.dataset.reviewId) && item.isOwner);
    if (!review) return;
    if (button.dataset.action === 'edit') {
        if (!stallReviewOrder) return;
        stallReviewOrder.value = review.orderId;
        fillStallReviewForm(review.orderId);
        document.querySelector('#stallReviewText').focus();
    } else if (button.dataset.action === 'delete' && window.confirm('Delete your review for this order?')) {
        setStallReviewBusy(true);
        try {
            await window.GreenDashReviews.remove(review.orderId, reviewCsrfToken);
            await refreshStallReviews();
        } catch (error) { showStallReviewError(error); }
        finally { setStallReviewBusy(false); }
    }
});

stallReviewForm.addEventListener('submit', async event => {
    event.preventDefault();
    if (stallReviewBusy || !stallReviewOrder || !stallReviewForm.reportValidity()) return;
    if (!selectedReviewRating) {showStallReviewError(new Error('Choose a rating from 1 to 5.')); return;}
    const orderId = Number(stallReviewOrder.value);
    const comment = document.querySelector('#stallReviewText').value.trim();
    setStallReviewBusy(true);
    try {
        await window.GreenDashReviews.save(orderId, selectedReviewRating, comment, reviewCsrfToken);
        await refreshStallReviews();
    } catch (error) { showStallReviewError(error); }
    finally { setStallReviewBusy(false); }
});

document.querySelector('#cancelReviewEdit').addEventListener('click', () => updateReviewFormVisibility());


const banner = document.querySelector('#stallBanner');
banner.src = stallImageUrl(stall.banner);
banner.hidden = !stall.banner;
banner.alt = `Banner for ${stall.name}`;

const logo = document.querySelector('#stallLogo');
logo.src = stallImageUrl(stall.logo);
logo.hidden = !stall.logo;
logo.alt = `Logo of ${stall.name}`;

const menuItems = document.querySelector('#menuItems');
const cart = [];
let selectedItem = null;
let selectedQuantity = 1;
let activeCategory = 'all';
let menuAnimationDirection = 'next';
const menuCategories = serverStallData ? ['all'] : ['all', 'drinks', 'food', 'refreshers', 'coffee'];

const modal = document.querySelector('#itemModal');
const modalPrice = document.querySelector('#modalBasePrice');
const addToCartButton = document.querySelector('#addToCartButton');

const menuProducts = serverStallData ? serverStallData.products.map(product => ({
    productId: Number(product.id), name: product.name, price: Number(product.price),
    image: product.image_path || '',
})) : stall.menu.map(([name, price, image]) => ({name, price, image}));

menuProducts.forEach(({productId, name, price, image}, index) => {
    const item = document.createElement('div');
    item.className = 'col-12 col-sm-6 col-md-4 col-lg-3 menu-column';
    item.dataset.category = serverStallData ? 'all' : index === 0 ? 'all coffee' : menuCategories[index];
    item.dataset.name = name.toLowerCase();
    item.dataset.price = String(price);
    item.innerHTML = `
        <div class="menu-item">
            <div class="menu-image-container">
                <img src="${escapeStallHtml(stallImageUrl(image))}" alt="${escapeStallHtml(name)}" ${image ? '' : 'hidden'}>
                <button type="button" class="add-item-button" aria-label="Add ${escapeStallHtml(name)}"
                    data-product-id="${productId || ''}" data-menu-name="${escapeStallHtml(name)}"
                    data-menu-price="${price}" data-menu-image="${escapeStallHtml(image)}">
                    <span aria-hidden="true">+</span>
                </button>
            </div>
            <h3>${escapeStallHtml(name)}</h3>
            <p>from &#8369; ${price}</p>
        </div>`;
    menuItems.appendChild(item);
});

function belongsToCategory(item, category) {
    if (category === 'all') {
        return true;
    }

    return item.dataset.category.split(' ').includes(category);
}

function showMenuCategory(category, direction = 'next') {
    activeCategory = category;
    menuAnimationDirection = direction;

    document.querySelectorAll('.menu-column').forEach((item) => {
        item.hidden = !belongsToCategory(item, activeCategory);
    });

    document.querySelectorAll('.category-button, .category-button-active').forEach((button) => {
        button.className = 'category-button';

        if (button.dataset.category === activeCategory) {
            button.className = 'category-button-active';
        }
    });

    menuItems.classList.remove('menu-slide-next', 'menu-slide-previous');
    void menuItems.offsetWidth;
    menuItems.classList.add(`menu-slide-${menuAnimationDirection}`);
    applyMenuFilters();
}

document.querySelectorAll('.menu-categories button').forEach((button) => {
    button.addEventListener('click', () => {
        showMenuCategory(button.dataset.category);
    });
});

document.querySelector('#previousMenuItem').addEventListener('click', () => {
    const categoryIndex = menuCategories.indexOf(activeCategory);
    const previousIndex = (categoryIndex - 1 + menuCategories.length) % menuCategories.length;
    showMenuCategory(menuCategories[previousIndex], 'previous');
});

document.querySelector('#nextMenuItem').addEventListener('click', () => {
    const categoryIndex = menuCategories.indexOf(activeCategory);
    const nextIndex = (categoryIndex + 1) % menuCategories.length;
    showMenuCategory(menuCategories[nextIndex]);
});

showMenuCategory(activeCategory);

function getCustomizationPrice() {
    const milk = document.querySelector('input[name="milk"]:checked');
    const size = document.querySelector('input[name="size"]:checked');
    const cents = [selectedItem.price, milk?.dataset.price || 0, size?.dataset.price || 0]
        .reduce((sum, price) => sum + Math.round(Number(price) * 100), 0);
    return cents / 100;
}

function updateModalPrice() {
    const total = Math.round(getCustomizationPrice() * 100) * selectedQuantity / 100;
    modalPrice.textContent = selectedItem.price;
    addToCartButton.innerHTML = `Add to cart - &#8369; ${total.toFixed(2)}`;
    document.querySelector('#itemQuantity').textContent = selectedQuantity;
}

function openItemModal(item) {
    selectedItem = item;
    selectedQuantity = 1;
    document.querySelector('#modalItemName').textContent = item.name;
    document.querySelector('#modalItemDescription').textContent = `${item.name} prepared fresh and customized to your preference.`;
    const image = document.querySelector('#modalItemImage');
    image.src = stallImageUrl(item.image);
    image.hidden = !item.image;
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
                <img src="${escapeStallHtml(stallImageUrl(item.image))}" alt="${escapeStallHtml(item.name)}" ${item.image ? '' : 'hidden'}>
                <div><h3>${escapeStallHtml(item.name)}</h3><p>${escapeStallHtml(item.size)} · ${escapeStallHtml(item.milk || 'No milk customization')} · ${item.quantity}</p></div>
                <strong>&#8369; ${item.total.toFixed(2)}</strong>
            </div>`).join('');
    }
    const total = cart.reduce((sum, item) => sum + Math.round(item.total * 100), 0) / 100;
    document.querySelector('#checkoutButton').innerHTML = `Checkout - &#8369; ${total.toFixed(2)}`;
}

function openCheckout() {
    const desktopPickupTime = document.querySelector('#pickupTime').value;
    const mobilePickupTime = document.querySelector('#pickupTimeMobile').value;
    const pickupTime = desktopPickupTime || mobilePickupTime;
    const total = cart.reduce((sum, item) => sum + Math.round(item.total * 100), 0) / 100;

    if (!cart.length) {
        document.querySelector('#emptyCartMessage').hidden = false;
        return;
    }

    const checkoutData = {
        stall: {
            id: stall.id || null,
            name: stall.name,
            location: stall.location,
            hours: stall.hours,
            logo: stallImageUrl(stall.logo),
            pickupTimes: stall.pickupTimes,
        },
        items: cart,
        pickupTime,
        total
    };

    sessionStorage.setItem('greendash-checkout', JSON.stringify(checkoutData));
    const extension = window.location.pathname.endsWith('.html') ? '.html' : '.php';
    window.location.href = serverStallData
        ? new URL('controller-user/checkout.php', stallApplicationUrl).href
        : `checkout${extension}`;
}

document.querySelectorAll('.add-item-button').forEach((button) => {
    button.addEventListener('click', () => openItemModal({
        productId: Number(button.dataset.productId) || null,
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
    const milk = document.querySelector('input[name="milk"]:checked').value;
    cart.push({
        ...selectedItem,
        size,
        milk,
        quantity: selectedQuantity,
        total: Math.round(getCustomizationPrice() * 100) * selectedQuantity / 100
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

document.querySelector('#checkoutButton').addEventListener('click', openCheckout);
document.querySelector('#closeEmptyCartMessage').addEventListener('click', () => {
    document.querySelector('#emptyCartMessage').hidden = true;
});
document.querySelector('#emptyCartMessage').addEventListener('click', (event) => {
    if (event.target === document.querySelector('#emptyCartMessage')) {
        document.querySelector('#emptyCartMessage').hidden = true;
    }
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
    document.querySelector('#menuSearch').value = search;
    applyMenuFilters();
});

document.querySelector('#priceRange').addEventListener('input', (event) => {
    document.querySelector('#priceValue').textContent = event.target.value;
});

function applyMenuFilters() {
    const search = document.querySelector('#menuSearch').value.trim().toLowerCase();
    const maximumPrice = Number(document.querySelector('#priceRange').value);
    const menuItemElements = [...document.querySelectorAll('.menu-column')];

    menuItemElements.forEach((item) => {
        item.hidden = !belongsToCategory(item, activeCategory)
            || !item.dataset.name.includes(search)
            || Number(item.dataset.price) > maximumPrice;
    });

    const hasVisibleItems = menuItemElements.some((item) => !item.hidden);
    document.querySelector('#noMenuItemsMessage').hidden = hasVisibleItems;
}

function sortMenuItems() {
    const sortOrder = document.querySelector('#priceSort').value;
    const menuItemElements = [...document.querySelectorAll('.menu-column')];

    if (sortOrder === 'lowest' || sortOrder === 'highest') {
        menuItemElements.sort((firstItem, secondItem) => {
            const priceDifference = Number(firstItem.dataset.price) - Number(secondItem.dataset.price);

            if (sortOrder === 'lowest') {
                return priceDifference;
            }

            return -priceDifference;
        });
    }

    menuItemElements.forEach((item) => menuItems.appendChild(item));
}

document.querySelector('#applyFilters').addEventListener('click', () => {
    sortMenuItems();
    applyMenuFilters();
    document.querySelector('#filterPanel').style.display = 'none';
});

document.querySelector('#resetFilters').addEventListener('click', () => {
    document.querySelector('#priceRange').value = 1000;
    document.querySelector('#priceValue').textContent = '1000';
    document.querySelector('#priceSort').value = 'default';
    document.querySelector('#menuSearch').value = '';
    sortMenuItems();
    applyMenuFilters();
});
