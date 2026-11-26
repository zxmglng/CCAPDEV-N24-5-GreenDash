const passwordForm = document.querySelector('.password-form');
const newPassword = document.getElementById('new_password');
const confirmPassword = document.getElementById('confirm_password');
const passwordModal = document.getElementById('passwordModal');

document.getElementById('openPasswordModal')?.addEventListener('click', () => {
    passwordModal?.classList.add('open');
    document.getElementById('current_password')?.focus();
});

passwordModal?.querySelectorAll('[data-close]').forEach(button => {
    button.addEventListener('click', () => passwordModal.classList.remove('open'));
});

document.addEventListener('keydown', event => {
    if (event.key === 'Escape') passwordModal?.classList.remove('open');
});

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
