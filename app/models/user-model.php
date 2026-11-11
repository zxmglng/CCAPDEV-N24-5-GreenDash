<?php

function getCheckoutUser(PDO $pdo, int $userId): ?array
{
    $stmt = $pdo->prepare('SELECT user_id, acc_status FROM users WHERE user_id = ?');
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}
