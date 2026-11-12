<?php

function getCategoriesByStall(PDO $pdo, int $stallId): array
{
    $stmt = $pdo->prepare("
        SELECT name
        FROM categories
        WHERE stall_id = ?
        ORDER BY id
    ");

    $stmt->execute([$stallId]);

    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}