const orderGrid = document.querySelector('#vendorOrderGrid');
const sortControl = document.querySelector('#vendorOrderSort');
const statusFilter = document.querySelector('#vendorOrderStatusFilter');
const orderSearch = document.querySelector('#vendorOrderSearchInput');
const emptyMessage = document.querySelector('#vendorOrdersEmpty');
const orderModal = document.querySelector('#vendorOrderModal');
const cancelModal = document.querySelector('#vendorCancelModal');
const cancelForm = document.querySelector('#vendorCancelForm');
const cancelReasonInput = document.querySelector('#vendorCancelReason');
const historyBody = document.querySelector('#vendorHistoryBody');
const historyEmpty = document.querySelector('#vendorHistoryEmpty');
const historyFilter = document.querySelector('#vendorHistoryFilter');
const historySearch = document.querySelector('#vendorHistorySearch');
const historyModal = document.querySelector('#vendorHistoryModal');

const statusOrder = ['cancelled', 'pending', 'preparing', 'ready', 'completed'];
const editableStatuses = ['pending', 'preparing', 'ready', 'completed'];
const statusLabels = {
    pending: 'Pending',
    preparing: 'Preparing',
    ready: 'Ready',
    completed: 'Completed',
    cancelled: 'Cancelled'
};
const orderStateKey = 'greendash.vendor.order-state.v1';
const archivedOrdersKey = 'greendash.vendor.archived-orders.v1';

function daysAgo(days) {
    const date = new Date();
    date.setDate(date.getDate() - days);
    return date.toISOString();
}

function daysAgoAt(days, hours, minutes) {
    const date = new Date();
    date.setDate(date.getDate() - days);
    date.setHours(hours, minutes, 0, 0);
    return date.toISOString();
}

function generateReferenceNumber() {
    return String(Math.floor(1_000_000_000_000 + Math.random() * 9_000_000_000_000));
}

// Seed realistic archive examples so the history view demonstrates dates, mixed items, and ratings.
const sampleArchivedOrders = [
    {
        orderId: '6758UV',
        customer: 'Lara',
        status: 'completed',
        items: [{ name: 'Caffè Americano', image: 'americano.png', options: 'Iced, Grande', quantity: 1, price: 180 }],
        payment: 'Online payment',
        paymentStatus: 'Paid',
        referenceNumber: '0928475163021',
        total: 180,
        rating: 5,
        completedAt: daysAgoAt(1, 12, 34),
        cancellationReason: '',
        archivedAt: daysAgo(1)
    },
    {
        orderId: '6757TU',
        customer: 'Miguel',
        status: 'completed',
        items: [
            { name: 'Caramel Macchiato', image: 'caramel.png', options: 'Oat milk, Grande', quantity: 1, price: 230 },
            { name: 'Java Chip Frappuccino', image: 'javachip.png', options: 'Tall', quantity: 1, price: 190 }
        ],
        payment: 'Online payment',
        paymentStatus: 'Paid',
        referenceNumber: '4710392851642',
        total: 420,
        rating: 4,
        completedAt: daysAgoAt(2, 13, 18),
        cancellationReason: '',
        archivedAt: daysAgo(2)
    },
    {
        orderId: '6756ST',
        customer: 'Andrea',
        status: 'cancelled',
        items: [{ name: 'Matcha Latte', image: 'matchalatte.png', options: 'Iced, Tall', quantity: 1, price: 165 }],
        payment: 'Online payment',
        paymentStatus: 'Not charged',
        total: 165,
        rating: null,
        cancellationReason: 'Customer could not make the scheduled pickup.',
        archivedAt: daysAgo(3)
    },
    {
        orderId: '6755RS',
        customer: 'Noah',
        status: 'completed',
        items: [{ name: 'Java Chip Frappuccino', image: 'javachip.png', options: 'Non-fat, Grande', quantity: 2, price: 230 }],
        payment: 'Cash',
        paymentStatus: 'Paid',
        total: 460,
        rating: null,
        completedAt: daysAgoAt(4, 10, 52),
        cancellationReason: '',
        archivedAt: daysAgo(4)
    },
    {
        orderId: '6754QR',
        customer: 'Mika',
        status: 'completed',
        items: [
            { name: 'Matcha Latte', image: 'matchalatte.png', options: 'Iced, Grande', quantity: 1, price: 195 },
            { name: 'Caffè Americano', image: 'americano.png', options: 'Hot, Tall', quantity: 1, price: 150 }
        ],
        payment: 'Online payment',
        paymentStatus: 'Paid',
        referenceNumber: '1837604925184',
        total: 345,
        rating: 5,
        completedAt: daysAgoAt(6, 15, 7),
        cancellationReason: '',
        archivedAt: daysAgo(6)
    },
    {
        orderId: '6753PQ',
        customer: 'Ethan',
        status: 'completed',
        items: [{ name: 'Caramel Macchiato', image: 'caramel.png', options: 'Hot, Grande', quantity: 1, price: 220 }],
        payment: 'Online payment',
        paymentStatus: 'Paid',
        referenceNumber: '6251840392716',
        total: 220,
        rating: null,
        completedAt: daysAgoAt(7, 11, 41),
        cancellationReason: '',
        archivedAt: daysAgo(7)
    }
];

