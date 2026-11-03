<?php
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../config/db.php';

$userId = isset($_GET["user_id"]) ? (int)$_GET["user_id"] : null;
$email = trim($_GET["email"] ?? "");

if (!$userId && !empty($email)) {
    $uStmt = $conn->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $uStmt->execute([':email' => $email]);
    $uRow = $uStmt->fetch();
    if ($uRow) $userId = (int)$uRow['id'];
}

if (!$userId) {
    http_response_code(400);
    echo json_encode(["status" => "error", "success" => false, "message" => "user_id or email is required"]);
    exit;
}

try {
    $sql = "SELECT c.id as cart_id, c.quantity, c.created_at,
                   p.id as product_id, p.name, p.brand, p.category_name, p.price_text, 
                   p.numeric_price, p.stock_quantity
            FROM cart c
            INNER JOIN products p ON c.product_id = p.id
            WHERE c.user_id = :uid";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':uid' => $userId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalAmount = 0;
    $totalItems = 0;

    foreach ($items as &$item) {
        $item['quantity'] = (int)$item['quantity'];
        $item['numeric_price'] = (int)$item['numeric_price'];
        $item['item_total'] = $item['quantity'] * $item['numeric_price'];
        $item['is_available'] = (int)$item['stock_quantity'] > 0;
        $item['stock_status'] = $item['is_available'] ? "In Stock" : "Unavailable";

        $totalAmount += $item['item_total'];
        $totalItems += $item['quantity'];
    }

    echo json_encode([
        "status" => "success",
        "success" => true,
        "total_items" => $totalItems,
        "total_amount" => $totalAmount,
        "formatted_total" => "₹" . number_format($totalAmount),
        "cart" => $items
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "success" => false, "message" => "Failed to load cart: " . $e->getMessage()]);
}

