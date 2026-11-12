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

// Add a stall. $hours_text is optional; if it contains something like "09:00-17:00"
// we store those hours for all 7 days. Day-specific hours would need a richer UI.
function add_stall($name, $vendor_id, $location, $is_open, $hours_text = '')
{
    global $pdo;

    $stmt = $pdo->prepare(
        "INSERT INTO stalls (name, vendor_id, location, is_open) VALUES (?, ?, ?, ?)"
    );
    $stmt->execute([$name, $vendor_id ?: null, $location, $is_open ? 1 : 0]);
    $stall_id = $pdo->lastInsertId();

    if ($hours_text && preg_match('/(\d{1,2}:\d{2})-(\d{1,2}:\d{2})/', $hours_text, $m)) {
        $insert = $pdo->prepare(
            "INSERT INTO stall_operating_hours (stall_id, day_of_week, opens_at, closes_at)
             VALUES (?, ?, ?, ?)"
        );
        for ($d = 0; $d < 7; $d++) {
            $insert->execute([$stall_id, $d, $m[1], $m[2]]);
        }
    }

    return $stall_id;
}

function update_stall($id, $name, $vendor_id, $location, $is_open, $hours_text = '')
{
    global $pdo;

    // 1. Update the basic stall info
    $stmt = $pdo->prepare(
        "UPDATE stalls
         SET name = ?, vendor_id = ?, location = ?, is_open = ?
         WHERE id = ?"
    );
    $stmt->execute([$name, $vendor_id ?: null, $location, $is_open ? 1 : 0, $id]);

    // 2. Update hours if provided (format: "09:00-17:00")
    if ($hours_text && preg_match('/(\d{1,2}:\d{2})-(\d{1,2}:\d{2})/', $hours_text, $m)) {
        // Delete old hours
        $pdo->prepare("DELETE FROM stall_operating_hours WHERE stall_id = ?")->execute([$id]);
        
        // Insert new hours for all 7 days
        $insert = $pdo->prepare(
            "INSERT INTO stall_operating_hours (stall_id, day_of_week, opens_at, closes_at)
             VALUES (?, ?, ?, ?)"
        );
        for ($d = 0; $d < 7; $d++) {
            $insert->execute([$id, $d, $m[1], $m[2]]);
        }
    }

    return true;
}

function delete_stall($id)
{
    global $pdo;

    $stmt = $pdo->prepare("DELETE FROM stalls WHERE id = ?");
    $stmt->execute([$id]);

    return true;
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
