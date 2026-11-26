const adminSignupForm = document.getElementById('adminSignupForm');

adminSignupForm?.addEventListener('submit', (event) => {
    const firstName = document.getElementById('firstName');
    const lastName  = document.getElementById('lastName');
    const email     = document.getElementById('email');
    const password  = document.getElementById('password');
    const confirm   = document.getElementById('confirmPassword');
    const key       = document.getElementById('adminKey');
    const error     = document.getElementById('adminSignupError');
    error.textContent = '';

    if (!firstName.value.trim() || !lastName.value.trim()
        || !email.value.trim() || !key.value.trim()) {
        event.preventDefault();
        error.textContent = 'Please fill in all fields.';
        return;
    }
    if (password.value.length < 8) {
        event.preventDefault();
        error.textContent = 'Password must be at least 8 characters.';
        password.focus();
        return;
    }
    if (password.value !== confirm.value) {
        event.preventDefault();
        error.textContent = 'Passwords do not match.';
        confirm.focus();
    }
});