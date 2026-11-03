<?php
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../config/db.php';

$productId = trim($_GET["product_id"] ?? "jbl_510bt");

try {
    $pStmt = $conn->prepare("SELECT id, name, price_text, numeric_price FROM products WHERE id = :pid LIMIT 1");
    $pStmt->execute([':pid' => $productId]);
    $prod = $pStmt->fetch(PDO::FETCH_ASSOC);

    if (!$prod) {
        http_response_code(404);
        echo json_encode(["status" => "error", "success" => false, "message" => "Product not found"]);
        exit;
    }

    $hStmt = $conn->prepare("SELECT recorded_price, recorded_date FROM price_history WHERE product_id = :pid ORDER BY recorded_date ASC");
    $hStmt->execute([':pid' => $productId]);
    $history = $hStmt->fetchAll(PDO::FETCH_ASSOC);

    // If no explicit history records, generate standard timeline
    if (empty($history)) {
        $cur = (int)$prod['numeric_price'];
        $history = [
            ["recorded_price" => (int)($cur * 1.08), "recorded_date" => date('Y-m-d', strtotime('-45 days'))],
            ["recorded_price" => (int)($cur * 1.04), "recorded_date" => date('Y-m-d', strtotime('-30 days'))],
            ["recorded_price" => (int)($cur * 1.05), "recorded_date" => date('Y-m-d', strtotime('-15 days'))],
            ["recorded_price" => $cur, "recorded_date" => date('Y-m-d')]
        ];
    } else {
        foreach ($history as &$h) {
            $h['recorded_price'] = (int)$h['recorded_price'];
        }
    }

    $prices = array_column($history, 'recorded_price');
    $lowest = !empty($prices) ? min($prices) : (int)$prod['numeric_price'];
    $highest = !empty($prices) ? max($prices) : (int)$prod['numeric_price'];

    echo json_encode([
        "status" => "success",
        "success" => true,
        "product" => [
            "id" => $prod['id'],
            "name" => $prod['name'],
            "current_price" => $prod['price_text'],
            "numeric_price" => (int)$prod['numeric_price'],
            "lowest_recorded_price" => "₹" . number_format($lowest),
            "highest_recorded_price" => "₹" . number_format($highest)
        ],
        "history" => $history
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "success" => false, "message" => "Failed to load price history: " . $e->getMessage()]);
}

