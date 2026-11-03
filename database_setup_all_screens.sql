-- ===================================================
-- Smart Cart AI - Complete Database Schema & Seed Data
-- Database: smart_cart (Preserves existing users table)
-- ===================================================

USE `smart_cart`;

-- 1. Categories Table
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL UNIQUE,
  `icon` varchar(50) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Products Table
CREATE TABLE IF NOT EXISTS `products` (
  `id` varchar(50) NOT NULL,
  `name` varchar(150) NOT NULL,
  `brand` varchar(100) NOT NULL,
  `category_name` varchar(50) NOT NULL,
  `subcategory` varchar(100) NOT NULL,
  `price_text` varchar(50) NOT NULL,
  `numeric_price` int(11) NOT NULL,
  `rating` decimal(2,1) DEFAULT 4.0,
  `review_count` int(11) DEFAULT 0,
  `stock_quantity` int(11) NOT NULL DEFAULT 10,
  `description` text DEFAULT NULL,
  `specs` text DEFAULT NULL,
  `battery` varchar(50) DEFAULT 'N/A',
  `call_mic` varchar(100) DEFAULT 'N/A',
  `weight` varchar(50) DEFAULT 'N/A',
  `multipoint` varchar(50) DEFAULT 'N/A',
  `active_nc` varchar(50) DEFAULT 'N/A',
  `app_eq` varchar(50) DEFAULT 'N/A',
  `warranty` varchar(50) DEFAULT '1 year',
  `value_score` decimal(3,1) DEFAULT 8.0,
  `match_score_text` varchar(50) DEFAULT '90% match',
  `match_percentage` int(11) DEFAULT 90,
  `decision_tag` enum('BUY','CONSIDER','WAIT') DEFAULT 'BUY',
  `best_for` varchar(255) DEFAULT 'General use',
  `recommendation_summary` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category_name`),
  KEY `idx_numeric_price` (`numeric_price`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Product Needs Tags Table
CREATE TABLE IF NOT EXISTS `product_needs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` varchar(50) NOT NULL,
  `need_tag` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_product_id` (`product_id`),
  CONSTRAINT `fk_needs_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Wishlist Table
CREATE TABLE IF NOT EXISTS `wishlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` varchar(50) NOT NULL,
  `price_drop_alert` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_product_wishlist` (`user_id`, `product_id`),
  CONSTRAINT `fk_wishlist_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_wishlist_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Cart Table
CREATE TABLE IF NOT EXISTS `cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` varchar(50) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_product_cart` (`user_id`, `product_id`),
  CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Shopping History Table
CREATE TABLE IF NOT EXISTS `shopping_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` varchar(50) NOT NULL,
  `viewed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_history_user` (`user_id`),
  CONSTRAINT `fk_history_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_history_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Saved Comparisons Table
CREATE TABLE IF NOT EXISTS `saved_comparisons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product1_id` varchar(50) NOT NULL,
  `product2_id` varchar(50) NOT NULL,
  `winner_product_id` varchar(50) DEFAULT NULL,
  `comparison_title` varchar(150) DEFAULT 'Online classes comparison',
  `summary` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_comp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comp_p1` FOREIGN KEY (`product1_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comp_p2` FOREIGN KEY (`product2_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Price Tracking & History Tables
CREATE TABLE IF NOT EXISTS `price_tracking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` varchar(50) NOT NULL,
  `target_price` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_product_tracking` (`user_id`, `product_id`),
  CONSTRAINT `fk_tracking_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tracking_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `price_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` varchar(50) NOT NULL,
  `recorded_price` int(11) NOT NULL,
  `recorded_date` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_price_history_prod` (`product_id`),
  CONSTRAINT `fk_ph_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. AI Reviews & Sentiment Table
CREATE TABLE IF NOT EXISTS `ai_reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` varchar(50) NOT NULL UNIQUE,
  `sample_count` int(11) DEFAULT 240,
  `sample_period` varchar(100) DEFAULT 'September 2026',
  `star_5_count` int(11) DEFAULT 144,
  `star_4_count` int(11) DEFAULT 48,
  `star_3_count` int(11) DEFAULT 24,
  `star_2_count` int(11) DEFAULT 14,
  `star_1_count` int(11) DEFAULT 10,
  `positive_pct` int(11) DEFAULT 78,
  `neutral_pct` int(11) DEFAULT 14,
  `negative_pct` int(11) DEFAULT 8,
  `ai_summary` text DEFAULT NULL,
  `pros` text DEFAULT NULL,
  `cons` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_review_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. User Preferences Table
CREATE TABLE IF NOT EXISTS `user_preferences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL UNIQUE,
  `categories` text DEFAULT NULL,
  `audio_budget` decimal(10,2) DEFAULT 3000.00,
  `feature_priorities` text DEFAULT NULL,
  `price_drop_alerts` tinyint(1) DEFAULT 1,
  `new_recommendations` tinyint(1) DEFAULT 0,
  `use_shopping_history` tinyint(1) DEFAULT 1,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_pref_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------
-- SEED DATA INSERTION
-- ---------------------------------------------------

-- Categories Seed
INSERT IGNORE INTO `categories` (`id`, `name`, `icon`, `description`) VALUES
(1, 'Mobiles', 'smartphone', 'Smartphones and mobile devices'),
(2, 'Laptops', 'laptop', 'Laptops for work, study and gaming'),
(3, 'Audio', 'headphones', 'Headphones, earbuds and bluetooth speakers'),
(4, 'Fashion', 'checkroom', 'Apparel, clothing and personal accessories'),
(5, 'Home Appliances', 'kitchen', 'Smart appliances for home and kitchen');

-- Products Seed (22 catalog products matching ProductCatalog.kt)
INSERT INTO `products` 
(`id`, `name`, `brand`, `category_name`, `subcategory`, `price_text`, `numeric_price`, `rating`, `review_count`, `stock_quantity`, `description`, `specs`, `battery`, `call_mic`, `weight`, `multipoint`, `active_nc`, `app_eq`, `warranty`, `value_score`, `match_score_text`, `match_percentage`, `decision_tag`, `best_for`, `recommendation_summary`, `image_url`)
VALUES
-- Mobiles
('mob_s23', 'Samsung Galaxy S23', 'Samsung', 'Mobiles', 'Smartphones', '₹49,999', 49999, 4.6, 520, 12, 'Compact Android flagship with Snapdragon 8 Gen 2 processor, Dynamic AMOLED 2X 120Hz display, and 50MP triple rear camera system.', '8GB RAM · 256GB Storage · 5G', '3900 mAh', 'Clear Dual-Mic', '168 g', 'Yes', 'N/A', 'Yes', '1 year', 9.2, '95% match', 95, 'BUY', 'Compact flagship performance & cameras', 'Top Android pick under ₹50k with premium build, dynamic 120Hz display, and pro cameras.', ''),
('mob_ip15', 'Apple iPhone 15', 'Apple', 'Mobiles', 'Smartphones', '₹71,900', 71900, 4.7, 890, 8, 'Featuring Dynamic Island, 48MP Main camera with 2x Telephoto, USB-C connectivity, and durable color-infused glass design.', 'A16 Bionic · 128GB Storage · 5G', 'All-Day', 'Studio Mic Array', '171 g', 'Yes', 'N/A', 'No', '1 year', 9.0, '94% match', 94, 'BUY', 'iOS ecosystem & video recording', 'Premier smartphone for video creators, seamless iOS performance, and all-day battery.', ''),
('mob_op12r', 'OnePlus 12R', 'OnePlus', 'Mobiles', 'Smartphones', '₹39,999', 39999, 4.5, 340, 15, 'Powered by Snapdragon 8 Gen 2, 5500mAh massive battery, 100W SUPERVOOC charging, and 120Hz ProXDR display.', '16GB RAM · 256GB Storage · 100W Fast Charge', '5500 mAh', 'Dual Noise-Cancelling', '207 g', 'Yes', 'N/A', 'Yes', '1 year', 9.1, '91% match', 91, 'BUY', '100W Fast charging & gaming speed', 'Ideal for power users who want rapid charging and high FPS gaming at mid-tier pricing.', ''),
('mob_redmi13p', 'Redmi Note 13 Pro', 'Xiaomi', 'Mobiles', 'Smartphones', '₹21,999', 21999, 4.3, 610, 20, '200MP OIS camera, 1.5K 120Hz AMOLED display, Snapdragon 7s Gen 2 chip, and 67W Turbo Charge.', '8GB RAM · 128GB Storage · 200MP Camera', '5100 mAh', 'Clear Voice Mic', '187 g', 'No', 'N/A', 'No', '1 year', 8.7, '88% match', 88, 'BUY', 'Budget high resolution camera', 'Best value under ₹25k for detailed photography and sharp 1.5K display.', ''),
('mob_realme12p', 'Realme 12 Pro 5G', 'Realme', 'Mobiles', 'Smartphones', '₹23,999', 23999, 4.2, 280, 0, 'Submariner-inspired luxury design with Sony IMX882 OIS camera and 67W charging.', '8GB RAM · 256GB Storage · Curved AMOLED', '5000 mAh', 'HD Mic', '190 g', 'No', 'N/A', 'No', '1 year', 8.4, '86% match', 86, 'WAIT', 'Curved display & premium back finish', 'Stylish design with telephoto portrait lens.', ''),

-- Laptops
('lap_macm2', 'Apple MacBook Air M2', 'Apple', 'Laptops', 'Thin-and-light laptops', '₹89,900', 89900, 4.8, 740, 10, 'Incredibly thin fanless laptop with Apple M2 chip, 13.6-inch Liquid Retina display, and up to 18 hours of battery life.', 'M2 Chip · 8GB RAM · 256GB SSD · 18h Battery', '18 hours', 'Three-mic array', '1.24 kg', 'Yes', 'N/A', 'No', '1 year', 9.4, '96% match', 96, 'BUY', 'All-day productivity & fanless silent laptop', 'Top recommendation for college and work due to silent, super-fast M2 chip and 18h battery.', ''),
('lap_dellxps', 'Dell XPS 13', 'Dell', 'Laptops', 'Business laptops', '₹94,990', 94990, 4.6, 210, 5, 'Precision-crafted machined aluminum chassis, 13.4-inch InfinityEdge display, 13th Gen Intel Core i7 processor.', 'Core i7 · 16GB RAM · 512GB SSD · Windows 11', '12 hours', 'Dual-array mics', '1.17 kg', 'Yes', 'N/A', 'Yes', '1 year', 8.9, '91% match', 91, 'BUY', 'Windows premium ultra-portable', 'Ultralight business laptop with edge-to-edge screen and premium typing comfort.', ''),
('lap_asusrog', 'Asus ROG Strix G16', 'ASUS', 'Laptops', 'Gaming laptops', '₹1,12,990', 112990, 4.7, 310, 7, 'Intel Core i7-13650HX, Nvidia GeForce RTX 4060 8GB VRAM, ROG Intelligent Cooling, 165Hz ROG Nebula display.', 'Core i7 · RTX 4060 · 16GB RAM · 1TB SSD', '6 hours', 'AI noise cancelling mic', '2.50 kg', 'No', 'N/A', 'Yes', '1 year', 9.0, '93% match', 93, 'BUY', 'High FPS Gaming & 3D rendering', 'Powerhouse gaming laptop capable of ultra graphics gaming, video editing, and 3D work.', ''),
('lap_hppavilion', 'HP Pavilion 14', 'HP', 'Laptops', 'Student laptops', '₹52,990', 52990, 4.3, 450, 14, '13th Gen Intel Core i5 processor, 14-inch Full HD IPS Anti-glare display, audio by B&O, aluminum cover.', 'Core i5 · 16GB RAM · 512GB SSD · Backlit KB', '8 hours', 'Dual array mic', '1.41 kg', 'No', 'N/A', 'No', '1 year', 8.6, '87% match', 87, 'CONSIDER', 'Student study & home office under ₹55k', 'Balanced student laptop with 16GB RAM for multitasking and fast NVMe storage.', ''),
('lap_lenovoidea', 'Lenovo IdeaPad Slim 3', 'Lenovo', 'Laptops', 'Student laptops', '₹38,990', 38990, 4.2, 180, 0, '12th Gen Intel Core i3, 8GB RAM, 512GB SSD, 15.6-inch FHD screen.', 'Core i3 · 8GB RAM · 512GB SSD', '7 hours', 'HD Mic', '1.62 kg', 'No', 'N/A', 'No', '1 year', 8.2, '82% match', 82, 'WAIT', 'Entry budget laptop', 'Affordable budget laptop for online classes and document work.', ''),

-- Audio
('jbl_510bt', 'JBL Tune 510BT', 'JBL', 'Audio', 'Headphones', '₹2,799', 2799, 4.3, 980, 25, 'Wireless on-ear headphones with JBL Pure Bass sound, 40 hours battery life, Bluetooth 5.0, hands-free call controls.', '40 hours battery · On-Ear · Hands-free mic', '40 hours', 'Clear indoors', '160 g', 'Yes', 'No', 'No', '1 year', 8.6, '92% match', 92, 'BUY', 'Clear calls + lighter fit', 'Lighter weight and clearer indoor calls make it a top pick for classes.', ''),
('soundcore_h30i', 'Soundcore H30i', 'Soundcore', 'Audio', 'Headphones', '₹2,499', 2499, 4.2, 430, 18, 'Foldable wireless headphones with 70 hours playtime, 40mm drivers, customized EQ via Soundcore app.', '70 hours battery · App EQ · Foldable', '70 hours', 'Mixed in noise', '183 g', 'Yes', 'No', 'Yes', '1 year', 8.2, '88% match', 88, 'CONSIDER', 'Battery + app EQ', 'Extra long 70-hour battery life and custom app EQ at a lower budget.', ''),
('boat_450', 'boAt Rockerz 450', 'boAt', 'Audio', 'Headphones', '₹1,499', 1499, 4.1, 1250, 30, '40mm dynamic drivers, padded ear cushions, integrated controls, up to 15 hours playback.', '15 hours battery · On-Ear · HD Sound', '15 hours', 'Basic mic', '168 g', 'No', 'No', 'No', '1 year', 8.0, '84% match', 84, 'CONSIDER', 'Lower spend & budget value', 'Unbeatable entry-level price for daily casual listening.', ''),
('boat_stone_350', 'boAt Stone 350 Speaker', 'boAt', 'Audio', 'Bluetooth speakers', '₹1,299', 1299, 4.2, 670, 15, '10W stereo sound, IPX7 splash resistance, TWS feature to pair 2 speakers together.', '10W RMS · IPX7 Water Resistant · 12h Battery', '12 hours', 'Inbuilt mic', '210 g', 'No', 'No', 'No', '1 year', 8.1, '85% match', 85, 'BUY', 'Portable room speaker', 'Loud, punchy bass for small rooms and outdoor gatherings.', ''),
('sony_ch520', 'Sony WH-CH520', 'Sony', 'Audio', 'Headphones', '₹4,490', 4490, 4.4, 820, 12, 'DSEE sound enhancement, 50 hours battery life, multipoint connection, hands-free calling with beamforming mic.', '50 hours battery · DSEE · App Connect', '50 hours', 'HD voice mic', '147 g', 'Yes', 'No', 'Yes', '1 year', 8.8, '90% match', 90, 'BUY', 'Premium comfort & Sony sound', 'Ultra-lightweight design with Sony Headphones Connect app customization.', ''),
('realme_buds3', 'Realme Buds Wireless 3', 'Realme', 'Audio', 'TWS earbuds', '₹1,699', 1699, 4.0, 310, 22, '30dB Active Noise Cancellation, 13.6mm Bass Boost Driver, 360-degree spatial audio, 40 hours total battery.', '30dB ANC · 40 hours battery · 13.6mm driver', '40 hours', 'ENC mic', '30 g', 'Yes', '30dB ANC', 'Yes', '1 year', 8.4, '82% match', 82, 'CONSIDER', 'Active Noise Cancellation', 'Incredible active noise cancellation for commuters at budget prices.', ''),

-- Fashion
('fas_levis_jacket', 'Men\'s Levi\'s Trucker Denim Jacket', 'Levi\'s', 'Fashion', 'Men', '₹3,499', 3499, 4.4, 290, 11, '100% premium heavy cotton denim jacket with button closure, point collar, and chest flap pockets.', '100% Cotton · Classic Fit · Men Outerwear', 'N/A', 'N/A', '650 g', 'N/A', 'N/A', 'N/A', 'N/A', 8.7, '90% match', 90, 'BUY', 'Durable casual layering for Men', 'Timeless denim styling with rugged stitching for everyday casual wear.', ''),
('fas_zara_dress', 'Women\'s Floral Summer Dress', 'Zara', 'Fashion', 'Women', '₹2,290', 2290, 4.5, 410, 16, 'Flowy midi dress with vibrant botanical floral print, V-neckline, and comfortable elasticated waist fit.', 'Lightweight Viscose · Breathable · Women', 'N/A', 'N/A', '280 g', 'N/A', 'N/A', 'N/A', 'N/A', 8.9, '92% match', 92, 'BUY', 'Breathable summer elegance for Women', 'Flowy, elegant floral print dress ideal for sunny outings and events.', ''),
('fas_hm_hoodie', 'Boys Printed Fleece Hoodie', 'H&M', 'Fashion', 'Boys', '₹1,199', 1199, 4.3, 180, 20, 'Soft fleece sweatshirt hoodie with graphic chest print, kangaroo front pocket, and ribbed hem.', 'Soft Brushed Fleece · Boys 6-14 Yrs', 'N/A', 'N/A', '320 g', 'N/A', 'N/A', 'N/A', 'N/A', 8.5, '88% match', 88, 'BUY', 'Cozy daily warmth for Boys', 'Ribbed cuffs and soft fleece lining keep active boys comfortable all day.', ''),
('fas_max_frock', 'Girls Satin Party Frock', 'Max', 'Fashion', 'Girls', '₹999', 999, 4.2, 150, 14, 'A-line party frock with glossy satin finish, ribbon belt tie, and skin-friendly cotton lining.', 'Satin Finish · Girls 4-12 Yrs', 'N/A', 'N/A', '220 g', 'N/A', 'N/A', 'N/A', 'N/A', 8.4, '86% match', 86, 'BUY', 'Festive celebrations for Girls', 'Soft interior lining and vibrant satin flare for birthdays and family functions.', ''),
('fas_fossil_watch', 'Fossil Grant Chronograph Watch', 'Fossil', 'Fashion', 'Accessories', '₹8,495', 8495, 4.6, 530, 6, 'Genuine brown leather strap, stainless steel case, Roman numeral markers, 50m water resistance.', 'Chronograph · Leather Strap · 44mm Case', '2 Years Battery', 'N/A', '88 g', 'N/A', 'N/A', 'N/A', '2 years', 9.1, '93% match', 93, 'BUY', 'Classic leather watch accessory', 'Elegant analog timepiece with durable leather band for formal and casual wristwear.', ''),
('fas_rayban_sunglasses', 'Ray-Ban Aviator Classic Sunglasses', 'Ray-Ban', 'Fashion', 'Accessories', '₹7,290', 7290, 4.7, 620, 0, 'Iconic gold metal frame with G-15 green polarized lenses providing 100% UV protection.', 'Polarized Lens · Gold Frame · UV Protection', 'N/A', 'N/A', '32 g', 'N/A', 'N/A', 'N/A', '1 year', 8.8, '89% match', 89, 'WAIT', 'Classic UV eye protection', 'Legendary pilot style sunglasses with anti-glare polarized optics.', ''),

-- Home Appliances
('app_dyson_purifier', 'Dyson Air Purifier Cool', 'Dyson', 'Home Appliances', 'Air Purifiers', '₹32,900', 32900, 4.7, 380, 8, 'Fully sealed HEPA H13 filtration system captures 99.95% of pollutants as small as 0.1 microns.', 'HEPA H13 Filter · Air Multiplier · Oscillates 350°', 'Corded', 'N/A', '4.65 kg', 'N/A', 'N/A', 'Yes', '2 years', 9.1, '95% match', 95, 'BUY', 'Allergen removal & cooling airflow', 'Senses and captures 99.95% of ultrafine particles and allergens automatically.', ''),
('app_lg_washing', 'LG 8kg Front Load Washing Machine', 'LG', 'Home Appliances', 'Washing Machines', '₹34,990', 34990, 4.6, 710, 10, 'AI Direct Drive detects fabric weight and softness, Steam wash removes 99.9% virus and allergens.', 'AI Direct Drive · Steam Wash · 5 Star Inverter', 'Corded', 'N/A', '60 kg', 'N/A', 'N/A', 'Yes', '2 years', 9.0, '93% match', 93, 'BUY', 'Fabric protection & 5-star energy saving', 'AI Direct Drive detects weight and fabric softness to optimize wash patterns.', ''),
('app_philips_airfryer', 'Philips Digital Air Fryer', 'Philips', 'Home Appliances', 'Kitchen Appliances', '₹8,999', 8999, 4.5, 1100, 22, 'Rapid Air Technology cooks food with 90% less oil, 4.1L capacity, touch screen with 7 presets.', 'Rapid Air Tech · 4.1L Capacity · 1400W', 'Corded', 'N/A', '4.5 kg', 'N/A', 'N/A', 'No', '2 years', 8.8, '91% match', 91, 'BUY', 'Oil-free healthy cooking', 'Fries with up to 90% less fat while maintaining crispy outer texture.', ''),
('app_samsung_fridge', 'Samsung 253L Double Door Refrigerator', 'Samsung', 'Home Appliances', 'Refrigerators', '₹24,990', 24990, 4.4, 890, 15, 'Digital Inverter compressor automatically adjusts speed in response to cooling demand, Frost Free.', '3 Star Inverter · 253L Capacity · Frost Free', 'Corded', 'N/A', '46 kg', 'N/A', 'N/A', 'No', '1 year', 8.6, '89% match', 89, 'BUY', 'Long lasting food freshness & silent cooling', 'Digital Inverter compressor uses less energy and automatically adjusts speed.', ''),
('app_mi_tv', 'Xiaomi Smart TV 43 Inch 4K', 'Xiaomi', 'Home Appliances', 'Televisions', '₹22,999', 22999, 4.3, 950, 0, '4K Ultra HD display, Dolby Vision, 30W speaker sound with Dolby Audio, Google TV interface.', '4K UHD · Dolby Vision · Google TV · 30W Audio', 'Corded', 'Google Assistant Mic', '7.1 kg', 'N/A', 'N/A', 'Yes', '1 year', 8.5, '87% match', 87, 'WAIT', 'Budget 4K Smart TV', 'Crisp 4K resolution with Google TV built-in.', '')
ON DUPLICATE KEY UPDATE 
  `price_text` = VALUES(`price_text`),
  `numeric_price` = VALUES(`numeric_price`),
  `stock_quantity` = VALUES(`stock_quantity`);

-- Product Needs Tags Seed
INSERT IGNORE INTO `product_needs` (`product_id`, `need_tag`) VALUES
('jbl_510bt', 'Clear calls'), ('jbl_510bt', 'Under ₹3,000'), ('jbl_510bt', 'Work-from-home'),
('soundcore_h30i', 'Long battery'), ('soundcore_h30i', 'Under ₹3,000'), ('soundcore_h30i', 'Work-from-home'),
('boat_450', 'Under ₹3,000'), ('boat_450', 'Travel audio'),
('boat_stone_350', 'Under ₹3,000'), ('boat_stone_350', 'Travel audio'),
('sony_ch520', 'Clear calls'), ('sony_ch520', 'Long battery'), ('sony_ch520', 'Work-from-home'),
('realme_buds3', 'Travel audio'), ('realme_buds3', 'Under ₹3,000'),
('mob_s23', 'Mobiles'), ('mob_s23', 'Camera'), ('mob_s23', 'Flagship'),
('mob_ip15', 'Mobiles'), ('mob_ip15', 'Camera'), ('mob_ip15', 'iOS'),
('mob_op12r', 'Mobiles'), ('mob_op12r', 'Fast charging'), ('mob_op12r', 'Gaming'),
('mob_redmi13p', 'Mobiles'), ('mob_redmi13p', 'Under ₹25,000'), ('mob_redmi13p', 'Camera'),
('mob_realme12p', 'Mobiles'), ('mob_realme12p', 'Under ₹25,000'),
('lap_macm2', 'Laptops'), ('lap_macm2', 'Long battery'), ('lap_macm2', 'Study'), ('lap_macm2', 'Work'),
('lap_dellxps', 'Laptops'), ('lap_dellxps', 'Business'), ('lap_dellxps', 'Work'),
('lap_asusrog', 'Laptops'), ('lap_asusrog', 'Gaming'),
('lap_hppavilion', 'Laptops'), ('lap_hppavilion', 'Student'), ('lap_hppavilion', 'Study'),
('lap_lenovoidea', 'Laptops'), ('lap_lenovoidea', 'Student'),
('fas_levis_jacket', 'Fashion'), ('fas_levis_jacket', 'Men'),
('fas_zara_dress', 'Fashion'), ('fas_zara_dress', 'Women'),
('fas_hm_hoodie', 'Fashion'), ('fas_hm_hoodie', 'Boys'),
('fas_max_frock', 'Fashion'), ('fas_max_frock', 'Girls'),
('fas_fossil_watch', 'Fashion'), ('fas_fossil_watch', 'Accessories'),
('fas_rayban_sunglasses', 'Fashion'), ('fas_rayban_sunglasses', 'Accessories'),
('app_dyson_purifier', 'Home Appliances'), ('app_dyson_purifier', 'Air Purifier'),
('app_lg_washing', 'Home Appliances'), ('app_lg_washing', 'Washing Machine'),
('app_philips_airfryer', 'Home Appliances'), ('app_philips_airfryer', 'Kitchen'),
('app_samsung_fridge', 'Home Appliances'), ('app_samsung_fridge', 'Refrigerator'),
('app_mi_tv', 'Home Appliances'), ('app_mi_tv', 'Televisions');

-- AI Reviews Seed for Key Products
INSERT INTO `ai_reviews` 
(`product_id`, `sample_count`, `sample_period`, `star_5_count`, `star_4_count`, `star_3_count`, `star_2_count`, `star_1_count`, `positive_pct`, `neutral_pct`, `negative_pct`, `ai_summary`, `pros`, `cons`)
VALUES
('jbl_510bt', 240, 'September 2026', 144, 48, 24, 14, 10, 78, 14, 8,
 'Customers appreciate battery life and clear indoor calls, but some report ear pressure during long sessions and a weaker mic in noisy surroundings.',
 '["Exceptional 40-hour battery life", "Clear indoor call microphone", "Lighter 160g on-ear design", "Fast USB-C charging"]',
 '["Ear cushion pressure on glasses after 2+ hours", "Microphone picks up loud street noise"]')
ON DUPLICATE KEY UPDATE `sample_count` = VALUES(`sample_count`);

-- Price History Seed (for PriceTrackingScreen graph)
INSERT IGNORE INTO `price_history` (`product_id`, `recorded_price`, `recorded_date`) VALUES
('jbl_510bt', 2999, '2026-08-01'),
('jbl_510bt', 2899, '2026-08-15'),
('jbl_510bt', 2949, '2026-09-01'),
('jbl_510bt', 2799, '2026-09-15'),
('jbl_510bt', 2799, '2026-09-30');

