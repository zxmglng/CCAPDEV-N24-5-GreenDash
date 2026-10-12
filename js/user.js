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
        status: 'Preparing, pickup by 2:15 PM', statusClass: 'preparing',
        paymentMethod: 'Online payment', paymentStatus: 'Paid', total: 555,
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
].map((order) => ({
    ...order,
    ...storeDetails[order.store],
    isOngoing: order.statusClass === 'preparing' || order.statusClass === 'ready',
    reviewable: order.statusClass === 'completed'
}));

const orderById = Object.fromEntries(orders.map((order) => [order.id, order]));
const currency = new Intl.NumberFormat('en-PH', { maximumFractionDigits: 0 });
const ongoingOrdersSection = document.querySelector('#ongoing-heading')?.parentElement;
const previousOrdersSection = document.querySelector('#previous-heading')?.parentElement;

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
    const orderId = document.createElement('span');
    orderId.className = 'order-id';
    orderId.textContent = `ORDER ID: ${order.id}`;
    const status = document.createElement('span');
    status.className = `order-status ${order.statusClass}`;
    status.textContent = order.status;
    title.append(storeName, orderId, status);
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
    return card;
}

const sortedOrders = [...orders].sort((first, second) => Date.parse(second.date) - Date.parse(first.date));

sortedOrders.forEach((order) => {
    const section = order.isOngoing ? ongoingOrdersSection : previousOrdersSection;
    section?.append(createOrderCard(order));
});

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

const orderInfoModal = document.querySelector('#orderInfoModal');

if (orderInfoModal) {
    const modalCurrency = new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2
    });

    orderInfoModal.addEventListener('show.bs.modal', (event) => {
        const orderId = event.relatedTarget?.dataset.orderId;
        const order = orderById[orderId];

        if (!order) {
            console.error('Unable to show order details: the selected order was not found.');
            event.preventDefault();
            return;
        }

        const orderStatus = orderInfoModal.querySelector('#orderModalStatus');
        const itemList = orderInfoModal.querySelector('#orderModalItems');
        const reviewLink = orderInfoModal.querySelector('#reviewOrderLink');

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
        orderInfoModal.querySelector('#orderPaymentStatus').textContent = order.paymentStatus;
        reviewLink.hidden = !order.reviewable;
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
}