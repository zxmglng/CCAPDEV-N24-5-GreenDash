const lucideScript = document.createElement('script');
lucideScript.src = 'https://unpkg.com/lucide@latest';
lucideScript.onload = () => lucide.createIcons();
document.head.appendChild(lucideScript);

// This centralized theme toggle is specifically for the shared dark/light mode across all pages.
const themeStorageKey = 'greendash-theme';
const themeButton = document.querySelector('#eclipse');
const logo = document.querySelector('#logo');
// Replace this placeholder with the actual light-colored logo file for dark mode.
const darkModeLogoPath = '../../assets/main/GreenDash_LOGO_DARK.png';
const lightModeLogoPath = '../../assets/main/GreenDash_LOGO.png';

function applyTheme(theme) {
    document.body.classList.toggle('dark-mode', theme === 'dark');
    if (logo) {
        logo.src = theme === 'dark' ? darkModeLogoPath : lightModeLogoPath;
    }
    if (themeButton) {
        themeButton.setAttribute(
            'aria-label',
            theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'
        );
    }
}

const savedTheme = localStorage.getItem(themeStorageKey) || 'light';
applyTheme(savedTheme);

if (themeButton) {
    themeButton.addEventListener('click', () => {
        const nextTheme = document.body.classList.contains('dark-mode') ? 'light' : 'dark';
        localStorage.setItem(themeStorageKey, nextTheme);
        applyTheme(nextTheme);
    });
}

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

// Admin logout
const adminLogoutButton = document.querySelector('#adminLogoutBtn');

if (adminLogoutButton) {
    // Lock down admin pages: if not signed in, send to the landing page.
    if (sessionStorage.getItem('adminAuthenticated') !== 'true') {
        window.location.href = '../../index.html';
    }

    adminLogoutButton.addEventListener('click', (event) => {
        if (!confirm('Sign out of the admin panel?')) {
            event.preventDefault();
            return;
        }
        sessionStorage.removeItem('adminAuthenticated');
        window.location.href = '../../index.html';
    });
}