function readStoredValue(key, fallback) {
    const value = localStorage.getItem(key);
    return value === null ? fallback : JSON.parse(value);
}

let savedOrderState = readStoredValue(orderStateKey, {});
const storedArchivedOrders = localStorage.getItem(archivedOrdersKey);
let archivedOrders = storedArchivedOrders === null
    ? sampleArchivedOrders
    : JSON.parse(storedArchivedOrders);
let archiveStorageNeedsSave = storedArchivedOrders === null || archivedOrders.length === 0;
if (archiveStorageNeedsSave) {
    archivedOrders = sampleArchivedOrders;
}
const sampleOrdersById = new Map(sampleArchivedOrders.map(order => [order.orderId, order]));
archivedOrders = archivedOrders.map(order => {
    const sampleOrder = sampleOrdersById.get(order.orderId);
    const payment = ['GCash', 'Card'].includes(order.payment) ? 'Online payment' : order.payment;
    const paymentStatus = order.paymentStatus || (
        order.status === 'cancelled'
            ? 'Not charged'
            : order.status === 'completed' ? 'Paid' : 'Pending'
    );
    let referenceNumber = order.referenceNumber || '';
    if (payment === 'Online payment' && paymentStatus === 'Paid' && !referenceNumber) {
        referenceNumber = sampleOrder?.referenceNumber || generateReferenceNumber();
    }
    if (payment !== order.payment || paymentStatus !== order.paymentStatus
        || referenceNumber !== order.referenceNumber) {
        archiveStorageNeedsSave = true;
    }
    if (sampleOrder?.completedAt && order.status === 'completed' && !order.completedAt) {
        archiveStorageNeedsSave = true;
        return { ...order, payment, paymentStatus, referenceNumber, completedAt: sampleOrder.completedAt };
    }
    return { ...order, payment, paymentStatus, referenceNumber };
});
if (archiveStorageNeedsSave) {
    localStorage.setItem(archivedOrdersKey, JSON.stringify(archivedOrders));
}
let activeOrderCard = null;

function persistOrderState(card, status, cancellationReason = '') {
    savedOrderState[card.dataset.orderId] = {
        status,
        cancellationReason,
        completedAt: card.dataset.completedAt || '',
        referenceNumber: card.dataset.referenceNumber || '',
        paymentStatus: card.dataset.paymentStatus || 'Pending'
    };
    localStorage.setItem(orderStateKey, JSON.stringify(savedOrderState));
}

function updateCardPaymentStatus(card) {
    const badge = card.querySelector('.vendor-card-payment-status');
    const paymentStatus = card.dataset.paymentStatus || 'Pending';
    badge.textContent = `Payment: ${paymentStatus}`;
    badge.className = `vendor-card-payment-status ${paymentStatus.toLowerCase().replace(/\s+/g, '-')}`;
}

