<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "OPTIONS") {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/services/FlipkartAffiliateService.php';

$query = trim($_GET['query'] ?? $_GET['q'] ?? $_GET['keywords'] ?? '');
$limit = (int)($_GET['limit'] ?? 5);

if (empty($query)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'success' => false,
        'error_code' => 'MISSING_QUERY',
        'message' => 'Query parameter "query" is required.'
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

$flipkartService = new FlipkartAffiliateService();
$result = $flipkartService->searchProducts($query, $limit);

if ($result['success']) {
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'success' => true,
        'platform' => 'flipkart',
        'query' => $query,
        'total_results' => count($result['items']),
        'items' => $result['items']
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
} else {
    // Return transparent error details as per guidelines
    $statusCode = ($result['raw_status'] >= 400 && $result['raw_status'] <= 599) ? $result['raw_status'] : 502;
    http_response_code($statusCode);
    echo json_encode([
        'status' => 'error',
        'success' => false,
        'platform' => 'flipkart',
        'query' => $query,
        'error_code' => $result['error_code'] ?? 'FLIPKART_API_ERROR',
        'message' => $result['message'],
        'hint' => (!isFlipkartConfigured()) 
            ? 'Add your live Flipkart Affiliate ID and Token in smartcart_backend/integrations/config/credentials.php' 
            : 'Check Flipkart Affiliate Portal for API authorization status.'
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

