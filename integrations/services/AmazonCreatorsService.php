<?php
/**
 * Smart Cart AI - Amazon India Creators API (PA-API v5) Service
 * 
 * Implements native AWS Signature Version 4 (SigV4) HMAC-SHA256 signing
 * to interact with Amazon Product Advertising API v5 (India Marketplace).
 */

require_once __DIR__ . '/../config/credentials.php';

class AmazonCreatorsService {
    private string $accessKey;
    private string $secretKey;
    private string $associateTag;
    private string $region;
    private string $host;
    private string $service = 'ProductAdvertisingAPI';

    public function __construct() {
        $this->accessKey = AMAZON_ACCESS_KEY;
        $this->secretKey = AMAZON_SECRET_KEY;
        $this->associateTag = AMAZON_ASSOCIATE_TAG;
        $this->region = AMAZON_REGION;
        $this->host = AMAZON_HOST;
    }

    /**
     * Searches items by keyword on Amazon India.
     */
    public function searchProducts(string $keywords, int $limit = 5): array {
        if (!isAmazonConfigured()) {
            return [
                'success' => false,
                'error_code' => 'CREDENTIALS_NOT_CONFIGURED',
                'message' => 'Amazon API credentials are set to default placeholders. Please configure AMAZON_ACCESS_KEY and AMAZON_SECRET_KEY in credentials.php.',
                'raw_status' => 401,
                'items' => []
            ];
        }

        $payload = [
            'Keywords' => $keywords,
            'PartnerTag' => $this->associateTag,
            'PartnerType' => 'Associates',
            'Marketplace' => 'www.amazon.in',
            'ItemCount' => max(1, min(10, $limit)),
            'Resources' => [
                'ItemInfo.Title',
                'ItemInfo.ByLineInfo',
                'ItemInfo.Features',
                'Images.Primary.Large',
                'Offers.Listings.Price',
                'Offers.Listings.SavingBasis',
                'Offers.Listings.Availability.Message',
                'Offers.Listings.MerchantInfo',
                'CustomerReviews.Count',
                'CustomerReviews.StarRating'
            ]
        ];

        return $this->executeRequest('com.amazon.paapi5.v1.ProductAdvertisingAPIv1.SearchItems', '/paapi5/searchitems', $payload);
    }

    /**
     * Retrieves specific items by their ASINs.
     */
    public function getItems(array $asins): array {
        if (!isAmazonConfigured()) {
            return [
                'success' => false,
                'error_code' => 'CREDENTIALS_NOT_CONFIGURED',
                'message' => 'Amazon API credentials are set to default placeholders.',
                'raw_status' => 401,
                'items' => []
            ];
        }

        $payload = [
            'ItemIds' => array_values($asins),
            'ItemIdType' => 'ASIN',
            'PartnerTag' => $this->associateTag,
            'PartnerType' => 'Associates',
            'Marketplace' => 'www.amazon.in',
            'Resources' => [
                'ItemInfo.Title',
                'ItemInfo.ByLineInfo',
                'Images.Primary.Large',
                'Offers.Listings.Price',
                'Offers.Listings.SavingBasis',
                'Offers.Listings.Availability.Message',
                'Offers.Listings.MerchantInfo',
                'CustomerReviews.Count',
                'CustomerReviews.StarRating'
            ]
        ];

        return $this->executeRequest('com.amazon.paapi5.v1.ProductAdvertisingAPIv1.GetItems', '/paapi5/getitems', $payload);
    }

    /**
     * Tests connectivity to Amazon PA-API.
     */
    public function testConnection(): array {
        if (!isAmazonConfigured()) {
            return [
                'configured' => false,
                'connected' => false,
                'message' => 'Credentials not configured (using placeholders)'
            ];
        }

        $res = $this->searchProducts('phone', 1);
        if ($res['success']) {
            return [
                'configured' => true,
                'connected' => true,
                'message' => 'Amazon PA-API v5 connected and authenticated successfully'
            ];
        } else {
            return [
                'configured' => true,
                'connected' => false,
                'error_code' => $res['error_code'] ?? 'UNKNOWN',
                'message' => $res['message'] ?? 'Connection test failed',
                'http_status' => $res['raw_status'] ?? 0
            ];
        }
    }

    /**
     * Signs and dispatches an AWS SigV4 signed request via cURL.
     */
    private function executeRequest(string $target, string $path, array $payloadArray): array {
        $payload = json_encode($payloadArray, JSON_UNESCAPED_SLASHES);
        $timestamp = gmdate('Ymd\THis\Z');
        $date = substr($timestamp, 0, 8);

        $headers = [
            'content-encoding' => 'amz-1.0',
            'content-type' => 'application/json; charset=utf-8',
            'host' => $this->host,
            'x-amz-date' => $timestamp,
            'x-amz-target' => $target
        ];

        // 1. Build Canonical Request
        $canonicalHeaders = '';
        foreach ($headers as $k => $v) {
            $canonicalHeaders .= strtolower($k) . ':' . trim($v) . "\n";
        }
        $signedHeaders = implode(';', array_keys($headers));
        $hashedPayload = hash('sha256', $payload);

        $canonicalRequest = "POST\n"
            . $path . "\n"
            . "\n" // canonical query string empty for POST
            . $canonicalHeaders . "\n"
            . $signedHeaders . "\n"
            . $hashedPayload;

        // 2. Build String to Sign
        $credentialScope = "$date/{$this->region}/{$this->service}/aws4_request";
        $stringToSign = "AWS4-HMAC-SHA256\n"
            . $timestamp . "\n"
            . $credentialScope . "\n"
            . hash('sha256', $canonicalRequest);

        // 3. Calculate Signature Key (kSigning)
        $kSecret  = 'AWS4' . $this->secretKey;
        $kDate    = hash_hmac('sha256', $date, $kSecret, true);
        $kRegion  = hash_hmac('sha256', $this->region, $kDate, true);
        $kService = hash_hmac('sha256', $this->service, $kRegion, true);
        $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);
        $signature = hash_hmac('sha256', $stringToSign, $kSigning);

