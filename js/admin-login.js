const ADMIN_KEY = 'greendashN24';
const ADMIN_HOME_URL = 'home.html';
const ADMIN_AUTH_KEY = 'adminAuthenticated';

document.getElementById('adminLoginForm').addEventListener('submit', (event) => {
    event.preventDefault();

    const key = document.getElementById('adminKey').value.trim();
    const error = document.getElementById('adminLoginError');

    if (!key) {
        error.textContent = 'Please enter the admin key.';
        return;
    }

    if (key !== ADMIN_KEY) {
        error.textContent = 'Incorrect admin key. Please try again.';
        document.getElementById('adminKey').value = '';
        document.getElementById('adminKey').focus();
        return;
    }

    // Mark this session as authenticated, then redirect.
    sessionStorage.setItem(ADMIN_AUTH_KEY, 'true');
    window.location.href = ADMIN_HOME_URL;
});