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
$prod1 = trim($data["prod1"] ?? "");
$prod2 = trim($data["prod2"] ?? "");
$title = trim($data["title"] ?? "1 comparison · Online classes");

if (!$userId && !empty($email)) {
    $uStmt = $conn->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $uStmt->execute([':email' => $email]);
    $uRow = $uStmt->fetch();
    if ($uRow) $userId = (int)$uRow['id'];
}

if (!$userId || empty($prod1) || empty($prod2)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "success" => false, "message" => "user_id, prod1, and prod2 are required"]);
    exit;
}

try {
   
$fetchP = function($q) use ($conn) {
    $stmt = $conn->prepare(
        "SELECT id, name, match_percentage, best_for
         FROM products
         WHERE id = :product_id OR name = :product_name
         LIMIT 1"
    );

    $stmt->execute([
        ':product_id' => $q,
        ':product_name' => $q
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
};


    $p1 = $fetchP($prod1);
    $p2 = $fetchP($prod2);

    if (!$p1 || !$p2) {
        http_response_code(404);
        echo json_encode(["status" => "error", "success" => false, "message" => "One or both products not found"]);
        exit;
    }

    $winner = ($p1['match_percentage'] >= $p2['match_percentage']) ? $p1 : $p2;
    $summary = "{$winner['name']} is rated higher for your priority use case ({$winner['best_for']}).";

    $insSql = "INSERT INTO saved_comparisons (user_id, product1_id, product2_id, winner_product_id, comparison_title, summary)
               VALUES (:uid, :p1, :p2, :w, :t, :s)";
    $stmt = $conn->prepare($insSql);
    $stmt->execute([
        ':uid' => $userId,
        ':p1' => $p1['id'],
        ':p2' => $p2['id'],
        ':w' => $winner['id'],
        ':t' => $title,
        ':s' => $summary
    ]);

    echo json_encode([
        "status" => "success",
        "success" => true,
        "message" => "Comparison saved successfully",
        "comparison_id" => (int)$conn->lastInsertId(),
        "summary" => $summary
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "success" => false, "message" => "Failed to save comparison: " . $e->getMessage()]);
}

