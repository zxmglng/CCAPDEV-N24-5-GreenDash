<?php

function getCheckoutStall(PDO $pdo, int $stallId): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM stalls WHERE id = ?');
    $stmt->execute([$stallId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}
