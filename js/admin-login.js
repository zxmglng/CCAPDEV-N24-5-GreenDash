// The HTML prototype uses the same server-verified admin login.
if (window.location.pathname.endsWith('.html')) {
    const loginUrl = new URL('../controller-admin/login.php', document.currentScript.src);
    window.location.replace(loginUrl.href);
} else {
    const adminLoginForm = document.getElementById('adminLoginForm');

    adminLoginForm?.addEventListener('submit', (event) => {
        const email = document.getElementById('email');
        const password = document.getElementById('password');
        const error = document.getElementById('adminLoginError');
        error.textContent = '';
if (!email.value.trim() || !password.value) {
            event.preventDefault();
            error.textContent = 'Please enter your email and password.';
        }
    });
}
