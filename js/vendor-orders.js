(() => {
    const applicationUrl = new URL('../', document.currentScript.src);
    const data = JSON.parse(document.querySelector('#vendorOrdersData').textContent);
    const page = document.body.dataset.vendorPage;
    const select = selector => document.querySelector(selector);
    const activeStatuses = ['pending', 'preparing', 'ready'];
    const statusLabels = {pending: 'Pending', preparing: 'Preparing', ready: 'Ready for pickup',
        completed: 'Collected', cancelled: 'Cancelled'};
    const paymentLabels = {pending: 'Pending', paid: 'Paid', not_charged: 'Not charged'};
    const nextStatuses = {pending: 'preparing', preparing: 'ready'};
    const actionLabels = {pending: 'Start preparing', preparing: 'Mark ready for pickup'};
    const currency = new Intl.NumberFormat('en-PH', {style: 'currency', currency: 'PHP'});
    const dateFormat = new Intl.DateTimeFormat('en-PH', {dateStyle: 'medium', timeStyle: 'short', timeZone: 'Asia/Manila'});
    let orders = data.orders;
    let selectedOrder = null;
    let verifiedPickup = null;
    let detailRequest = 0;
    let busy = false;

    function element(tag, className = '', text = '') {
        const node = document.createElement(tag);
        node.className = className;
        node.textContent = text;
        return node;
    }

    function feedback(node, message, isError = false) {
        node.textContent = message;
        node.dataset.error = String(isError);
    }

    function orderUrl(endpoint, params = {}) {
        const url = new URL(`controller-vendor/${endpoint}`, applicationUrl);
        url.search = new URLSearchParams(params).toString();
        return url;
    }

    async function request(url, options = {}) {
        const response = await fetch(url, {credentials: 'same-origin', cache: 'no-store', ...options});
        let result;
        try {
            result = await response.json();
        } catch {
            throw new Error('Unable to read the server response. Please reload the page and try again.');
        }
        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Unable to update this order. Please try again.');
        }
        return result;
    }

    function pickupLabel(order) {
        return order.pickupType === 'now' ? 'Now' : dateFormat.format(new Date(order.pickupAt));
    }

    function matchesFilters(order) {
        const search = (select('#vendorOrderSearchInput') || select('#vendorHistorySearch')).value.trim().toLocaleLowerCase();
        const status = (select('#vendorOrderStatusFilter') || select('#vendorHistoryFilter')).value;
        const stall = select('#vendorStallFilter').value;
        const text = [order.orderNumber, order.customer, order.stallName,
            ...order.items.map(item => `${item.name} ${item.detail}`)].join(' ').toLocaleLowerCase();
        return (status === 'all' || order.status === status)
            && (stall === 'all' || String(order.stallId) === stall) && text.includes(search);
    }

    function compareOrders(first, second) {
        if (select('#vendorOrderSort').value === 'status') {
            const difference = activeStatuses.indexOf(first.status) - activeStatuses.indexOf(second.status);
            if (difference) return difference;
        }
        const firstPickup = first.pickupType === 'now' ? 0 : Date.parse(first.pickupAt);
        const secondPickup = second.pickupType === 'now' ? 0 : Date.parse(second.pickupAt);
        return firstPickup - secondPickup || Date.parse(first.createdAt) - Date.parse(second.createdAt)
            || first.orderId - second.orderId;
    }

    function createCard(order) {
        const card = element('article', 'vendor-order-card');
        card.append(element('h2', '', order.customer), element('span', 'vendor-card-order-id', order.orderNumber),
            element('p', 'vendor-card-price', currency.format(order.total)), element('p', '', order.stallName));
        order.items.forEach(item => card.append(element('p', 'vendor-card-options', `${item.quantity} × ${item.name}`)));
        const metadata = element('div', 'vendor-card-meta');
        metadata.append(element('span', `vendor-order-status ${order.status}`, statusLabels[order.status]),
            element('span', '', `Pickup: ${pickupLabel(order)}`));
        const actions = element('div', 'vendor-card-actions');
        const details = element('button', 'vendor-open-order', 'View order');
        details.type = 'button';
        details.setAttribute('aria-label', `View order ${order.orderNumber}`);
        details.addEventListener('click', () => openDetails({orderId: order.orderId}));
        actions.append(details);
        card.append(metadata, element('p', `vendor-card-payment-status ${order.paymentStatus.replaceAll('_', '-')}`,
            `Payment: ${paymentLabels[order.paymentStatus]}`), actions);
        return card;
    }

    function renderOrders() {
        if (page === 'pickup') return;
        const filtered = orders.filter(matchesFilters);
        if (page === 'orders') {
            const active = filtered.filter(order => activeStatuses.includes(order.status)).sort(compareOrders);
            select('#vendorOrderGrid').replaceChildren(...active.map(createCard));
            select('#vendorOrdersEmpty').hidden = active.length > 0;
        } else {
            const rows = filtered.map(order => {
                const row = element('tr');
                const orderCell = element('td');
                const details = element('button', 'vendor-action secondary', order.orderNumber);
                details.type = 'button';
                details.setAttribute('aria-label', `View order ${order.orderNumber}`);
                details.addEventListener('click', () => openDetails({orderId: order.orderId}));
                orderCell.append(details);
                row.append(element('td', '', order.customer), orderCell, element('td', '', order.stallName),
                    element('td', '', statusLabels[order.status]), element('td', '', dateFormat.format(new Date(order.createdAt))),
                    element('td', '', order.items.map(item => `${item.quantity} × ${item.name}`).join(', ')),
                    element('td', '', currency.format(order.total)),
                    element('td', '', order.review ? `${order.review.rating}/5` : order.status === 'completed' ? 'Unrated' : '—'));
                return row;
            });
            select('#vendorHistoryBody').replaceChildren(...rows);
            select('#vendorHistoryEmpty').hidden = rows.length > 0;
        }
    }

    function renderDetails(order, root, isPickup = false) {
        root.hidden = false;
        const values = {...order, orderNumber: `ORDER ID: ${order.orderNumber}`, status: statusLabels[order.status],
            pickup: pickupLabel(order), createdAt: dateFormat.format(new Date(order.createdAt)),
            paymentMethod: order.paymentMethod === 'online' ? 'Online payment' : 'Cash',
            paymentStatus: paymentLabels[order.paymentStatus], total: currency.format(order.total)};
        root.querySelectorAll('[data-order-field]').forEach(node => {
            node.textContent = values[node.dataset.orderField] || '—';
        });
        root.querySelector('[data-order-reference]').hidden = !order.paymentReference;
        const itemRows = order.items.map(item => {
            const row = element('div', 'vendor-order-item');
            const copy = element('div', 'vendor-order-item-copy');
            copy.append(element('h3', '', item.name), element('p', '', item.detail),
                element('p', '', `Quantity: ${item.quantity} · ${currency.format(item.unitPrice)} each`));
            row.append(copy, element('strong', 'vendor-order-item-price', currency.format(item.total)));
            return row;
        });
        root.querySelector('[data-order-items]').replaceChildren(...itemRows);
        const review = root.querySelector('[data-order-review]');
        review.hidden = !order.review;
        review.replaceChildren();
        if (order.review) {
            review.append(element('strong', '', `Customer rating: ${order.review.rating}/5`), element('p', '', order.review.comment));
        }
        const actions = root.querySelector('[data-order-actions]');
        actions.hidden = isPickup || !activeStatuses.includes(order.status);
        const advance = root.querySelector('[data-order-advance]');
        advance.hidden = !nextStatuses[order.status];
        advance.textContent = actionLabels[order.status] || '';
        const pickup = root.querySelector('[data-order-pickup]');
        pickup.hidden = order.status !== 'ready';
        pickup.href = orderUrl('pickup.php', {orderNumber: order.orderNumber});
        root.querySelector('[data-order-cancel]').hidden = !activeStatuses.includes(order.status) || order.paymentStatus !== 'pending';
    }

    function rememberOrder(order) {
        orders = orders.map(existing => existing.orderId === order.orderId ? order : existing);
        renderOrders();
    }

    async function openDetails(params) {
        const currentRequest = ++detailRequest;
        selectedOrder = null;
        const modal = select('#vendorOrderModal');
        const root = modal.querySelector('[data-order-details]');
        root.hidden = true;
        feedback(select('#vendorModalFeedback'), 'Loading order…');
        bootstrap.Modal.getOrCreateInstance(modal).show();
        try {
            const result = await request(orderUrl('order.php', params));
            if (currentRequest !== detailRequest) return;
            selectedOrder = result.order;
            rememberOrder(selectedOrder);
            renderDetails(selectedOrder, root);
            feedback(select('#vendorModalFeedback'), '');
        } catch (error) {
            if (currentRequest === detailRequest) feedback(select('#vendorModalFeedback'), error.message, true);
        }
    }

    function setBusy(value) {
        busy = value;
        document.querySelectorAll('[data-order-advance], [data-order-cancel], #vendorCollectionForm button, '
            + '#vendorPaymentConfirmed, #vendorPickupForm input, #vendorPickupForm button').forEach(node => { node.disabled = value; });
    }

    async function updateStatus(order, status, paymentConfirmed = false) {
        const result = await request(orderUrl('update-order.php'), {method: 'POST', body: new URLSearchParams({
            orderId: order.orderId, expectedStatus: order.status, status,
            paymentConfirmed: paymentConfirmed ? '1' : '0', csrfToken: data.csrfToken
        })});
        rememberOrder(result.order);
        return result.order;
    }

    async function updateSelectedOrder(status) {
        if (busy || !selectedOrder) return;
        const order = selectedOrder;
        const currentRequest = detailRequest;
        setBusy(true);
        const message = select('#vendorModalFeedback');
        feedback(message, 'Saving…');
        try {
            const updatedOrder = await updateStatus(order, status);
            if (currentRequest !== detailRequest) return;
            selectedOrder = updatedOrder;
            renderDetails(selectedOrder, select('#vendorOrderModal [data-order-details]'));
            feedback(message, `Order updated: ${statusLabels[selectedOrder.status]}.`);
        } catch (error) {
            if (currentRequest === detailRequest) feedback(message, error.message, true);
        } finally {
            setBusy(false);
        }
    }

    function displayPickup(order) {
        verifiedPickup = order;
        select('#vendorPickupDetails').hidden = false;
        renderDetails(order, select('#vendorPickupDetails [data-order-details]'), true);
        select('#vendorPaymentConfirmed').checked = false;
        select('#vendorPickupTotal').textContent = currency.format(order.total);
        select('#vendorCollectionForm').hidden = order.status !== 'ready';
        const messages = {
            pending: 'This order is still pending. It must be prepared and marked ready before collection.',
            preparing: 'This order is being prepared. Mark it ready before collection.',
            ready: 'Order verified and ready. Check the customer’s order details and payment before handing it over.',
            completed: 'This order has already been collected.', cancelled: 'This order was cancelled and cannot be collected.'
        };
        feedback(select('#vendorPickupFeedback'), messages[order.status]);
    }

    async function verifyPickup() {
        if (busy) return;
        const input = select('#vendorPickupOrderNumber');
        if (!select('#vendorPickupForm').reportValidity()) return;
        verifiedPickup = null;
        select('#vendorPickupDetails').hidden = true;
        select('#vendorCollectionForm').hidden = true;
        setBusy(true);
        feedback(select('#vendorPickupFeedback'), 'Checking order…');
        try {
            const result = await request(orderUrl('order.php', {orderNumber: input.value.trim()}));
            displayPickup(result.order);
            history.replaceState(null, '', orderUrl('pickup.php', {orderNumber: result.order.orderNumber}));
        } catch (error) {
            feedback(select('#vendorPickupFeedback'), error.message, true);
        } finally {
            setBusy(false);
        }
    }

    select('#vendorRefreshOrders')?.addEventListener('click', () => window.location.reload());
    ['#vendorOrderSearchInput', '#vendorHistorySearch'].forEach(selector => select(selector)?.addEventListener('input', renderOrders));
    ['#vendorOrderSort', '#vendorOrderStatusFilter', '#vendorHistoryFilter', '#vendorStallFilter'].forEach(selector => {
        select(selector)?.addEventListener('change', renderOrders);
    });
    select('[data-order-advance]')?.addEventListener('click', () => updateSelectedOrder(nextStatuses[selectedOrder?.status]));
    select('[data-order-cancel]')?.addEventListener('click', () => {
        if (selectedOrder && !busy && confirm(`Cancel unpaid order ${selectedOrder.orderNumber}?`)) updateSelectedOrder('cancelled');
    });
    select('#vendorOrderModal')?.addEventListener('hidden.bs.modal', () => { ++detailRequest; });
    select('#vendorPickupForm')?.addEventListener('submit', event => { event.preventDefault(); verifyPickup(); });
    select('#vendorPickupOrderNumber')?.addEventListener('input', () => {
        verifiedPickup = null;
        select('#vendorPickupDetails').hidden = true;
        select('#vendorCollectionForm').hidden = true;
        feedback(select('#vendorPickupFeedback'), '');
    });
    select('#vendorCollectionForm')?.addEventListener('submit', async event => {
        event.preventDefault();
        if (busy || verifiedPickup?.status !== 'ready' || !event.currentTarget.reportValidity()) return;
        const paymentConfirmed = select('#vendorPaymentConfirmed').checked;
        setBusy(true);
        feedback(select('#vendorPickupFeedback'), 'Saving collection…');
        try {
            displayPickup(await updateStatus(verifiedPickup, 'completed', paymentConfirmed));
            feedback(select('#vendorPickupFeedback'), 'Order marked as collected. The customer can now leave a review.');
        } catch (error) {
            verifiedPickup = null;
            select('#vendorCollectionForm').hidden = true;
            feedback(select('#vendorPickupFeedback'), `${error.message} Verify the order again to continue.`, true);
        } finally {
            setBusy(false);
        }
    });
    select('#vendorSessionLogoutForm')?.addEventListener('submit', () => {
        ['userEmail', 'userFirstName', 'userLastName', 'userFullName', 'userPicture', 'userRole', 'isLoggedIn']
            .forEach(key => localStorage.removeItem(key));
    });

    renderOrders();
    const params = new URLSearchParams(window.location.search);
    if (page === 'pickup' && params.get('orderNumber')) {
        select('#vendorPickupOrderNumber').value = params.get('orderNumber');
        verifyPickup();
    } else if (page !== 'pickup' && (params.get('orderId') || params.get('orderNumber'))) {
        openDetails(params.get('orderId') ? {orderId: params.get('orderId')} : {orderNumber: params.get('orderNumber')});
    }
})();
