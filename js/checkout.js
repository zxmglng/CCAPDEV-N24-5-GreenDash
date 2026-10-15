const checkoutStorageKey = 'greendash-checkout';
const checkoutData = JSON.parse(sessionStorage.getItem(checkoutStorageKey) || 'null');

function formatStoredPickupTime(pickupTime) {
    if (!/^\d+-\d+$/.test(pickupTime)) {
        return pickupTime;
    }

    function formatMinutes(minutes) {
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

    const [start, end] = pickupTime.split('-').map(Number);
    return `${formatMinutes(start)} - ${formatMinutes(end)}`;
}

function parseTime(time) {
    const match = time.match(/(\d+):(\d+)\s*(AM|PM)/i);
    if (!match) {
        return null;
    }

    let hour = Number(match[1]);
    const minute = Number(match[2]);
    const period = match[3].toUpperCase();

    if (period === 'PM' && hour !== 12) {
        hour += 12;
    }

    if (period === 'AM' && hour === 12) {
        hour = 0;
    }

    return hour * 60 + minute;
}

function getPickupTimes(hours) {
    const [opening, closing] = hours.split(/\s*-\s*/).map(parseTime);
    if (opening === null || closing === null) {
        return [];
    }

    const pickupTimes = [];
    for (let start = opening; start < closing; start += 15) {
        pickupTimes.push(formatStoredPickupTime(`${start}-${start + 15}`));
    }
    return pickupTimes;
}

function formatMilk(milk) {
    if (milk === 'Oatmilk') {
        return 'Oat milk';
    }

    return milk || 'No milk customization';
}

if (!checkoutData || !checkoutData.items || checkoutData.items.length === 0) {
    window.location.href = 'stalls-directory.html';
} else {
    document.querySelector('#checkoutStallLogo').src = checkoutData.stall.logo;
    document.querySelector('#checkoutStallLogo').alt = `Logo of ${checkoutData.stall.name}`;
    document.querySelector('#checkoutStallName').textContent = checkoutData.stall.name;
    document.querySelector('#checkoutStallLocation').textContent = checkoutData.stall.location;
    document.querySelector('#checkoutStallHours').textContent = checkoutData.stall.hours;
    const checkoutPickupTime = document.querySelector('#checkoutPickupTime');
    const pickupNowOption = document.createElement('option');
    pickupNowOption.value = 'now';
    pickupNowOption.textContent = 'Pick up now';
    checkoutPickupTime.appendChild(pickupNowOption);

    getPickupTimes(checkoutData.stall.hours).forEach((pickupTime) => {
        const pickupOption = document.createElement('option');
        pickupOption.value = pickupTime;
        pickupOption.textContent = pickupTime;
        checkoutPickupTime.appendChild(pickupOption);
    });
    document.querySelector('#checkoutTotal').textContent = `₱${checkoutData.total}`;

    document.querySelector('#checkoutItems').innerHTML = checkoutData.items.map((item) => `
        <div class="checkout-order-item">
            <img src="../../assets/starbucksMenu/${item.image}" alt="${item.name}">
            <div>
                <h3>${item.name}</h3>
                <p>${item.size || 'Regular'}</p>
                <p>${formatMilk(item.milk)}</p>
                <p>Quantity: ${item.quantity}</p>
            </div>
            <strong>₱${item.total}</strong>
        </div>`).join('');

    document.querySelectorAll('input[name="payment"]').forEach((paymentOption) => {
        paymentOption.addEventListener('change', () => {
            const onlinePayment = document.querySelector('input[name="payment"][value="online"]').checked;
            document.querySelector('#onlinePaymentFields').hidden = !onlinePayment;
            document.querySelector('#referenceNumber').required = onlinePayment;
            document.querySelector('.qr-placeholder').hidden = !onlinePayment;
        });
    });

    document.querySelector('#checkoutForm').addEventListener('submit', (event) => {
        event.preventDefault();
        const onlinePayment = document.querySelector('input[name="payment"][value="online"]').checked;
        const referenceNumber = document.querySelector('#referenceNumber').value.trim();

        if (onlinePayment && !referenceNumber) {
            showCheckoutMessage('please input a reference number.');
            document.querySelector('#referenceNumber').focus();
            return;
        }

        sessionStorage.removeItem(checkoutStorageKey);
        showCheckoutMessage('Order submitted! Your order ID is 6767HI.', true);
    });

    document.querySelector('#closeCheckoutMessage').addEventListener('click', () => {
        const message = document.querySelector('#checkoutMessage');
        const isOrderSubmitted = message.dataset.orderSubmitted === 'true';
        message.hidden = true;
        delete message.dataset.orderSubmitted;

        if (isOrderSubmitted) {
            window.location.href = 'orders.html';
        }
    });

    function showCheckoutMessage(text, isOrderSubmitted) {
        const message = document.querySelector('#checkoutMessage');
        document.querySelector('#checkoutMessageTitle').textContent = text;
        message.dataset.orderSubmitted = isOrderSubmitted ? 'true' : 'false';
        message.hidden = false;
    }
}
