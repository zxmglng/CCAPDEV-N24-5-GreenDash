<?php

function getSavedCartStallIds(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare("SELECT stall_id FROM user_carts WHERE user_id = ? AND CAST(items_json AS CHAR) <> '[]'");
    $stmt->execute([$userId]);
    return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}

function getUserCart(PDO $pdo, int $userId, int $stallId, bool $forUpdate = false): array
{
    $sql = 'SELECT version, items_json FROM user_carts WHERE user_id = ? AND stall_id = ?';
    if ($forUpdate && $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
        $sql .= ' FOR UPDATE';
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId, $stallId]);
    $cart = $stmt->fetch(PDO::FETCH_ASSOC);

    return $cart ? [
        'version' => (int) $cart['version'],
        'items' => json_decode($cart['items_json'], true, 32, JSON_THROW_ON_ERROR),
    ] : ['version' => 0, 'items' => []];
}

function replaceUserCart(PDO $pdo, int $userId, int $stallId, int $version, array $items): bool
{
    $itemsJson = json_encode($items, JSON_THROW_ON_ERROR);
    if ($version === 0) {
        $stmt = $pdo->prepare('INSERT INTO user_carts (user_id, stall_id, items_json) VALUES (?, ?, ?)');
        try {
            return $stmt->execute([$userId, $stallId, $itemsJson]);
        } catch (PDOException $error) {
            // Another request can create the cart after the caller read version zero.
            if ($error->getCode() === '23000' && getUserCart($pdo, $userId, $stallId)['version'] !== 0) {
                return false;
            }
            throw $error;
        }
    }

    $stmt = $pdo->prepare(
        'UPDATE user_carts SET items_json = ?, version = version + 1
         WHERE user_id = ? AND stall_id = ? AND version = ?'
    );
    $stmt->execute([$itemsJson, $userId, $stallId, $version]);
    return $stmt->rowCount() === 1;
}

function clearUserCart(PDO $pdo, int $userId, int $stallId, int $version): bool
{
    return replaceUserCart($pdo, $userId, $stallId, $version, []);
}
