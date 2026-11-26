const stallDataElement = document.querySelector('#stallData');
const serverStallData = stallDataElement ? JSON.parse(stallDataElement.textContent) : null;
if (!serverStallData?.stall) {
    const page = document.querySelector('.stall-info-page');
    const message = document.createElement('p');
    message.textContent = 'Open a stall from the live stalls directory to load its menu.';
    page?.replaceChildren(message);
} else {
// The controller embeds the selected stall, its available menu, and customization groups here.
// Everything below runs only for a valid stall payload from the PHP controller.
const stall = serverStallData.stall;
const stallId = String(stall.id);
const stallApplicationUrl = new URL('../', document.currentScript.src);
const cartUrl = new URL('controller-user/cart.php', stallApplicationUrl);
cartUrl.searchParams.set('stallId', stallId);

function stallImageUrl(path) {
    if (!path) return '';
    const marker = path.indexOf('assets/');
    if (marker !== -1) return new URL(path.slice(marker), stallApplicationUrl).href;
    if (/^(https?:|data:|\/)/i.test(path)) return new URL(path, stallApplicationUrl).href;
    return new URL(`assets/starbucksMenu/${path}`, stallApplicationUrl).href;
}

// Escape database text before inserting it into the menu's generated markup.
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
    // Prefer today's valid slots from the controller; derive them from display hours only for prototype pages.
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

// Mirror the selected pickup slot between the desktop and mobile controls.
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
    // Use textContent for review text so user-written comments are displayed safely.
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
    // Apply the selected review filters before rebuilding the list.
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
    // Load public reviews and eligible orders together, then synchronize the modal and form.
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
const orderItems = document.querySelector('#orderItems');
const checkoutButton = document.querySelector('#checkoutButton');
const cartFeedback = document.querySelector('#cartFeedback');
const cartItemFeedback = document.querySelector('#cartItemFeedback');
const retryCartButton = document.querySelector('#retryCartButton');
let cart = [];
let cartVersion = null;
let cartBusy = false;
let editingCartIndex = null;
let selectedItem = null;
let selectedQuantity = 1;
let activeCategory = 'all';
let menuAnimationDirection = 'next';
const menuCategories = [...document.querySelectorAll('.menu-categories button')]
    .map(button => button.dataset.category);
const DEFAULT_PRODUCT_IMAGE = 'assets/main/no-image-set.png';       // Default image for products without an image set

const modal = document.querySelector('#itemModal');
const modalPrice = document.querySelector('#modalBasePrice');
const addToCartButton = document.querySelector('#addToCartButton');

const menuProducts = serverStallData.products.map(product => ({
    productId: Number(product.id),
    name: product.name,
    price: Number(product.price),
    image: product.image_path || DEFAULT_PRODUCT_IMAGE,     // Use default image if none is set for the product
    categories: Array.isArray(product.categories) && product.categories.length      // Use product categories if available, otherwise fallback to single category or 'all'
        ? product.categories.map(category => category.name.toLowerCase())           // Map category names to lowercase for consistency
        : [product.category ? product.category.toLowerCase() : 'all'],
    optionGroups: Array.isArray(product.optionGroups) ? product.optionGroups : []
}));
const menuProductsById = new Map(menuProducts.map(product => [product.productId, product]));

// Turn controller-provided product records into cards that can be filtered and opened.
menuProducts.forEach(({productId, name, price, image, categories}, index) => {
    const item = document.createElement('div');
    item.className = 'col-12 col-sm-6 col-md-4 col-lg-3 menu-column';
    item.dataset.categories = JSON.stringify(categories);       // stringify: store categories as a JSON string for later parsing
    item.dataset.name = name.toLowerCase();
    item.dataset.price = String(price);
    item.innerHTML = `
        <div class="menu-item">
            <div class="menu-image-container">
                <img src="${escapeStallHtml(stallImageUrl(image))}" alt="${escapeStallHtml(name)}">
                <button type="button" class="add-item-button" aria-label="Add ${escapeStallHtml(name)}"
                    data-product-index="${index}"
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
    try {
        return JSON.parse(item.dataset.categories).includes(category);  // Check if the item belongs to the given category
    } catch {
        return false;
    }
}

function showMenuCategory(category, direction = 'next') {
    // Keep active category, button styling, animation, and current filters in sync.
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
    const optionCents = [...document.querySelectorAll('#customizationGroups input[type="radio"]:checked')]
        .reduce((sum, input) => sum + Math.round(Number(input.dataset.price || 0) * 100), 0);
    const cents = Math.round(selectedItem.price * 100) + optionCents;
    return cents / 100;
}

function selectedCustomizations() {
    return [...document.querySelectorAll('#customizationGroups input[type="radio"]:checked')]
        .filter(input => input.value)
        .map(input => ({groupId: Number(input.dataset.groupId), optionId: Number(input.value)}));
}

function renderCustomizationGroups(item, savedOptions) {
    const container = document.querySelector('#customizationGroups');
    container.replaceChildren();
    for (const group of item.optionGroups || []) {
        const fieldset = document.createElement('fieldset');
        fieldset.className = 'customization-group';
        const legend = document.createElement('legend');
        legend.textContent = group.name + (group.required ? ' (required)' : '');
        fieldset.appendChild(legend);

        if (!group.choices.length) {
            const emptyMessage = document.createElement('p');
            emptyMessage.textContent = 'No options are currently available.';
            fieldset.appendChild(emptyMessage);
        }

        const choices = group.required
            ? group.choices
            : [{optionId: '', name: 'No selection', extraPrice: '0.00'}, ...group.choices];
        const selectedChoice = savedOptions
            ? choices.find(choice => choice.optionId === savedOptions.get(group.groupId))
                ?? (group.required ? undefined : choices[0])
            : choices[0];
        for (const choice of choices) {
            const label = document.createElement('label');
            const input = document.createElement('input');
            input.type = 'radio';
            input.name = `customization-${group.groupId}`;
            input.value = choice.optionId;
            input.dataset.groupId = group.groupId;
            input.dataset.price = choice.extraPrice;
            input.checked = choice === selectedChoice;
            label.append(input, document.createTextNode(` ${choice.name} `));

            const price = document.createElement('span');
            const extraPrice = Number(choice.extraPrice);
            price.textContent = extraPrice > 0 ? `+₱ ${extraPrice.toFixed(2)}` : 'Free';
            label.appendChild(price);
            fieldset.appendChild(label);
        }
        container.appendChild(fieldset);
    }
}

function updateModalPrice() {
    const total = Math.round(getCustomizationPrice() * 100) * selectedQuantity / 100;
    const action = editingCartIndex === null ? 'Add to cart' : 'Update cart';
    const locked = cartBusy || cartVersion === null;
    modalPrice.textContent = selectedItem.price;
    addToCartButton.textContent = `${action} - ₱ ${total.toFixed(2)}`;
    document.querySelector('#itemQuantity').textContent = selectedQuantity;
    document.querySelector('#decreaseQuantity').disabled = locked || selectedQuantity <= 1;
    document.querySelector('#increaseQuantity').disabled = locked || selectedQuantity >= 999;
    document.querySelectorAll('#customizationGroups fieldset').forEach(group => { group.disabled = locked; });
    addToCartButton.disabled = locked || (selectedItem.optionGroups || [])
        .some(group => group.required && (!group.choices.length
            || !document.querySelector(`input[name="customization-${group.groupId}"]:checked`)));
}

function openItemModal(item, cartIndex = null) {
    if (cartBusy || cartVersion === null) {
        document.body.classList.add('cart-open');
        return;
    }
    const savedItem = cart[cartIndex];
    selectedItem = item;
    editingCartIndex = cartIndex;
    selectedQuantity = savedItem?.quantity ?? 1;
    document.querySelector('#modalItemName').textContent = item.name;
    document.querySelector('#modalItemDescription').textContent = `${item.name} prepared fresh and customized to your preference.`;
    const image = document.querySelector('#modalItemImage');
    image.src = stallImageUrl(item.image);
    image.hidden = !item.image;
    image.alt = item.name;
    const savedOptions = savedItem && new Map(savedItem.options.map(({groupId, optionId}) => [groupId, optionId]));
    renderCustomizationGroups(item, savedOptions);
    cartItemFeedback.hidden = true;
    modal.hidden = false;
    updateModalPrice();
}

function cartItemDetails(line, product) {
    if (!product) return {selections: [], issue: 'This product is no longer available.', totalCents: 0};

    const options = new Map(line.options.map(({groupId, optionId}) => [groupId, optionId]));
    const selections = [];
    let unitCents = Math.round(product.price * 100);
    let missingRequiredOption = false;

    for (const group of product.optionGroups) {
        const choice = group.choices.find(choice => choice.optionId === options.get(group.groupId));
        if (choice) {
            selections.push(`${group.name}: ${choice.name}`);
            unitCents += Math.round(Number(choice.extraPrice) * 100);
        } else if (group.required) {
            missingRequiredOption = true;
        }
    }

    // Flag changed options while keeping the saved line available for editing.
    if (missingRequiredOption || selections.length !== options.size) {
        return {selections, issue: 'Update this item’s options or remove it.', totalCents: 0};
    }
    return {selections, issue: '', totalCents: unitCents * line.quantity};
}

function cartLineButton(label, productName, onClick) {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'btn btn-sm btn-outline-secondary py-0';
    button.textContent = label;
    button.setAttribute('aria-label', `${label} ${productName}`);
    button.addEventListener('click', onClick);
    return button;
}

function renderOrder() {
    orderItems.replaceChildren();
    if (!cart.length && cartVersion !== null) {
        const message = document.createElement('p');
        message.className = 'empty-order-message';
        message.textContent = 'Your cart is empty!';
        orderItems.appendChild(message);
    }

    let totalCents = 0;
    cart.forEach((line, index) => {
        const product = menuProductsById.get(line.productId);
        const {selections, issue, totalCents: itemTotal} = cartItemDetails(line, product);
        totalCents += itemTotal;
        const name = product?.name || 'Unavailable item';
        const row = document.createElement('div');
        row.className = 'order-item';
        row.dataset.unavailable = String(Boolean(issue));
        const image = document.createElement(product?.image ? 'img' : 'span');
        if (product?.image) {
            image.src = stallImageUrl(product.image);
            image.alt = '';
        }

        const details = document.createElement('div');
        const title = document.createElement('h3');
        title.textContent = name;
        const summary = document.createElement('p');
        summary.textContent = [...selections, `Quantity: ${line.quantity}`].join(' · ');
        details.append(title, summary);
        if (issue) {
            const message = document.createElement('p');
            message.textContent = issue;
            details.appendChild(message);
        }

        const actions = document.createElement('div');
        actions.className = 'd-flex flex-wrap gap-1 mt-1';
        if (product) {
            actions.appendChild(cartLineButton('Edit', name, () => openItemModal(product, index)));
        }
        actions.appendChild(cartLineButton('Remove', name, () => saveCart(cart.filter((_, i) => i !== index))));
        details.appendChild(actions);

        const price = document.createElement('strong');
        price.textContent = issue ? '' : `₱ ${(itemTotal / 100).toFixed(2)}`;
        row.append(image, details, price);
        orderItems.appendChild(row);
    });
    checkoutButton.textContent = `Checkout - ₱ ${(totalCents / 100).toFixed(2)}`;
}

function updateCartControls() {
    const locked = cartBusy || cartVersion === null;
    checkoutButton.disabled = locked || !cart.length || Boolean(orderItems.querySelector('[data-unavailable="true"]'));
    orderItems.querySelectorAll('button').forEach(button => { button.disabled = locked; });
    retryCartButton.disabled = cartBusy;
    if (!modal.hidden) updateModalPrice();
}

function setCartFeedback(message, canRetry = false) {
    cartFeedback.textContent = message;
    cartFeedback.hidden = !message;
    retryCartButton.hidden = !canRetry;
}

async function requestCart(body) {
    const response = await fetch(cartUrl, {
        method: body ? 'POST' : 'GET', body, headers: {Accept: 'application/json'}
    }).catch(() => null);
    const result = await response?.json().catch(() => null);

    if (!response?.ok || !result?.success) {
        const fallback = body ? 'Unable to save your cart. Please try again.' : 'Unable to load your cart.';
        throw Object.assign(new Error(result?.message || fallback), {status: response?.status});
    }
    cart = result.items;
    cartVersion = result.version;
    renderOrder();
}

async function loadCart() {
    if (cartBusy) return;
    cartBusy = true;
    setCartFeedback('Loading your cart…');
    updateCartControls();
    try {
        await requestCart();
        setCartFeedback('');
    } catch (error) {
        cartVersion = null;
        renderOrder();
        setCartFeedback(error.message, true);
    } finally {
        cartBusy = false;
        updateCartControls();
    }
}

async function saveCart(items) {
    if (cartBusy || cartVersion === null) return false;
    cartBusy = true;
    cartItemFeedback.hidden = true;
    setCartFeedback('Saving your cart…');
    updateCartControls();

    const body = new FormData();
    body.set('csrfToken', serverStallData.cartCsrfToken);
    body.set('version', String(cartVersion));
    body.set('items', JSON.stringify(items));
    try {
        await requestCart(body);
        setCartFeedback('');
        return true;
    } catch (error) {
        if (error.status === 409) {
            // Reload before another edit so a stale tab cannot overwrite a newer cart.
            cartVersion = null;
            await requestCart().catch(() => {});
            modal.hidden = true;
            document.body.classList.add('cart-open');
            setCartFeedback('Your cart changed elsewhere. Review the latest cart and try again.', cartVersion === null);
        } else if (!modal.hidden) {
            setCartFeedback('');
            cartItemFeedback.textContent = error.message;
            cartItemFeedback.hidden = false;
        } else {
            setCartFeedback(error.message);
        }
        return false;
    } finally {
        cartBusy = false;
        updateCartControls();
    }
}

function openCheckout() {
    if (checkoutButton.disabled) return;
    const pickupTime = document.querySelector('#pickupTime').value || document.querySelector('#pickupTimeMobile').value;
    const checkoutUrl = new URL('controller-user/checkout.php', stallApplicationUrl);
    checkoutUrl.searchParams.set('stallId', stallId);
    if (pickupTime) checkoutUrl.searchParams.set('pickupTime', pickupTime);
    window.location.href = checkoutUrl.href;
}

document.querySelectorAll('.add-item-button').forEach((button) => {
    button.addEventListener('click', () => openItemModal({
        ...menuProducts[Number(button.dataset.productIndex)],
    }));
});

document.querySelector('#customizationGroups').addEventListener('change', updateModalPrice);

document.querySelector('#increaseQuantity').addEventListener('click', () => {
    selectedQuantity += 1;
    updateModalPrice();
});

document.querySelector('#decreaseQuantity').addEventListener('click', () => {
    selectedQuantity = Math.max(1, selectedQuantity - 1);
    updateModalPrice();
});

addToCartButton.addEventListener('click', async () => {
    const line = {
        productId: selectedItem.productId,
        options: selectedCustomizations(),
        quantity: selectedQuantity,
        specialInstructions: cart[editingCartIndex]?.specialInstructions ?? ''
    };
    const items = [...cart];
    if (editingCartIndex === null) {
        items.push(line);
    } else {
        items[editingCartIndex] = line;
    }
    if (await saveCart(items)) {
        modal.hidden = true;
        document.body.classList.add('cart-open');
    }
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

checkoutButton.addEventListener('click', openCheckout);
retryCartButton.addEventListener('click', loadCart);
window.addEventListener('pageshow', event => {
    if (event.persisted) {
        modal.hidden = true;
        cart = [];
        cartVersion = null;
        renderOrder();
        loadCart();
    }
});
if (new URLSearchParams(window.location.search).get('cart') === '1') {
    document.body.classList.add('cart-open');
}
loadCart();

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
}
