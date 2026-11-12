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

function getProductOptionGroups(PDO $pdo, array $productIds, bool $includeUnavailable = false): array
{
    $productIds = array_values(array_unique(array_map('intval', $productIds)));
    if (!$productIds) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    $availabilityFilter = $includeUnavailable ? '' : 'AND c.is_available = 1';
    $stmt = $pdo->prepare(
        "SELECT g.group_id, g.product_id, g.group_name, g.is_required, g.display_order AS group_order,
                c.option_id, c.option_name, c.extra_price, c.is_available,
                c.display_order AS choice_order
         FROM product_option_groups g
         LEFT JOIN product_option_choices c
           ON c.group_id = g.group_id $availabilityFilter
         WHERE g.product_id IN ($placeholders)
         ORDER BY g.product_id, g.display_order, g.group_id, c.display_order, c.option_id"
    );
    $stmt->execute($productIds);

    $groups = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $productId = (int) $row['product_id'];
        $groupId = (int) $row['group_id'];
        if (!isset($groups[$productId][$groupId])) {
            $groups[$productId][$groupId] = [
                'groupId' => $groupId,
                'name' => $row['group_name'],
                'required' => (bool) $row['is_required'],
                'choices' => [],
            ];
        }
        if ($row['option_id'] !== null) {
            $groups[$productId][$groupId]['choices'][] = [
                'optionId' => (int) $row['option_id'],
                'name' => $row['option_name'],
                'extraPrice' => (string) $row['extra_price'],
                'available' => (bool) $row['is_available'],
            ];
        }
    }

    foreach ($groups as $productId => $productGroups) {
        $groups[$productId] = array_values($productGroups);
    }
    return $groups;
}

function addProduct(PDO $pdo, int $stallId, int $categoryId, string $name, ?string $description,
    string $price, ?string $imagePath, bool $isAvailable): int
{
    $stmt = $pdo->prepare(
        "INSERT INTO products (stall_id, category_id, name, description, price, image_path, is_available)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([$stallId, $categoryId, $name, $description, $price, $imagePath, $isAvailable ? 1 : 0]);
    return (int) $pdo->lastInsertId();
}

function updateProduct(PDO $pdo, int $productId, int $stallId, int $categoryId, string $name,
    ?string $description, string $price, ?string $imagePath, bool $isAvailable): bool
{
    $stmt = $pdo->prepare(
        "UPDATE products
         SET category_id = ?, name = ?, description = ?, price = ?, image_path = ?, is_available = ?
         WHERE id = ? AND stall_id = ?"
    );
    $stmt->execute([$categoryId, $name, $description, $price, $imagePath, $isAvailable ? 1 : 0, $productId, $stallId]);
    return $stmt->rowCount() > 0;
}

function deleteProduct(PDO $pdo, int $productId, int $stallId): bool
{
    // Refuse if this product appears in order history.
    $check = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE menu_item_id = ?");
    $check->execute([$productId]);
    if ((int) $check->fetchColumn() > 0) {
        throw new RuntimeException('This item appears in order history and cannot be deleted. Mark it unavailable instead.');
    }
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND stall_id = ?");
    $stmt->execute([$productId, $stallId]);
    return $stmt->rowCount() > 0;
}

function setProductAvailability(PDO $pdo, int $productId, int $stallId, bool $available): bool
{
    $stmt = $pdo->prepare("UPDATE products SET is_available = ? WHERE id = ? AND stall_id = ?");
    $stmt->execute([$available ? 1 : 0, $productId, $stallId]);
    return $stmt->rowCount() > 0;
}