        // 4. Build Authorization Header
        $authorizationHeader = "AWS4-HMAC-SHA256 Credential={$this->accessKey}/$credentialScope, SignedHeaders=$signedHeaders, Signature=$signature";

        $curlHeaders = [
            'Content-Encoding: amz-1.0',
            'Content-Type: application/json; charset=utf-8',
            'Host: ' . $this->host,
            'x-amz-date: ' . $timestamp,
            'x-amz-target: ' . $target,
            'Authorization: ' . $authorizationHeader,
            'User-Agent: SmartCartAI/1.0 (Language=PHP)'
        ];

        $ch = curl_init('https://' . $this->host . $path);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return [
                'success' => false,
                'error_code' => 'NETWORK_ERROR',
                'message' => 'cURL request to Amazon failed: ' . $curlError,
                'raw_status' => 0,
                'items' => []
            ];
        }

        $decoded = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300) {
            $rawItems = $decoded['SearchResult']['Items'] ?? $decoded['ItemsResult']['Items'] ?? [];
            $parsedItems = $this->parseAmazonItems($rawItems);

            return [
                'success' => true,
                'error_code' => null,
                'message' => 'Fetched successfully from Amazon',
                'raw_status' => $httpCode,
                'items' => $parsedItems,
                'raw_payload' => $decoded
            ];
        }

        // Process Amazon Errors
        $amazonError = $decoded['Errors'][0] ?? null;
        $errorCode = $amazonError['Code'] ?? ('HTTP_' . $httpCode);
        $errorMessage = $amazonError['Message'] ?? ($decoded['message'] ?? 'Amazon API request returned HTTP ' . $httpCode);

        return [
            'success' => false,
            'error_code' => $errorCode,
            'message' => $errorMessage,
            'raw_status' => $httpCode,
            'items' => [],
            'raw_payload' => $decoded
        ];
    }

    /**
     * Parses raw Amazon PA-API items into normalized Smart Cart structure.
     */
    private function parseAmazonItems(array $rawItems): array {
        $items = [];
        foreach ($rawItems as $item) {
            $asin = $item['ASIN'] ?? '';
            $title = $item['ItemInfo']['Title']['DisplayValue'] ?? 'Amazon Product';
            $brand = $item['ItemInfo']['ByLineInfo']['Brand']['DisplayValue'] ?? '';
            $productUrl = $item['DetailPageURL'] ?? ("https://www.amazon.in/dp/" . $asin . "?tag=" . $this->associateTag);
            $imageUrl = $item['Images']['Primary']['Large']['URL'] ?? '';

            // Price extraction
            $listing = $item['Offers']['Listings'][0] ?? null;
            $price = 0;
            $originalPrice = 0;
            $currency = 'INR';

            if ($listing && isset($listing['Price']['Amount'])) {
                $price = (int)round((float)$listing['Price']['Amount']);
                $currency = $listing['Price']['Currency'] ?? 'INR';
            }

            if ($listing && isset($listing['SavingBasis']['Amount'])) {
                $originalPrice = (int)round((float)$listing['SavingBasis']['Amount']);
            }
            if ($originalPrice <= $price) {
                $originalPrice = $price;
            }

            $discountPercent = '';
            if ($originalPrice > $price && $originalPrice > 0) {
                $discountPercent = round((($originalPrice - $price) / $originalPrice) * 100) . '% off';
            }

            $rating = isset($item['CustomerReviews']['StarRating']['Value']) ? (string)$item['CustomerReviews']['StarRating']['Value'] : '4.2';
            $ratingsCount = isset($item['CustomerReviews']['Count']) ? number_format((int)$item['CustomerReviews']['Count']) . ' ratings' : 'Sample rating';

            $inStock = true;
            $availMessage = $listing['Availability']['Message'] ?? 'In Stock';
            if (stripos($availMessage, 'currently unavailable') !== false || stripos($availMessage, 'out of stock') !== false) {
                $inStock = false;
            }

            $seller = $listing['MerchantInfo']['Name'] ?? 'Appario Retail (Amazon Fulfilled)';

            $items[] = [
                'platform' => 'amazon',
                'asin' => $asin,
                'product_id' => $asin,
                'title' => $title,
                'brand' => $brand,
                'price' => $price,
                'price_formatted' => '₹' . number_format($price),
                'original_price' => $originalPrice,
                'original_price_formatted' => '₹' . number_format($originalPrice),
                'discount_percent' => $discountPercent,
                'rating' => $rating,
                'ratings_count' => $ratingsCount,
                'in_stock' => $inStock,
                'availability_message' => $availMessage,
                'seller' => $seller,
                'delivery' => '⚡ Prime FREE Delivery Tomorrow',
                'offer' => 'Flat ₹100 Instant Cashback with Amazon Pay UPI',
                'product_url' => $productUrl,
                'affiliate_url' => $productUrl,
                'image_url' => $imageUrl,
                'app_package' => 'com.amazon.mShop.android.shopping',
                'accent_color' => '#FF9900'
            ];
        }

        return $items;
    }
}

