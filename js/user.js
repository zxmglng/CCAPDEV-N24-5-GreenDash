function pageRoute(path) {
    // Use prototype HTML pages when this script is running outside the PHP app.
    return window.location.pathname.endsWith('.html')
        ? path.replace(/\.php(?=[?#]|$)/, '.html').replace('../index.html', '../../index.html')
        : path;
}

// Shared by the stall page and used to expand/collapse its menu filters.
if (window.jQuery) {
    window.jQuery('#filterButton').click(function () {
        window.jQuery('#filterPanel').slideToggle();
    });
}

function renderGoogleLoginButton() {
    const googleLoginButton = document.getElementById('googleLoginButton');
    if (!googleLoginButton || !window.google?.accounts?.id) {
        return;
    }

    // Configure Google's sign-in widget to send its credential to our login handler.
    google.accounts.id.initialize({
        client_id: '480778889925-f2akgk8hkv2b6rtoqhc9laag22l21irc.apps.googleusercontent.com',
        callback: handleGoogleLogin,
        hd: 'dlsu.edu.ph'
    });

    google.accounts.id.renderButton(googleLoginButton, {
        theme: 'outline',
        size: 'large',
        text: 'continue_with',
        width: 300
    });
}

const googleIdentityScript = document.querySelector('script[src="https://accounts.google.com/gsi/client"]');
if (window.google?.accounts?.id) {
    renderGoogleLoginButton();
} else {
    // The identity library loads asynchronously, so wait before rendering its button.
    googleIdentityScript?.addEventListener('load', renderGoogleLoginButton, { once: true });
}

function getUserLoginRedirect() {
    // Prefer a page-specific redirect; otherwise choose the directory route for this environment.
    const configuredRedirect = document.getElementById('googleLoginButton')?.dataset.loginRedirect;
    if (configuredRedirect) return configuredRedirect;

    if (/\/user\/[^/]*$/.test(window.location.pathname)) {
        return pageRoute('stalls-directory.php');
    }
    return window.location.pathname.endsWith('.html')
        ? 'pages/user/stalls-directory.html'
        : 'user/stalls-directory.php';
}

// Google verifies the credential in the backend; the browser only handles the response and redirect.
async function handleGoogleLogin(response) {
    try {
        const loginButton = document.getElementById('googleLoginButton');
        const loginEndpoint = loginButton?.dataset.loginEndpoint
            || (window.location.pathname.includes('/controller-user/')
                ? 'google-login.php'
                : 'controller-user/google-login.php');
        const backendResponse = await fetch(loginEndpoint, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({credential: response.credential})
        });

        // Stop on any HTTP or application-level error before changing the current page.
        const result = await backendResponse.json();
        if (!backendResponse.ok || !result.success) {
            alert(result.message || 'Login failed. Please try again.');
            return;
        }
        if (result.needs_role) {
            // New accounts must choose a role before proceeding to the user area.
            window.location.href = 'controller-user/select-role.php';
            return;
        }

        window.location.href = getUserLoginRedirect();
    } catch (error) {
        console.error(error);
        alert('An error occurred during login. Please try again.');
    }
}

$(document).ready(function () {
    let newProfilePic = null;

    // The button switches between edit mode and saving the current form values.
    $('#editProfileBtn').click(function () {
        const button = $(this);
        if (button.text().trim() === 'Edit Profile') {
            $('#firstName, #lastName, #contactNumber').prop('readonly', false);
            $('#profileAvatar').addClass('editable-avatar');
            button.text('Save Changes');
            return;
        }

        const newFirstName = $('#firstName').val().trim();
        const newLastName = $('#lastName').val().trim();
        const newContactNumber = $('#contactNumber').val().trim();

        // Send the edited fields and optional new image to the server-side profile endpoint.
        $.ajax({
            url: 'profile.php',
            type: 'POST',
            data: {
                first_name: newFirstName,
                last_name: newLastName,
                contact_num: newContactNumber,
                profile_image: newProfilePic || ''
            },
            success: function () {
                $('#profileName').text(`${newFirstName} ${newLastName}`);
                $('#firstName, #lastName, #contactNumber').prop('readonly', true);
                $('#profileAvatar').removeClass('editable-avatar');
                button.text('Edit Profile');
                newProfilePic = null;
                alert('Profile updated successfully!');
            },
            error: function () {
                alert('Error updating profile. Please try again.');
            }
        });
    });

    $('#profileAvatar').click(function () {
        // Only allow changing the avatar while the profile form is in edit mode.
        if ($('#editProfileBtn').text().trim() === 'Save Changes') {
            $('#profilePictureInput').click();
        }
    });

    $('#profilePictureInput').change(function () {
        const file = this.files[0];
        if (!file) return;

        // Preview the selected image immediately; it is sent with the profile form on save.
        const reader = new FileReader();
        reader.onload = event => {
            newProfilePic = event.target.result;
            $('#profileAvatar').attr('src', newProfilePic);
        };
        reader.readAsDataURL(file);
    });

    $('#logoutBtn').click(function () {
        window.location.href = 'profile.php?logout=1';
    });
});

$(document).ready(function () {
    const chartCanvas = $('#orderFrequencyChart');
    const profileChartData = $('#profileChartData');
    if (chartCanvas.length && profileChartData.length) {
        // The profile page embeds monthly order counts as JSON for this chart.
        // Skip chart setup on pages that do not contain the profile chart elements.
        const monthlyOrders = JSON.parse(profileChartData.text());
        new Chart(chartCanvas[0].getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Number of Orders',
                    data: monthlyOrders,
                    backgroundColor: '#006937',
                    borderColor: '#006937',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {stepSize: 1}
                    }
                }
            }
        });
    }
});

