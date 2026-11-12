// The controller embeds only this signed-in user's database orders and review CSRF token.
const ordersData = JSON.parse(document.querySelector('#ordersData').textContent);
const orders = ordersData.orders;
const orderById = new Map(orders.map(order => [order.id, order]));
const orderApplicationUrl = new URL('../', document.currentScript.src);
const currency = new Intl.NumberFormat('en-PH', {style: 'currency', currency: 'PHP'});
const ongoingSection = document.querySelector('#ongoing-heading').parentElement;
const previousSection = document.querySelector('#previous-heading').parentElement;
const orderSearchInput = document.querySelector('#orderSearchInput');
const noOrdersFound = document.querySelector('#noOrdersFound');
const orderInfoModal = document.querySelector('#orderInfoModal');
const orderReviewModal = document.querySelector('#orderReviewModal');
const cancelOrderModal = document.querySelector('#cancelUserOrderModal');
const ratingControl = document.querySelector('#orderRatingControl');
const reviewButton = document.querySelector('#reviewOrderButton');
const deleteReviewButton = document.querySelector('#deleteOrderReviewButton');
const cancelOrderButton = document.querySelector('#cancelUserOrderButton');
const reviewForm = document.querySelector('#orderReviewForm');
const reviewComment = document.querySelector('#orderReviewComment');
const reviewRating = document.querySelector('#orderReviewRating');
const actionFeedback = document.querySelector('#orderActionFeedback');
const reviewFeedback = document.querySelector('#orderReviewFeedback');
let selectedOrderId = null;
let reviewBusy = false;
let returnToDetails = false;

function orderElement(tag, className, text) {
    const element = document.createElement(tag);
    if (className) element.className = className;
    if (text !== undefined) element.textContent = text;
    return element;
}

function orderLogoUrl(path) {
    if (!path) return new URL('assets/main/defaultAvatar.png', orderApplicationUrl).href;
    const marker = path.indexOf('assets/');
    return new URL(marker === -1 ? path : path.slice(marker), orderApplicationUrl).href;
}

function orderDate(order) {
    return new Date(order.date.replace(' ', 'T')).toLocaleDateString('en-PH', {
        year: 'numeric', month: 'long', day: 'numeric',
    });
}

function createOrderCard(order) {
    const card = orderElement('article', 'order-card');
    const logo = orderElement('img', 'order-stall-logo');
    logo.src = orderLogoUrl(order.logo);
    logo.alt = `${order.name} logo`;
    const details = orderElement('div', 'order-details');
    const top = orderElement('div', 'order-topline');
    const title = orderElement('div', 'order-title-line');
    title.append(orderElement('h3', '', order.name));
    if (order.review) {
        const stars = orderElement('span', 'order-card-rating', '★'.repeat(order.review.rating) + '☆'.repeat(5 - order.review.rating));
        stars.setAttribute('aria-label', `Your rating: ${order.review.rating} out of 5 stars`);
        title.append(stars);
    }
    title.append(orderElement('span', 'order-id', `ORDER ID: ${order.id}`),
        orderElement('span', `order-status ${order.statusClass}`, order.status));
    top.append(title);
    const bottom = orderElement('div', 'order-bottomline');
    bottom.append(orderElement('p', 'order-payment', `${order.paymentMethod}: ${currency.format(order.total)}`));
    details.append(top, orderElement('p', 'order-location', `Location: ${order.location}`),
        orderElement('p', 'order-date', orderDate(order)),
        orderElement('p', 'order-items', order.items.map(item => `${item.name} (x${item.quantity})`).join(', ')), bottom);
    const action = orderElement('div', 'order-action');
    const view = orderElement('button', 'view-stall-button', 'View order');
    view.type = 'button';
    view.dataset.orderId = order.id;
    view.dataset.bsToggle = 'modal';
    view.dataset.bsTarget = '#orderInfoModal';
    action.append(view);
    details.append(action);
    card.append(logo, details);
    return card;
}

