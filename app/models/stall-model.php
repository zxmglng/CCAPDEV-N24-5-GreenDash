<?php
// models/stall_model.php - simple stall functions.
function getCheckoutStall(PDO $pdo, int $stallId): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM stalls WHERE id = ?');
    $stmt->execute([$stallId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function getVendorStalls(PDO $pdo, int $vendorId): array
{
    $stmt = $pdo->prepare('SELECT id, name, location FROM stalls WHERE vendor_id = ? ORDER BY name');
    $stmt->execute([$vendorId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// gets all stalls for the user stalls directory
function getAllStalls(PDO $pdo): array
{
    $stmt = $pdo->query("
        SELECT *
        FROM stalls
        ORDER BY name
    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// All stalls with vendor_name and a readable hours_text, for manage-stores.php.
function get_stalls() // for the admin
{
    global $pdo;

    $sql = "SELECT s.id, s.vendor_id, s.name, s.location, s.is_open,
                   CONCAT(COALESCE(u.first_name,''), ' ', COALESCE(u.last_name,'')) AS vendor_name
            FROM stalls s
            LEFT JOIN users u ON u.user_id = s.vendor_id
            ORDER BY s.name";

    $stalls = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($stalls as &$s) {
        $s['hours_text'] = get_hours_text($s['id']);
    }

    return $stalls;
}

function get_stall($id)
{
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM stalls WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) return null;

    $row['hours_text'] = get_hours_text($id);
    return $row;
}

// Distinct locations, for the filter dropdown.
function get_locations()
{
    global $pdo;

    return $pdo->query(
        "SELECT DISTINCT location
         FROM stalls
         WHERE location IS NOT NULL AND location <> ''
         ORDER BY location"
    )->fetchAll(PDO::FETCH_COLUMN);
}

function add_stall($name, $vendor_id, $location, $is_open, array $hours_rows = [])
{
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO stalls (name, vendor_id, location, is_open) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $vendor_id ?: null, $location, $is_open ? 1 : 0]);
    $stall_id = (int) $pdo->lastInsertId();

    if (!empty($hours_rows)) {
        replace_stall_hours($pdo, $stall_id, $hours_rows);
    }
    return $stall_id;
}

function update_stall($id, $name, $vendor_id, $location, $is_open, array $hours_rows = [])
{
    global $pdo;
    $stmt = $pdo->prepare("UPDATE stalls SET name = ?, vendor_id = ?, location = ?, is_open = ? WHERE id = ?");
    $stmt->execute([$name, $vendor_id ?: null, $location, $is_open ? 1 : 0, $id]);

    // replace_stall_hours handles the delete and insert safely in a transaction
    replace_stall_hours($pdo, $id, $hours_rows);
    return true;
}

function delete_stall($id)
{
    global $pdo;

    // Safety: refuse if the stall has a menu or orders.
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE stall_id = ?");
    $stmt->execute([$id]);
    if ((int) $stmt->fetchColumn() > 0) {
        throw new RuntimeException(
            'This stall has menu products and cannot be removed. Close the stall or clear its menu first.'
        );
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE stall_id = ?");
    $stmt->execute([$id]);
    if ((int) $stmt->fetchColumn() > 0) {
        throw new RuntimeException(
            'This stall has order history and cannot be removed. Mark it closed instead.'
        );
    }

    // Delete child rows first, then the stall, in one transaction.
    $pdo->beginTransaction();
    try {
        $pdo->prepare("DELETE FROM stall_operating_hours WHERE stall_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM categories WHERE stall_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM store_requests WHERE stall_id = ? AND status = 'pending'")->execute([$id]);
        $pdo->prepare("DELETE FROM stalls WHERE id = ?")->execute([$id]);
        $pdo->commit();
        return true;
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// Turn the stall's operating hours into a single string like "Mon-Fri 09:00-17:00".
function get_hours_text($stall_id)
{
    global $pdo;

    $stmt = $pdo->prepare(
        "SELECT day_of_week, opens_at, closes_at
         FROM stall_operating_hours
         WHERE stall_id = ?
         ORDER BY day_of_week"
    );
    $stmt->execute([$stall_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows) return 'Hours not set';

    $names = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    // Group consecutive days that share the same open/close time.
    $groups = [];
    foreach ($rows as $r) {
        $time = substr($r['opens_at'], 0, 5) . '-' . substr($r['closes_at'], 0, 5);
        $day  = (int)$r['day_of_week'];
        $last = count($groups) - 1;

        if ($last >= 0 && $groups[$last]['time'] === $time && $groups[$last]['last'] === $day - 1) {
            $groups[$last]['last'] = $day;
            $groups[$last]['days']++;
        } else {
            $groups[] = ['time' => $time, 'first' => $day, 'last' => $day, 'days' => 1];
        }
    }

    $parts = [];
    foreach ($groups as $g) {
        if ($g['days'] > 1) {
            $parts[] = $names[$g['first']] . '-' . $names[$g['last']] . ' ' . $g['time'];
        } else {
            $parts[] = $names[$g['first']] . ' ' . $g['time'];
        }
    }

    return implode('; ', $parts);
}

function get_stall_counts(PDO $pdo): array
{
    return [
        'total' => (int) $pdo->query("SELECT COUNT(*) FROM stalls")->fetchColumn(),
        'open'  => (int) $pdo->query("SELECT COUNT(*) FROM stalls WHERE is_open = 1")->fetchColumn(),
    ];
}

function setStallOpenStatus(PDO $pdo, int $stallId, int $vendorId, bool $isOpen): bool
{
    $stmt = $pdo->prepare("UPDATE stalls SET is_open = ? WHERE id = ? AND vendor_id = ?");
    $stmt->execute([$isOpen ? 1 : 0, $stallId, $vendorId]);
    return $stmt->rowCount() > 0;
}