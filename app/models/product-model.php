<?php

function getStallProduct(PDO $pdo, int $stallId, int $productId): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM products WHERE stall_id = ? AND id = ?');
    $stmt->execute([$stallId, $productId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

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
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Add categories to each product
    $categoriesByProduct = getProductCategories($pdo, array_column($products, 'id'));
    foreach ($products as &$product) {
        $product['categories'] = $categoriesByProduct[(int) $product['id']] ?? [];
    }
    unset($product);    // Break the reference to the last product
    return $products;   // Return the products with their associated categories
}

// Get all products for a stall, including their option groups and choices.
function getProductCategories(PDO $pdo, array $productIds): array
{
    $productIds = array_values(array_unique(array_filter(array_map('intval', $productIds), static fn(int $id): bool => $id > 0)));
    if (!$productIds) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    $stmt = $pdo->prepare(
        /* pc = product_categories, c = categories */

        "SELECT pc.product_id, c.id, c.name /* Fetch category id and name */
         FROM product_categories pc /* Join with categories to get category details */
         JOIN categories c ON c.id = pc.category_id /* Ensure we only get categories that exist */
         WHERE pc.product_id IN ($placeholders) /* Filter by the provided product IDs */
         ORDER BY pc.product_id, c.id"  
    );
    $stmt->execute($productIds);

    // Build an array mapping product IDs to their categories
    $categoriesByProduct = [];  // Initialize an empty array to hold the categories for each product
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {   // Loop through each row returned from the query
        $categoriesByProduct[(int) $row['product_id']][] = [
            'id' => (int) $row['id'],   // Store the category ID as an integer
            'name' => $row['name'],     // Store the category name
        ];
    }
    return $categoriesByProduct;        // Return the mapping of product IDs to their associated categories
}

// Replace all categories for a product with the given list of category IDs.
function replaceProductCategories(PDO $pdo, int $productId, array $categoryIds): void
{
    $delete = $pdo->prepare('DELETE FROM product_categories WHERE product_id = ?'); // Delete existing category associations for the product
    $delete->execute([$productId]); // Execute the delete statement with the product ID

    $insert = $pdo->prepare('INSERT INTO product_categories (product_id, category_id) VALUES (?, ?)');  // Prepare the insert statement to add new category associations
    foreach (array_values(array_unique(array_map('intval', $categoryIds))) as $categoryId) {
        $insert->execute([$productId, $categoryId]);    // Execute the insert statement for each unique category ID associated with the product
    }
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

function addProduct(PDO $pdo, int $stallId, array $categoryIds, string $name, ?string $description,
    string $price, ?string $imagePath, bool $isAvailable): int
{
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO products (stall_id, category_id, name, description, price, image_path, is_available)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$stallId, $categoryIds[0], $name, $description, $price, $imagePath, $isAvailable ? 1 : 0]);
        $productId = (int) $pdo->lastInsertId();
        replaceProductCategories($pdo, $productId, $categoryIds);
        $pdo->commit();
        return $productId;
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $error;
    }
}

function updateProduct(PDO $pdo, int $productId, int $stallId, array $categoryIds, string $name,
    ?string $description, string $price, ?string $imagePath, bool $isAvailable): bool
{
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare(
            "UPDATE products
             SET category_id = ?, name = ?, description = ?, price = ?, image_path = ?, is_available = ?
             WHERE id = ? AND stall_id = ?"
        );
        $stmt->execute([$categoryIds[0], $name, $description, $price, $imagePath, $isAvailable ? 1 : 0, $productId, $stallId]);
        $updated = $stmt->rowCount() > 0;
        replaceProductCategories($pdo, $productId, $categoryIds);
        $pdo->commit();
        return $updated;
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $error;
    }
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
