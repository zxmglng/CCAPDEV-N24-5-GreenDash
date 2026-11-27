<?php
require __DIR__ . '/_bootstrap.php';
try {

    /* if the request method is not GET or POST, throw an exception */
    if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'], true)) {
        header('Allow: GET, POST');
        throw new VendorRequestException('Method not allowed.', 405);
    }

    /* Initialize the password change error message */
    $passwordChangeError = '';
    $passwordHash = $vendor['password_hash'] ?? null;
    $hasLocalPassword = is_string($passwordHash) && $passwordHash !== '';
    $passwordAuthenticatedAt = (int) ($_SESSION['vendor_password_authenticated_at'] ?? 0);
    $hasRecentPasswordAuthentication = $passwordAuthenticatedAt > 0
        && time() - $passwordAuthenticatedAt <= 300;
    $requiresCurrentPassword = $hasLocalPassword && !$hasRecentPasswordAuthentication;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        requireVendorCsrfToken($_POST['csrfToken'] ?? null);    /* check the CSRF token */
        $currentPassword = is_string($_POST['current_password'] ?? null) ? $_POST['current_password'] : ''; /* get the current password from the POST data */
        $newPassword = is_string($_POST['new_password'] ?? null) ? $_POST['new_password'] : ''; /* get the new password from the POST data */
        $confirmPassword = is_string($_POST['confirm_password'] ?? null) ? $_POST['confirm_password'] : ''; /* get the confirm password from the POST data */

        /* Validate the current password, new password, and confirm password */

        /* Accept old plain-text records only for a matching current password; the new password is always stored hashed below. */
        $currentPasswordIsValid = !$requiresCurrentPassword
            || (password_verify($currentPassword, $passwordHash)
                || hash_equals($passwordHash, $currentPassword));

        if (!$currentPasswordIsValid) {
            $passwordChangeError = 'Current password is incorrect.';
            http_response_code(422);
        } 
        
        /* Check if the new password meets the length requirements */
        elseif (strlen($newPassword) < 8 || strlen($newPassword) > 128) {
            $passwordChangeError = 'New password must be between 8 and 128 characters.';
            http_response_code(422);
        } 
        
        /* Check if the new password matches the confirm password */
        elseif ($newPassword !== $confirmPassword) {
            $passwordChangeError = 'New passwords do not match.';
            http_response_code(422);
        } 
        
        /* Check if the new password is different from the current password */
        elseif ($hasLocalPassword
            && ($newPassword === $currentPassword || password_verify($newPassword, $passwordHash))) {
            $passwordChangeError = 'Choose a new password that differs from your current password.';
            http_response_code(422);
        } 
        /* If all validations pass, update the password */
        else {
            $update = $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ? AND user_role = 'vendor'");
            $update->execute([password_hash($newPassword, PASSWORD_DEFAULT), $vendorId]);
            if ($update->rowCount() !== 1) {    /* Check if the update affected exactly one row */
                throw new RuntimeException('Unable to update the vendor password.');
            }
            if (!session_regenerate_id(true)) {    /* Refresh the secure session if the session ID could not be regenerated */
                throw new RuntimeException('Unable to refresh the secure session.');
            }
            unset($_SESSION['vendor_password_authenticated_at']);
            header('Location: profile.php?password_changed=1', true, 303);
            exit;
        }
    }

    $vendorStalls = getVendorStalls($pdo, $vendorId);
    $vendorPage = 'profile';
    define('GREENDASH_VENDOR_PROFILE_VIEW', true);
    require __DIR__ . '/../app/views/vendor/profile.php';
} catch (Throwable $error) {
    vendorControllerError($error);
}
