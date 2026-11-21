<?php
/**
 * Smart Cart AI - External Affiliate API Credentials
 * 
 * SECURITY NOTICE:
 * This file contains sensitive server-side API keys and affiliate secrets.
 * Direct web access is strictly forbidden by .htaccess.
 * NEVER commit production credentials to public version control.
 * NEVER expose these secrets to the client Android application.
 */

// Amazon India Creators API (PA-API v5)
// Sign up at: https://affiliate-program.amazon.in/assoc_credentials/home
defined('AMAZON_ACCESS_KEY')     || define('AMAZON_ACCESS_KEY',     getenv('AMAZON_ACCESS_KEY')     ?: 'YOUR_AMAZON_ACCESS_KEY');
defined('AMAZON_SECRET_KEY')     || define('AMAZON_SECRET_KEY',     getenv('AMAZON_SECRET_KEY')     ?: 'YOUR_AMAZON_SECRET_KEY');
defined('AMAZON_ASSOCIATE_TAG')  || define('AMAZON_ASSOCIATE_TAG',  getenv('AMAZON_ASSOCIATE_TAG')  ?: 'smartcart-21');
defined('AMAZON_REGION')         || define('AMAZON_REGION',         getenv('AMAZON_REGION')         ?: 'eu-west-1');
defined('AMAZON_HOST')           || define('AMAZON_HOST',           getenv('AMAZON_HOST')           ?: 'webservices.amazon.in');

// Flipkart Affiliate API
// Sign up / manage at: https://affiliate.flipkart.com/api-tools
defined('FLIPKART_AFFILIATE_ID')    || define('FLIPKART_AFFILIATE_ID',    getenv('FLIPKART_AFFILIATE_ID')    ?: 'YOUR_FLIPKART_AFFILIATE_ID');
defined('FLIPKART_AFFILIATE_TOKEN') || define('FLIPKART_AFFILIATE_TOKEN', getenv('FLIPKART_AFFILIATE_TOKEN') ?: 'YOUR_FLIPKART_AFFILIATE_TOKEN');

// RapidAPI Real-Time Amazon Data (100% Free - No Affiliate Program Needed)
// Sign up free at: https://rapidapi.com/
// Subscribe (Free plan $0/mo): https://rapidapi.com/letscrape-6bRBa33Odf5/api/real-time-amazon-data
defined('RAPIDAPI_KEY')         || define('RAPIDAPI_KEY',         getenv('RAPIDAPI_KEY')         ?: '5daa6adb57msh5a4a3ffec32d875p1b6f6djsnadd0ae2db52e');
defined('RAPIDAPI_AMAZON_HOST') || define('RAPIDAPI_AMAZON_HOST', getenv('RAPIDAPI_AMAZON_HOST') ?: 'real-time-amazon-data.p.rapidapi.com');
defined('RAPIDAPI_COUNTRY')     || define('RAPIDAPI_COUNTRY',     getenv('RAPIDAPI_COUNTRY')     ?: 'IN');

// Cache configuration
// Seconds before querying external APIs again for the same product (default: 3600s / 1 hour)
defined('AFFILIATE_CACHE_TTL') || define('AFFILIATE_CACHE_TTL', 3600);

/**
 * Returns whether RapidAPI credentials have been configured.
 */
function isRapidApiConfigured(): bool {
    return RAPIDAPI_KEY !== 'YOUR_RAPIDAPI_KEY' 
        && !empty(RAPIDAPI_KEY);
}

/**
 * Returns whether Amazon credentials have been configured with non-placeholder values.
 */
function isAmazonConfigured(): bool {
    return AMAZON_ACCESS_KEY !== 'YOUR_AMAZON_ACCESS_KEY' 
        && AMAZON_SECRET_KEY !== 'YOUR_AMAZON_SECRET_KEY' 
        && !empty(AMAZON_ACCESS_KEY) 
        && !empty(AMAZON_SECRET_KEY);
}

/**
 * Returns whether Flipkart credentials have been configured with non-placeholder values.
 */
function isFlipkartConfigured(): bool {
    return FLIPKART_AFFILIATE_ID !== 'YOUR_FLIPKART_AFFILIATE_ID' 
        && FLIPKART_AFFILIATE_TOKEN !== 'YOUR_FLIPKART_AFFILIATE_TOKEN' 
        && !empty(FLIPKART_AFFILIATE_ID) 
        && !empty(FLIPKART_AFFILIATE_TOKEN);
}

