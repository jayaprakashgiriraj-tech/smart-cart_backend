<?php
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../config/db.php';

$userId = isset($_GET["user_id"]) ? (int)$_GET["user_id"] : null;
$email = trim($_GET["email"] ?? "");

if (!$userId && empty($email)) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "success" => false,
        "message" => "user_id or email query parameter is required"
    ]);
    exit;
}

try {
    if (!$userId && !empty($email)) {
        $uStmt = $conn->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $uStmt->execute([':email' => $email]);
        $uRow = $uStmt->fetch();
        if ($uRow) $userId = (int)$uRow['id'];
    }

    if (!$userId) {
        http_response_code(404);
        echo json_encode([
            "status" => "error",
            "success" => false,
            "message" => "User not found"
        ]);
        exit;
    }

    $sql = "SELECT w.id as wishlist_id, w.price_drop_alert, w.created_at as saved_at,
                   p.id, p.name, p.brand, p.category_name, p.price_text, p.numeric_price, 
                   p.rating, p.review_count, p.stock_quantity, p.match_score_text, p.specs
            FROM wishlist w
            INNER JOIN products p ON w.product_id = p.id
            WHERE w.user_id = :uid
            ORDER BY w.created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':uid' => $userId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items as &$item) {
        $item['numeric_price'] = (int)$item['numeric_price'];
        $item['stock_quantity'] = (int)$item['stock_quantity'];
        $item['price_drop_alert'] = (bool)$item['price_drop_alert'];
        $item['is_available'] = $item['stock_quantity'] > 0;
        $item['stock_status'] = $item['is_available'] ? "In Stock" : "Unavailable";
    }

    echo json_encode([
        "status" => "success",
        "success" => true,
        "count" => count($items),
        "wishlist" => $items
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "success" => false,
        "message" => "Failed to load wishlist: " . $e->getMessage()
    ]);
}

