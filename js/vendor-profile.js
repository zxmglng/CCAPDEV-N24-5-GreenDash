const passwordForm = document.querySelector('.password-form');
const newPassword = document.getElementById('new_password');
const confirmPassword = document.getElementById('confirm_password');

if (passwordForm && newPassword && confirmPassword) {
    const validatePasswordMatch = () => {
        confirmPassword.setCustomValidity(
            confirmPassword.value && confirmPassword.value !== newPassword.value
                ? 'New passwords do not match.'
                : ''
        );
    };

    newPassword.addEventListener('input', validatePasswordMatch);
    confirmPassword.addEventListener('input', validatePasswordMatch);
    passwordForm.addEventListener('submit', validatePasswordMatch);
}