$(document).ready(function () {
    $('#stallFilterButton').click(function () {
        $('#stallFilterPanel').slideToggle();
    });

    // Filter the directory cards rendered by the PHP controller using their data attributes.
    function filterStalls() {
        // Read all selected criteria once, then apply them together to each stall card.
        const search = $('#searchInput').val().toLowerCase().trim();
        const location = $('#locationFilter').val();
        const openOnly = $('#openOnly').prop('checked');
        const selectedTypes = $('.stall-type-filter:checked').map(function () {
            return $(this).val();
        }).get();
        let visibleStalls = 0;

        $('.stall-card').each(function () {
            const stall = $(this);
            const matchesSearch = stall.data('name').includes(search)
                || stall.data('location').includes(search)
                || stall.data('tags').includes(search);
            const matchesLocation = location === 'all' || stall.data('location') === location;
            const matchesStatus = !openOnly || stall.data('status') === 'open';
            const matchesType = selectedTypes.length === 0
                || selectedTypes.some(type => stall.data('type').includes(type));
            // A stall remains visible only when it matches every active filter.
            const visible = matchesSearch && matchesLocation && matchesStatus && matchesType;
            stall.toggle(visible);
            visibleStalls += Number(visible);
        });

        $('#noStallsFound').prop('hidden', visibleStalls !== 0);
    }

    $('#searchInput').on('input', filterStalls);
    $('#openOnly, .stall-type-filter, #locationFilter').on('change', filterStalls);
    $('#resetStallFilters').click(function () {
        // Reset the controls and re-run the same filtering logic to show all matching stalls.
        $('#searchInput').val('');
        $('#openOnly').prop('checked', false);
        $('.stall-type-filter').prop('checked', false);
        $('#locationFilter').val('all');
        filterStalls();
    });

    $('.closed-store-link').click(function (event) {
        // Closed stalls cannot be opened, so keep the user on the directory page.
        event.preventDefault();
        alert('Sorry, this store is currently closed and cannot be viewed at the moment.');
    });
});
