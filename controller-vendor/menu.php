<?php
$vendorJsonRequest = true;
require __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../app/models/category-model.php';
require_once __DIR__ . '/../app/models/product-model.php';
require_once __DIR__ . '/../app/models/stall-model.php';

header('Content-Type: application/json; charset=utf-8');

function menuRespond(int $status, array $body): void
{
    http_response_code($status);
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    exit;
}

function validateProductImagePath(mixed $path, int $stallId, ?string $previous = null): ?string
{
    if ($path === null || $path === '') {
        return null;
    }
    if (is_string($path)) {
        $root = dirname(__DIR__);

        // NEW: default image path for added items without an image set
        if ($path === 'assets/main/no-image-set.png' && is_file($root . '/' . $path)) {
            return $path;
        }
        $uploadPattern = '#\Aassets/uploads/products/' . $stallId . '/[a-f0-9]{32}\.(?:jpg|png|webp|gif)\z#';
        $file = $root . '/' . $path;
        if (preg_match($uploadPattern, $path) && realpath($file) === $file && is_file($file)) {
            return $path;
        }

        // Only the existing item may retain a legacy image reference.
        if ($path === $previous) {
            $relative = str_starts_with($path, '../assets/') ? substr($path, 3) : $path;
            $seedPattern = '#\Aassets/(?!uploads/)(?:[a-z0-9_-]+/)*[a-z0-9_][a-z0-9_.-]*\.(?:jpe?g|png|webp|gif)\z#i';
            $legacyUploadPattern = '#\Aassets/uploads/products/[a-f0-9]{32}\.(?:jpg|png|webp|gif)\z#';
            if (preg_match($seedPattern, $relative) || preg_match($legacyUploadPattern, $relative)) {
                $legacyFile = $root . '/' . $relative;
                $resolved = realpath($legacyFile);
                if ($resolved === false || ($resolved === $legacyFile && is_file($legacyFile))) {
                    return $path;
                }
            }
        }
    }
    throw new InvalidArgumentException('Choose an image uploaded for this stall.');
}

