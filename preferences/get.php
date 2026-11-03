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
    $stmt = $conn->prepare("SELECT * FROM user_preferences WHERE user_id = :uid LIMIT 1");
    $stmt->execute([':uid' => $userId]);
    $pref = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($pref) {
        $categories = json_decode($pref["categories"] ?? "[]", true);
        $priorities = json_decode($pref["feature_priorities"] ?? "[]", true);

        echo json_encode([
            "status" => "success",
            "success" => true,
            "user_id" => $userId,
            "preferences" => [
                "categories" => is_array($categories) ? $categories : ["Audio", "Mobiles", "Laptops"],
                "audio_budget" => (float)$pref["audio_budget"],
                "feature_priorities" => is_array($priorities) ? $priorities : ["Clear mic", "Comfort", "Battery"],
                "price_drop_alerts" => (bool)$pref["price_drop_alerts"],
                "new_recommendations" => (bool)$pref["new_recommendations"],
                "use_shopping_history" => (bool)$pref["use_shopping_history"],
                "updated_at" => $pref["updated_at"]
            ]
        ]);
    } else {
        // Return UI defaults from PreferencesScreen.kt
        echo json_encode([
            "status" => "success",
            "success" => true,
            "user_id" => $userId,
            "is_default" => true,
            "preferences" => [
                "categories" => ["Audio", "Mobiles", "Laptops"],
                "audio_budget" => 3000.0,
                "feature_priorities" => ["Clear mic", "Comfort", "Battery"],
                "price_drop_alerts" => true,
                "new_recommendations" => false,
                "use_shopping_history" => true,
                "updated_at" => null
            ]
        ]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "success" => false, "message" => "Failed to load preferences: " . $e->getMessage()]);
}

