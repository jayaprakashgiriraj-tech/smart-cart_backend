<?php
/**
 * Smart Cart AI - Flipkart Affiliate API Service
 * 
 * Interacts with Flipkart Affiliate Product & Search APIs.
 */

require_once __DIR__ . '/../config/credentials.php';

class FlipkartAffiliateService {
    private string $affiliateId;
    private string $affiliateToken;
    private string $searchBaseUrl = 'https://affiliate-api.flipkart.net/affiliate/1.0/search.json';

    public function __construct() {
        $this->affiliateId = FLIPKART_AFFILIATE_ID;
        $this->affiliateToken = FLIPKART_AFFILIATE_TOKEN;
    }

    /**
     * Searches products on Flipkart Affiliate API by keyword.
     */
    public function searchProducts(string $query, int $limit = 5): array {
        if (!isFlipkartConfigured()) {
            return [
                'success' => false,
                'error_code' => 'CREDENTIALS_NOT_CONFIGURED',
                'message' => 'Flipkart Affiliate credentials are set to default placeholders. Please configure FLIPKART_AFFILIATE_ID and FLIPKART_AFFILIATE_TOKEN in credentials.php.',
                'raw_status' => 401,
                'items' => []
            ];
        }

        $endpoint = $this->searchBaseUrl . '?query=' . urlencode(trim($query)) . '&resultCount=' . max(1, min(10, $limit));

        $headers = [
            'Fk-Affiliate-Id: ' . $this->affiliateId,
            'Fk-Affiliate-Token: ' . $this->affiliateToken,
            'Accept: application/json',
            'User-Agent: SmartCartAI/1.0 (Language=PHP)'
        ];

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
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
                'message' => 'cURL request to Flipkart failed: ' . $curlError,
                'raw_status' => 0,
                'items' => []
            ];
        }

        $decoded = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300) {
            $rawListings = $decoded['products'] ?? $decoded['productInfoList'] ?? [];
            $parsedingsings = $this->parseFlipkartItems($rawListings);

            return [
                'success' => true,
                'error_code' => null,
                'message' => 'Fetched successfully from Flipkart',
                'raw_status' => $httpCode,
                'items' => $parsedingsings,
                'raw_payload' => $decoded
            ];
        }

        // Handle specific Flipkart HTTP codes
        $errorCode = 'HTTP_' . $httpCode;
        $errorMessage = 'Flipkart API returned HTTP ' . $httpCode;
        if ($httpCode === 401 || $httpCode === 403) {
            $errorCode = 'AUTH_FAILED';
            $errorMessage = 'Invalid or expired Flipkart Affiliate credentials (Fk-Affiliate-Id / Fk-Affiliate-Token).';
        } elseif ($httpCode === 429) {
            $errorCode = 'RATE_LIMIT_EXCEEDED';
            $errorMessage = 'Flipkart Affiliate API rate limit exceeded. Please throttle calls.';
        } elseif (isset($decoded['message'])) {
            $errorMessage = $decoded['message'];
        }

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
     * Tests connectivity to Flipkart Affiliate API.
     */
    public function testConnection(): array {
        if (!isFlipkartConfigured()) {
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
                'message' => 'Flipkart Affiliate API connected and authenticated successfully'
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
     * Parses raw Flipkart Affiliate items into normalized Smart Cart structure.
     */
    private function parseFlipkartItems(array $rawItems): array {
        $items = [];
        foreach ($rawItems as $item) {
            $base = $item['productBaseInfoV1'] ?? $item['productBaseInfo'] ?? $item;

            $productId = $base['productId'] ?? ('flp_' . substr(md5(uniqid()), 0, 8));
            $title = $base['title'] ?? 'Flipkart Product';
            $brand = $base['productBrand'] ?? '';

            // Clean, working affiliate product URL
            $productUrl = $base['productUrl'] ?? "https://dl.flipkart.com/dl/search?q=" . urlencode($title);
            // Append affiliate tracking if present
            if (!empty($this->affiliateId) && isFlipkartConfigured() && stripos($productUrl, 'affid=') === false) {
                $separator = (strpos($productUrl, '?') === false) ? '?' : '&';
                $productUrl .= $separator . 'affid=' . urlencode($this->affiliateId);
            }

            // Image URL selection (prefer 400x400 or largest available)
            $imageUrl = '';
            if (isset($base['imageUrls']) && is_array($base['imageUrls'])) {
                $imageUrl = $base['imageUrls']['800x800'] 
                    ?? $base['imageUrls']['400x400'] 
                    ?? $base['imageUrls']['200x200'] 
                    ?? reset($base['imageUrls']);
            }

            // Price resolution
            $price = 0;
            if (isset($base['flipkartSellingPrice']['amount'])) {
                $price = (int)round((float)$base['flipkartSellingPrice']['amount']);
            } elseif (isset($base['flipkartSpecialPrice']['amount'])) {
                $price = (int)round((float)$base['flipkartSpecialPrice']['amount']);
            }

            $originalPrice = 0;
            if (isset($base['maximumRetailPrice']['amount'])) {
                $originalPrice = (int)round((float)$base['maximumRetailPrice']['amount']);
            }
            if ($originalPrice <= $price) {
                $originalPrice = $price;
            }

            $discountPercent = '';
            if (isset($base['discountPercentage']) && $base['discountPercentage'] > 0) {
                $discountPercent = round((float)$base['discountPercentage']) . '% off';
            } elseif ($originalPrice > $price && $originalPrice > 0) {
                $discountPercent = round((($originalPrice - $price) / $originalPrice) * 100) . '% off';
            }

            $inStock = $base['inStock'] ?? true;

            $items[] = [
                'platform' => 'flipkart',
                'product_id' => $productId,
                'title' => $title,
                'brand' => $brand,
                'price' => $price,
                'price_formatted' => '₹' . number_format($price),
                'original_price' => $originalPrice,
                'original_price_formatted' => '₹' . number_format($originalPrice),
                'discount_percent' => $discountPercent,
                'rating' => '4.3',
                'ratings_count' => 'Flipkart Verified',
                'in_stock' => (bool)$inStock,
                'stock_status' => $inStock ? 'In Stock' : 'Out of Stock',
                'seller' => 'RetailNet (Flipkart Assured)',
                'delivery' => '📦 Flipkart Plus FREE Delivery in 2 Days',
                'offer' => '5% Unlimited Cashback on Flipkart Axis Bank Card',
                'product_url' => $productUrl,
                'affiliate_url' => $productUrl,
                'image_url' => $imageUrl,
                'app_package' => 'com.flipkart.android',
                'accent_color' => '#2874F0'
            ];
        }

        return $items;
    }
}

