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

if (!$userId && !empty($email)) {
    $uStmt = $conn->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $uStmt->execute([':email' => $email]);
    $uRow = $uStmt->fetch();
    if ($uRow) $userId = (int)$uRow['id'];
}

if (!$userId) {
    http_response_code(400);
    echo json_encode(["status" => "error", "success" => false, "message" => "Valid user_id or email is required"]);
    exit;
}

$categories = $data["categories"] ?? ["Audio", "Mobiles", "Laptops"];
$categoriesJson = json_encode(is_array($categories) ? array_values($categories) : [$categories]);

$audioBudget = isset($data["audio_budget"]) ? (float)$data["audio_budget"] : 3000.00;

$priorities = $data["feature_priorities"] ?? ["Clear mic", "Comfort", "Battery"];
$prioritiesJson = json_encode(is_array($priorities) ? array_values($priorities) : [$priorities]);

$priceDropAlerts = isset($data["price_drop_alerts"]) ? ($data["price_drop_alerts"] ? 1 : 0) : 1;
$newRecommendations = isset($data["new_recommendations"]) ? ($data["new_recommendations"] ? 1 : 0) : 0;
$useShoppingHistory = isset($data["use_shopping_history"]) ? ($data["use_shopping_history"] ? 1 : 0) : 1;

try {
    $upsertSql = "INSERT INTO user_preferences 
                    (user_id, categories, audio_budget, feature_priorities, price_drop_alerts, new_recommendations, use_shopping_history)
                  VALUES 
                    (:uid, :cats, :budget, :prios, :alerts, :recs, :hist)
                  ON DUPLICATE KEY UPDATE 
                    categories = VALUES(categories),
                    audio_budget = VALUES(audio_budget),
                    feature_priorities = VALUES(feature_priorities),
                    price_drop_alerts = VALUES(price_drop_alerts),
                    new_recommendations = VALUES(new_recommendations),
                    use_shopping_history = VALUES(use_shopping_history)";

    $stmt = $conn->prepare($upsertSql);
    $stmt->execute([
        ':uid' => $userId,
        ':cats' => $categoriesJson,
        ':budget' => $audioBudget,
        ':prios' => $prioritiesJson,
        ':alerts' => $priceDropAlerts,
        ':recs' => $newRecommendations,
        ':hist' => $useShoppingHistory
    ]);

    echo json_encode([
        "status" => "success",
        "success" => true,
        "message" => "Preferences saved successfully",
        "user_id" => $userId,
        "preferences" => [
            "categories" => is_array($categories) ? $categories : [$categories],
            "audio_budget" => $audioBudget,
            "feature_priorities" => is_array($priorities) ? $priorities : [$priorities],
            "price_drop_alerts" => (bool)$priceDropAlerts,
            "new_recommendations" => (bool)$newRecommendations,
            "use_shopping_history" => (bool)$useShoppingHistory
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "success" => false, "message" => "Failed to save preferences: " . $e->getMessage()]);
}

