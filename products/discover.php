<?php
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../config/db.php';

$need = trim($_GET["need"] ?? "Clear calls");
$query = trim($_GET["search"] ?? $_GET["q"] ?? "");

try {
    $sql = "SELECT DISTINCT p.* FROM products p 
            INNER JOIN product_needs pn ON p.id = pn.product_id
            WHERE 1=1";
    $params = [];

    if (!empty($need) && strtolower($need) !== "all") {
        $sql .= " AND pn.need_tag = :need";
        $params[':need'] = $need;
    }

    if (!empty($query)) {
        $sql .= " AND (p.name LIKE :q OR p.brand LIKE :q OR p.specs LIKE :q)";
        $params[':q'] = "%" . $query . "%";
    }

    $sql .= " ORDER BY p.match_percentage DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as &$p) {
        $p['numeric_price'] = (int)$p['numeric_price'];
        $p['stock_quantity'] = (int)$p['stock_quantity'];
        $p['match_percentage'] = (int)$p['match_percentage'];
        $p['is_available'] = $p['stock_quantity'] > 0;
        $p['stock_status'] = $p['is_available'] ? "In Stock" : "Unavailable";

        $needsStmt = $conn->prepare("SELECT need_tag FROM product_needs WHERE product_id = :pid");
        $needsStmt->execute([':pid' => $p['id']]);
        $p['needs'] = $needsStmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Also fetch all available need chips
    $allNeedsStmt = $conn->query("SELECT DISTINCT need_tag FROM product_needs ORDER BY need_tag ASC");
    $availableNeeds = $allNeedsStmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode([
        "status" => "success",
        "success" => true,
        "selected_need" => $need,
        "available_needs" => $availableNeeds,
        "count" => count($products),
        "products" => $products
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "success" => false,
        "message" => "Discover failed: " . $e->getMessage()
    ]);
}

