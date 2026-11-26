<?php
// models/user_model.php - simple user/admin functions.

function getCheckoutUser(PDO $pdo, int $userId): ?array
{
    // Minimal account lookup used to confirm the user is still active.
    $stmt = $pdo->prepare('SELECT user_id, acc_status FROM users WHERE user_id = ?');
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

// Return null when the user ID has no matching account.
function getUserById(PDO $pdo, int $userId): ?array
{
    $stmt = $pdo->prepare("
        SELECT *
        FROM users
        WHERE user_id = ?
        LIMIT 1
    ");

    $stmt->execute([$userId]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

// Used by Google login to find an existing account before creating one.
function getUserByEmail(PDO $pdo, string $email): ?array
{
    $stmt = $pdo->prepare("
        SELECT *
        FROM users
        WHERE email_add = ?
        LIMIT 1
    ");

    $stmt->execute([$email]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

// Create the local account record from verified Google profile details.
function createGoogleUser(PDO $pdo, string $email, string $firstName, string $lastName, ?string $profileImage): int
{
    $stmt = $pdo->prepare("
        INSERT INTO users (email_add, first_name, last_name, profile_image)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([
        $email,
        $firstName,
        $lastName,
        $profileImage
    ]);
    return (int) $pdo->lastInsertId();
}

// Create a new vendor account with the given details. The account is pending until an admin approves it.
function createVendorAccount(PDO $pdo, string $email, string $firstName, string $lastName, string $password): int
{
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO users (email_add, first_name, last_name, password_hash, user_role, acc_status)
        VALUES (?, ?, ?, ?, 'vendor', 'pending')
    ");
    $stmt->execute([$email, $firstName, $lastName, $passwordHash]);
    return (int) $pdo->lastInsertId();
}

// Save profile fields edited by the authenticated user.
function updateUserProfile(PDO $pdo, int $userId, string $firstName, string $lastName, ?string $contactNum, ?string $profileImage): bool
{
    $stmt = $pdo->prepare("
        UPDATE users
        SET first_name = ?,
            last_name = ?,
            contact_num = ?,
            profile_image = COALESCE(?, profile_image)
        WHERE user_id = ?
    ");

    return $stmt->execute([
        $firstName,
        $lastName,
        $contactNum,
        $profileImage,
        $userId
    ]);
}

// Persist the role selected after the user's first Google sign-in.
function updateUserRole(PDO $pdo, int $userId, string $role): bool
{
    $stmt = $pdo->prepare("
        UPDATE users
        SET user_role = ?
        WHERE user_id = ?
    ");

    return $stmt->execute([$role, $userId]);
}

// Active and inactive accounts remain visible in the Users Directory.
// Pending vendors are listed separately on the Activate Vendors page.
function get_users()
{
    global $pdo;

    return $pdo->query(
        "SELECT user_id, email_add, first_name, last_name, contact_num, user_role, acc_status, created_at
         FROM users
         WHERE acc_status IN ('active', 'inactive')
         ORDER BY last_name, first_name"
    )->fetchAll(PDO::FETCH_ASSOC);
}

// All pending vendor accounts, for the Activate Vendors page.
function getPendingVendors(PDO $pdo): array
{
    return $pdo->query(
        "SELECT user_id, email_add, first_name, last_name
         FROM users
         WHERE user_role = 'vendor' AND acc_status = 'pending'
         ORDER BY created_at, user_id"
    )->fetchAll(PDO::FETCH_ASSOC);
}

// Activate a pending vendor account. Returns true if the account was activated,
// false if it was already active or not a vendor.
function activateVendor(PDO $pdo, int $userId): bool
{
    $stmt = $pdo->prepare(
        "UPDATE users
         SET acc_status = 'active'
         WHERE user_id = ? AND user_role = 'vendor' AND acc_status = 'pending'"
    );
    $stmt->execute([$userId]);
    return $stmt->rowCount() === 1;
}

// Vendor dropdown for the Add Stall form.
function get_vendors()
{
    global $pdo;

    return $pdo->query(
        "SELECT user_id, first_name, last_name
         FROM users
         WHERE user_role = 'vendor' AND acc_status = 'active'
         ORDER BY first_name, last_name"
    )->fetchAll(PDO::FETCH_ASSOC);
}

// Insert a new user. $password is optional — pass null to skip.
function add_user($first_name, $last_name, $email, $role, $contact_num = null, $password = null)
{
    global $pdo;

    // Hash optional passwords before storing them; never persist the raw value.
    $hash = $password ? password_hash($password, PASSWORD_DEFAULT) : null;
    $stmt = $pdo->prepare(
        "INSERT INTO users (first_name, last_name, email_add, user_role, contact_num, password_hash)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([$first_name, $last_name, $email, $role, $contact_num, $hash]);
    return $pdo->lastInsertId();
}

// Update an existing user. $password is optional — pass null to skip.
function update_user($id, $first_name, $last_name, $email, $role, $contact_num = null, $status = 'active')
{
    global $pdo;
    $stmt = $pdo->prepare(
        "UPDATE users
         SET first_name = ?, last_name = ?, email_add = ?, user_role = ?, contact_num = ?, acc_status = ?
         WHERE user_id = ?"
    );
    $stmt->execute([$first_name, $last_name, $email, $role, $contact_num, $status, $id]);
    return true;
}

// Soft delete — flips acc_status to 'inactive'.
// Better than a real delete because users are referenced from orders/stalls/requests.
function delete_user($id)
{
    global $pdo;

    $stmt = $pdo->prepare("UPDATE users SET acc_status = 'inactive' WHERE user_id = ?");
    $stmt->execute([$id]);

    return true;
}

// Used by add_user/update_user to check for duplicate email.
function find_user_by_email($email)
{
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email_add = ? LIMIT 1");
    $stmt->execute([$email]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function get_active_user_count(PDO $pdo): int
{
    return (int) $pdo->query(
        "SELECT COUNT(*) FROM users WHERE acc_status = 'active'"
    )->fetchColumn();
}
