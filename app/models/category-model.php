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

// Full category rows (id + name) for a stall, ordered.
function getCategoryRowsByStall(PDO $pdo, int $stallId): array
{
    $stmt = $pdo->prepare("
        SELECT id, name
        FROM categories
        WHERE stall_id = ?
        ORDER BY id
    ");
    $stmt->execute([$stallId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addCategory(PDO $pdo, int $stallId, string $name): int
{
    $stmt = $pdo->prepare("INSERT INTO categories (stall_id, name) VALUES (?, ?)");
    $stmt->execute([$stallId, $name]);
    return (int) $pdo->lastInsertId();
}

function renameCategory(PDO $pdo, int $stallId, int $categoryId, string $name): bool
{
    $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ? AND stall_id = ?");
    $stmt->execute([$name, $categoryId, $stallId]);
    return $stmt->rowCount() > 0;
}

function deleteCategory(PDO $pdo, int $stallId, int $categoryId): bool
{
    // Refuse if any products still use this category.
    // We check the product_categories table for any products that belong to this category and also belong to the given stall.
    $check = $pdo->prepare(
        "SELECT COUNT(*)
         FROM product_categories pc
         JOIN products p ON p.id = pc.product_id
         WHERE pc.category_id = ? AND p.stall_id = ?"
    );
    $check->execute([$categoryId, $stallId]);
    if ((int) $check->fetchColumn() > 0) {
        throw new RuntimeException('Move or delete the products in this category before removing it.');
    }
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ? AND stall_id = ?");
    $stmt->execute([$categoryId, $stallId]);
    return $stmt->rowCount() > 0;
}
