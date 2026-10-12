// stall-info filter button
$("#filterButton").click(function () {
    $("#filterPanel").slideToggle();
});const orderSearchInput = document.querySelector('#orderSearchInput');

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
            store: 'Starbucks',
            location: 'Green Court',
            logo: '../../assets/stalls/starbucks.png',
            date: 'October 12, 2026',
            status: 'Preparing, pickup by 2:15 PM',
            statusClass: 'preparing',
            paymentMethod: 'Online payment',
            paymentStatus: 'Paid',
            total: 555,
            reviewable: false,
            items: [
                { name: 'Caffè Latte', detail: 'Grande', quantity: 2, total: 480 },
                { name: 'Chocolate Chip Cookie', detail: 'Classic', quantity: 1, total: 75 }
            ]
        },
        '1N2C7T': {
            store: 'Dairy Queen',
            location: 'Agno',
            logo: '../../assets/stalls/dq.png',
            date: 'October 10, 2026',
            status: 'Order collected at 3:43 PM',
            statusClass: 'completed',
            paymentMethod: 'Cash',
            paymentStatus: 'Paid',
            total: 317,
            reviewable: true,
            items: [
                { name: 'Oreo Blizzard', detail: 'Regular', quantity: 1, total: 179 },
                { name: 'Vanilla Cone', detail: 'Single scoop', quantity: 2, total: 138 }
            ]
        },
        '8B4K2M': {
            store: 'Belgian Waffle',
            location: 'Gokongwei Hall',
            logo: '../../assets/stalls/belgian.png',
            date: 'October 8, 2026',
            status: 'Order cancelled',
            statusClass: 'cancelled',
            paymentMethod: 'Cash',
            paymentStatus: 'Not charged',
            total: 435,
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
        const order = orders[trigger?.dataset.orderId];

        if (!order) {
            console.error('Unable to show order details: the selected order was not found.');
            event.preventDefault();
            return;
        }

        const orderStatus = orderInfoModal.querySelector('#orderModalStatus');
        const itemList = orderInfoModal.querySelector('#orderModalItems');
        const reviewLink = orderInfoModal.querySelector('#reviewOrderLink');

        orderInfoModal.querySelector('#orderStoreLogo').src = order.logo;
        orderInfoModal.querySelector('#orderStoreName').textContent = order.store;
        orderInfoModal.querySelector('#orderStoreLocation').textContent = order.location;
        orderInfoModal.querySelector('#orderModalId').textContent = `ORDER ID: ${trigger.dataset.orderId}`;
        orderStatus.textContent = order.status;
        orderStatus.className = `order-status ${order.statusClass}`;
        orderInfoModal.querySelector('#orderModalDate').textContent = order.date;
        orderInfoModal.querySelector('#orderSubtotal').textContent = currency.format(order.total);
        orderInfoModal.querySelector('#orderTotal').textContent = currency.format(order.total);
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
            price.textContent = currency.format(item.total);

            description.append(name, detail, quantity);
            row.append(description, price);
            itemList.append(row);
        });
    });
}