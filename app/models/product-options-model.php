<?php

function replaceProductOptionGroups(PDO $pdo, int $productId, array $groups): void
{
    $stmt = $pdo->prepare(
        'SELECT group_id FROM product_option_groups WHERE product_id = ?'
    );
    $stmt->execute([$productId]);
    $existingGroupIds = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    $savedGroupIds = [];

    foreach ($groups as $group) {
        $groupId = $group['groupId'];
        if ($groupId !== null) {
            if (!in_array($groupId, $existingGroupIds, true)) {
                throw new InvalidArgumentException('A customization group does not belong to this product.');
            }
            $update = $pdo->prepare(
                'UPDATE product_option_groups
                 SET group_name = ?, is_required = ?, display_order = ?
                 WHERE group_id = ? AND product_id = ?'
            );
            $update->execute([
                $group['name'], $group['required'] ? 1 : 0, $group['displayOrder'],
                $groupId, $productId,
            ]);
        } else {
            $insert = $pdo->prepare(
                'INSERT INTO product_option_groups (product_id, group_name, is_required, display_order)
                 VALUES (?, ?, ?, ?)'
            );
            $insert->execute([
                $productId, $group['name'], $group['required'] ? 1 : 0, $group['displayOrder'],
            ]);
            $groupId = (int) $pdo->lastInsertId();
        }
        $savedGroupIds[] = $groupId;

        $choiceQuery = $pdo->prepare(
            'SELECT option_id FROM product_option_choices WHERE group_id = ?'
        );
        $choiceQuery->execute([$groupId]);
        $existingChoiceIds = array_map('intval', $choiceQuery->fetchAll(PDO::FETCH_COLUMN));
        $savedChoiceIds = [];

        foreach ($group['choices'] as $choice) {
            $optionId = $choice['optionId'];
            if ($optionId !== null) {
                if (!in_array($optionId, $existingChoiceIds, true)) {
                    throw new InvalidArgumentException('A customization choice does not belong to its group.');
                }
                $updateChoice = $pdo->prepare(
                    'UPDATE product_option_choices
                     SET option_name = ?, extra_price = ?, is_available = ?, display_order = ?
                     WHERE option_id = ? AND group_id = ?'
                );
                $updateChoice->execute([
                    $choice['name'], $choice['extraPrice'], $choice['available'] ? 1 : 0,
                    $choice['displayOrder'], $optionId, $groupId,
                ]);
            } else {
                $insertChoice = $pdo->prepare(
                    'INSERT INTO product_option_choices
                     (group_id, option_name, extra_price, is_available, display_order)
                     VALUES (?, ?, ?, ?, ?)'
                );
                $insertChoice->execute([
                    $groupId, $choice['name'], $choice['extraPrice'],
                    $choice['available'] ? 1 : 0, $choice['displayOrder'],
                ]);
                $optionId = (int) $pdo->lastInsertId();
            }
            $savedChoiceIds[] = $optionId;
        }

        if ($savedChoiceIds) {
            $placeholders = implode(',', array_fill(0, count($savedChoiceIds), '?'));
            $deleteChoices = $pdo->prepare(
                "DELETE FROM product_option_choices WHERE group_id = ? AND option_id NOT IN ($placeholders)"
            );
            $deleteChoices->execute(array_merge([$groupId], $savedChoiceIds));
        } else {
            $pdo->prepare('DELETE FROM product_option_choices WHERE group_id = ?')->execute([$groupId]);
        }
    }

    if ($savedGroupIds) {
        $placeholders = implode(',', array_fill(0, count($savedGroupIds), '?'));
        $deleteGroups = $pdo->prepare(
            "DELETE FROM product_option_groups WHERE product_id = ? AND group_id NOT IN ($placeholders)"
        );
        $deleteGroups->execute(array_merge([$productId], $savedGroupIds));
    } else {
        $pdo->prepare('DELETE FROM product_option_groups WHERE product_id = ?')->execute([$productId]);
    }
}
