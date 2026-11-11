const checkoutStorageKey = 'greendash-checkout';
const checkoutApplicationUrl = new URL('../', document.currentScript.src);
const checkoutForm = document.querySelector('#checkoutForm');
const pageExtension = window.location.pathname.endsWith('.html') ? '.html' : '.php';
let checkoutData = null;
try {
    checkoutData = JSON.parse(sessionStorage.getItem(checkoutStorageKey) || 'null');
} catch (error) {
    console.error('Unable to read your cart.', error);
}

function formatStoredPickupTime(pickupTime) {
    if (!/^\d+-\d+$/.test(pickupTime)) return pickupTime;
    const formatMinutes = (minutes) => {
        const hour = Math.floor(minutes / 60) % 24;
        return `${hour % 12 || 12}:${String(minutes % 60).padStart(2, '0')} ${hour >= 12 ? 'PM' : 'AM'}`;
    };
    const [start, end] = pickupTime.split('-').map(Number);
    return `${formatMinutes(start)} - ${formatMinutes(end)}`;
}

function parseTime(time) {
    const match = time.match(/^(\d{1,2}):(\d{2})(?::\d{2})?\s*(AM|PM)?$/i);
    if (!match) return null;
    let hour = Number(match[1]);
    const minute = Number(match[2]);
    if (minute > 59 || (match[3] ? hour < 1 || hour > 12 : hour > 23)) return null;
    if (match[3]) hour = hour % 12 + (match[3].toUpperCase() === 'PM' ? 12 : 0);
    return hour * 60 + minute;
}

function getPickupTimes(hours) {
    if (typeof hours !== 'string') return [];
    const parts = hours.split(/\s*-\s*/);
    if (parts.length !== 2) return [];
    const opening = parseTime(parts[0]);
    let closing = parseTime(parts[1]);
    if (opening === null || closing === null) return [];
    if (closing <= opening) closing += 1440;
    const times = [];
    for (let start = opening; start + 15 <= closing; start += 15) {
        times.push(formatStoredPickupTime(`${start}-${start + 15}`));
    }
    return times;
}

function checkoutImageUrl(path) {
    if (!path) return '';
    const marker = path.indexOf('assets/');
    if (marker !== -1) return new URL(path.slice(marker), checkoutApplicationUrl).href;
    if (/^(https?:|data:|\/)/i.test(path)) return new URL(path, checkoutApplicationUrl).href;
    return new URL(`assets/starbucksMenu/${path}`, checkoutApplicationUrl).href;
}

function appendCheckoutText(parent, tag, text) {
    const element = document.createElement(tag);
    element.textContent = text;
    parent.appendChild(element);
    return element;
}

