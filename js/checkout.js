const checkoutStorageKey = 'greendash-checkout';        // key used to store checkout data in sessionStorage
const checkoutApplicationUrl = new URL('../', document.currentScript.src);  // base URL of the application, used to resolve relative paths for images
const checkoutForm = document.querySelector('#checkoutForm');   // form element for submitting the checkout data
const pageExtension = window.location.pathname.endsWith('.html') ? '.html' : '.php';  // file extension used for page URLs, based on the current page's URL
let checkoutData = null;    // object containing the checkout data, including stall info, items, and pickup time

// Attempt to read the checkout data from sessionStorage and parse it as JSON.
// if parsing fails, log an error and set checkoutData to null.
try {
    checkoutData = JSON.parse(sessionStorage.getItem(checkoutStorageKey) || 'null');
} catch (error) {
    console.error('Unable to read your cart.', error);
}

// Convert the selected stall's pickup schedule into options and keep changes with the cart.
// format a stored pickup time string (e.g., "540-600") into a human-readable format (e.g., "9:00 AM - 10:00 AM")
function formatStoredPickupTime(pickupTime) {
    if (!/^\d+-\d+$/.test(pickupTime)) return pickupTime;   // if the pickup time string does not match the expected format, return it as-is
    const formatMinutes = (minutes) => {
        const hour = Math.floor(minutes / 60) % 24; // convert the total minutes into hours (0-23)
        return `${hour % 12 || 12}:${String(minutes % 60).padStart(2, '0')} ${hour >= 12 ? 'PM' : 'AM'}`;   // format the hour and minute into a string with AM/PM notation
    };
    const [start, end] = pickupTime.split('-').map(Number);
    return `${formatMinutes(start)} - ${formatMinutes(end)}`;
}

// parse a time string (e.g., "9:00 AM") into the number of minutes since midnight.
function parseTime(time) {
    const match = time.match(/^(\d{1,2}):(\d{2})(?::\d{2})?\s*(AM|PM)?$/i);     // match the time string against a regular expression to extract the hour, minute, and optional AM/PM indicator
    if (!match) return null;    // if the time string does not match the expected format, return null
    let hour = Number(match[1]);    // convert the hour string to a number
    const minute = Number(match[2]);    // convert the minute string to a number
    if (minute > 59 || (match[3] ? hour < 1 || hour > 12 : hour > 23)) return null; // if the minute is greater than 59, or if the hour is out of range (1-12 for AM/PM, 0-23 for 24-hour format), return null
    if (match[3]) hour = hour % 12 + (match[3].toUpperCase() === 'PM' ? 12 : 0);    // if the time string includes an AM/PM indicator, convert the hour to 24-hour format
    return hour * 60 + minute;  // return the total number of minutes since midnight
}

// generate an array of pickup time slots (in 15-minute intervals) based on the stall's operating hours string (e.g., "9:00 AM - 5:00 PM").
function getPickupTimes(hours) {
    if (typeof hours !== 'string') return [];   // if the hours parameter is not a string, return an empty array
    const parts = hours.split(/\s*-\s*/);   // split the hours string into two parts (opening and closing times)
    if (parts.length !== 2) return [];  // if the hours string does not contain exactly two parts, return an empty array
    const opening = parseTime(parts[0]);    // parse the opening time string into the number of minutes since midnight
    let closing = parseTime(parts[1]);  // parse the closing time string into the number of minutes since midnight
    if (opening === null || closing === null) return [];    // if either the opening or closing time could not be parsed, return an empty array
    if (closing <= opening) closing += 1440;    // if the closing time is earlier than or equal to the opening time, assume the stall closes after midnight and add 24 hours (1440 minutes) to the closing time
    const times = [];   // initialize an empty array to hold the pickup time slots
    for (let start = opening; start + 15 <= closing; start += 15) { // iterate through the time range in 15-minute intervals, starting from the opening time and ending before the closing time
        times.push(formatStoredPickupTime(`${start}-${start + 15}`));   // format the start and end times of the current interval into a human-readable string and add it to the times array
    }
    return times;
}

