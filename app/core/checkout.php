<?php
require_once __DIR__ . '/../models/user-model.php';
require_once __DIR__ . '/../models/stall-model.php';
require_once __DIR__ . '/../models/stall-hours-model.php';
require_once __DIR__ . '/../models/product-model.php';
require_once __DIR__ . '/../models/order-model.php';

class CheckoutValidationException extends RuntimeException {}

function startCheckoutSession(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    header('Cache-Control: no-store');
}

function checkoutCsrfToken(): string
{
    if (!isset($_SESSION['checkout_csrf_token'])) {
        $_SESSION['checkout_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['checkout_csrf_token'];
}

function checkoutPositiveInteger($value, string $label): int
{
    if ((!is_int($value) && !is_string($value))
        || !preg_match('/^[1-9][0-9]*$/D', (string) $value)
        || filter_var($value, FILTER_VALIDATE_INT) === false) {
        throw new CheckoutValidationException($label . ' must be a positive whole number.');
    }
    return (int) $value;
}

function checkoutMoney(int $cents): string
{
    return intdiv($cents, 100) . '.' . str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
}

function checkoutPriceCents($price): int
{
    if (!preg_match('/^([0-9]{1,8})(?:\.([0-9]{1,2}))?$/D', (string) $price, $parts)) {
        throw new RuntimeException('Invalid database product price.');
    }
    return (int) $parts[1] * 100 + (int) str_pad($parts[2] ?? '', 2, '0');
}

function validateCheckoutRequest(array $data): array
{
    $stallId = checkoutPositiveInteger($data['stallId'] ?? null, 'Stall ID');
    $orderItems = $data['orderItems'] ?? null;
    if (!is_array($orderItems) || !array_is_list($orderItems) || !$orderItems || count($orderItems) > 100) {
        throw new CheckoutValidationException('Your order must contain between 1 and 100 items.');
    }

    $items = [];
    foreach ($orderItems as $item) {
        if (!is_array($item)) {
            throw new CheckoutValidationException('Invalid order item.');
        }
        $quantity = checkoutPositiveInteger($item['quantity'] ?? null, 'Quantity');
        if ($quantity > 999) {
            throw new CheckoutValidationException('Quantity cannot exceed 999 per item.');
        }
        $instructions = $item['specialInstructions'] ?? '';
        if (!is_string($instructions) || !preg_match('//u', $instructions)
            || mb_strlen($instructions, 'UTF-8') > 255) {
            throw new CheckoutValidationException('Special instructions cannot exceed 255 characters.');
        }
        $normalized = [
            'productId' => checkoutPositiveInteger($item['productId'] ?? null, 'Product ID'),
            'quantity' => $quantity,
            'specialInstructions' => trim($instructions),
        ];
        $options = $item['options'] ?? [];
        if (!is_array($options) || !array_is_list($options) || count($options) > 50) {
            throw new CheckoutValidationException('Invalid product customizations.');
        }
        $selectedGroups = [];
        foreach ($options as $option) {
            if (!is_array($option)) {
                throw new CheckoutValidationException('Invalid product customization.');
            }
            $groupId = checkoutPositiveInteger($option['groupId'] ?? null, 'Customization group');
            $optionId = checkoutPositiveInteger($option['optionId'] ?? null, 'Customization choice');
            if (isset($selectedGroups[$groupId])) {
                throw new CheckoutValidationException('Choose only one option per customization group.');
            }
            $selectedGroups[$groupId] = $optionId;
        }
        $normalized['options'] = array_map(
            static fn(int $groupId, int $optionId): array => ['groupId' => $groupId, 'optionId' => $optionId],
            array_keys($selectedGroups),
            array_values($selectedGroups)
        );
        $items[] = $normalized;
    }

    $paymentMethod = $data['paymentMethod'] ?? null;
    if (!in_array($paymentMethod, ['cash', 'online'], true)) {
        throw new CheckoutValidationException('Select a valid payment method.');
    }
    $referenceNumber = $data['referenceNumber'] ?? '';
    if (!is_string($referenceNumber) || !preg_match('//u', $referenceNumber)
        || mb_strlen(trim($referenceNumber), 'UTF-8') > 40) {
        throw new CheckoutValidationException('The reference number cannot exceed 40 characters.');
    }
    $referenceNumber = trim($referenceNumber);
    if ($paymentMethod === 'online' && $referenceNumber === '') {
        throw new CheckoutValidationException('Please enter a payment reference number.');
    }
    $pickupTime = $data['pickupTime'] ?? null;
    if (!is_string($pickupTime) || strlen($pickupTime) > 50 || trim($pickupTime) === '') {
        throw new CheckoutValidationException('Select a valid pick-up time.');
    }
    return [
        'stallId' => $stallId, 'orderItems' => $items, 'pickupTime' => trim($pickupTime),
        'paymentMethod' => $paymentMethod,
        'referenceNumber' => $paymentMethod === 'online' ? $referenceNumber : null,
    ];
}

// School operating hours use 1 = Monday through 7 = Sunday, as in the directory template.
function checkoutOpeningIntervals(array $hours, DateTimeImmutable $now): array
{
    $intervals = [];
    foreach ([$now, $now->modify('-1 day')] as $day) {
        foreach ($hours as $row) {
            if ((int) $row['day_of_week'] !== (int) $day->format('N')) {
                continue;
            }
            $opens = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s',
                $day->format('Y-m-d') . ' ' . $row['opens_at'], $now->getTimezone());
            $closes = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s',
                $day->format('Y-m-d') . ' ' . $row['closes_at'], $now->getTimezone());
            if (!$opens || !$closes) {
                continue;
            }
            if ($closes <= $opens) {
                $closes = $closes->modify('+1 day');
            }
            $intervals[] = [$opens, $closes];
        }
    }
    return $intervals;
}

