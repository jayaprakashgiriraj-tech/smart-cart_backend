<?php
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../config/db.php';

$category = trim($_GET["category"] ?? "");
$subcategory = trim($_GET["subcategory"] ?? "");
$limit = isset($_GET["limit"]) ? (int)$_GET["limit"] : 50;

try {
    $where = [];
    $params = [];

    if ($category !== "" && strtolower($category) !== "all") {
        $where[] = "category_name = :category";
        $params[':category'] = $category;
    }

    if ($subcategory !== "" && strtolower($subcategory) !== "all") {
        $where[] = "subcategory = :subcategory";
        $params[':subcategory'] = $subcategory;
    }

    $sql = "SELECT * FROM products";
    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    $sql .= " ORDER BY match_percentage DESC LIMIT " . max(1, min(100, $limit));

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as &$p) {
        $p['numeric_price'] = (int)$p['numeric_price'];
        $p['stock_quantity'] = (int)$p['stock_quantity'];
        $p['match_percentage'] = (int)$p['match_percentage'];
        $p['review_count'] = (int)$p['review_count'];
        $p['value_score'] = (float)$p['value_score'];
        $p['rating'] = (string)$p['rating'];

        // Availability calculation
        $p['is_available'] = $p['stock_quantity'] > 0;
        $p['stock_status'] = $p['is_available'] ? "In Stock" : "Unavailable";

        // Fetch needs tags
        $needsStmt = $conn->prepare("SELECT need_tag FROM product_needs WHERE product_id = :pid");
        $needsStmt->execute([':pid' => $p['id']]);
        $p['needs'] = $needsStmt->fetchAll(PDO::FETCH_COLUMN);
    }

    echo json_encode([
        "status" => "success",
        "success" => true,
        "count" => count($products),
        "products" => $products
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "success" => false,
        "message" => "Failed to load products: " . $e->getMessage()
    ]);
}

