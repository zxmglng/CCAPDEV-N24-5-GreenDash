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
    const orders = {
        '6767HI': {
            paymentStatus: 'Paid',
            reviewable: false,
            items: [
                { name: 'Caffè Latte', detail: 'Grande', quantity: 2, total: 480 },
                { name: 'Chocolate Chip Cookie', detail: 'Classic', quantity: 1, total: 75 }
            ]
        },
        '1N2C7T': {
            paymentStatus: 'Paid',
            reviewable: true,
            items: [
                { name: 'Oreo Blizzard', detail: 'Regular', quantity: 1, total: 179 },
                { name: 'Vanilla Cone', detail: 'Single scoop', quantity: 2, total: 138 }
            ]
        },
        '8B4K2M': {
            paymentStatus: 'Not charged',
            reviewable: false,
            items: [
                { name: 'Chocolate Waffle', detail: 'Classic', quantity: 1, total: 145 },
                { name: 'Strawberry Waffle', detail: 'Classic', quantity: 2, total: 290 }
            ]
        }
    };
    const currency = new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2
    });

    orderInfoModal.addEventListener('show.bs.modal', (event) => {
        const trigger = event.relatedTarget;
        const orderId = trigger?.dataset.orderId;
        const card = trigger?.closest('.order-card');
        const order = orders[orderId];

        if (!order || !card) {
            console.error('Unable to show order details: the selected order preview was not found.');
            event.preventDefault();
            return;
        }

        const store = card.querySelector('.order-title-line h3')?.textContent.trim();
        const logo = card.querySelector('.order-stall-logo');
        const location = card.querySelector('.order-location')?.textContent.replace(/^Location:\s*/i, '').trim();
        const status = card.querySelector('.order-status');
        const date = card.querySelector('.order-date')?.textContent.trim();
        const paymentPreview = card.querySelector('.order-payment')?.textContent.trim() || '';
        const totalMatch = paymentPreview.match(/[\d,]+(?:\.\d{1,2})?/);
        const total = totalMatch ? Number(totalMatch[0].replace(/,/g, '')) : NaN;

        if (!store || !logo || !location || !status || !date || !Number.isFinite(total)) {
            console.error('Unable to show order details: the selected order preview is incomplete.');
            event.preventDefault();
            return;
        }

        const orderStatus = orderInfoModal.querySelector('#orderModalStatus');
        const itemList = orderInfoModal.querySelector('#orderModalItems');
        const reviewLink = orderInfoModal.querySelector('#reviewOrderLink');

        const orderLogo = orderInfoModal.querySelector('#orderStoreLogo');
        orderLogo.src = logo.src;
        orderLogo.alt = `${store} logo`;
        orderInfoModal.querySelector('#orderStoreName').textContent = store;
        orderInfoModal.querySelector('#orderStoreLocation').textContent = location;
        orderInfoModal.querySelector('#orderModalId').textContent = `ORDER ID: ${orderId}`;
        orderStatus.textContent = status.textContent.trim();
        orderStatus.className = status.className;
        orderInfoModal.querySelector('#orderModalDate').textContent = date;
        orderInfoModal.querySelector('#orderSubtotal').textContent = currency.format(total);
        orderInfoModal.querySelector('#orderTotal').textContent = currency.format(total);
        orderInfoModal.querySelector('#orderPaymentMethod').textContent = /^ONLINE:/i.test(paymentPreview)
            ? 'Online payment'
            : 'Cash';
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
            price.textContent = currency.format(item.total);

            description.append(name, detail, quantity);
            row.append(description, price);
            itemList.append(row);
        });
    });
}