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
$priceDropAlert = isset($data["price_drop_alert"]) ? ($data["price_drop_alert"] ? 1 : 0) : 1;

if (!$userId && !empty($email)) {
    $uStmt = $conn->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $uStmt->execute([':email' => $email]);
    $uRow = $uStmt->fetch();
    if ($uRow) $userId = (int)$uRow['id'];
}

if (!$userId || empty($productId)) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "success" => false,
        "message" => "user_id (or email) and product_id are required"
    ]);
    exit;
}

try {
    // Check if product exists
    $pStmt = $conn->prepare("SELECT id FROM products WHERE id = :pid LIMIT 1");
    $pStmt->execute([':pid' => $productId]);
    if (!$pStmt->fetch()) {
        http_response_code(404);
        echo json_encode(["status" => "error", "success" => false, "message" => "Product not found"]);
        exit;
    }

    // Check if already in wishlist
    $checkStmt = $conn->prepare("SELECT id FROM wishlist WHERE user_id = :uid AND product_id = :pid LIMIT 1");
    $checkStmt->execute([':uid' => $userId, ':pid' => $productId]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // Remove from wishlist
        $delStmt = $conn->prepare("DELETE FROM wishlist WHERE id = :id");
        $delStmt->execute([':id' => $existing['id']]);

        echo json_encode([
            "status" => "success",
            "success" => true,
            "is_wishlisted" => false,
            "message" => "Product removed from wishlist",
            "product_id" => $productId
        ]);
    } else {
        // Add to wishlist
        $addStmt = $conn->prepare("INSERT INTO wishlist (user_id, product_id, price_drop_alert) VALUES (:uid, :pid, :alert)");
        $addStmt->execute([':uid' => $userId, ':pid' => $productId, ':alert' => $priceDropAlert]);

        echo json_encode([
            "status" => "success",
            "success" => true,
            "is_wishlisted" => true,
            "message" => "Product added to wishlist",
            "product_id" => $productId
        ]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "success" => false,
        "message" => "Wishlist toggle failed: " . $e->getMessage()
    ]);
}

