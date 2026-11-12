<?php
$vendorJsonRequest = true;
require __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../app/models/product-model.php';
require_once __DIR__ . '/../app/models/stall-model.php';
require_once __DIR__ . '/../app/models/product-options-model.php';
require_once __DIR__ . '/../app/core/product-options.php';

function productOptionsResponse(int $status, array $body): void
{
    http_response_code($status);
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    exit;
}

function loadVendorStoreProducts(PDO $pdo, int $vendorId, string $storeName): array
{
    foreach (getVendorStalls($pdo, $vendorId) as $stall) {
        if (mb_strtolower(trim($stall['name']), 'UTF-8') === mb_strtolower(trim($storeName), 'UTF-8')) {
            $products = getStallProducts($pdo, (int) $stall['id']);
            $groupsByProduct = getProductOptionGroups($pdo, array_column($products, 'id'), true);

            foreach ($products as &$product) {
                $product['optionGroups'] = $groupsByProduct[(int) $product['id']] ?? [];
            }
            unset($product);

            return ['stall' => $stall, 'products' => $products];
        }
    }

    throw new VendorRequestException('Store not found for this vendor.', 404);
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $storeName = $_GET['store'] ?? '';
        if (!is_string($storeName) || trim($storeName) === '' || mb_strlen($storeName, 'UTF-8') > 100) {
            productOptionsResponse(422, ['success' => false, 'message' => 'Invalid store.']);
        }

        $store = loadVendorStoreProducts($pdo, $vendorId, $storeName);
        productOptionsResponse(200, [
            'success' => true,
            'csrfToken' => vendorCsrfToken(),
            'stall' => $store['stall'],
            'products' => $store['products'],
        ]);
    }

    vendorRequireMethod('POST');
    $request = json_decode(file_get_contents('php://input'), true, 32, JSON_THROW_ON_ERROR);
    if (!is_array($request)) {
        productOptionsResponse(400, ['success' => false, 'message' => 'Invalid request body.']);
    }

    requireVendorCsrfToken($request['csrfToken'] ?? null);
    $productId = vendorPositiveId($request['productId'] ?? null);
    $groups = validateProductOptionGroups($request['groups'] ?? null);

    $product = $pdo->prepare(
        'SELECT p.id FROM products p
         JOIN stalls s ON s.id = p.stall_id
         WHERE p.id = ? AND s.vendor_id = ?'
    );
    $product->execute([$productId, $vendorId]);
    if (!$product->fetchColumn()) {
        productOptionsResponse(404, ['success' => false, 'message' => 'Product not found for this vendor.']);
    }

    $pdo->beginTransaction();
    replaceProductOptionGroups($pdo, $productId, $groups);
    $pdo->commit();

    $savedGroups = getProductOptionGroups($pdo, [$productId], true)[$productId] ?? [];
    productOptionsResponse(200, ['success' => true, 'groups' => $savedGroups]);
} catch (InvalidArgumentException $error) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    productOptionsResponse(422, ['success' => false, 'message' => $error->getMessage()]);
} catch (JsonException $error) {
    productOptionsResponse(400, ['success' => false, 'message' => 'Invalid JSON request.']);
} catch (VendorRequestException $error) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    productOptionsResponse($error->getCode(), ['success' => false, 'message' => $error->getMessage()]);
} catch (Throwable $error) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Product customizations failed: ' . $error->getMessage());
    productOptionsResponse(500, [
        'success' => false,
        'message' => 'Unable to load or save product customizations. Please try again.',
    ]);
}