function renderOrderList() {
    // Rebuild the two sections so the current search applies consistently to both.
    document.querySelectorAll('.order-card').forEach(card => card.remove());
    const search = orderSearchInput.value.trim().toLowerCase();
    let ongoingCount = 0;
    let previousCount = 0;
    orders.forEach(order => {
        const card = createOrderCard(order);
        if (!card.textContent.toLowerCase().includes(search)) return;
        if (order.isOngoing) {
            ongoingSection.append(card);
            ongoingCount++;
        } else {
            previousSection.append(card);
            previousCount++;
        }
    });
    ongoingSection.hidden = ongoingCount === 0;
    previousSection.hidden = previousCount === 0;
    noOrdersFound.hidden = ongoingCount + previousCount > 0;
    noOrdersFound.textContent = orders.length ? 'No orders match your search.' : "You haven't placed any orders yet.";
}

function renderRating(rating, preview = rating) {
    ratingControl.querySelectorAll('.order-rating-star').forEach(star => {
        const value = Number(star.dataset.rating);
        star.textContent = value <= preview ? '★' : '☆';
        star.classList.toggle('is-active', value <= preview);
        star.setAttribute('aria-pressed', String(value === rating));
    });
}

function renderOrderDetails() {
    // Populate the modal from the selected record and enable actions allowed by its status.
    const order = orderById.get(selectedOrderId);
    if (!order) return;
    const logo = document.querySelector('#orderStoreLogo');
    logo.src = orderLogoUrl(order.logo);
    logo.alt = `${order.name} logo`;
    for (const [selector, value] of [
        ['#orderStoreName', order.name], ['#orderStoreLocation', order.location],
        ['#orderModalId', `ORDER ID: ${order.id}`], ['#orderModalDate', orderDate(order)],
        ['#orderSubtotal', currency.format(order.total)], ['#orderTotal', currency.format(order.total)],
        ['#orderPaymentMethod', order.paymentMethod], ['#orderPaymentStatus', order.paymentStatus],
    ]) document.querySelector(selector).textContent = value;
    const status = document.querySelector('#orderModalStatus');
    status.textContent = order.status;
    status.className = `order-status ${order.statusClass}`;
    const canReview = order.statusClass === 'completed';
    ratingControl.closest('.order-modal-rating').hidden = !canReview;
    ratingControl.querySelectorAll('.order-rating-star').forEach(star => {star.disabled = !canReview || reviewBusy;});
    renderRating(order.review?.rating || 0);
    reviewButton.hidden = !canReview;
    reviewButton.textContent = order.review ? 'Edit review' : 'Leave a review';
    deleteReviewButton.hidden = !canReview || !order.review;
    reviewButton.disabled = deleteReviewButton.disabled = reviewBusy;
    cancelOrderButton.hidden = order.statusClass !== 'pending';
    const items = document.querySelector('#orderModalItems');
    items.replaceChildren();
    order.items.forEach(item => {
        const row = orderElement('div', 'order-modal-item');
        const description = orderElement('div', 'order-modal-item-description');
        description.append(orderElement('strong', '', item.name), orderElement('span', '', item.detail),
            orderElement('span', 'order-modal-item-quantity', `Quantity: ${item.quantity}`));
        row.append(description, orderElement('strong', 'order-modal-item-price', currency.format(item.total)));
        items.append(row);
    });
}

function reviewError(element, error) {
    element.textContent = error.message || 'Your review could not be saved. Please try again.';
    element.hidden = false;
}

function switchOrderModal(from, to) {
    from.addEventListener('hidden.bs.modal', () => bootstrap.Modal.getOrCreateInstance(to).show(), {once: true});
    bootstrap.Modal.getOrCreateInstance(from).hide();
}

async function saveOrderReview(order, rating, comment) {
    // Disable repeat actions while the server saves the review.
    reviewBusy = true;
    actionFeedback.hidden = reviewFeedback.hidden = true;
    renderOrderDetails();
    reviewForm.querySelector('button[type="submit"]').disabled = true;
    try {
        // Use the server's saved review as the new source of truth for the current page.
        const result = await window.GreenDashReviews.save(order.orderId, rating, comment, ordersData.csrfToken);
        order.review = result.review;
        renderOrderList();
    } finally {
        reviewBusy = false;
        reviewForm.querySelector('button[type="submit"]').disabled = false;
        renderOrderDetails();
    }
}

orderSearchInput.addEventListener('input', renderOrderList);
orderInfoModal.addEventListener('show.bs.modal', event => {
    // Resolve the clicked card to its matching order record before filling the details modal.
    const orderId = event.relatedTarget?.dataset.orderId || selectedOrderId;
    if (!orderById.has(orderId)) {event.preventDefault(); return;}
    selectedOrderId = orderId;
    actionFeedback.hidden = true;
    renderOrderDetails();
});

