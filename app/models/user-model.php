<?php
// models/user_model.php - simple user/admin functions.

function getCheckoutUser(PDO $pdo, int $userId): ?array
{
    $stmt = $pdo->prepare('SELECT user_id, acc_status FROM users WHERE user_id = ?');
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function getUserById(PDO $pdo, int $userId): ?array  // get a user by their ID, returns null if not found
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

function getUserByEmail(PDO $pdo, string $email): ?array // get a user by their email
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

// creates new user fron their google acc info
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

// uppdates the login user profile information
function updateUserProfile(PDO $pdo, int $userId, string $firstName, string $lastName, ?string $contactNum, ?string $profileImage): bool
{
    $stmt = $pdo->prepare("
        UPDATE users
        SET first_name = ?,
            last_name = ?,
            contact_num = ?,
            profile_image = ?
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

// updates the user's role
function updateUserRole(PDO $pdo, int $userId, string $role): bool
{
    $stmt = $pdo->prepare("
        UPDATE users
        SET user_role = ?
        WHERE user_id = ?
    ");

    return $stmt->execute([$role, $userId]);
}

// All active users, for the manage-users.php table.
function get_users()
{
    global $pdo;

    return $pdo->query(
        "SELECT user_id, email_add, first_name, last_name, user_role, acc_status
         FROM users
         WHERE acc_status = 'active'
         ORDER BY last_name, first_name"
    )->fetchAll(PDO::FETCH_ASSOC);
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
function add_user($first_name, $last_name, $email, $role, $password = null)
{
    global $pdo;

    $hash = $password ? password_hash($password, PASSWORD_DEFAULT) : null;

    $stmt = $pdo->prepare(
        "INSERT INTO users (first_name, last_name, email_add, user_role, password_hash)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->execute([$first_name, $last_name, $email, $role, $hash]);

    return $pdo->lastInsertId();
}

function update_user($id, $first_name, $last_name, $email, $role)
{
    global $pdo;

    $stmt = $pdo->prepare(
        "UPDATE users
         SET first_name = ?, last_name = ?, email_add = ?, user_role = ?
         WHERE user_id = ?"
    );
    $stmt->execute([$first_name, $last_name, $email, $role, $id]);

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
