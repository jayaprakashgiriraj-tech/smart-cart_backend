<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "OPTIONS") {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/config/credentials.php';
require_once __DIR__ . '/services/RapidApiAmazonService.php';
require_once __DIR__ . '/services/AmazonCreatorsService.php';
require_once __DIR__ . '/services/FlipkartAffiliateService.php';

// 1. Check PHP Modules
$modules = [
    'curl' => extension_loaded('curl'),
    'openssl' => extension_loaded('openssl'),
    'json' => extension_loaded('json'),
    'pdo_mysql' => extension_loaded('pdo_mysql')
];

// 2. Check Database Status safely
$dbStatus = [
    'connected' => false,
    'host' => 'localhost',
    'database' => 'smart_cart',
    'table_exists' => false,
    'cached_records_count' => 0,
    'message' => 'Not connected'
];

$conn = null;
try {
    $conn = new PDO(
        "mysql:host=localhost;dbname=smart_cart;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 2
        ]
    );
    $dbStatus['connected'] = true;
    $dbStatus['message'] = 'MySQL connected successfully';

    $stmt = $conn->query("SHOW TABLES LIKE 'store_platform_prices'");
    if ($stmt && $stmt->fetch()) {
        $dbStatus['table_exists'] = true;
        $countStmt = $conn->query("SELECT COUNT(*) AS total FROM store_platform_prices");
        $dbStatus['cached_records_count'] = (int)($countStmt->fetchColumn() ?? 0);
    } else {
        $dbStatus['table_exists'] = false;
        $dbStatus['table_hint'] = 'Run smartcart_backend/integrations/sql/create_store_platform_prices.sql in MySQL/phpMyAdmin';
    }
} catch (Exception $e) {
    $dbStatus['connected'] = false;
    $dbStatus['message'] = 'Could not connect to MySQL server. Start MySQL in XAMPP Control Panel.';
    $dbStatus['raw_error'] = $e->getMessage();
}

// 3. RapidAPI Amazon Status (No Affiliate Needed)
$rapidService = new RapidApiAmazonService();
$rapidConfigured = isRapidApiConfigured();
$rapidTest = [
    'configured' => $rapidConfigured,
    'host' => RAPIDAPI_AMAZON_HOST,
    'country' => RAPIDAPI_COUNTRY,
    'status' => $rapidConfigured ? 'Ready to connect' : 'Pending RapidAPI free key',
    'hint' => $rapidConfigured ? 'Key configured' : 'Add RAPIDAPI_KEY in integrations/config/credentials.php'
];

if ($rapidConfigured && isset($_GET['test_ping']) && $_GET['test_ping'] === '1') {
    $rapidTest['live_ping'] = $rapidService->testConnection();
}

// 4. Amazon Official PA-API Status (Affiliate)
$amazonService = new AmazonCreatorsService();
$amazonConfigured = isAmazonConfigured();
$amazonTest = [
    'configured' => $amazonConfigured,
    'partner_tag' => AMAZON_ASSOCIATE_TAG,
    'host' => AMAZON_HOST,
    'region' => AMAZON_REGION,
    'status' => $amazonConfigured ? 'Ready to connect' : 'Pending credentials configuration',
    'hint' => $amazonConfigured ? 'Credentials configured' : 'Configure AMAZON_ACCESS_KEY & AMAZON_SECRET_KEY in integrations/config/credentials.php'
];

if ($amazonConfigured && isset($_GET['test_ping']) && $_GET['test_ping'] === '1') {
    $amazonTest['live_ping'] = $amazonService->testConnection();
}

// 5. Flipkart Status
$flipkartService = new FlipkartAffiliateService();
$flipkartConfigured = isFlipkartConfigured();
$flipkartTest = [
    'configured' => $flipkartConfigured,
    'affiliate_id' => $flipkartConfigured ? FLIPKART_AFFILIATE_ID : 'Not set',
    'status' => $flipkartConfigured ? 'Ready to connect' : 'Pending credentials configuration',
    'hint' => $flipkartConfigured ? 'Credentials configured' : 'Configure FLIPKART_AFFILIATE_ID & FLIPKART_AFFILIATE_TOKEN in integrations/config/credentials.php'
];

if ($flipkartConfigured && isset($_GET['test_ping']) && $_GET['test_ping'] === '1') {
    $flipkartTest['live_ping'] = $flipkartService->testConnection();
}

echo json_encode([
    'status' => 'success',
    'timestamp' => date('c'),
    'environment' => [
        'php_version' => PHP_VERSION,
        'required_extensions' => $modules,
        'database' => $dbStatus
    ],
    'integrations' => [
        'rapidapi_amazon' => $rapidTest,
        'amazon_creators_paapi' => $amazonTest,
        'flipkart_affiliate_api' => $flipkartTest
    ],
    'instructions' => [
        'credentials_file' => 'smartcart_backend/integrations/config/credentials.php',
        'sql_schema_file' => 'smartcart_backend/integrations/sql/create_store_platform_prices.sql',
        'test_live_ping' => 'Pass ?test_ping=1 to test live external API connectivity once credentials are set'
    ]
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
