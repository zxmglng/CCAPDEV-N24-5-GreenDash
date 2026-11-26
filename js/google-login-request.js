const controllerUrl = new URL('../controller-user/', import.meta.url);

export async function submitGoogleCredential(credential) {
    const tokenResponse = await fetch(new URL('google-login-token.php', controllerUrl), {
        credentials: 'same-origin',
        cache: 'no-store',
    });
    const token = await tokenResponse.json().catch(() => null);
    if (!tokenResponse.ok || token?.success !== true || typeof token.csrfToken !== 'string') {
        throw new Error(token?.message || 'Unable to start sign-in. Please try again.');
    }

    const loginResponse = await fetch(new URL('google-login.php', controllerUrl), {
        method: 'POST',
        credentials: 'same-origin',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({credential, csrfToken: token.csrfToken}),
    });
    const result = await loginResponse.json().catch(() => null);
    if (!loginResponse.ok || result?.success !== true) {
        throw new Error(result?.message || 'Unable to sign in. Please try again.');
    }
    return result;
}
