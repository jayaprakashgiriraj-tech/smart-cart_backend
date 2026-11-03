<?php
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../config/db.php';

$p1Query = trim($_GET["prod1"] ?? "JBL Tune 510BT");
$p2Query = trim($_GET["prod2"] ?? "Soundcore H30i");

try {
    $fetchP = function($q) use ($conn) {
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = :id OR name = :name LIMIT 1");
        $stmt->execute([':id' => $q, ':name' => $q]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    };

    $p1 = $fetchP($p1Query);
    $p2 = $fetchP($p2Query);

    if (!$p1 || !$p2) {
        http_response_code(404);
        echo json_encode([
            "status" => "error",
            "success" => false,
            "message" => "One or both comparison products could not be found"
        ]);
        exit;
    }

    // Determine winner based on match score and value score
    $winner = ($p1['match_percentage'] >= $p2['match_percentage']) ? $p1 : $p2;

    $specRows = [
        [
            "label" => "Battery",
            "value1" => $p1['battery'],
            "value2" => $p2['battery'],
            "is_highlighted" => false,
            "winner_column" => (strcasecmp($p1['battery'], $p2['battery']) > 0) ? 1 : 2
        ],
        [
            "label" => "Call mic",
            "value1" => $p1['call_mic'],
            "value2" => $p2['call_mic'],
            "is_highlighted" => false,
            "winner_column" => 1
        ],
        [
            "label" => "Weight",
            "value1" => $p1['weight'],
            "value2" => $p2['weight'],
            "is_highlighted" => false,
            "winner_column" => 1
        ],
        [
            "label" => "Multipoint",
            "value1" => $p1['multipoint'],
            "value2" => $p2['multipoint'],
            "is_highlighted" => false,
            "winner_column" => null
        ],
        [
            "label" => "Active NC",
            "value1" => $p1['active_nc'],
            "value2" => $p2['active_nc'],
            "is_highlighted" => false,
            "winner_column" => null
        ],
        [
            "label" => "App EQ",
            "value1" => $p1['app_eq'],
            "value2" => $p2['app_eq'],
            "is_highlighted" => false,
            "winner_column" => ($p1['app_eq'] === "Yes") ? 1 : (($p2['app_eq'] === "Yes") ? 2 : null)
        ],
        [
            "label" => "Warranty",
            "value1" => $p1['warranty'],
            "value2" => $p2['warranty'],
            "is_highlighted" => false,
            "winner_column" => null
        ],
        [
            "label" => "Value (/10)",
            "value1" => (string)$p1['value_score'],
            "value2" => (string)$p2['value_score'],
            "is_highlighted" => true,
            "winner_column" => ($p1['value_score'] >= $p2['value_score']) ? 1 : 2
        ]
    ];

    $aiSummary = "{$winner['name']} is rated higher for your priority use case due to its superior {$winner['best_for']}, balanced weight ({$winner['weight']}), and high value score ({$winner['value_score']}/10).";

    echo json_encode([
        "status" => "success",
        "success" => true,
        "product1" => [
            "id" => $p1['id'],
            "name" => $p1['name'],
            "brand" => $p1['brand'],
            "price" => $p1['price_text'],
            "numeric_price" => (int)$p1['numeric_price'],
            "match_percentage" => (int)$p1['match_percentage'],
            "rating" => (string)$p1['rating']
        ],
        "product2" => [
            "id" => $p2['id'],
            "name" => $p2['name'],
            "brand" => $p2['brand'],
            "price" => $p2['price_text'],
            "numeric_price" => (int)$p2['numeric_price'],
            "match_percentage" => (int)$p2['match_percentage'],
            "rating" => (string)$p2['rating']
        ],
        "winner" => [
            "id" => $winner['id'],
            "name" => $winner['name'],
            "best_for" => $winner['best_for']
        ],
        "ai_summary" => $aiSummary,
        "comparison_matrix" => $specRows
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "success" => false,
        "message" => "Comparison failed: " . $e->getMessage()
    ]);
}

