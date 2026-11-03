<?php
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../config/db.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["status" => "error", "success" => false, "message" => "Only POST requests are allowed"]);
    exit;
}

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);
if (!is_array($data)) $data = $_POST;

$userId = isset($data["user_id"]) ? (int)$data["user_id"] : null;
$email = trim($data["email"] ?? "");
$productId = trim($data["product_id"] ?? "");
$quantity = isset($data["quantity"]) ? max(1, (int)$data["quantity"]) : 1;

if (!$userId && !empty($email)) {
    $uStmt = $conn->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $uStmt->execute([':email' => $email]);
    $uRow = $uStmt->fetch();
    if ($uRow) $userId = (int)$uRow['id'];
}

if (!$userId || empty($productId)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "success" => false, "message" => "user_id (or email) and product_id are required"]);
    exit;
}

try {
    // Check product and stock
    $pStmt = $conn->prepare("SELECT id, name, price_text, numeric_price, stock_quantity FROM products WHERE id = :pid LIMIT 1");
    $pStmt->execute([':pid' => $productId]);
    $prod = $pStmt->fetch(PDO::FETCH_ASSOC);

    if (!$prod) {
        http_response_code(404);
        echo json_encode(["status" => "error", "success" => false, "message" => "Product not found"]);
        exit;
    }

    if ($prod['stock_quantity'] <= 0) {
        http_response_code(400);
        echo json_encode(["status" => "error", "success" => false, "message" => "Product is currently out of stock / Unavailable"]);
        exit;
    }

    $upsertSql = "INSERT INTO cart (user_id, product_id, quantity) 
                  VALUES (:uid, :pid, :qty) 
                  ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)";
    $stmt = $conn->prepare($upsertSql);
    $stmt->execute([':uid' => $userId, ':pid' => $productId, ':qty' => $quantity]);

    echo json_encode([
        "status" => "success",
        "success" => true,
        "message" => "Product added to cart successfully",
        "cart_item" => [
            "product_id" => $prod['id'],
            "name" => $prod['name'],
            "price" => $prod['price_text'],
            "quantity_added" => $quantity
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "success" => false, "message" => "Failed to add to cart: " . $e->getMessage()]);
}