if (!checkoutData?.stall || !Array.isArray(checkoutData.items) || checkoutData.items.length === 0) {
    window.location.href = checkoutForm?.dataset.directoryUrl || `stalls-directory${pageExtension}`;
} else {
    const logo = document.querySelector('#checkoutStallLogo');
    logo.src = checkoutImageUrl(checkoutData.stall.logo);
    logo.alt = `Logo of ${checkoutData.stall.name}`;
    document.querySelector('#checkoutStallName').textContent = checkoutData.stall.name;
    document.querySelector('#checkoutStallLocation').textContent = checkoutData.stall.location;
    document.querySelector('#checkoutStallHours').textContent = checkoutData.stall.hours;

    const pickupSelect = document.querySelector('#checkoutPickupTime');
    const pickupTimes = Array.isArray(checkoutData.stall.pickupTimes)
        ? checkoutData.stall.pickupTimes : getPickupTimes(checkoutData.stall.hours);
    for (const value of ['now', ...pickupTimes]) {
        const option = document.createElement('option');
        option.value = value;
        option.textContent = value === 'now' ? 'Pick up now' : value;
        pickupSelect.appendChild(option);
    }
    const savedPickupTime = formatStoredPickupTime(checkoutData.pickupTime || 'now');
    if ([...pickupSelect.options].some(option => option.value === savedPickupTime)) {
        pickupSelect.value = savedPickupTime;
    }
    pickupSelect.addEventListener('change', () => {
        checkoutData.pickupTime = pickupSelect.value;
        sessionStorage.setItem(checkoutStorageKey, JSON.stringify(checkoutData));
    });

    if (!/^[a-f0-9]{32}$/.test(checkoutData.requestId || '')) {
        checkoutData.requestId = Array.from(crypto.getRandomValues(new Uint8Array(16)),
            byte => byte.toString(16).padStart(2, '0')).join('');
        sessionStorage.setItem(checkoutStorageKey, JSON.stringify(checkoutData));
    }
    const itemsContainer = document.querySelector('#checkoutItems');
    itemsContainer.replaceChildren();
    checkoutData.items.forEach(item => {
        const row = document.createElement('div');
        row.className = 'checkout-order-item';
        const image = document.createElement('img');
        image.src = checkoutImageUrl(item.image);
        image.alt = item.name;
        image.hidden = !item.image;
        row.appendChild(image);
        const details = document.createElement('div');
        appendCheckoutText(details, 'h3', item.name);
        appendCheckoutText(details, 'p', item.size || 'Regular');
        appendCheckoutText(details, 'p', item.milk === 'Oatmilk' ? 'Oat milk' : item.milk || 'No milk customization');
        appendCheckoutText(details, 'p', `Quantity: ${item.quantity}`);
        row.appendChild(details);
        appendCheckoutText(row, 'strong', `₱${Number(item.total).toFixed(2)}`);
        itemsContainer.appendChild(row);
    });
    document.querySelector('#checkoutTotal').textContent = `₱${Number(checkoutData.total).toFixed(2)}`;

    const paymentOptions = document.querySelectorAll('input[name="paymentMethod"], input[name="payment"]');
    function selectedPaymentMethod() {
        return [...paymentOptions].find(option => option.checked)?.value;
    }
    paymentOptions.forEach(option => option.addEventListener('change', () => {
        const online = selectedPaymentMethod() === 'online';
        document.querySelector('#onlinePaymentFields').hidden = !online;
        document.querySelector('#referenceNumber').required = online;
        document.querySelector('.qr-placeholder').hidden = !online;
    }));

    let isSubmitting = false;
    let isSubmitted = false;
    checkoutForm.addEventListener('submit', async event => {
        event.preventDefault();
        if (isSubmitting || isSubmitted) return;
        const paymentMethod = selectedPaymentMethod();
        const referenceNumber = document.querySelector('#referenceNumber').value.trim();
        if (paymentMethod === 'online' && !referenceNumber) {
            showCheckoutMessage('Please enter a payment reference number.');
            document.querySelector('#referenceNumber').focus();
            return;
        }
        if (!Number.isSafeInteger(Number(checkoutData.stall.id)) || Number(checkoutData.stall.id) <= 0
            || checkoutData.items.some(item => !Number.isSafeInteger(Number(item.productId)) || Number(item.productId) <= 0)) {
            showCheckoutMessage('Your cart is out of date. Please reopen the stall menu and add your items again.');
            return;
        }
        if (!checkoutForm.querySelector('input[name="csrfToken"]')) {
            window.location.href = new URL('controller-user/checkout.php', checkoutApplicationUrl).href;
            return;
        }
        const orderItems = checkoutData.items.map(item => ({
            productId: item.productId, quantity: item.quantity,
            milk: item.milk ?? null, size: item.size ?? null,
            specialInstructions: item.specialInstructions || '',
        }));
        document.querySelector('#checkoutStallId').value = checkoutData.stall.id;
        document.querySelector('#checkoutOrderItems').value = JSON.stringify(orderItems);
        document.querySelector('#checkoutRequestId').value = checkoutData.requestId;
        const fields = new FormData(checkoutForm);
        fields.set('paymentMethod', paymentMethod || '');
        fields.set('referenceNumber', referenceNumber);
        fields.set('pickupTime', pickupSelect.value);
        const submitButton = checkoutForm.querySelector('button[type="submit"]');
        isSubmitting = true;
        submitButton.disabled = true;
        submitButton.textContent = 'Submitting…';
        try {
            const response = await fetch(checkoutForm.action, {method: 'POST', body: fields});
            const result = await response.json();
            if (!response.ok || result.success !== true) {
                showCheckoutMessage(result.message || 'Your order could not be saved. Please try again.');
                return;
            }
            if (typeof result.orderNumber !== 'string' || !result.orderNumber
                || !Number.isFinite(Number(result.totalAmount))) {
                throw new Error('Invalid order receipt.');
            }
            isSubmitted = true;
            sessionStorage.removeItem(checkoutStorageKey);
            document.querySelector('#checkoutTotal').textContent = `₱${Number(result.totalAmount).toFixed(2)}`;
            document.querySelector('#checkoutOrderNumber').textContent = `ORDER ID: ${result.orderNumber}`;
            showCheckoutMessage(`Order submitted! Your order ID is ${result.orderNumber}. Total: ₱${Number(result.totalAmount).toFixed(2)}.`, true);
        } catch (error) {
            console.error('Checkout submission failed.', error);
            showCheckoutMessage('Unable to confirm your order. Your cart has been kept; please try again.');
        } finally {
            isSubmitting = false;
            submitButton.disabled = isSubmitted;
            submitButton.textContent = isSubmitted ? 'Order submitted' : 'Submit order';
        }
    });

    document.querySelector('#closeCheckoutMessage').addEventListener('click', () => {
        document.querySelector('#checkoutMessage').hidden = true;
        if (isSubmitted) window.location.href = checkoutForm.dataset.ordersUrl || `orders${pageExtension}`;
    });
    function showCheckoutMessage(text, submitted = false) {
        const message = document.querySelector('#checkoutMessage');
        document.querySelector('#checkoutMessageTitle').textContent = text;
        message.dataset.orderSubmitted = String(submitted);
        message.hidden = false;
    }
}