// generate a full URL for an image path, based on the checkout application's base URL
function checkoutImageUrl(path) {
    if (!path) return '';   // if the path parameter is falsy (e.g., null, undefined, or an empty string), return an empty string
    const marker = path.indexOf('assets/'); // find the index of the "assets/" substring in the path parameter
    if (marker !== -1) return new URL(path.slice(marker), checkoutApplicationUrl).href; // if the "assets/" substring is found, create a new URL using the substring starting from "assets/" and return its href
    if (/^(https?:|data:|\/)/i.test(path)) return new URL(path, checkoutApplicationUrl).href;   // if the path parameter starts with "http:", "https:", "data:", or "/", create a new URL using the entire path and return its href
    return new URL(`assets/starbucksMenu/${path}`, checkoutApplicationUrl).href;    // if the path parameter does not match any of the previous conditions, create a new URL using the "assets/starbucksMenu/" prefix and return its href
}

// append a new element with the specified tag and text content to the parent element, and return the newly created element
function appendCheckoutText(parent, tag, text) {
    const element = document.createElement(tag);    // create a new element with the specified tag name
    element.textContent = text;     // set the text content of the new element to the specified text
    parent.appendChild(element);    // append the new element to the parent element
    return element;
}

// Render the checkout only when the previous page supplied a stall and at least one cart item.
if (!checkoutData?.stall || !Array.isArray(checkoutData.items) || checkoutData.items.length === 0) {    // if the checkout data is missing or invalid (e.g., no stall info or no items), redirect the user to the stalls directory page
    window.location.href = checkoutForm?.dataset.directoryUrl || `stalls-directory${pageExtension}`;
} else {    // if the checkout data is valid, populate the checkout page with the stall and order information
    // Fill stall details and pickup slots before the user submits the order.
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
        // Reuse this ID on retries so the server can recognize duplicate submissions.
        checkoutData.requestId = Array.from(crypto.getRandomValues(new Uint8Array(16)),
            byte => byte.toString(16).padStart(2, '0')).join('');
        sessionStorage.setItem(checkoutStorageKey, JSON.stringify(checkoutData));
    }
    // Build the order summary from cart data; the server will independently verify its prices.
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
        for (const option of item.options || []) {
            appendCheckoutText(details, 'p', `${option.groupName}: ${option.optionName}`);
        }
        appendCheckoutText(details, 'p', `Quantity: ${item.quantity}`);
        row.appendChild(details);
        appendCheckoutText(row, 'strong', `₱${Number(item.total).toFixed(2)}`);
        itemsContainer.appendChild(row);
    });
    document.querySelector('#checkoutTotal').textContent = `₱${Number(checkoutData.total).toFixed(2)}`;

    // Offer QR payment only when the selected stall has a QR image.
    const paymentOptions = document.querySelectorAll('input[name="paymentMethod"], input[name="payment"]');
    const onlinePaymentOption = document.querySelector('.online-payment-option');
    const onlinePaymentInput = document.querySelector('input[name="paymentMethod"][value="online"]');
    const qrCode = document.querySelector('.stall-qr-code');
    const qrCodePath = checkoutData.stall.qrCodePath;
    if (qrCodePath) {
        qrCode.src = checkoutImageUrl(qrCodePath);
        qrCode.hidden = true;
    } else {
        onlinePaymentOption.hidden = true;
        onlinePaymentInput.disabled = true;
    }

    // Return the currently selected payment method, or undefined if none is selected.
    function selectedPaymentMethod() {
        return [...paymentOptions].find(option => option.checked)?.value;
    }
    // Show or hide the online payment fields when the user selects a payment method.
    paymentOptions.forEach(option => option.addEventListener('change', () => {
        const online = selectedPaymentMethod() === 'online';
        document.querySelector('#onlinePaymentFields').hidden = !online;
        document.querySelector('#referenceNumber').required = online;
        qrCode.hidden = !online || !qrCodePath;
    }));

    // Handle the checkout form submission, including validation, sending the data to the server, and displaying a confirmation message or error.
    let isSubmitting = false;
    let isSubmitted = false;
    // Prepare the cart for the POST, then show the server-created receipt on success.
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
            options: (item.options || []).map(option => ({
                groupId: option.groupId, optionId: option.optionId,
            })),
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
            // The server recalculates prices and validates the submitted IDs before saving.
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
