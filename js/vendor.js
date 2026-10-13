const orderGrid = document.querySelector('#vendorOrderGrid');
const sortControl = document.querySelector('#vendorOrderSort');
const statusFilter = document.querySelector('#vendorOrderStatusFilter');
const orderModal = document.querySelector('#vendorOrderModal');
const statusModal = document.querySelector('#vendorStatusModal');
const statusOptions = document.querySelector('.vendor-status-options');

const statusOrder = ['pending', 'preparing', 'ready', 'completed', 'cancelled'];
const editableStatuses = statusOrder.slice(0, 4);
const statusLabels = {
    pending: 'Pending',
    preparing: 'Preparing',
    ready: 'Ready',
    completed: 'Completed',
    cancelled: 'Cancelled'
};

function sortOrders() {
    if (!orderGrid || !sortControl) {
        return;
    }

    const cards = Array.from(orderGrid.querySelectorAll('.vendor-order-card'));
    cards.sort((first, second) => {
        const firstPickup = getPickupTimeValue(first.dataset.pickupTime);
        const secondPickup = getPickupTimeValue(second.dataset.pickupTime);
        if (sortControl.value === 'status') {
            const statusDifference = statusOrder.indexOf(first.dataset.status)
                - statusOrder.indexOf(second.dataset.status);
            if (statusDifference !== 0) {
                return statusDifference;
            }
        }

        return firstPickup - secondPickup;
    });

    cards.forEach(card => orderGrid.append(card));
    filterOrders();
}

function filterOrders() {
    if (!orderGrid || !statusFilter) {
        return;
    }

    orderGrid.querySelectorAll('.vendor-order-card').forEach(card => {
        card.hidden = statusFilter.value !== 'all' && card.dataset.status !== statusFilter.value;
    });
}

function getPickupTimeValue(pickupTime) {
    if (pickupTime === 'now') {
        return -1;
    }

    const [hours, minutes] = pickupTime.split(':').map(Number);
    return hours * 60 + minutes;
}

function formatPickupTime(pickupTime) {
    if (pickupTime === 'now') {
        return 'Now';
    }

    const [hours, minutes] = pickupTime.split(':').map(Number);
    return new Date(2000, 0, 1, hours, minutes)
        .toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
}

function setOrderDetails(card) {
    if (!orderModal) {
        return;
    }

    const status = card.dataset.status;
    const pickupTime = card.dataset.pickupTime;
    const formattedPickup = formatPickupTime(pickupTime);

    orderModal.querySelector('#selectedOrderHeading').textContent = `${card.dataset.customer}’s order`;
    orderModal.querySelector('#vendorModalItemImage').src =
        `../../assets/starbucksMenu/${card.dataset.image}`;
    orderModal.querySelector('#vendorModalItemImage').alt = card.dataset.item;
    orderModal.querySelector('#vendorModalItemName').textContent = card.dataset.item;
    orderModal.querySelector('#vendorModalOptions').textContent = card.dataset.options;
    orderModal.querySelector('#vendorModalQuantity').textContent = `Quantity: ${card.dataset.quantity}`;
    orderModal.querySelector('#vendorModalTotal').textContent = `₱${card.dataset.total}`;
    orderModal.querySelector('#vendorModalOrderId').textContent = `ORDER ID: ${card.dataset.orderId}`;
    orderModal.querySelector('#vendorModalSubtotal').textContent = `₱${card.dataset.total}`;
    orderModal.querySelector('#vendorModalPayment').textContent = card.dataset.payment;
    orderModal.querySelector('#vendorModalPickup').textContent = formattedPickup;

    const statusElement = orderModal.querySelector('#vendorModalStatus');
    statusElement.textContent = statusLabels[status];
    statusElement.className = `vendor-payment-status ${status}`;
}

if (sortControl) {
    sortControl.addEventListener('change', sortOrders);
}

if (statusFilter) {
    statusFilter.addEventListener('change', filterOrders);
}

if (orderGrid && orderModal) {
    orderGrid.addEventListener('click', event => {
        const button = event.target.closest('.vendor-open-order');
        const card = button?.closest('.vendor-order-card');
        if (card) {
            setOrderDetails(card);
        }
    });
}

if (statusModal && statusOptions && orderGrid) {
    let selectedCard;

    statusModal.addEventListener('show.bs.modal', event => {
        const trigger = event.relatedTarget;
        selectedCard = trigger?.closest('.vendor-order-card');
        if (selectedCard) {
            statusModal.querySelector('#vendorStatusModalHeading').textContent =
                `Change status for order #${selectedCard.dataset.orderId}`;
        }
    });

    statusOptions.addEventListener('click', event => {
        const button = event.target.closest('.vendor-status-option');
        const status = button?.dataset.status;
        if (!selectedCard || !editableStatuses.includes(status)) {
            return;
        }

        selectedCard.dataset.status = status;
        const badge = selectedCard.querySelector('.vendor-order-status');
        badge.textContent = statusLabels[status];
        badge.className = `vendor-order-status ${status}`;

        if (orderModal.classList.contains('show')
            && orderModal.querySelector('#vendorModalOrderId').textContent
                === `ORDER ID: ${selectedCard.dataset.orderId}`) {
            setOrderDetails(selectedCard);
        }

        sortOrders();
        bootstrap.Modal.getInstance(statusModal).hide();
    });
}

sortOrders();