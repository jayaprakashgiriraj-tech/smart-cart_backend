<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "OPTIONS") {
    http_response_code(200);
    exit;
}

// Database Connection (matching config/db.php)
$conn = null;
try {
    $conn = new PDO(
        "mysql:host=localhost;dbname=smart_cart;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 2
        ]
    );
} catch (Exception $e) {
    error_log("Database connection in sync_product_prices unavailable: " . $e->getMessage());
}
require_once __DIR__ . '/config/credentials.php';
require_once __DIR__ . '/services/AmazonCreatorsService.php';
require_once __DIR__ . '/services/FlipkartAffiliateService.php';

// Accept JSON body or Query Params
$body = json_decode(file_get_contents('php://input'), true) ?? [];
$productId = trim($body['product_id'] ?? $_GET['product_id'] ?? $_POST['product_id'] ?? '');
$productName = trim($body['product_name'] ?? $_GET['product_name'] ?? $_POST['product_name'] ?? '');

if (empty($productId) && empty($productName)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'success' => false,
        'message' => 'Please provide "product_id" or "product_name" to synchronize prices.'
    ], JSON_UNESCAPED_SLASHES);
    exit;
}

if (!isset($conn) || !($conn instanceof PDO)) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'success' => false,
        'message' => 'Database connection unavailable.'
    ], JSON_UNESCAPED_SLASHES);
    exit;
}

// 1. Resolve product from DB
$resolvedProduct = null;
try {
    if (!empty($productId)) {
        $stmt = $conn->prepare("SELECT id, name FROM products WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $productId]);
        $resolvedProduct = $stmt->fetch();
    }
    if (!$resolvedProduct && !empty($productName)) {
        $stmt = $conn->prepare("SELECT id, name FROM products WHERE name LIKE :name LIMIT 1");
        $stmt->execute([':name' => '%' . $productName . '%']);
        $resolvedProduct = $stmt->fetch();
    }
} catch (Exception $e) {
    error_log("Sync query error: " . $e->getMessage());
}

$finalId = $resolvedProduct['id'] ?? $productId;
$finalName = $resolvedProduct['name'] ?? $productName;

$storesUpdated = [];
$errors = [];

// 2. Fetch Amazon
$amazonService = new AmazonCreatorsService();
$amzResult = $amazonService->searchProducts($finalName, 1);
if ($amzResult['success'] && !empty($amzResult['items'])) {
    $item = $amzResult['items'][0];
    try {
        $stmt = $conn->prepare("INSERT INTO store_platform_prices 
            (product_id, platform, platform_item_id, title, price, original_price, discount_percent, rating, ratings_count, in_stock, seller_name, delivery_info, offer_text, product_url, affiliate_url, image_url, last_synced_at)
            VALUES 
            (:pid, 'amazon', :item_id, :title, :price, :orig, :disc, :rating, :rcount, :instock, :seller, :deliv, :offer, :purl, :aurl, :img, NOW())
            ON DUPLICATE KEY UPDATE 
            title = VALUES(title),
            price = VALUES(price),
            original_price = VALUES(original_price),
            discount_percent = VALUES(discount_percent),
            rating = VALUES(rating),
            ratings_count = VALUES(ratings_count),
            in_stock = VALUES(in_stock),
            seller_name = VALUES(seller_name),
            delivery_info = VALUES(delivery_info),
            offer_text = VALUES(offer_text),
            product_url = VALUES(product_url),
            affiliate_url = VALUES(affiliate_url),
            image_url = VALUES(image_url),
            last_synced_at = NOW()");

        $stmt->execute([
            ':pid' => $finalId,
            ':item_id' => $item['asin'] ?? '',
            ':title' => $item['title'],
            ':price' => $item['price'],
            ':orig' => $item['original_price'],
            ':disc' => $item['discount_percent'],
            ':rating' => (float)$item['rating'],
            ':rcount' => $item['ratings_count'],
            ':instock' => $item['in_stock'] ? 1 : 0,
            ':seller' => $item['seller'],
            ':deliv' => $item['delivery'],
            ':offer' => $item['offer'],
            ':purl' => $item['product_url'],
            ':aurl' => $item['affiliate_url'],
            ':img' => $item['image_url']
        ]);
        $storesUpdated[] = 'amazon';
    } catch (Exception $e) {
        $errors['amazon_db'] = $e->getMessage();
    }
} else {
    $errors['amazon_api'] = [
        'error_code' => $amzResult['error_code'] ?? 'AMAZON_UNAVAILABLE',
        'message' => $amzResult['message'] ?? 'No items found'
    ];
}

// 3. Fetch Flipkart
$flipkartService = new FlipkartAffiliateService();
$flpResult = $flipkartService->searchProducts($finalName, 1);
if ($flpResult['success'] && !empty($flpResult['items'])) {
    $item = $flpResult['items'][0];
    try {
        $stmt = $conn->prepare("INSERT INTO store_platform_prices 
            (product_id, platform, platform_item_id, title, price, original_price, discount_percent, rating, ratings_count, in_stock, seller_name, delivery_info, offer_text, product_url, affiliate_url, image_url, last_synced_at)
            VALUES 
            (:pid, 'flipkart', :item_id, :title, :price, :orig, :disc, :rating, :rcount, :instock, :seller, :deliv, :offer, :purl, :aurl, :img, NOW())
            ON DUPLICATE KEY UPDATE 
            title = VALUES(title),
            price = VALUES(price),
            original_price = VALUES(original_price),
            discount_percent = VALUES(discount_percent),
            rating = VALUES(rating),
            ratings_count = VALUES(ratings_count),
            in_stock = VALUES(in_stock),
            seller_name = VALUES(seller_name),
            delivery_info = VALUES(delivery_info),
            offer_text = VALUES(offer_text),
            product_url = VALUES(product_url),
            affiliate_url = VALUES(affiliate_url),
            image_url = VALUES(image_url),
            last_synced_at = NOW()");

        $stmt->execute([
            ':pid' => $finalId,
            ':item_id' => $item['product_id'] ?? '',
            ':title' => $item['title'],
            ':price' => $item['price'],
            ':orig' => $item['original_price'],
            ':disc' => $item['discount_percent'],
            ':rating' => (float)$item['rating'],
            ':rcount' => $item['ratings_count'],
            ':instock' => $item['in_stock'] ? 1 : 0,
            ':seller' => $item['seller'],
            ':deliv' => $item['delivery'],
            ':offer' => $item['offer'],
            ':purl' => $item['product_url'],
            ':aurl' => $item['affiliate_url'],
            ':img' => $item['image_url']
        ]);
        $storesUpdated[] = 'flipkart';
    } catch (Exception $e) {
        $errors['flipkart_db'] = $e->getMessage();
    }
} else {
    $errors['flipkart_api'] = [
        'error_code' => $flpResult['error_code'] ?? 'FLIPKART_UNAVAILABLE',
        'message' => $flpResult['message'] ?? 'No items found'
    ];
}

$isFullSuccess = count($storesUpdated) === 2;
$isPartial = count($storesUpdated) > 0;

echo json_encode([
    'status' => $isFullSuccess ? 'success' : ($isPartial ? 'partial_success' : 'failed'),
    'product_id' => $finalId,
    'product_name' => $finalName,
    'timestamp' => date('c'),
    'stores_updated' => $storesUpdated,
    'diagnostics' => $errors,
    'message' => $isFullSuccess 
        ? 'Synchronized live Amazon and Flipkart prices into MySQL store_platform_prices table.' 
        : ($isPartial ? 'Partially synchronized available platform prices.' : 'Could not synchronize live prices. Check credentials and diagnostics.')
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