function parsePrice($value): string
{
    if (!is_string($value) && !is_int($value) && !is_float($value)) {
        throw new InvalidArgumentException('Enter a valid price.');
    }
    $value = (string) $value;
    if (!preg_match('/^(?:0|[1-9][0-9]{0,7})(?:\.[0-9]{1,2})?$/D', $value)) {
        throw new InvalidArgumentException('Price must be 0 or more with up to 2 decimal places.');
    }
    if (!str_contains($value, '.')) {
        $value .= '.00';
    } elseif (strlen(substr(strrchr($value, '.'), 1)) === 1) {
        $value .= '0';
    }
    return $value;
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stallId = vendorPositiveId($_GET['stallId'] ?? null, 'stall ID');
        requireVendorOwnedStall($pdo, $vendorId, $stallId);
        $categories = getCategoryRowsByStall($pdo, $stallId);
        $products = getStallProducts($pdo, $stallId);
        $groupsByProduct = getProductOptionGroups($pdo, array_column($products, 'id'), true);
        foreach ($products as &$p) {
            $p['optionGroups'] = $groupsByProduct[(int) $p['id']] ?? [];
        }
        unset($p);
        menuRespond(200, [
            'success' => true,
            'csrfToken' => vendorCsrfToken(),
            'categories' => $categories,
            'products' => $products,
        ]);
    }

    vendorRequireMethod('POST');
    $request = json_decode(file_get_contents('php://input'), true, 32, JSON_THROW_ON_ERROR);
    if (!is_array($request)) {
        menuRespond(400, ['success' => false, 'message' => 'Invalid request body.']);
    }
    requireVendorCsrfToken($request['csrfToken'] ?? null);
    $action = $request['action'] ?? '';
    $stallId = vendorPositiveId($request['stallId'] ?? null, 'stall ID');
    requireVendorOwnedStall($pdo, $vendorId, $stallId);

    if ($action === 'category_add') {
        $name = trim((string) ($request['name'] ?? ''));
        if ($name === '' || mb_strlen($name, 'UTF-8') > 40) {
            throw new InvalidArgumentException('Category names must be 1–40 characters.');
        }
        $id = addCategory($pdo, $stallId, $name);
        menuRespond(200, ['success' => true, 'category' => ['id' => $id, 'name' => $name]]);
    }

    if ($action === 'category_rename') {
        $categoryId = vendorPositiveId($request['categoryId'] ?? null);
        $name = trim((string) ($request['name'] ?? ''));
        if ($name === '' || mb_strlen($name, 'UTF-8') > 40) {
            throw new InvalidArgumentException('Category names must be 1–40 characters.');
        }
        if (!renameCategory($pdo, $stallId, $categoryId, $name)) {
            throw new VendorRequestException('Category not found.', 404);
        }
        menuRespond(200, ['success' => true]);
    }

    if ($action === 'category_delete') {
        $categoryId = vendorPositiveId($request['categoryId'] ?? null);
        if (!deleteCategory($pdo, $stallId, $categoryId)) {
            throw new VendorRequestException('Category not found.', 404);
        }
        menuRespond(200, ['success' => true]);
    }

    if ($action === 'product_add') {
        $categoryId = vendorPositiveId($request['categoryId'] ?? null);
        $name = trim((string) ($request['name'] ?? ''));
        $description = trim((string) ($request['description'] ?? ''));
        $price = parsePrice($request['price'] ?? null);
        $imagePath = validateProductImagePath($request['imagePath'] ?? null, $stallId)
            ?? 'assets/main/no-image-set.png';
        $available = !empty($request['available']);

        if ($name === '' || mb_strlen($name, 'UTF-8') > 150) {
            throw new InvalidArgumentException('Item names must be 1–150 characters.');
        }
        if (mb_strlen($description, 'UTF-8') > 1000) {
            throw new InvalidArgumentException('Description is too long.');
        }
        $id = addProduct($pdo, $stallId, $categoryId, $name, $description ?: null, $price, $imagePath, $available);
        menuRespond(200, ['success' => true, 'productId' => $id, 'imagePath' => $imagePath]);
    }

    if ($action === 'product_update') {
        $productId = vendorPositiveId($request['productId'] ?? null);
        $product = getStallProduct($pdo, $stallId, $productId);
        if (!$product) {
            throw new VendorRequestException('Item not found.', 404);
        }
        $categoryId = vendorPositiveId($request['categoryId'] ?? null);
        $name = trim((string) ($request['name'] ?? ''));
        $description = trim((string) ($request['description'] ?? ''));
        $price = parsePrice($request['price'] ?? null);
        $currentImage = $product['image_path'];
        $requestedImage = $request['imagePath'] ?? $currentImage ?? 'assets/main/no-image-set.png';
        if ($requestedImage === '') {
            $requestedImage = $currentImage ?? 'assets/main/no-image-set.png';
        }
        $imagePath = validateProductImagePath($requestedImage, $stallId, $currentImage);
        $available = !empty($request['available']);

        if ($name === '' || mb_strlen($name, 'UTF-8') > 150) {
            throw new InvalidArgumentException('Item names must be 1–150 characters.');
        }
        if (!updateProduct($pdo, $productId, $stallId, $categoryId, $name, $description ?: null, $price, $imagePath, $available)
            && !getStallProduct($pdo, $stallId, $productId)) {
            throw new VendorRequestException('Item not found.', 404);
        }
        menuRespond(200, ['success' => true]);
    }

    if ($action === 'product_delete') {
        $productId = vendorPositiveId($request['productId'] ?? null);
        if (!deleteProduct($pdo, $productId, $stallId)) {
            throw new VendorRequestException('Item not found.', 404);
        }
        menuRespond(200, ['success' => true]);
    }

    if ($action === 'product_availability') {
        $productId = vendorPositiveId($request['productId'] ?? null);
        $available = !empty($request['available']);
        if (!setProductAvailability($pdo, $productId, $stallId, $available)) {
            throw new VendorRequestException('Item not found.', 404);
        }
        menuRespond(200, ['success' => true]);
    }

    if ($action === 'stall_toggle') {
        $isOpen = !empty($request['isOpen']);
        if (!setStallOpenStatus($pdo, $stallId, $vendorId, $isOpen)) {
            throw new VendorRequestException('Stall not found.', 404);
        }
        menuRespond(200, ['success' => true]);
    }

    throw new VendorRequestException('Unknown action.', 400);

} catch (InvalidArgumentException $error) {
    menuRespond(422, ['success' => false, 'message' => $error->getMessage()]);
} catch (JsonException $error) {
    menuRespond(400, ['success' => false, 'message' => 'Invalid JSON request.']);
} catch (VendorRequestException $error) {
    menuRespond($error->getCode() ?: 400, ['success' => false, 'message' => $error->getMessage()]);
} catch (RuntimeException $error) {
    menuRespond(409, ['success' => false, 'message' => $error->getMessage()]);
} catch (Throwable $error) {
    error_log('Vendor menu action failed: ' . $error->getMessage());
    menuRespond(500, ['success' => false, 'message' => 'Unable to complete that action. Please try again.']);
}
