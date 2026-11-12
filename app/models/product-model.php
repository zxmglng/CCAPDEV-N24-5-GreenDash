<?php

function getStallProducts(PDO $pdo, int $stallId): array
{
    $stmt = $pdo->prepare("
        SELECT p.id, p.stall_id, p.category_id, p.name, p.description, p.price, p.image_path, p.is_available, c.name AS category
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.stall_id = ?
        ORDER BY p.id
    ");

    $stmt->execute([$stallId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
