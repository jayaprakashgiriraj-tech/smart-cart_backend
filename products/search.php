<?php
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../config/db.php';

$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput, true);
if (!is_array($data)) {
    $data = $_GET;
}

$query = trim($data["query"] ?? $data["q"] ?? "");
$category = trim($data["category"] ?? "");
$maxPrice = isset($data["max_price"]) && is_numeric($data["max_price"]) ? (int)$data["max_price"] : null;
$sortBy = trim($data["sort_by"] ?? "match");
$userId = isset($data["user_id"]) && is_numeric($data["user_id"]) ? (int)$data["user_id"] : null;

try {
    $where = [];
    $params = [];

    // Query filter across name, brand, category, subcategory, description, specs
    if ($query !== "") {
        $where[] = "(
            p.name LIKE :q1 OR 
            p.brand LIKE :q2 OR 
            p.category_name LIKE :q3 OR 
            p.subcategory LIKE :q4 OR 
            p.description LIKE :q5 OR 
            p.specs LIKE :q6 OR 
            p.best_for LIKE :q7 OR
            EXISTS (SELECT 1 FROM product_needs pn WHERE pn.product_id = p.id AND pn.need_tag LIKE :q8)
        )";
        $searchTerm = "%" . $query . "%";
        $params[':q1'] = $searchTerm;
        $params[':q2'] = $searchTerm;
        $params[':q3'] = $searchTerm;
        $params[':q4'] = $searchTerm;
        $params[':q5'] = $searchTerm;
        $params[':q6'] = $searchTerm;
        $params[':q7'] = $searchTerm;
        $params[':q8'] = $searchTerm;
    }

    // Category filter
    if ($category !== "" && strtolower($category) !== "all") {
        $where[] = "p.category_name = :category";
        $params[':category'] = $category;
    }

    // Max budget / price filter
    if ($maxPrice !== null && $maxPrice > 0) {
        $where[] = "p.numeric_price <= :max_price";
        $params[':max_price'] = $maxPrice;
    }

    $sql = "SELECT DISTINCT p.* FROM products p";
    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }

    // Sort mapping
    switch (strtolower($sortBy)) {
        case "price: low to high":
        case "price_asc":
            $sql .= " ORDER BY p.numeric_price ASC";
            break;
        case "price: high to low":
        case "price_desc":
            $sql .= " ORDER BY p.numeric_price DESC";
            break;
        case "rating":
            $sql .= " ORDER BY p.rating DESC, p.review_count DESC";
            break;
        case "match":
        case "popularity":
        default:
            $sql .= " ORDER BY p.match_percentage DESC, p.value_score DESC";
            break;
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Save search query into history if user is provided
    if ($userId && $query !== "") {
        try {
            $shStmt = $conn->prepare("INSERT INTO search_history (user_id, search_query, results_count) VALUES (:uid, :q, :cnt)");
            $shStmt->execute([':uid' => $userId, ':q' => $query, ':cnt' => count($products)]);
        } catch (Exception $e) {}
    }

    // Handle Empty Searches
    if (empty($products)) {
        echo json_encode([
            "status" => "success",
            "success" => true,
            "message" => "No items found",
            "count" => 0,
            "query" => $query,
            "category" => $category,
            "max_price" => $maxPrice,
            "products" => []
        ]);
        exit;
    }

    foreach ($products as &$p) {
        $p['numeric_price'] = (int)$p['numeric_price'];
        $p['stock_quantity'] = (int)$p['stock_quantity'];
        $p['match_percentage'] = (int)$p['match_percentage'];
        $p['review_count'] = (int)$p['review_count'];
        $p['value_score'] = (float)$p['value_score'];
        $p['rating'] = (string)$p['rating'];

        // Availability requirement: Handle zero-stock products as Unavailable
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
        "query" => $query,
        "category" => $category,
        "max_price" => $maxPrice,
        "sort_by" => $sortBy,
        "products" => $products
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "success" => false,
        "message" => "Search query failed: " . $e->getMessage()
    ]);
}

