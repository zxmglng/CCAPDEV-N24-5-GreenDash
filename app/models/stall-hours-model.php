<?php

function getStallOperatingHours(PDO $pdo, int $stallId): array
{
    $stmt = $pdo->prepare('SELECT day_of_week, opens_at, closes_at
                          FROM stall_operating_hours WHERE stall_id = ?
                          ORDER BY day_of_week, opens_at');
    $stmt->execute([$stallId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
