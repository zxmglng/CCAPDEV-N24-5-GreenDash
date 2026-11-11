// The HTML prototype uses the same server-verified admin login.
if (window.location.pathname.endsWith('.html')) {
    const loginUrl = new URL('../controller-admin/login.php', document.currentScript.src);
    window.location.replace(loginUrl.href);
} else {
    const adminLoginForm = document.getElementById('adminLoginForm');

    adminLoginForm?.addEventListener('submit', (event) => {
        const key = document.getElementById('adminKey');
        const error = document.getElementById('adminLoginError');

        if (!key.value.trim()) {
            event.preventDefault();
            error.textContent = 'Please enter the admin key.';
            key.focus();
        }
        // Nonempty submissions continue to the PHP controller for verification.
    });
}
