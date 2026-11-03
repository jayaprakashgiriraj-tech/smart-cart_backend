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
$cartId = isset($data["cart_id"]) ? (int)$data["cart_id"] : null;
$productId = trim($data["product_id"] ?? "");

if (!$userId || (!$cartId && empty($productId))) {
    http_response_code(400);
    echo json_encode(["status" => "error", "success" => false, "message" => "user_id and either cart_id or product_id are required"]);
    exit;
}

try {
    if ($cartId) {
        $stmt = $conn->prepare("DELETE FROM cart WHERE id = :cid AND user_id = :uid");
        $stmt->execute([':cid' => $cartId, ':uid' => $userId]);
    } else {
        $stmt = $conn->prepare("DELETE FROM cart WHERE product_id = :pid AND user_id = :uid");
        $stmt->execute([':pid' => $productId, ':uid' => $userId]);
    }

    echo json_encode([
        "status" => "success",
        "success" => true,
        "message" => "Item removed from cart"
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "success" => false, "message" => "Failed to remove item: " . $e->getMessage()]);
}

