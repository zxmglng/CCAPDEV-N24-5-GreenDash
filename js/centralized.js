const lucideScript = document.createElement('script');
lucideScript.src = 'https://unpkg.com/lucide@latest';
lucideScript.onload = () => lucide.createIcons();
document.head.appendChild(lucideScript);

const locationStorageKey = 'greendash-location';
const locationSelect = document.querySelector('.location-select');

if (locationSelect) {
    const savedLocation = localStorage.getItem(locationStorageKey);
    if (savedLocation && [...locationSelect.options].some((option) => option.value === savedLocation)) {
        locationSelect.value = savedLocation;
    }

    locationSelect.addEventListener('change', () => {
        localStorage.setItem(locationStorageKey, locationSelect.value);
    });
}

// Cart functionality
const cartButton = document.querySelector('.icon-button');

if (cartButton) {
    cartButton.addEventListener('click', () => {
        // Handle cart button click
    });
}

// Theme functionality
const themeButton = document.querySelector('#eclipse');
if (themeButton) {
    themeButton.addEventListener('click', () => {
        // Handle theme button click
    });
}