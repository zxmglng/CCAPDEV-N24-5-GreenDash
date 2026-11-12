<?php
// models/store_request_model.php - simple store request functions.
require_once __DIR__ . '/stall-model.php';

// List requests. Pass '' to get all statuses.
function get_requests($status = 'pending')
{
    global $pdo;

    $sql = "SELECT r.id, r.vendor_id, r.type, r.status,
                   r.proposed_name, r.proposed_location, r.proposed_hours, r.proposed_description,
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

// Create a new "add store" request from the vendor submission form.
// $meta is stored as JSON in proposed_description (store_type + logo_path).
function create_store_request(PDO $pdo, int $vendorId, string $name, string $location,
    string $hoursText, array $meta): int
{
    $stmt = $pdo->prepare(
        "INSERT INTO store_requests (vendor_id, type, proposed_name, proposed_location,
         proposed_hours, proposed_description)
         VALUES (?, 'add', ?, ?, ?, ?)"
    );
    $stmt->execute([
        $vendorId,
        $name,
        $location,
        $hoursText,
        json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
    ]);
    return (int) $pdo->lastInsertId();
}

// Approve a request: apply its change to the stalls table and mark it approved.
// Returns true on success, false if the request wasn't found or wasn't pending.
function approve_request($request_id, ?int $admin_id)
{
    global $pdo;
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("SELECT * FROM store_requests WHERE id = ? FOR UPDATE");
        $stmt->execute([$request_id]);
        $req = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$req || $req['status'] !== 'pending') {
            $pdo->rollBack();
            return false;
        }

        if ($req['type'] === 'add') {
            // Vendor submissions store extras (store_type, logo_path) as JSON in proposed_description.
            $storeType = null;
            $logoPath = null;
            $description = $req['proposed_description'];

            if (is_string($description) && $description !== '') {
                $meta = json_decode($description, true);
                if (is_array($meta)) {
                    if (isset($meta['store_type']) && is_string($meta['store_type'])) {
                        $storeType = $meta['store_type'];
                    }
                    $pendingLogo = $meta['logo_path'] ?? null;
                    if (is_string($pendingLogo)
                        && preg_match('#^assets/stalls/pending/[A-Za-z0-9._-]+$#', $pendingLogo)) {
                        $src = __DIR__ . '/../../' . $pendingLogo;
                        $dest = __DIR__ . '/../../assets/stalls/' . basename($pendingLogo);
                        if (is_file($src) && @rename($src, $dest)) {
                            $logoPath = 'assets/stalls/' . basename($pendingLogo);
                        }
                    }
                    // JSON payload is not user-facing text; leave stalls.description NULL.
                    $description = null;
                }
                // If decode failed, $description stays as the original plain string
                // (backward compatibility with any pre-existing rows).
            }

            $stmt = $pdo->prepare(
                "INSERT INTO stalls (vendor_id, name, location, description, image_path, stall_type, is_open)
                 VALUES (?, ?, ?, ?, ?, ?, 1)"
            );
            $stmt->execute([
                $req['vendor_id'],
                $req['proposed_name'],
                $req['proposed_location'],
                $description,
                $logoPath,
                $storeType,
            ]);

        } elseif ($req['type'] === 'remove' && $req['stall_id']) {
            // Ownership check: only remove if the stall still belongs to the requesting vendor.
            $stmt = $pdo->prepare("SELECT id FROM stalls WHERE id = ? AND vendor_id = ?");
            $stmt->execute([$req['stall_id'], $req['vendor_id']]);
            if (!$stmt->fetchColumn()) {
                throw new RuntimeException(
                    'This stall no longer belongs to the requesting vendor. Deny the request instead.'
                );
            }

            // Safety checks: same as delete_stall().
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE stall_id = ?");
            $stmt->execute([$req['stall_id']]);
            if ((int) $stmt->fetchColumn() > 0) {
                throw new RuntimeException('This stall has products. Remove them before approving.');
            }
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE stall_id = ?");
            $stmt->execute([$req['stall_id']]);
            if ((int) $stmt->fetchColumn() > 0) {
                throw new RuntimeException('This stall has order history. Deny the request and close the stall instead.');
            }

            $pdo->prepare("DELETE FROM stall_operating_hours WHERE stall_id = ?")->execute([$req['stall_id']]);
            $pdo->prepare("DELETE FROM categories WHERE stall_id = ?")->execute([$req['stall_id']]);
            $pdo->prepare("DELETE FROM stalls WHERE id = ?")->execute([$req['stall_id']]);
        }

        $stmt = $pdo->prepare(
            "UPDATE store_requests
             SET status = 'approved', resolved_by = ?, resolved_at = NOW()
             WHERE id = ?"
        );
        $stmt->execute([$admin_id, $request_id]);

        $pdo->commit();
        return true;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}

// Deny a request. Just updates the status.
function deny_request($request_id, ?int $admin_id)
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

function get_pending_request_count(PDO $pdo): int
{
    return (int) $pdo->query(
        "SELECT COUNT(*) FROM store_requests WHERE status = 'pending'"
    )->fetchColumn();
}