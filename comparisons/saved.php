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
    $sql = "SELECT sc.id as comparison_id, sc.comparison_title, sc.summary, sc.created_at,
                   p1.id as p1_id, p1.name as p1_name, p1.price_text as p1_price, p1.rating as p1_rating, p1.match_percentage as p1_match,
                   p2.id as p2_id, p2.name as p2_name, p2.price_text as p2_price, p2.rating as p2_rating, p2.match_percentage as p2_match,
                   w.id as winner_id, w.name as winner_name
            FROM saved_comparisons sc
            INNER JOIN products p1 ON sc.product1_id = p1.id
            INNER JOIN products p2 ON sc.product2_id = p2.id
            LEFT JOIN products w ON sc.winner_product_id = w.id
            WHERE sc.user_id = :uid
            ORDER BY sc.created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':uid' => $userId]);
    $comparisons = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "success" => true,
        "count" => count($comparisons),
        "comparisons" => $comparisons
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "success" => false, "message" => "Failed to load comparisons: " . $e->getMessage()]);
}

