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

function getProductOptionGroups(PDO $pdo, array $productIds): array
{
    $productIds = array_values(array_unique(array_map('intval', $productIds)));
    if (!$productIds) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    $stmt = $pdo->prepare(
        "SELECT g.group_id, g.product_id, g.group_name, g.is_required, g.display_order AS group_order,
                c.option_id, c.option_name, c.extra_price, c.display_order AS choice_order
         FROM product_option_groups g
         LEFT JOIN product_option_choices c
           ON c.group_id = g.group_id AND c.is_available = 1
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
            ];
        }
    }

    foreach ($groups as $productId => $productGroups) {
        $groups[$productId] = array_values($productGroups);
    }
    return $groups;
}
