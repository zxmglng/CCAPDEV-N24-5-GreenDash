<?php

require_once __DIR__ . '/checkout.php';

function decodeCartItems($input): array
{
    if (!is_string($input) || strlen($input) > 100000 || !str_starts_with(ltrim($input), '[')) {
        throw new CheckoutValidationException('Invalid cart items.');
    }
    try {
        $items = json_decode($input, true, 32, JSON_THROW_ON_ERROR);
    } catch (JsonException $error) {
        throw new CheckoutValidationException('Invalid cart items.');
    }
    return $items === [] ? [] : validateCheckoutItems($items);
}

function cartLineKey(array $item): string
{
    $options = array_column($item['options'], 'optionId', 'groupId');
    ksort($options, SORT_NUMERIC);
    return json_encode([
        $item['productId'], $item['quantity'], $item['specialInstructions'], $options,
    ], JSON_THROW_ON_ERROR);
}

function validateCartMenu(PDO $pdo, int $stallId, array $items, array $savedItems): void
{
    // Validate changed lines so stale menu choices can still be removed.
    $savedLineCounts = array_count_values(array_map('cartLineKey', $savedItems));
    $changedItems = [];
    foreach ($items as $item) {
        $key = cartLineKey($item);
        if (($savedLineCounts[$key] ?? 0) > 0) {
            --$savedLineCounts[$key];
        } else {
            $changedItems[] = $item;
        }
    }
    if ($changedItems) {
        priceCheckoutItems($pdo, $stallId, $changedItems);
    }
}