function checkoutPickup(string $pickupTime, array $hours, DateTimeImmutable $now): array
{
    $now = $now->setTimezone(new DateTimeZone('Asia/Manila'));
    if ($pickupTime === 'now') {
        $start = $now;
        $end = $now;
    } else {
        if (!preg_match('/^(\d{1,2}):(\d{2})\s*(AM|PM)\s*-\s*(\d{1,2}):(\d{2})\s*(AM|PM)$/iD', $pickupTime, $parts)) {
            throw new CheckoutValidationException('Select a valid pick-up time.');
        }
        $minutes = [];
        foreach ([[1, 2, 3], [4, 5, 6]] as [$hour, $minute, $period]) {
            if ((int) $parts[$hour] < 1 || (int) $parts[$hour] > 12 || (int) $parts[$minute] > 59) {
                throw new CheckoutValidationException('Select a valid pick-up time.');
            }
            $minutes[] = ((int) $parts[$hour] % 12 + (strtoupper($parts[$period]) === 'PM' ? 12 : 0)) * 60
                + (int) $parts[$minute];
        }
        $duration = ($minutes[1] - $minutes[0] + 1440) % 1440;
        if ($duration !== 15) {
            throw new CheckoutValidationException('Select a 15-minute pick-up window.');
        }
        $start = $now->setTime(intdiv($minutes[0], 60), $minutes[0] % 60, 0);
        $end = $start->modify('+15 minutes');
        if ($start < $now) {
            throw new CheckoutValidationException('Pick-up time must be in the future.');
        }
    }
    foreach (checkoutOpeningIntervals($hours, $now) as [$opens, $closes]) {
        if ($start >= $opens && $start < $closes && $end <= $closes) {
            return ['pickup_type' => $pickupTime === 'now' ? 'now' : 'scheduled',
                    'pickup_at' => $pickupTime === 'now' ? null : $start->format('Y-m-d H:i:s')];
        }
    }
    throw new CheckoutValidationException('The selected pick-up time is outside this stall\'s operating hours.');
}

