<?php

function getStallOperatingHours(PDO $pdo, int $stallId): array
{
    $stmt = $pdo->prepare('SELECT day_of_week, opens_at, closes_at
                          FROM stall_operating_hours WHERE stall_id = ?
                          ORDER BY day_of_week, opens_at');
    $stmt->execute([$stallId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// Model: stall_operating_hours
// SQL only. No HTML, $_POST or $_SESSION in here.
//
// day_of_week convention: 0 = Sunday, 1 = Monday ... 6 = Saturday
// (same as PHP's date('w'), so no conversion is needed)
//
// An "hours row" is an array: ['day_of_week' => int, 'opens_at' => 'HH:MM', 'closes_at' => 'HH:MM']
// Overnight hours (closing after midnight) are not supported: opens_at must be earlier than closes_at.

// Display order for hours text: Monday first, Sunday last
const HOURS_DAY_ORDER = [1, 2, 3, 4, 5, 6, 0];
const HOURS_DAY_NAMES = [0 => 'Sun', 1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat'];

/**
 * All hours rows for one stall, ordered by day.
 */
function get_hours_by_stall(PDO $pdo, int $stallId): array
{
    $stmt = $pdo->prepare(
        "SELECT id, stall_id, day_of_week, opens_at, closes_at
         FROM stall_operating_hours
         WHERE stall_id = :stall_id
         ORDER BY day_of_week"
    );
    $stmt->execute([':stall_id' => $stallId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Hours for every stall in one query, grouped as [stall_id => [rows...]].
 */
function get_all_hours_grouped(PDO $pdo): array
{
    $stmt = $pdo->query(
        "SELECT id, stall_id, day_of_week, opens_at, closes_at
         FROM stall_operating_hours
         ORDER BY stall_id, day_of_week"
    );

    $grouped = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $grouped[(int) $row['stall_id']][] = $row;
    }
    return $grouped;
}

/**
 * Replace ALL hours of a stall with the given rows (delete + insert).
 * Pass an empty array to clear the stall's hours.
 * Throws InvalidArgumentException on bad input and PDOException on DB errors.
 */
function replace_stall_hours(PDO $pdo, int $stallId, array $rows): void
{
    $clean = [];
    foreach ($rows as $row) {
        $day    = $row['day_of_week'] ?? null;
        $opens  = $row['opens_at'] ?? null;
        $closes = $row['closes_at'] ?? null;

        if (!is_numeric($day) || (int) $day < 0 || (int) $day > 6) {
            throw new InvalidArgumentException('Invalid day of week.');
        }
        $pattern = '/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/';
        if (!is_string($opens) || !is_string($closes)
            || !preg_match($pattern, $opens) || !preg_match($pattern, $closes)) {
            throw new InvalidArgumentException('Invalid opening or closing time.');
        }
        if (substr($opens, 0, 5) >= substr($closes, 0, 5)) {
            throw new InvalidArgumentException('Opening time must be earlier than closing time.');
        }
        if (isset($clean[(int) $day])) {
            throw new InvalidArgumentException('Duplicate day in operating hours.');
        }
        $clean[(int) $day] = [substr($opens, 0, 5) . ':00', substr($closes, 0, 5) . ':00'];
    }

    $ownsTransaction = !$pdo->inTransaction();
    if ($ownsTransaction) $pdo->beginTransaction();

    try {
        $del = $pdo->prepare("DELETE FROM stall_operating_hours WHERE stall_id = :stall_id");
        $del->execute([':stall_id' => $stallId]);

        $ins = $pdo->prepare(
            "INSERT INTO stall_operating_hours (stall_id, day_of_week, opens_at, closes_at)
             VALUES (:stall_id, :day, :opens, :closes)"
        );
        foreach ($clean as $day => [$opens, $closes]) {
            $ins->execute([
                ':stall_id' => $stallId,
                ':day'      => $day,
                ':opens'    => $opens,
                ':closes'   => $closes,
            ]);
        }

        if ($ownsTransaction) $pdo->commit();
    } catch (Throwable $e) {
        if ($ownsTransaction && $pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}

/**
 * Delete all hours of a stall.
 */
function delete_hours_by_stall(PDO $pdo, int $stallId): void
{
    $stmt = $pdo->prepare("DELETE FROM stall_operating_hours WHERE stall_id = :stall_id");
    $stmt->execute([':stall_id' => $stallId]);
}

/**
 * Turn hours rows into display text, e.g. "Mon-Fri 09:00-17:00; Sat 10:00-14:00".
 * Consecutive days with identical times are grouped. Pure function (no DB).
 */
function format_hours_text(array $rows): string
{
    if (empty($rows)) {
        return 'Hours not set';
    }

    $byDay = [];
    foreach ($rows as $row) {
        $byDay[(int) $row['day_of_week']] = substr($row['opens_at'], 0, 5) . '-' . substr($row['closes_at'], 0, 5);
    }

    $groups  = [];
    $current = null;
    foreach (HOURS_DAY_ORDER as $day) {
        $time = $byDay[$day] ?? null;

        if ($time !== null && $current !== null && $current['time'] === $time) {
            $current['end'] = $day;
        } else {
            if ($current !== null) $groups[] = $current;
            $current = ($time !== null) ? ['start' => $day, 'end' => $day, 'time' => $time] : null;
        }
    }
    if ($current !== null) $groups[] = $current;

    $parts = [];
    foreach ($groups as $g) {
        $label = ($g['start'] === $g['end'])
            ? HOURS_DAY_NAMES[$g['start']]
            : HOURS_DAY_NAMES[$g['start']] . '-' . HOURS_DAY_NAMES[$g['end']];
        $parts[] = $label . ' ' . $g['time'];
    }
    return implode('; ', $parts);
}
