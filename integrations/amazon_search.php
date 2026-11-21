<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "OPTIONS") {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/config/credentials.php';
require_once __DIR__ . '/services/RapidApiAmazonService.php';
require_once __DIR__ . '/services/AmazonCreatorsService.php';

if (empty($_GET) && !empty($_SERVER['QUERY_STRING'] ?? '')) {
    parse_str($_SERVER['QUERY_STRING'], $_GET);
}

$query = trim($_GET['query'] ?? $_GET['q'] ?? $_GET['keywords'] ?? '');
$limit = (int)($_GET['limit'] ?? 5);

// Allow passing key via query parameter for quick testing: ?rapidapi_key=...
$customKey = trim($_GET['rapidapi_key'] ?? '');

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

if (!empty($customKey) || isRapidApiConfigured()) {
    $rapidService = new RapidApiAmazonService($customKey ?: null);
    $result = $rapidService->searchProducts($query, $limit);
} else {
    $amazonService = new AmazonCreatorsService();
    $result = $amazonService->searchProducts($query, $limit);
}

if ($result['success']) {
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'success' => true,
        'platform' => 'amazon',
        'provider' => $result['provider'] ?? 'amazon',
        'query' => $query,
        'total_results' => count($result['items']),
        'items' => $result['items']
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
} else {
    $statusCode = ($result['raw_status'] >= 400 && $result['raw_status'] <= 599) ? $result['raw_status'] : 502;
    http_response_code($statusCode);
    echo json_encode([
        'status' => 'error',
        'success' => false,
        'platform' => 'amazon',
        'query' => $query,
        'error_code' => $result['error_code'] ?? 'AMAZON_API_ERROR',
        'message' => $result['message'],
        'hint' => 'Add your free RapidAPI key in smartcart_backend/integrations/config/credentials.php (or pass ?rapidapi_key=YOUR_KEY)'
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

