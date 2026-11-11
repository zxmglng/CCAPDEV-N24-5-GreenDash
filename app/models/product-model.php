<?php

function getStallProducts(PDO $pdo, int $stallId): array
{
    $stmt = $pdo->prepare('SELECT id, stall_id, category_id, name, description, price, image_path, is_available
                          FROM products WHERE stall_id = ? ORDER BY id');
    $stmt->execute([$stallId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