// Keep references only for paid online orders, reusing generated values across reloads.
function ensureReferenceNumber(card) {
    if (card.dataset.payment !== 'Online payment' || card.dataset.paymentStatus !== 'Paid') {
        card.dataset.referenceNumber = '';
        return;
    }

    const savedReference = savedOrderState[card.dataset.orderId]?.referenceNumber;
    if (!card.dataset.referenceNumber && savedReference) {
        card.dataset.referenceNumber = savedReference;
    }
    if (!card.dataset.referenceNumber) {
        card.dataset.referenceNumber = generateReferenceNumber();
    }

    savedOrderState[card.dataset.orderId] = {
        ...savedOrderState[card.dataset.orderId],
        referenceNumber: card.dataset.referenceNumber
    };
    localStorage.setItem(orderStateKey, JSON.stringify(savedOrderState));
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

function getOrderItems(card) {
    if (card.dataset.items) {
        return JSON.parse(card.dataset.items);
    }

    return [{
        name: card.dataset.item,
        image: card.dataset.image,
        options: card.dataset.options,
        quantity: Number(card.dataset.quantity),
        price: Number(card.dataset.total) / Number(card.dataset.quantity)
    }];
}

function updateCardStatus(card, status) {
    card.dataset.status = status;
    const badge = card.querySelector('.vendor-order-status');
    badge.textContent = statusLabels[status];
    badge.className = `vendor-order-status ${status}`;
    syncCardActions(card);
}

function syncCardActions(card) {
    let archiveButton = card.querySelector('.vendor-archive-order');
    const canArchive = ['completed', 'cancelled'].includes(card.dataset.status);
    if (!archiveButton) {
        archiveButton = document.createElement('button');
        archiveButton.className = 'vendor-archive-order';
        archiveButton.type = 'button';
        archiveButton.textContent = 'Archive';
        card.querySelector('.vendor-card-actions').append(archiveButton);
    }
    archiveButton.hidden = !canArchive;

    if (!card.querySelector('.vendor-card-price')) {
        const orderNumber = card.querySelector('.vendor-card-order-id');
        const price = document.createElement('p');
        price.className = 'vendor-card-price';
        price.textContent = `₱${card.dataset.total}`;
        orderNumber.after(price);
    }

    if (!card.querySelector('.vendor-card-avatar')) {
        const heading = card.querySelector('h2');
        const header = document.createElement('div');
        header.className = 'vendor-card-topline';
        const avatar = document.createElement('img');
        avatar.className = 'vendor-card-avatar';
        avatar.src = '../../assets/main/defaultAvatar.png';
        avatar.alt = `${card.dataset.customer} profile`;
        heading.before(header);
        header.append(heading, avatar);
    }
}

function setOrderDetails(card) {
    if (!orderModal) {
        return;
    }

    const status = card.dataset.status;
    const itemList = orderModal.querySelector('#vendorModalItems');
    itemList.replaceChildren();

    getOrderItems(card).forEach(item => {
        const row = document.createElement('div');
        row.className = 'vendor-order-item';

        const image = document.createElement('img');
        image.src = `../../assets/starbucksMenu/${item.image}`;
        image.alt = item.name;

        const copy = document.createElement('div');
        copy.className = 'vendor-order-item-copy';

        const name = document.createElement('h3');
        name.textContent = item.name;
        const options = document.createElement('p');
        options.textContent = item.options;
        const quantity = document.createElement('p');
        quantity.textContent = `Quantity: ${item.quantity}`;
        copy.append(name, options, quantity);

        const price = document.createElement('strong');
        price.className = 'vendor-order-item-price';
        price.textContent = `₱${item.price * item.quantity}`;

        row.append(image, copy, price);
        itemList.append(row);
    });

    orderModal.querySelector('#selectedOrderHeading').textContent =
        `${card.dataset.customer}’s order`;
    orderModal.querySelector('#vendorModalOrderId').textContent =
        `ORDER ID: ${card.dataset.orderId}`;
    orderModal.querySelector('#vendorModalSubtotal').textContent = `₱${card.dataset.total}`;
    orderModal.querySelector('#vendorModalPayment').textContent = card.dataset.payment;
    orderModal.querySelector('#vendorModalPickup').textContent = formatPickupTime(card.dataset.pickupTime);

    const statusElement = orderModal.querySelector('#vendorModalStatus');
    statusElement.textContent = statusLabels[status];
    statusElement.className = `vendor-payment-status ${status}`;
    const paymentStatus = card.dataset.paymentStatus || 'Pending';
    const paymentStatusElement = orderModal.querySelector('#vendorModalPaymentStatus');
    paymentStatusElement.textContent = paymentStatus;
    paymentStatusElement.className =
        `vendor-payment-status ${paymentStatus.toLowerCase().replace(/\s+/g, '-')}`;
    ensureReferenceNumber(card);
    const referenceRow = orderModal.querySelector('#vendorModalReferenceRow');
    const hasReference = card.dataset.payment === 'Online payment' && paymentStatus === 'Paid';
    referenceRow.hidden = !hasReference;
    orderModal.querySelector('#vendorModalReferenceNumber').textContent =
        hasReference ? card.dataset.referenceNumber : '';

    const cancellationReason = orderModal.querySelector('#vendorModalCancellationReason');
    cancellationReason.hidden = status !== 'cancelled';
    cancellationReason.textContent = status === 'cancelled'
        ? `Cancellation reason: ${card.dataset.cancellationReason || 'Not provided'}`
        : '';

    orderModal.querySelectorAll('.vendor-status-option').forEach(button => {
        button.disabled = status === 'completed' || status === 'cancelled';
        button.setAttribute('aria-pressed', String(button.dataset.status === status));
    });
    orderModal.querySelector('#vendorCancelOrderButton').hidden =
        status === 'completed' || status === 'cancelled' || paymentStatus !== 'Pending';
}

function filterOrders() {
    if (!orderGrid) {
        return;
    }

    const searchTerm = orderSearch?.value.trim().toLowerCase() ?? '';
    const selectedStatus = statusFilter?.value ?? 'all';
    let visibleCount = 0;

    orderGrid.querySelectorAll('.vendor-order-card').forEach(card => {
        const matchesStatus = selectedStatus === 'all' || card.dataset.status === selectedStatus;
        const searchableText = [
            card.dataset.orderId,
            card.dataset.customer,
            card.dataset.item
        ].join(' ').toLowerCase();
        card.hidden = !matchesStatus || !searchableText.includes(searchTerm);
        if (!card.hidden) {
            visibleCount += 1;
        }
    });

    if (emptyMessage) {
        emptyMessage.hidden = visibleCount > 0;
    }
}

function sortOrders() {
    if (!orderGrid || !sortControl) {
        return;
    }

    const cards = Array.from(orderGrid.querySelectorAll('.vendor-order-card'));
    cards.sort((first, second) => {
        if (sortControl.value === 'status') {
            const statusDifference = statusOrder.indexOf(first.dataset.status)
                - statusOrder.indexOf(second.dataset.status);
            if (statusDifference !== 0) {
                return statusDifference;
            }
        }
        return getPickupTimeValue(first.dataset.pickupTime)
            - getPickupTimeValue(second.dataset.pickupTime);
    });

    cards.forEach(card => orderGrid.append(card));
    filterOrders();
}

function serializeOrder(card) {
    return {
        orderId: card.dataset.orderId,
        customer: card.dataset.customer,
        status: card.dataset.status,
        items: getOrderItems(card),
        payment: card.dataset.payment,
        paymentStatus: card.dataset.paymentStatus || 'Pending',
        total: Number(card.dataset.total),
        paymentStatus: card.dataset.paymentStatus || 'Pending',
        rating: card.dataset.rating ? Number(card.dataset.rating) : null,
        referenceNumber: card.dataset.referenceNumber || '',
        completedAt: card.dataset.completedAt || '',
        cancellationReason: card.dataset.cancellationReason || '',
        archivedAt: new Date().toISOString()
    };
}

function archiveOrder(card) {
    if (!['completed', 'cancelled'].includes(card.dataset.status)) {
        return;
    }

    if (!archivedOrders.some(order => order.orderId === card.dataset.orderId)) {
        archivedOrders.push(serializeOrder(card));
        localStorage.setItem(archivedOrdersKey, JSON.stringify(archivedOrders));
    }
    card.remove();
    filterOrders();
}

function renderHistory() {
    if (!historyBody) {
        return;
    }

    const searchTerm = historySearch?.value.trim().toLowerCase() ?? '';
    const selectedStatus = historyFilter?.value ?? 'all';
    const visibleOrders = archivedOrders
        .filter(order => selectedStatus === 'all' || order.status === selectedStatus)
        .filter(order => {
            const itemNames = order.items.map(item => item.name).join(' ');
            return [
                order.orderId,
                order.customer,
                order.status,
                itemNames
            ].join(' ').toLowerCase().includes(searchTerm);
        })
        .sort((first, second) => new Date(second.archivedAt) - new Date(first.archivedAt));

    historyBody.replaceChildren();
    visibleOrders.forEach(order => {
        const row = document.createElement('tr');
        row.className = 'vendor-history-row';
        row.tabIndex = 0;
        row.dataset.orderId = order.orderId;
        row.setAttribute('role', 'button');
        row.setAttribute('aria-label', `View order ${order.orderId} details`);

        const cells = [
            order.customer,
            order.orderId,
            statusLabels[order.status] || order.status,
            new Date(order.completedAt || order.archivedAt).toLocaleDateString(),
            order.items.map(item => `${item.name} × ${item.quantity}`).join(', '),
            `₱${order.total}`
        ];
        cells.forEach((text, index) => {
            const cell = document.createElement('td');
            cell.textContent = text;
            if (index === 2) {
                cell.className = `vendor-history-status ${order.status}`;
            }
            row.append(cell);
        });

        const timeCell = document.createElement('td');
        timeCell.textContent = order.completedAt
            ? new Date(order.completedAt).toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' })
            : '—';
        row.insertBefore(timeCell, row.children[4]);

        const ratingCell = document.createElement('td');
        ratingCell.append(createRatingDisplay(order.rating));
        row.append(ratingCell);
        historyBody.append(row);
    });

    if (historyEmpty) {
        historyEmpty.hidden = visibleOrders.length > 0;
    }
}

function createRatingDisplay(rating) {
    const display = document.createElement('div');
    display.className = 'vendor-history-rating';

    if (Number.isInteger(rating)) {
        const stars = document.createElement('span');
        stars.className = 'vendor-rating-stars';
        stars.setAttribute('aria-label', `${rating} out of 5 stars`);
        for (let star = 1; star <= 5; star += 1) {
            const icon = document.createElement('span');
            icon.className = star <= rating ? 'is-filled' : 'is-empty';
            icon.textContent = star <= rating ? '★' : '☆';
            icon.setAttribute('aria-hidden', 'true');
            stars.append(icon);
        }
        display.append(stars);
    } else {
        const value = document.createElement('span');
        value.className = 'vendor-history-rating-value';
        value.textContent = 'Not rated';
        display.append(value);
    }
    return display;
}

function showHistoryDetails(order) {
    if (!historyModal) {
        return;
    }

    const itemList = historyModal.querySelector('#vendorHistoryModalItems');
    itemList.replaceChildren();
    order.items.forEach(item => {
        const row = document.createElement('div');
        row.className = 'vendor-order-item';
        const image = document.createElement('img');
        image.src = `../../assets/starbucksMenu/${item.image}`;
        image.alt = item.name;
        const copy = document.createElement('div');
        copy.className = 'vendor-order-item-copy';
        const name = document.createElement('h3');
        name.textContent = item.name;
        const options = document.createElement('p');
        options.textContent = item.options;
        const quantity = document.createElement('p');
        quantity.textContent = `Quantity: ${item.quantity}`;
        copy.append(name, options, quantity);
        const price = document.createElement('strong');
        price.className = 'vendor-order-item-price';
        price.textContent = `₱${item.price * item.quantity}`;
        row.append(image, copy, price);
        itemList.append(row);
    });

    historyModal.querySelector('#vendorHistoryModalHeading').textContent =
        `${order.customer}’s order`;
    historyModal.querySelector('#vendorHistoryModalOrderId').textContent =
        `ORDER ID: ${order.orderId}`;
    historyModal.querySelector('#vendorHistoryModalStatus').textContent =
        statusLabels[order.status] || order.status;
    historyModal.querySelector('#vendorHistoryModalStatus').className =
        `vendor-payment-status ${order.status}`;
    historyModal.querySelector('#vendorHistoryModalPayment').textContent = order.payment;
    historyModal.querySelector('#vendorHistoryModalPaymentStatus').textContent =
        order.paymentStatus || 'Pending';
    historyModal.querySelector('#vendorHistoryModalTotal').textContent = `₱${order.total}`;
    const referenceRow = historyModal.querySelector('#vendorHistoryModalReferenceRow');
    const hasReference = order.payment === 'Online payment'
        && order.paymentStatus === 'Paid' && Boolean(order.referenceNumber);
    referenceRow.hidden = !hasReference;
    historyModal.querySelector('#vendorHistoryModalReferenceNumber').textContent =
        hasReference ? order.referenceNumber : '';
    historyModal.querySelector('#vendorHistoryModalDate').textContent =
        new Date(order.completedAt || order.archivedAt).toLocaleDateString();
    historyModal.querySelector('#vendorHistoryModalTime').textContent = order.completedAt
        ? new Date(order.completedAt).toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' })
        : '—';
    const rating = historyModal.querySelector('#vendorHistoryModalRating');
    rating.replaceChildren(createRatingDisplay(order.rating));

    const reason = historyModal.querySelector('#vendorHistoryModalCancellationReason');
    reason.hidden = order.status !== 'cancelled';
    reason.textContent = order.status === 'cancelled'
        ? `Cancellation reason: ${order.cancellationReason || 'Not provided'}`
        : '';
    bootstrap.Modal.getOrCreateInstance(historyModal).show();
}

if (orderGrid) {
    orderGrid.querySelectorAll('.vendor-order-card').forEach(card => {
        const savedState = savedOrderState[card.dataset.orderId];
        if (savedState?.status) {
            card.dataset.cancellationReason = savedState.cancellationReason || '';
            card.dataset.completedAt = savedState.completedAt || card.dataset.completedAt || '';
            card.dataset.referenceNumber = savedState.referenceNumber || card.dataset.referenceNumber || '';
            card.dataset.paymentStatus = savedState.paymentStatus || card.dataset.paymentStatus || 'Pending';
            updateCardStatus(card, savedState.status);
        }
        updateCardPaymentStatus(card);
        ensureReferenceNumber(card);
        syncCardActions(card);
        if (archivedOrders.some(order => order.orderId === card.dataset.orderId)) {
            card.remove();
        }
    });

    orderGrid.addEventListener('click', event => {
        const button = event.target.closest('button');
        const card = button?.closest('.vendor-order-card');
        if (!card) {
            return;
        }
        if (button.matches('.vendor-open-order')) {
            activeOrderCard = card;
            setOrderDetails(card);
        } else if (button.matches('.vendor-archive-order')) {
            archiveOrder(card);
        }
    });
}

sortControl?.addEventListener('change', sortOrders);
statusFilter?.addEventListener('change', filterOrders);
orderSearch?.addEventListener('input', filterOrders);

orderModal?.querySelector('.vendor-status-options')?.addEventListener('click', event => {
    const button = event.target.closest('.vendor-status-option');
    const status = button?.dataset.status;
    if (!activeOrderCard || !editableStatuses.includes(status)
        || ['completed', 'cancelled'].includes(activeOrderCard.dataset.status)) {
        return;
    }

    updateCardStatus(activeOrderCard, status);
    activeOrderCard.dataset.cancellationReason = '';
    activeOrderCard.dataset.completedAt =
        status === 'completed' ? new Date().toISOString() : '';
    if (status === 'completed') {
        activeOrderCard.dataset.paymentStatus = 'Paid';
        updateCardPaymentStatus(activeOrderCard);
        ensureReferenceNumber(activeOrderCard);
    }
    persistOrderState(activeOrderCard, status);
    setOrderDetails(activeOrderCard);
    sortOrders();
});

orderModal?.querySelector('#vendorCancelOrderButton')?.addEventListener('click', () => {
    if (!activeOrderCard || !cancelModal || !cancelForm || !cancelReasonInput) {
        return;
    }

    cancelForm.reset();
    cancelReasonInput.setCustomValidity('');
    cancelModal.querySelector('#vendorCancelOrderLabel').textContent =
        `Order #${activeOrderCard.dataset.orderId} for ${activeOrderCard.dataset.customer}`;
    orderModal.addEventListener('hidden.bs.modal', () => {
        bootstrap.Modal.getOrCreateInstance(cancelModal).show();
    }, { once: true });
    // Close the details modal before opening the cancellation form to avoid stacked backdrops.
    bootstrap.Modal.getOrCreateInstance(orderModal).hide();
});

cancelForm?.addEventListener('submit', event => {
    event.preventDefault();
    const reason = cancelReasonInput.value.trim();
    if (!reason || !activeOrderCard
        || activeOrderCard.dataset.paymentStatus !== 'Pending'
        || ['completed', 'cancelled'].includes(activeOrderCard.dataset.status)) {
        cancelReasonInput.setCustomValidity('Please provide a cancellation reason.');
        cancelForm.reportValidity();
        return;
    }
    cancelReasonInput.setCustomValidity('');
    activeOrderCard.dataset.cancellationReason = reason;
    activeOrderCard.dataset.paymentStatus = 'Not charged';
    updateCardPaymentStatus(activeOrderCard);
    updateCardStatus(activeOrderCard, 'cancelled');
    persistOrderState(activeOrderCard, 'cancelled', reason);
    sortOrders();
    bootstrap.Modal.getOrCreateInstance(cancelModal).hide();
});

cancelReasonInput?.addEventListener('input', () => cancelReasonInput.setCustomValidity(''));

historyFilter?.addEventListener('change', renderHistory);
historySearch?.addEventListener('input', renderHistory);
historyBody?.addEventListener('click', event => {
    const row = event.target.closest('.vendor-history-row');
    const order = archivedOrders.find(entry => entry.orderId === row?.dataset.orderId);
    if (order) {
        showHistoryDetails(order);
    }
});
historyBody?.addEventListener('keydown', event => {
    if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        event.target.closest('.vendor-history-row')?.click();
    }
});

sortOrders();
renderHistory();
