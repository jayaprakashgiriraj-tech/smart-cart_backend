<?php
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../config/db.php';

$category = trim($_GET["category"] ?? "");
$userId = isset($_GET["user_id"]) ? (int)$_GET["user_id"] : null;

try {
    // If category not specified, check user preferences
    if (empty($category) && $userId) {
        $prefStmt = $conn->prepare("SELECT categories FROM user_preferences WHERE user_id = :uid LIMIT 1");
        $prefStmt->execute([':uid' => $userId]);
        $prefRow = $prefStmt->fetch(PDO::FETCH_ASSOC);
        if ($prefRow && !empty($prefRow['categories'])) {
            $catArr = json_decode($prefRow['categories'], true);
            if (is_array($catArr) && !empty($catArr)) {
                $category = $catArr[0];
            }
        }
    }

    if (empty($category)) {
        $category = "Audio";
    }

    $stmt = $conn->prepare("SELECT * FROM products WHERE category_name = :cat ORDER BY match_percentage DESC, value_score DESC LIMIT 6");
    $stmt->execute([':cat' => $category]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items as &$p) {
        $p['numeric_price'] = (int)$p['numeric_price'];
        $p['stock_quantity'] = (int)$p['stock_quantity'];
        $p['match_percentage'] = (int)$p['match_percentage'];
        $p['value_score'] = (float)$p['value_score'];
        $p['is_available'] = $p['stock_quantity'] > 0;
        $p['stock_status'] = $p['is_available'] ? "In Stock" : "Unavailable";

        $needsStmt = $conn->prepare("SELECT need_tag FROM product_needs WHERE product_id = :pid");
        $needsStmt->execute([':pid' => $p['id']]);
        $p['needs'] = $needsStmt->fetchAll(PDO::FETCH_COLUMN);
    }

    $topPick = !empty($items) ? $items[0] : null;

    echo json_encode([
        "status" => "success",
        "success" => true,
        "category" => $category,
        "top_pick" => $topPick,
        "recommendations" => $items
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "success" => false,
        "message" => "Failed to load recommendations: " . $e->getMessage()
    ]);
}

