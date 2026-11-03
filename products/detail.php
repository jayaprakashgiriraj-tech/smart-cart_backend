<?php
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../config/db.php';

$id = trim($_GET["id"] ?? "");
$name = trim($_GET["name"] ?? "");

if ($id === "" && $name === "") {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "success" => false,
        "message" => "Product id or name is required"
    ]);
    exit;
}

try {
    if (!empty($id)) {
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
    } else {
        $stmt = $conn->prepare("SELECT * FROM products WHERE name = :name LIMIT 1");
        $stmt->execute([':name' => $name]);
    }

    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        http_response_code(404);
        echo json_encode([
            "status" => "error",
            "success" => false,
            "message" => "Product not found"
        ]);
        exit;
    }

    $product['numeric_price'] = (int)$product['numeric_price'];
    $product['stock_quantity'] = (int)$product['stock_quantity'];
    $product['match_percentage'] = (int)$product['match_percentage'];
    $product['review_count'] = (int)$product['review_count'];
    $product['value_score'] = (float)$product['value_score'];
    $product['rating'] = (string)$product['rating'];

    // Availability
    $product['is_available'] = $product['stock_quantity'] > 0;
    $product['stock_status'] = $product['is_available'] ? "In Stock" : "Unavailable";

    // Needs tags
    $needsStmt = $conn->prepare("SELECT need_tag FROM product_needs WHERE product_id = :pid");
    $needsStmt->execute([':pid' => $product['id']]);
    $product['needs'] = $needsStmt->fetchAll(PDO::FETCH_COLUMN);

    // AI Review snapshot if available
    $revStmt = $conn->prepare("SELECT * FROM ai_reviews WHERE product_id = :pid LIMIT 1");
    $revStmt->execute([':pid' => $product['id']]);
    $aiReview = $revStmt->fetch(PDO::FETCH_ASSOC);
    if ($aiReview) {
        $aiReview['pros'] = json_decode($aiReview['pros'] ?? "[]", true);
        $aiReview['cons'] = json_decode($aiReview['cons'] ?? "[]", true);
        $product['ai_review_snapshot'] = $aiReview;
    }

    // Similar Products
    $simStmt = $conn->prepare("SELECT id, name, brand, price_text, rating, match_percentage FROM products WHERE category_name = :cat AND id != :pid LIMIT 4");
    $simStmt->execute([':cat' => $product['category_name'], ':pid' => $product['id']]);
    $product['similar_products'] = $simStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "success" => true,
        "product" => $product
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "success" => false,
        "message" => "Failed to load product details: " . $e->getMessage()
    ]);
}

