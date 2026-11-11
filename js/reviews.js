// Shared API for the order and stall review forms.
(() => {
    const endpoint = new URL('../controller-user/reviews.php', document.currentScript.src);

    async function request(url, options) {
        const response = await fetch(url, options);
        let result;
        try {
            result = await response.json();
        } catch (error) {
            throw new Error('Reviews are currently unavailable. Please try again.');
        }
        if (!response.ok || result.success !== true) {
            throw new Error(result.message || 'Your review could not be saved. Please try again.');
        }
        return result;
    }

    function post(fields) {
        return request(endpoint, {method: 'POST', body: new URLSearchParams(fields)});
    }

    window.GreenDashReviews = {
        load(stallId) {
            const url = new URL(endpoint);
            url.searchParams.set('stallId', stallId);
            return request(url);
        },
        save(orderId, rating, comment, csrfToken) {
            return post({action: 'save', orderId, rating, comment, csrfToken});
        },
        remove(orderId, csrfToken) {
            return post({action: 'delete', orderId, csrfToken});
        },
    };
})();
