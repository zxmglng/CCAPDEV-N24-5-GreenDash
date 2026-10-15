const VENDOR_KEYS = {
    email:     'userEmail',
    firstName: 'userFirstName',
    lastName:  'userLastName',
    fullName:  'userFullName',
    picture:   'userPicture',
    role:      'userRole',
    loggedIn:  'isLoggedIn'
};

const VENDOR_LOGIN_URL = 'login.html';

// LOGIN PAGE: GOOGLE BUTTON

// Renders the Google Sign-In button on the vendor login page
function initVendorGoogleLogin() {
    const button = document.getElementById('googleLoginButton');
    if (!button || !window.google) return;

    google.accounts.id.initialize({
        client_id: '480778889925-f2akgk8hkv2b6rtoqhc9laag22l21irc.apps.googleusercontent.com',
        callback: handleVendorGoogleLogin,
        hd: 'dlsu.edu.ph'
    });

    google.accounts.id.renderButton(button, {
        theme: 'outline',
        size: 'large',
        text: 'continue_with',
        width: 300
    });
}

// Runs after a successful Google sign-in
// Decodes the JWT and validate email domain
// Store account in localStorage
function handleVendorGoogleLogin(response) {
    let payload;
    try {
        payload = JSON.parse(atob(response.credential.split('.')[1]));
    } catch (error) {
        console.error('Unable to read the Google credential.', error);
        alert('Sign in failed. Please try again.');
        return;
    }

    if (!payload.email || !payload.email.endsWith('@dlsu.edu.ph')) {
        alert('Please sign in with your DLSU Google account (@dlsu.edu.ph).');
        return;
    }

    localStorage.setItem(VENDOR_KEYS.email, payload.email);
    localStorage.setItem(VENDOR_KEYS.firstName, payload.given_name || '');
    localStorage.setItem(VENDOR_KEYS.lastName, payload.family_name || '');
    localStorage.setItem(VENDOR_KEYS.fullName, payload.name || '');
    localStorage.setItem(VENDOR_KEYS.role, 'Vendor');
    localStorage.setItem(VENDOR_KEYS.loggedIn, 'true');

    if (payload.picture) {
        localStorage.setItem(VENDOR_KEYS.picture, payload.picture);
    } else {
        localStorage.removeItem(VENDOR_KEYS.picture);
    }

    window.location.href = getVendorRedirectTarget();
}

// Picks where to send the vendor after login
function getVendorRedirectTarget() {
    const params = new URLSearchParams(window.location.search);
    const fromQuery = params.get('redirect');
    const fallback = document.getElementById('googleLoginButton')
        ?.dataset.loginRedirect || 'profile.html';

    if (!fromQuery) return fallback;
    if (fromQuery.startsWith('//') || fromQuery.includes('://')) return fallback;
    return fromQuery;
}

// DATA ACCESSORS
// Returns the signed-in vendor's account data
function getVendorData() {
    return {
        email:      localStorage.getItem(VENDOR_KEYS.email) || '',
        firstName:  localStorage.getItem(VENDOR_KEYS.firstName) || '',
        lastName:   localStorage.getItem(VENDOR_KEYS.lastName) || '',
        fullName:   localStorage.getItem(VENDOR_KEYS.fullName) || '',
        picture:    localStorage.getItem(VENDOR_KEYS.picture) || '',
        role:       localStorage.getItem(VENDOR_KEYS.role) || '',
        isLoggedIn: localStorage.getItem(VENDOR_KEYS.loggedIn) === 'true'
    };
}

// Removes stored vendor keys
function clearVendorData() {
    Object.values(VENDOR_KEYS).forEach((key) => localStorage.removeItem(key));
}

// Updates every .vendor-greeting element on the page to "Hello, <name>!"
// Does nothing if the user isn't signed in, so the HTML placeholder stays.
function populateVendorGreeting() {
    const { firstName, fullName } = getVendorData();
    const displayName = firstName || (fullName ? fullName.split(' ')[0] : '');
    if (!displayName) return;

    document.querySelectorAll('.vendor-greeting').forEach((el) => {
        el.textContent = `Hello, ${displayName}!`;
    });
}

// Fills the profile page's account detail rows
function populateVendorProfile() {
    const { 
        fullName, email, picture, role 
    } = getVendorData();

    const nameEl = document.getElementById('vendorProfileName');
    if (nameEl && fullName) nameEl.textContent = fullName;

    const emailEl = document.getElementById('vendorProfileEmail');
    if (emailEl && email) emailEl.textContent = email;

    const roleEl = document.getElementById('vendorProfileRole');
    if (roleEl && role) roleEl.textContent = role;

    const avatarEl = document.getElementById('vendorProfileAvatar');
    if (avatarEl && picture) {
        avatarEl.src = picture;
        avatarEl.hidden = false;
    }
}

// LOGOUT
function initVendorLogout() {
    const button = document.getElementById('vendorLogoutBtn');
    if (!button) return;

    button.addEventListener('click', () => {
        if (!confirm('Sign out of GreenDash?')) return;
        clearVendorData();
        window.location.href = VENDOR_LOGIN_URL;
    });
}

document.addEventListener('DOMContentLoaded', () => {
    populateVendorGreeting();
    populateVendorProfile();
    initVendorLogout();
});

window.addEventListener('load', () => {
    if (document.getElementById('googleLoginButton')) {
        initVendorGoogleLogin();
    }
});