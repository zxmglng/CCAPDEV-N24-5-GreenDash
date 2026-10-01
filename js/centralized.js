const lucideScript = document.createElement('script');
lucideScript.src = 'https://unpkg.com/lucide@latest';
lucideScript.onload = () => lucide.createIcons();
document.head.appendChild(lucideScript);

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