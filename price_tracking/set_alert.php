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
$targetPrice = isset($data["target_price"]) ? (int)str_replace(['₹', ',', ' '], '', (string)$data["target_price"]) : null;

if (!$userId && !empty($email)) {
    $uStmt = $conn->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $uStmt->execute([':email' => $email]);
    $uRow = $uStmt->fetch();
    if ($uRow) $userId = (int)$uRow['id'];
}

if (!$userId || empty($productId) || !$targetPrice) {
    http_response_code(400);
    echo json_encode(["status" => "error", "success" => false, "message" => "user_id, product_id, and valid target_price are required"]);
    exit;
}

try {
    $pStmt = $conn->prepare("SELECT id, name, price_text, numeric_price FROM products WHERE id = :pid LIMIT 1");
    $pStmt->execute([':pid' => $productId]);
    $prod = $pStmt->fetch(PDO::FETCH_ASSOC);

    if (!$prod) {
        http_response_code(404);
        echo json_encode(["status" => "error", "success" => false, "message" => "Product not found"]);
        exit;
    }

    $upsertSql = "INSERT INTO price_tracking (user_id, product_id, target_price, is_active)
                  VALUES (:uid, :pid, :tp, 1)
                  ON DUPLICATE KEY UPDATE target_price = VALUES(target_price), is_active = 1";
    $stmt = $conn->prepare($upsertSql);
    $stmt->execute([':uid' => $userId, ':pid' => $productId, ':tp' => $targetPrice]);

    echo json_encode([
        "status" => "success",
        "success" => true,
        "message" => "Target price saved successfully",
        "tracking" => [
            "product_id" => $prod['id'],
            "product_name" => $prod['name'],
            "current_price" => $prod['price_text'],
            "target_price" => "₹" . number_format($targetPrice),
            "numeric_target_price" => $targetPrice,
            "status" => "Active"
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "success" => false, "message" => "Failed to set price alert: " . $e->getMessage()]);
}