function placeCheckoutOrder(PDO $pdo, int $userId, array $request, ?DateTimeImmutable $now = null): array
{
    $request = validateCheckoutRequest($request);
    $now = $now ?? new DateTimeImmutable('now', new DateTimeZone('Asia/Manila'));
    $pdo->beginTransaction();
    try {
        $user = getCheckoutUser($pdo, $userId);
        if (!$user || $user['acc_status'] === 'inactive') {
            throw new CheckoutValidationException('Your account cannot place orders. Please sign in again.');
        }
        $stall = getCheckoutStall($pdo, $request['stallId']);
        if (!$stall) {
            throw new CheckoutValidationException('This stall is no longer available.');
        }
        if ((isset($stall['is_open']) && !(bool) $stall['is_open'])
            || (isset($stall['status']) && $stall['status'] === 'closed')) {
            throw new CheckoutValidationException('This stall is currently closed.');
        }
        $pickup = checkoutPickup($request['pickupTime'], getStallOperatingHours($pdo, $request['stallId']), $now);
        $products = [];
        foreach (getStallProducts($pdo, $request['stallId']) as $product) {
            $products[(int) $product['id']] = $product;
        }
        $optionGroupsByProduct = getProductOptionGroups(
            $pdo,
            array_column($request['orderItems'], 'productId')
        );
        $items = [];
        $total = 0;
        foreach ($request['orderItems'] as $requestedItem) {
            $product = $products[$requestedItem['productId']] ?? null;
            if (!$product || !(bool) $product['is_available']) {
                throw new CheckoutValidationException('An item is unavailable. Please refresh the stall menu.');
            }
            $basePrice = checkoutPriceCents($product['price']);
            $unitPrice = $basePrice;
            $options = [];
            $productGroups = $optionGroupsByProduct[$requestedItem['productId']] ?? [];
            $selectedOptions = [];
            foreach ($requestedItem['options'] as $selection) {
                $selectedOptions[$selection['groupId']] = $selection['optionId'];
            }
            foreach ($productGroups as $group) {
                $selectedOptionId = $selectedOptions[$group['groupId']] ?? null;
                if ($selectedOptionId === null) {
                    if ($group['required']) {
                        throw new CheckoutValidationException(
                            'Choose an option for ' . $group['name'] . ' on ' . $product['name'] . '.'
                        );
                    }
                    continue;
                }

                $choice = null;
                foreach ($group['choices'] as $availableChoice) {
                    if ($availableChoice['optionId'] === $selectedOptionId) {
                        $choice = $availableChoice;
                        break;
                    }
                }
                if ($choice === null) {
                    throw new CheckoutValidationException(
                        'A selected customization is no longer available. Please refresh the menu.'
                    );
                }
                $extraPrice = checkoutPriceCents($choice['extraPrice']);
                $unitPrice += $extraPrice;
                $options[] = [
                    'option_id' => $choice['optionId'],
                    'option_group_name' => $group['name'],
                    'option_name' => $choice['name'],
                    'extra_price' => checkoutMoney($extraPrice),
                ];
                unset($selectedOptions[$group['groupId']]);
            }
            if ($selectedOptions) {
                throw new CheckoutValidationException(
                    'A selected customization does not belong to this product.'
                );
            }
            $itemTotal = $unitPrice * $requestedItem['quantity'];
            $total += $itemTotal;
            if ($total > 9999999999) {
                throw new CheckoutValidationException('The order total exceeds the supported amount.');
            }
            $items[] = [
                'menu_item_id' => (int) $product['id'], 'item_name' => $product['name'],
                // Category names are optional snapshots; no category-table schema was supplied.
                'category_name' => null,
                'special_instructions' => $requestedItem['specialInstructions'] !== '' ? $requestedItem['specialInstructions'] : null,
                'quantity' => $requestedItem['quantity'], 'base_price' => checkoutMoney($basePrice),
                'unit_price' => checkoutMoney($unitPrice), 'total_price' => checkoutMoney($itemTotal),
                'options' => $options,
            ];
        }
        $orderNumber = 'GD' . strtoupper(bin2hex(random_bytes(8)));
        $orderId = insertCheckoutOrder($pdo, [
            'order_number' => $orderNumber, 'user_id' => $userId, 'stall_id' => (int) $stall['id'],
            'stall_name' => $stall['name'], 'stall_location' => $stall['location'],
            'payment_method' => $request['paymentMethod'], 'payment_reference' => $request['referenceNumber'],
            'pickup_type' => $pickup['pickup_type'], 'pickup_at' => $pickup['pickup_at'],
            'total_amount' => checkoutMoney($total),
        ]);
        foreach ($items as $item) {
            $orderItemId = insertCheckoutItem($pdo, $orderId, $item);
            foreach ($item['options'] as $option) {
                insertCheckoutItemOption($pdo, $orderItemId, $option);
            }
        }
        $pdo->commit();
        return ['success' => true, 'orderId' => $orderId,
                'orderNumber' => $orderNumber, 'totalAmount' => checkoutMoney($total)];
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}
