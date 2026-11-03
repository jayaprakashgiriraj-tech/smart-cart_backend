<?php
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../config/db.php';

$productId = trim($_GET["product_id"] ?? $_GET["id"] ?? "");
$productName = trim($_GET["name"] ?? "");
$budget = isset($_GET["budget"]) && is_numeric($_GET["budget"]) ? (int)$_GET["budget"] : 3000;

if ($productId === "" && $productName === "") {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "success" => false,
        "message" => "product_id or name parameter is required"
    ]);
    exit;
}

try {
    if (!empty($productId)) {
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $productId]);
    } else {
        $stmt = $conn->prepare("SELECT * FROM products WHERE name = :name LIMIT 1");
        $stmt->execute([':name' => $productName]);
    }

    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        http_response_code(404);
        echo json_encode([
            "status" => "error",
            "success" => false,
            "message" => "Product not found"
        ]);
        exit;
    }

    $price = (int)$product['numeric_price'];
    $diff = $budget - $price;

    // AI Fit Analysis
    $budgetAnalysis = ($diff >= 0) 
        ? "Budget: ₹" . number_format($diff) . " below your ₹" . number_format($budget) . " limit."
        : "Budget: ₹" . number_format(abs($diff)) . " above your ₹" . number_format($budget) . " limit, but provides premium features.";

    $useCaseAnalysis = "Use-case: " . ($product['call_mic'] !== "N/A" ? $product['call_mic'] . " mic quality" : "optimised performance") . 
                       " with " . ($product['multipoint'] === "Yes" ? "multipoint switching between phone and laptop." : "seamless wireless connection.");

    $routineAnalysis = "Routine: " . ($product['battery'] !== "N/A" ? $product['battery'] . " cuts down frequent charging breaks." : "reliable all-day performance for your schedule.");

    // Fetch AI Pros & Cons
    $revStmt = $conn->prepare("SELECT pros, cons FROM ai_reviews WHERE product_id = :pid LIMIT 1");
    $revStmt->execute([':pid' => $product['id']]);
    $revRow = $revStmt->fetch(PDO::FETCH_ASSOC);

    $pros = $revRow ? json_decode($revRow['pros'] ?? "[]", true) : [
        "Strong value score of " . $product['value_score'] . "/10",
        $product['best_for'],
        "High match rating of " . $product['match_percentage'] . "%"
    ];

    $cons = $revRow ? json_decode($revRow['cons'] ?? "[]", true) : [
        "Feature set strictly targeted to " . $product['category_name'],
        "Standard warranty coverage (" . $product['warranty'] . ")"
    ];

    // Alternative product
    $altStmt = $conn->prepare("SELECT id, name, brand, price_text, rating, match_percentage FROM products WHERE category_name = :cat AND id != :pid ORDER BY match_percentage DESC LIMIT 1");
    $altStmt->execute([':cat' => $product['category_name'], ':pid' => $product['id']]);
    $alternative = $altStmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "success" => true,
        "product" => [
            "id" => $product['id'],
            "name" => $product['name'],
            "brand" => $product['brand'],
            "price" => $product['price_text'],
            "match_percentage" => (int)$product['match_percentage'],
            "value_score" => (float)$product['value_score']
        ],
        "ai_fit_reasons" => [
            "headline" => "Why this product is right for you",
            "budget_fit" => $budgetAnalysis,
            "use_case_fit" => $useCaseAnalysis,
            "routine_fit" => $routineAnalysis
        ],
        "pros" => $pros,
        "cons" => $cons,
        "alternative_product" => $alternative
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "success" => false,
        "message" => "AI analysis failed: " . $e->getMessage()
    ]);
}

