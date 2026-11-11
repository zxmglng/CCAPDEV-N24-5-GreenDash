<?php
// models/store_request_model.php - simple store request functions.
require_once __DIR__ . '/stall-model.php';

// List requests. Pass '' to get all statuses.
function get_requests($status = 'pending')
{
    global $pdo;

    $sql = "SELECT r.id, r.vendor_id, r.type, r.status,
                   r.proposed_name, r.proposed_location, r.proposed_description,
                   r.stall_id, r.created_at,
                   CONCAT(COALESCE(u.first_name,''), ' ', COALESCE(u.last_name,'')) AS vendor_name,
                   u.email_add AS vendor_email,
                   s.name     AS stall_name,
                   s.location AS stall_location
            FROM store_requests r
            JOIN users u ON u.user_id = r.vendor_id
            LEFT JOIN stalls s ON s.id = r.stall_id";

    if ($status !== '') {
        $sql .= " WHERE r.status = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$status]);
    } else {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    }

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Approve a request: apply its change to the stalls table and mark it approved.
// Returns true on success, false if the request wasn't found or wasn't pending.
function approve_request($request_id, $admin_id)
{
    global $pdo;

    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare("SELECT * FROM store_requests WHERE id = ? LIMIT 1");
        $stmt->execute([$request_id]);
        $req = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$req || $req['status'] !== 'pending') {
            $pdo->rollBack();
            return false;
        }

        if ($req['type'] === 'add') {
            $stmt = $pdo->prepare(
                "INSERT INTO stalls (vendor_id, name, location, description, is_open)
                 VALUES (?, ?, ?, ?, 1)"
            );
            $stmt->execute([
                $req['vendor_id'],
                $req['proposed_name'],
                $req['proposed_location'],
                $req['proposed_description'],
            ]);
        } elseif ($req['type'] === 'remove' && $req['stall_id']) {
            $stmt = $pdo->prepare("DELETE FROM stalls WHERE id = ?");
            $stmt->execute([$req['stall_id']]);
        }

        $stmt = $pdo->prepare(
            "UPDATE store_requests
             SET status = 'approved', resolved_by = ?, resolved_at = NOW()
             WHERE id = ?"
        );
        $stmt->execute([$admin_id, $request_id]);

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}

// Deny a request. Just updates the status.
function deny_request($request_id, $admin_id)
{
    global $pdo;

    $stmt = $pdo->prepare(
        "UPDATE store_requests
         SET status = 'denied', resolved_by = ?, resolved_at = NOW()
         WHERE id = ? AND status = 'pending'"
    );
    $stmt->execute([$admin_id, $request_id]);

    return $stmt->rowCount() > 0;
}