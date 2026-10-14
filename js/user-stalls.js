const stalls = {
    starbucks: {
        name: 'Starbucks',
        location: 'Green Court (Fidel A. Reyes Street)',
        hours: '9:00 AM - 11:00 PM',
        logo: '../../assets/stalls/starbucks.png',
        banner: '../../assets/stalls/starbucks-banner.png',
        menu: [
            ['Java Chip Frappuccino', 210, 'javachip.png'],
            ['Caramel Macchiato', 210, 'caramel.png'],
            ['Pure Matcha Latte', 190, 'matchalatte.png'],
            ['Caffe Americano', 180, 'americano.png']
        ]
    },
    'dairy-queen': {
        name: 'Dairy Queen',
        location: 'Agno',
        hours: '8:00 AM - 5:00 PM',
        logo: '../../assets/stalls/dq.png',
        banner: '../../assets/stalls/starbucks-banner.png',
        menu: [
            ['Classic Blizzard', 179, 'javachip.png'],
            ['Vanilla Cone', 99, 'caramel.png'],
            ['Chocolate Sundae', 129, 'matchalatte.png'],
            ['DQ Freeze', 149, 'americano.png']
        ]
    },
    'belgian-waffles': {
        name: 'The Famous Belgian Waffles',
        location: 'Gokongwei Hall',
        hours: '8:00 AM - 5:00 PM',
        logo: '../../assets/stalls/belgian.png',
        banner: '../../assets/stalls/starbucks-banner.png',
        menu: [
            ['Classic Belgian Waffle', 145, 'javachip.png'],
            ['Chocolate Waffle', 165, 'caramel.png'],
            ['Strawberry Waffle', 175, 'matchalatte.png'],
            ['Cheese Waffle', 155, 'americano.png']
        ]
    },
    'potato-corner': {
        name: 'Potato Corner',
        location: 'Bloemen',
        hours: '8:00 AM - 5:00 PM',
        logo: '../../assets/stalls/potcor.png',
        banner: '../../assets/stalls/starbucks-banner.png',
        menu: [
            ['Regular Fries', 90, 'javachip.png'],
            ['Cheese Fries', 110, 'caramel.png'],
            ['BBQ Fries', 110, 'matchalatte.png'],
            ['Mega Fries', 210, 'americano.png']
        ]
    }
};

const params = new URLSearchParams(window.location.search);
const stall = stalls[params.get('stall')] || stalls.starbucks;

window.scrollTo(0, 0);

function updateOrderDrawerOffset() {
    const header = document.querySelector('header');
    const orderSummary = document.querySelector('.order-summary');
    if (header && orderSummary) {
        const headerBottom = Math.max(0, header.getBoundingClientRect().bottom);
        orderSummary.style.setProperty('--header-height', `${headerBottom}px`);
    }
}

updateOrderDrawerOffset();
window.addEventListener('resize', updateOrderDrawerOffset);
window.addEventListener('scroll', updateOrderDrawerOffset, { passive: true });

document.title = `GreenDash - ${stall.name}`;
document.querySelector('#stallName').textContent = stall.name;
document.querySelector('#stallLocation').textContent = stall.location;
document.querySelector('#stallHours').textContent = stall.hours;

const banner = document.querySelector('#stallBanner');
banner.src = stall.banner;
banner.alt = `Banner for ${stall.name}`;

const logo = document.querySelector('#stallLogo');
logo.src = stall.logo;
logo.alt = `Logo of ${stall.name}`;

const menuItems = document.querySelector('#menuItems');
const cart = [];
let selectedItem = null;
let selectedQuantity = 1;

const modal = document.querySelector('#itemModal');
const modalPrice = document.querySelector('#modalBasePrice');
const addToCartButton = document.querySelector('#addToCartButton');

stall.menu.forEach(([name, price, image]) => {
    const item = document.createElement('div');
    item.className = 'col-12 col-sm-6 col-md-4 col-lg-3 menu-column';
    item.dataset.name = name.toLowerCase();
    item.dataset.price = String(price);
    item.innerHTML = `
        <div class="menu-item">
            <div class="menu-image-container">
                <img src="../../assets/starbucksMenu/${image}" alt="${name}">
                <button type="button" class="add-item-button" aria-label="Add ${name}" data-menu-name="${name}" data-menu-price="${price}" data-menu-image="${image}">+</button>
            </div>
            <h3>${name}</h3>
            <p>from &#8369; ${price}</p>
        </div>`;
    menuItems.appendChild(item);
});

