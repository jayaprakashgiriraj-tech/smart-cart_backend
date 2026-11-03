<?php
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../config/db.php';

try {
    // 1. Categories
    $catStmt = $conn->query("SELECT name, icon FROM categories ORDER BY id ASC");
    $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Popular Searches
    $popularSearches = [
        ["text" => "Headphones under ₹3k", "has_trending_icon" => true],
        ["text" => "Study laptops", "has_trending_icon" => false]
    ];

    // 3. Recommended Top Pick (JBL Tune 510BT or highest match)
    $recStmt = $conn->query("SELECT id, name, brand, category_name, price_text, numeric_price, rating, match_percentage, match_score_text, specs FROM products WHERE id = 'jbl_510bt' LIMIT 1");
    $topPick = $recStmt->fetch(PDO::FETCH_ASSOC);
    if (!$topPick) {
        $recStmt = $conn->query("SELECT id, name, brand, category_name, price_text, numeric_price, rating, match_percentage, match_score_text, specs FROM products ORDER BY match_percentage DESC LIMIT 1");
        $topPick = $recStmt->fetch(PDO::FETCH_ASSOC);
    }

    // 4. Trending Comparison
    $trendingComparison = [
        "title" => "Online classes comparison",
        "product1" => "JBL Tune 510BT",
        "product2" => "Soundcore H30i",
        "key_difference" => "Mic clarity vs 70h battery"
    ];

    // 5. Total counts
    $prodCount = (int)$conn->query("SELECT count(*) FROM products")->fetchColumn();
    $catCount = (int)$conn->query("SELECT count(*) FROM categories")->fetchColumn();

    echo json_encode([
        "status" => "success",
        "success" => true,
        "app_title" => "SmartCart AI",
        "tagline" => "Shop Smarter. Buy Better.",
        "search_prompt_suggestion" => 'Try "Best phone under ₹25,000 with a good camera"',
        "categories" => $categories,
        "popular_searches" => $popularSearches,
        "top_recommendation" => $topPick,
        "trending_comparison" => $trendingComparison,
        "stats" => [
            "total_products" => $prodCount,
            "total_categories" => $catCount
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "success" => false, "message" => "Failed to load dashboard: " . $e->getMessage()]);
}

