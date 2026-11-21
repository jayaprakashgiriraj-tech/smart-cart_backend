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
$comparisonId = isset($data["comparison_id"]) ? (int)$data["comparison_id"] : null;
$prod1 = trim($data["prod1"] ?? "");
$prod2 = trim($data["prod2"] ?? "");

if (!$userId && !empty($email)) {
    $uStmt = $conn->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $uStmt->execute([':email' => $email]);
    $uRow = $uStmt->fetch();
    if ($uRow) $userId = (int)$uRow['id'];
}

if (!$userId && empty($prod1) && empty($prod2) && !$comparisonId) {
    http_response_code(400);
    echo json_encode(["status" => "error", "success" => false, "message" => "user_id/email and comparison identifiers are required"]);
    exit;
}

try {
    if ($comparisonId && $userId) {
        $del = $conn->prepare("DELETE FROM saved_comparisons WHERE id = :id AND user_id = :uid");
        $del->execute([':id' => $comparisonId, ':uid' => $userId]);
    } else if ($comparisonId) {
        $del = $conn->prepare("DELETE FROM saved_comparisons WHERE id = :id");
        $del->execute([':id' => $comparisonId]);
    } else if (!empty($prod1) && !empty($prod2)) {
        // Find product ids
        $p1Stmt = $conn->prepare("SELECT id FROM products WHERE id = :q OR name = :q LIMIT 1");
        $p1Stmt->execute([':q' => $prod1]);
        $p1Id = $p1Stmt->fetchColumn() ?: $prod1;

        $p2Stmt = $conn->prepare("SELECT id FROM products WHERE id = :q OR name = :q LIMIT 1");
        $p2Stmt->execute([':q' => $prod2]);
        $p2Id = $p2Stmt->fetchColumn() ?: $prod2;

        if ($userId) {
            $del = $conn->prepare("DELETE FROM saved_comparisons WHERE user_id = :uid AND ((product1_id = :p1 AND product2_id = :p2) OR (product1_id = :p2 AND product2_id = :p1))");
            $del->execute([':uid' => $userId, ':p1' => $p1Id, ':p2' => $p2Id]);
        } else {
            $del = $conn->prepare("DELETE FROM saved_comparisons WHERE (product1_id = :p1 AND product2_id = :p2) OR (product1_id = :p2 AND product2_id = :p1)");
            $del->execute([':p1' => $p1Id, ':p2' => $p2Id]);
        }
    }

    echo json_encode(["status" => "success", "success" => true, "message" => "Comparison removed"]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "success" => false, "message" => "Failed to remove comparison: " . $e->getMessage()]);
}