function getCustomizationPrice() {
    const milk = document.querySelector('input[name="milk"]:checked');
    const size = document.querySelector('input[name="size"]:checked');
    return Number(selectedItem.price) + Number(milk?.dataset.price || 0) + Number(size?.dataset.price || 0);
}

function updateModalPrice() {
    const total = getCustomizationPrice() * selectedQuantity;
    modalPrice.textContent = selectedItem.price;
    addToCartButton.innerHTML = `Add to cart - &#8369; ${total}`;
    document.querySelector('#itemQuantity').textContent = selectedQuantity;
}

function openItemModal(item) {
    selectedItem = item;
    selectedQuantity = 1;
    document.querySelector('#modalItemName').textContent = item.name;
    document.querySelector('#modalItemDescription').textContent = `${item.name} prepared fresh and customized to your preference.`;
    const image = document.querySelector('#modalItemImage');
    image.src = `../../assets/starbucksMenu/${item.image}`;
    image.alt = item.name;
    modal.hidden = false;
    updateModalPrice();
}

function renderOrder() {
    const orderItems = document.querySelector('#orderItems');
    if (!cart.length) {
        orderItems.innerHTML = '<p class="empty-order-message">Your cart is empty!</p>';
    } else {
        orderItems.innerHTML = cart.map((item) => `
            <div class="order-item">
                <img src="../../assets/starbucksMenu/${item.image}" alt="${item.name}">
                <div><h3>${item.name}</h3><p>${item.size} · ${item.quantity}</p></div>
                <strong>&#8369; ${item.total}</strong>
            </div>`).join('');
    }
    const total = cart.reduce((sum, item) => sum + item.total, 0);
    document.querySelector('#checkoutButton').innerHTML = `Checkout - &#8369; ${total}`;
}

document.querySelectorAll('.add-item-button').forEach((button) => {
    button.addEventListener('click', () => openItemModal({
        name: button.dataset.menuName,
        price: Number(button.dataset.menuPrice),
        image: button.dataset.menuImage
    }));
});

document.querySelectorAll('input[name="milk"], input[name="size"]').forEach((input) => {
    input.addEventListener('change', updateModalPrice);
});

document.querySelector('#increaseQuantity').addEventListener('click', () => {
    selectedQuantity += 1;
    updateModalPrice();
});

document.querySelector('#decreaseQuantity').addEventListener('click', () => {
    selectedQuantity = Math.max(1, selectedQuantity - 1);
    updateModalPrice();
});

addToCartButton.addEventListener('click', () => {
    const size = document.querySelector('input[name="size"]:checked').value;
    cart.push({
        ...selectedItem,
        size,
        quantity: selectedQuantity,
        total: getCustomizationPrice() * selectedQuantity
    });
    modal.hidden = true;
    renderOrder();
    document.body.classList.add('cart-open');
});

document.querySelector('#modalCloseButton').addEventListener('click', () => {
    modal.hidden = true;
});

modal.addEventListener('click', (event) => {
    if (event.target === modal) modal.hidden = true;
});

document.querySelector('#cart').addEventListener('click', () => {
    updateOrderDrawerOffset();
    document.body.classList.toggle('cart-open');
});

document.querySelector('#closeOrderButton').addEventListener('click', () => {
    document.body.classList.remove('cart-open');
});

document.querySelector('#filterButton').addEventListener('click', () => {
    const panel = document.querySelector('#filterPanel');
    panel.style.display = panel.style.display === 'block' ? 'none' : 'block';
});

document.querySelector('#menuSearch').addEventListener('input', (event) => {
    const search = event.target.value.trim().toLowerCase();
    document.querySelectorAll('.menu-column').forEach((item) => {
        item.hidden = !item.dataset.name.includes(search);
    });
});

document.querySelector('#priceRange').addEventListener('input', (event) => {
    document.querySelector('#priceValue').textContent = event.target.value;
    document.querySelectorAll('.menu-column').forEach((item) => {
        item.hidden = Number(item.dataset.price) > Number(event.target.value);
    });
});

document.querySelector('#resetFilters').addEventListener('click', () => {
    document.querySelector('#priceRange').value = 1000;
    document.querySelector('#priceValue').textContent = '1000';
    document.querySelector('#menuSearch').value = '';
    document.querySelectorAll('.menu-column').forEach((item) => {
        item.hidden = false;
    });
});
