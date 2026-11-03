<?php
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../config/db.php';

try {
    $stmt = $conn->query("SELECT id, name, icon, description FROM categories ORDER BY id ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Attach subcategories and count for each category
    foreach ($categories as &$cat) {
        $subStmt = $conn->prepare("SELECT DISTINCT subcategory FROM products WHERE category_name = :cat ORDER BY subcategory ASC");
        $subStmt->execute([':cat' => $cat['name']]);
        $cat['subcategories'] = $subStmt->fetchAll(PDO::FETCH_COLUMN);

        $cntStmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE category_name = :cat");
        $cntStmt->execute([':cat' => $cat['name']]);
        $cat['product_count'] = (int)$cntStmt->fetchColumn();
    }

    echo json_encode([
        "status" => "success",
        "success" => true,
        "count" => count($categories),
        "categories" => $categories
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "success" => false,
        "message" => "Failed to load categories: " . $e->getMessage()
    ]);
}