ratingControl.addEventListener('pointerover', event => {
    const star = event.target.closest('.order-rating-star');
    const order = orderById.get(selectedOrderId);
    if (star && order?.statusClass === 'completed' && !reviewBusy) renderRating(order.review?.rating || 0, Number(star.dataset.rating));
});
ratingControl.addEventListener('pointerleave', () => renderRating(orderById.get(selectedOrderId)?.review?.rating || 0));
ratingControl.addEventListener('click', async event => {
    const star = event.target.closest('.order-rating-star');
    const order = orderById.get(selectedOrderId);
    if (!star || !order || order.statusClass !== 'completed' || reviewBusy) return;
    try {
        await saveOrderReview(order, Number(star.dataset.rating), order.review?.text || '');
    } catch (error) { reviewError(actionFeedback, error); }
});

reviewButton.addEventListener('click', () => {
    const order = orderById.get(selectedOrderId);
    if (!order || order.statusClass !== 'completed' || reviewBusy) return;
    document.querySelector('#orderReviewContext').textContent = `${order.name} — Order ${order.id}`;
    reviewRating.value = order.review?.rating || '';
    reviewComment.value = order.review?.text || '';
    reviewFeedback.hidden = true;
    document.querySelector('#orderReviewCount').textContent = `${reviewComment.value.length}/500`;
    returnToDetails = true;
    switchOrderModal(orderInfoModal, orderReviewModal);
});
reviewComment.addEventListener('input', () => {
    reviewFeedback.hidden = true;
    document.querySelector('#orderReviewCount').textContent = `${reviewComment.value.length}/500`;
});
reviewForm.addEventListener('submit', async event => {
    event.preventDefault();
    const order = orderById.get(selectedOrderId);
    if (!order || reviewBusy || !reviewForm.reportValidity()) return;
    try {
        await saveOrderReview(order, Number(reviewRating.value), reviewComment.value.trim());
        bootstrap.Modal.getOrCreateInstance(orderReviewModal).hide();
    } catch (error) { reviewError(reviewFeedback, error); }
});
orderReviewModal.addEventListener('hidden.bs.modal', () => {
    if (returnToDetails) {
        returnToDetails = false;
        bootstrap.Modal.getOrCreateInstance(orderInfoModal).show();
    }
});

deleteReviewButton.addEventListener('click', async () => {
    const order = orderById.get(selectedOrderId);
    if (!order?.review || reviewBusy || !window.confirm('Delete your review for this order?')) return;
    reviewBusy = true;
    actionFeedback.hidden = true;
    renderOrderDetails();
    try {
        await window.GreenDashReviews.remove(order.orderId, ordersData.csrfToken);
        order.review = null;
        renderOrderList();
    } catch (error) { reviewError(actionFeedback, error); }
    finally { reviewBusy = false; renderOrderDetails(); }
});

cancelOrderButton.addEventListener('click', () => {
    const order = orderById.get(selectedOrderId);
    if (!order || order.statusClass !== 'pending') return;
    document.querySelector('#cancelUserOrderMessage').textContent = `Cancel order ${order.id} from ${order.name}?`;
    returnToDetails = true;
    switchOrderModal(orderInfoModal, cancelOrderModal);
});
document.querySelector('#confirmCancelUserOrderButton').addEventListener('click', async event => {
    const order = orderById.get(selectedOrderId);
    if (!order || order.statusClass !== 'pending' || event.currentTarget.disabled) return;
    const button = event.currentTarget;
    button.disabled = true;
    try {
        // Submit the order ID with the page's CSRF token, then reload its server-updated status.
        const response = await fetch(new URL('controller-user/cancel-order.php', orderApplicationUrl), {
            method: 'POST', body: new URLSearchParams({orderId: order.orderId, csrfToken: ordersData.csrfToken}),
        });
        const result = await response.json();
        if (!response.ok || result.success !== true) throw new Error(result.message || 'Unable to cancel this order.');
        window.location.reload();
    } catch (error) { document.querySelector('#cancelUserOrderMessage').textContent = error.message; }
    finally { button.disabled = false; }
});
cancelOrderModal.addEventListener('hidden.bs.modal', () => {
    if (returnToDetails) {
        returnToDetails = false;
        bootstrap.Modal.getOrCreateInstance(orderInfoModal).show();
    }
});

renderOrderList();
