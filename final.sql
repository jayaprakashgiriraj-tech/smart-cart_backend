-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 03, 2026 at 09:23 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `smart_cart`
--

-- --------------------------------------------------------

--
-- Table structure for table `ai_reviews`
--

CREATE TABLE `ai_reviews` (
  `id` int(11) NOT NULL,
  `product_id` varchar(50) NOT NULL,
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
  `cons` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ai_reviews`
--

INSERT INTO `ai_reviews` (`id`, `product_id`, `sample_count`, `sample_period`, `star_5_count`, `star_4_count`, `star_3_count`, `star_2_count`, `star_1_count`, `positive_pct`, `neutral_pct`, `negative_pct`, `ai_summary`, `pros`, `cons`) VALUES
(1, 'jbl_510bt', 240, 'September 2026', 144, 48, 24, 14, 10, 78, 14, 8, 'Customers appreciate battery life and clear indoor calls, but some report ear pressure during long sessions and a weaker mic in noisy surroundings.', '[\"Exceptional 40-hour battery life\", \"Clear indoor call microphone\", \"Lighter 160g on-ear design\", \"Fast USB-C charging\"]', '[\"Ear cushion pressure on glasses after 2+ hours\", \"Microphone picks up loud street noise\"]');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` varchar(50) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `icon`, `description`) VALUES
(1, 'Mobiles', 'smartphone', 'Smartphones and mobile devices'),
(2, 'Laptops', 'laptop', 'Laptops for work, study and gaming'),
(3, 'Audio', 'headphones', 'Headphones, earbuds and bluetooth speakers'),
(4, 'Fashion', 'checkroom', 'Apparel, clothing and personal accessories'),
(5, 'Home Appliances', 'kitchen', 'Smart appliances for home and kitchen');

-- --------------------------------------------------------

--
-- Table structure for table `price_history`
--

CREATE TABLE `price_history` (
  `id` int(11) NOT NULL,
  `product_id` varchar(50) NOT NULL,
  `recorded_price` int(11) NOT NULL,
  `recorded_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `price_history`
--

INSERT INTO `price_history` (`id`, `product_id`, `recorded_price`, `recorded_date`) VALUES
(1, 'jbl_510bt', 2999, '2026-08-01'),
(2, 'jbl_510bt', 2899, '2026-08-15'),
(3, 'jbl_510bt', 2949, '2026-09-01'),
(4, 'jbl_510bt', 2799, '2026-09-15'),
(5, 'jbl_510bt', 2799, '2026-09-30');

-- --------------------------------------------------------

--
-- Table structure for table `price_tracking`
--

CREATE TABLE `price_tracking` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` varchar(50) NOT NULL,
  `target_price` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `price_tracking`
--

INSERT INTO `price_tracking` (`id`, `user_id`, `product_id`, `target_price`, `is_active`, `created_at`) VALUES
(1, 10, 'jbl_510bt', 2499, 1, '2026-10-28 04:40:32');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `brand`, `category_name`, `subcategory`, `price_text`, `numeric_price`, `rating`, `review_count`, `stock_quantity`, `description`, `specs`, `battery`, `call_mic`, `weight`, `multipoint`, `active_nc`, `app_eq`, `warranty`, `value_score`, `match_score_text`, `match_percentage`, `decision_tag`, `best_for`, `recommendation_summary`, `image_url`, `created_at`) VALUES
('app_dyson_purifier', 'Dyson Air Purifier Cool', 'Dyson', 'Home Appliances', 'Air Purifiers', 'Ôé╣32,900', 32900, 4.7, 380, 8, 'Fully sealed HEPA H13 filtration system captures 99.95% of pollutants as small as 0.1 microns.', 'HEPA H13 Filter ┬À Air Multiplier ┬À Oscillates 350┬░', 'Corded', 'N/A', '4.65 kg', 'N/A', 'N/A', 'Yes', '2 years', 9.1, '95% match', 95, 'BUY', 'Allergen removal & cooling airflow', 'Senses and captures 99.95% of ultrafine particles and allergens automatically.', '', '2026-10-28 04:21:12'),
('app_lg_washing', 'LG 8kg Front Load Washing Machine', 'LG', 'Home Appliances', 'Washing Machines', 'Ôé╣34,990', 34990, 4.6, 710, 10, 'AI Direct Drive detects fabric weight and softness, Steam wash removes 99.9% virus and allergens.', 'AI Direct Drive ┬À Steam Wash ┬À 5 Star Inverter', 'Corded', 'N/A', '60 kg', 'N/A', 'N/A', 'Yes', '2 years', 9.0, '93% match', 93, 'BUY', 'Fabric protection & 5-star energy saving', 'AI Direct Drive detects weight and fabric softness to optimize wash patterns.', '', '2026-10-28 04:21:12'),
('app_mi_tv', 'Xiaomi Smart TV 43 Inch 4K', 'Xiaomi', 'Home Appliances', 'Televisions', 'Ôé╣22,999', 22999, 4.3, 950, 0, '4K Ultra HD display, Dolby Vision, 30W speaker sound with Dolby Audio, Google TV interface.', '4K UHD ┬À Dolby Vision ┬À Google TV ┬À 30W Audio', 'Corded', 'Google Assistant Mic', '7.1 kg', 'N/A', 'N/A', 'Yes', '1 year', 8.5, '87% match', 87, 'WAIT', 'Budget 4K Smart TV', 'Crisp 4K resolution with Google TV built-in.', '', '2026-10-28 04:21:12'),
('app_philips_airfryer', 'Philips Digital Air Fryer', 'Philips', 'Home Appliances', 'Kitchen Appliances', 'Ôé╣8,999', 8999, 4.5, 1100, 22, 'Rapid Air Technology cooks food with 90% less oil, 4.1L capacity, touch screen with 7 presets.', 'Rapid Air Tech ┬À 4.1L Capacity ┬À 1400W', 'Corded', 'N/A', '4.5 kg', 'N/A', 'N/A', 'No', '2 years', 8.8, '91% match', 91, 'BUY', 'Oil-free healthy cooking', 'Fries with up to 90% less fat while maintaining crispy outer texture.', '', '2026-10-28 04:21:12'),
('app_samsung_fridge', 'Samsung 253L Double Door Refrigerator', 'Samsung', 'Home Appliances', 'Refrigerators', 'Ôé╣24,990', 24990, 4.4, 890, 15, 'Digital Inverter compressor automatically adjusts speed in response to cooling demand, Frost Free.', '3 Star Inverter ┬À 253L Capacity ┬À Frost Free', 'Corded', 'N/A', '46 kg', 'N/A', 'N/A', 'No', '1 year', 8.6, '89% match', 89, 'BUY', 'Long lasting food freshness & silent cooling', 'Digital Inverter compressor uses less energy and automatically adjusts speed.', '', '2026-10-28 04:21:12'),
('boat_450', 'boAt Rockerz 450', 'boAt', 'Audio', 'Headphones', 'Ôé╣1,499', 1499, 4.1, 1250, 30, '40mm dynamic drivers, padded ear cushions, integrated controls, up to 15 hours playback.', '15 hours battery ┬À On-Ear ┬À HD Sound', '15 hours', 'Basic mic', '168 g', 'No', 'No', 'No', '1 year', 8.0, '84% match', 84, 'CONSIDER', 'Lower spend & budget value', 'Unbeatable entry-level price for daily casual listening.', '', '2026-10-28 04:21:12'),
('boat_stone_350', 'boAt Stone 350 Speaker', 'boAt', 'Audio', 'Bluetooth speakers', 'Ôé╣1,299', 1299, 4.2, 670, 15, '10W stereo sound, IPX7 splash resistance, TWS feature to pair 2 speakers together.', '10W RMS ┬À IPX7 Water Resistant ┬À 12h Battery', '12 hours', 'Inbuilt mic', '210 g', 'No', 'No', 'No', '1 year', 8.1, '85% match', 85, 'BUY', 'Portable room speaker', 'Loud, punchy bass for small rooms and outdoor gatherings.', '', '2026-10-28 04:21:12'),
('fas_fossil_watch', 'Fossil Grant Chronograph Watch', 'Fossil', 'Fashion', 'Accessories', 'Ôé╣8,495', 8495, 4.6, 530, 6, 'Genuine brown leather strap, stainless steel case, Roman numeral markers, 50m water resistance.', 'Chronograph ┬À Leather Strap ┬À 44mm Case', '2 Years Battery', 'N/A', '88 g', 'N/A', 'N/A', 'N/A', '2 years', 9.1, '93% match', 93, 'BUY', 'Classic leather watch accessory', 'Elegant analog timepiece with durable leather band for formal and casual wristwear.', '', '2026-10-28 04:21:12'),
('fas_hm_hoodie', 'Boys Printed Fleece Hoodie', 'H&M', 'Fashion', 'Boys', 'Ôé╣1,199', 1199, 4.3, 180, 20, 'Soft fleece sweatshirt hoodie with graphic chest print, kangaroo front pocket, and ribbed hem.', 'Soft Brushed Fleece ┬À Boys 6-14 Yrs', 'N/A', 'N/A', '320 g', 'N/A', 'N/A', 'N/A', 'N/A', 8.5, '88% match', 88, 'BUY', 'Cozy daily warmth for Boys', 'Ribbed cuffs and soft fleece lining keep active boys comfortable all day.', '', '2026-10-28 04:21:12'),
('fas_levis_jacket', 'Men\'s Levi\'s Trucker Denim Jacket', 'Levi\'s', 'Fashion', 'Men', 'Ôé╣3,499', 3499, 4.4, 290, 11, '100% premium heavy cotton denim jacket with button closure, point collar, and chest flap pockets.', '100% Cotton ┬À Classic Fit ┬À Men Outerwear', 'N/A', 'N/A', '650 g', 'N/A', 'N/A', 'N/A', 'N/A', 8.7, '90% match', 90, 'BUY', 'Durable casual layering for Men', 'Timeless denim styling with rugged stitching for everyday casual wear.', '', '2026-10-28 04:21:12'),
('fas_max_frock', 'Girls Satin Party Frock', 'Max', 'Fashion', 'Girls', 'Ôé╣999', 999, 4.2, 150, 14, 'A-line party frock with glossy satin finish, ribbon belt tie, and skin-friendly cotton lining.', 'Satin Finish ┬À Girls 4-12 Yrs', 'N/A', 'N/A', '220 g', 'N/A', 'N/A', 'N/A', 'N/A', 8.4, '86% match', 86, 'BUY', 'Festive celebrations for Girls', 'Soft interior lining and vibrant satin flare for birthdays and family functions.', '', '2026-10-28 04:21:12'),
('fas_rayban_sunglasses', 'Ray-Ban Aviator Classic Sunglasses', 'Ray-Ban', 'Fashion', 'Accessories', 'Ôé╣7,290', 7290, 4.7, 620, 0, 'Iconic gold metal frame with G-15 green polarized lenses providing 100% UV protection.', 'Polarized Lens ┬À Gold Frame ┬À UV Protection', 'N/A', 'N/A', '32 g', 'N/A', 'N/A', 'N/A', '1 year', 8.8, '89% match', 89, 'WAIT', 'Classic UV eye protection', 'Legendary pilot style sunglasses with anti-glare polarized optics.', '', '2026-10-28 04:21:12'),
('fas_zara_dress', 'Women\'s Floral Summer Dress', 'Zara', 'Fashion', 'Women', 'Ôé╣2,290', 2290, 4.5, 410, 16, 'Flowy midi dress with vibrant botanical floral print, V-neckline, and comfortable elasticated waist fit.', 'Lightweight Viscose ┬À Breathable ┬À Women', 'N/A', 'N/A', '280 g', 'N/A', 'N/A', 'N/A', 'N/A', 8.9, '92% match', 92, 'BUY', 'Breathable summer elegance for Women', 'Flowy, elegant floral print dress ideal for sunny outings and events.', '', '2026-10-28 04:21:12'),
('jbl_510bt', 'JBL Tune 510BT', 'JBL', 'Audio', 'Headphones', 'Ôé╣2,799', 2799, 4.3, 980, 25, 'Wireless on-ear headphones with JBL Pure Bass sound, 40 hours battery life, Bluetooth 5.0, hands-free call controls.', '40 hours battery ┬À On-Ear ┬À Hands-free mic', '40 hours', 'Clear indoors', '160 g', 'Yes', 'No', 'No', '1 year', 8.6, '92% match', 92, 'BUY', 'Clear calls + lighter fit', 'Lighter weight and clearer indoor calls make it a top pick for classes.', '', '2026-10-28 04:21:12'),
('lap_asusrog', 'Asus ROG Strix G16', 'ASUS', 'Laptops', 'Gaming laptops', 'Ôé╣1,12,990', 112990, 4.7, 310, 7, 'Intel Core i7-13650HX, Nvidia GeForce RTX 4060 8GB VRAM, ROG Intelligent Cooling, 165Hz ROG Nebula display.', 'Core i7 ┬À RTX 4060 ┬À 16GB RAM ┬À 1TB SSD', '6 hours', 'AI noise cancelling mic', '2.50 kg', 'No', 'N/A', 'Yes', '1 year', 9.0, '93% match', 93, 'BUY', 'High FPS Gaming & 3D rendering', 'Powerhouse gaming laptop capable of ultra graphics gaming, video editing, and 3D work.', '', '2026-10-28 04:21:12'),
('lap_dellxps', 'Dell XPS 13', 'Dell', 'Laptops', 'Business laptops', 'Ôé╣94,990', 94990, 4.6, 210, 5, 'Precision-crafted machined aluminum chassis, 13.4-inch InfinityEdge display, 13th Gen Intel Core i7 processor.', 'Core i7 ┬À 16GB RAM ┬À 512GB SSD ┬À Windows 11', '12 hours', 'Dual-array mics', '1.17 kg', 'Yes', 'N/A', 'Yes', '1 year', 8.9, '91% match', 91, 'BUY', 'Windows premium ultra-portable', 'Ultralight business laptop with edge-to-edge screen and premium typing comfort.', '', '2026-10-28 04:21:12'),
('lap_hppavilion', 'HP Pavilion 14', 'HP', 'Laptops', 'Student laptops', 'Ôé╣52,990', 52990, 4.3, 450, 14, '13th Gen Intel Core i5 processor, 14-inch Full HD IPS Anti-glare display, audio by B&O, aluminum cover.', 'Core i5 ┬À 16GB RAM ┬À 512GB SSD ┬À Backlit KB', '8 hours', 'Dual array mic', '1.41 kg', 'No', 'N/A', 'No', '1 year', 8.6, '87% match', 87, 'CONSIDER', 'Student study & home office under Ôé╣55k', 'Balanced student laptop with 16GB RAM for multitasking and fast NVMe storage.', '', '2026-10-28 04:21:12'),
('lap_lenovoidea', 'Lenovo IdeaPad Slim 3', 'Lenovo', 'Laptops', 'Student laptops', 'Ôé╣38,990', 38990, 4.2, 180, 0, '12th Gen Intel Core i3, 8GB RAM, 512GB SSD, 15.6-inch FHD screen.', 'Core i3 ┬À 8GB RAM ┬À 512GB SSD', '7 hours', 'HD Mic', '1.62 kg', 'No', 'N/A', 'No', '1 year', 8.2, '82% match', 82, 'WAIT', 'Entry budget laptop', 'Affordable budget laptop for online classes and document work.', '', '2026-10-28 04:21:12'),
('lap_macm2', 'Apple MacBook Air M2', 'Apple', 'Laptops', 'Thin-and-light laptops', 'Ôé╣89,900', 89900, 4.8, 740, 10, 'Incredibly thin fanless laptop with Apple M2 chip, 13.6-inch Liquid Retina display, and up to 18 hours of battery life.', 'M2 Chip ┬À 8GB RAM ┬À 256GB SSD ┬À 18h Battery', '18 hours', 'Three-mic array', '1.24 kg', 'Yes', 'N/A', 'No', '1 year', 9.4, '96% match', 96, 'BUY', 'All-day productivity & fanless silent laptop', 'Top recommendation for college and work due to silent, super-fast M2 chip and 18h battery.', '', '2026-10-28 04:21:12'),
('mob_ip15', 'Apple iPhone 15', 'Apple', 'Mobiles', 'Smartphones', 'Ôé╣71,900', 71900, 4.7, 890, 8, 'Featuring Dynamic Island, 48MP Main camera with 2x Telephoto, USB-C connectivity, and durable color-infused glass design.', 'A16 Bionic ┬À 128GB Storage ┬À 5G', 'All-Day', 'Studio Mic Array', '171 g', 'Yes', 'N/A', 'No', '1 year', 9.0, '94% match', 94, 'BUY', 'iOS ecosystem & video recording', 'Premier smartphone for video creators, seamless iOS performance, and all-day battery.', '', '2026-10-28 04:21:12'),
('mob_op12r', 'OnePlus 12R', 'OnePlus', 'Mobiles', 'Smartphones', 'Ôé╣39,999', 39999, 4.5, 340, 15, 'Powered by Snapdragon 8 Gen 2, 5500mAh massive battery, 100W SUPERVOOC charging, and 120Hz ProXDR display.', '16GB RAM ┬À 256GB Storage ┬À 100W Fast Charge', '5500 mAh', 'Dual Noise-Cancelling', '207 g', 'Yes', 'N/A', 'Yes', '1 year', 9.1, '91% match', 91, 'BUY', '100W Fast charging & gaming speed', 'Ideal for power users who want rapid charging and high FPS gaming at mid-tier pricing.', '', '2026-10-28 04:21:12'),
('mob_realme12p', 'Realme 12 Pro 5G', 'Realme', 'Mobiles', 'Smartphones', 'Ôé╣23,999', 23999, 4.2, 280, 0, 'Submariner-inspired luxury design with Sony IMX882 OIS camera and 67W charging.', '8GB RAM ┬À 256GB Storage ┬À Curved AMOLED', '5000 mAh', 'HD Mic', '190 g', 'No', 'N/A', 'No', '1 year', 8.4, '86% match', 86, 'WAIT', 'Curved display & premium back finish', 'Stylish design with telephoto portrait lens.', '', '2026-10-28 04:21:12'),
('mob_redmi13p', 'Redmi Note 13 Pro', 'Xiaomi', 'Mobiles', 'Smartphones', 'Ôé╣21,999', 21999, 4.3, 610, 20, '200MP OIS camera, 1.5K 120Hz AMOLED display, Snapdragon 7s Gen 2 chip, and 67W Turbo Charge.', '8GB RAM ┬À 128GB Storage ┬À 200MP Camera', '5100 mAh', 'Clear Voice Mic', '187 g', 'No', 'N/A', 'No', '1 year', 8.7, '88% match', 88, 'BUY', 'Budget high resolution camera', 'Best value under Ôé╣25k for detailed photography and sharp 1.5K display.', '', '2026-10-28 04:21:12'),
('mob_s23', 'Samsung Galaxy S23', 'Samsung', 'Mobiles', 'Smartphones', 'Ôé╣49,999', 49999, 4.6, 520, 12, 'Compact Android flagship with Snapdragon 8 Gen 2 processor, Dynamic AMOLED 2X 120Hz display, and 50MP triple rear camera system.', '8GB RAM ┬À 256GB Storage ┬À 5G', '3900 mAh', 'Clear Dual-Mic', '168 g', 'Yes', 'N/A', 'Yes', '1 year', 9.2, '95% match', 95, 'BUY', 'Compact flagship performance & cameras', 'Top Android pick under Ôé╣50k with premium build, dynamic 120Hz display, and pro cameras.', '', '2026-10-28 04:21:12'),
('realme_buds3', 'Realme Buds Wireless 3', 'Realme', 'Audio', 'TWS earbuds', 'Ôé╣1,699', 1699, 4.0, 310, 22, '30dB Active Noise Cancellation, 13.6mm Bass Boost Driver, 360-degree spatial audio, 40 hours total battery.', '30dB ANC ┬À 40 hours battery ┬À 13.6mm driver', '40 hours', 'ENC mic', '30 g', 'Yes', '30dB ANC', 'Yes', '1 year', 8.4, '82% match', 82, 'CONSIDER', 'Active Noise Cancellation', 'Incredible active noise cancellation for commuters at budget prices.', '', '2026-10-28 04:21:12'),
('sony_ch520', 'Sony WH-CH520', 'Sony', 'Audio', 'Headphones', 'Ôé╣4,490', 4490, 4.4, 820, 12, 'DSEE sound enhancement, 50 hours battery life, multipoint connection, hands-free calling with beamforming mic.', '50 hours battery ┬À DSEE ┬À App Connect', '50 hours', 'HD voice mic', '147 g', 'Yes', 'No', 'Yes', '1 year', 8.8, '90% match', 90, 'BUY', 'Premium comfort & Sony sound', 'Ultra-lightweight design with Sony Headphones Connect app customization.', '', '2026-10-28 04:21:12'),
('soundcore_h30i', 'Soundcore H30i', 'Soundcore', 'Audio', 'Headphones', 'Ôé╣2,499', 2499, 4.2, 430, 18, 'Foldable wireless headphones with 70 hours playtime, 40mm drivers, customized EQ via Soundcore app.', '70 hours battery ┬À App EQ ┬À Foldable', '70 hours', 'Mixed in noise', '183 g', 'Yes', 'No', 'Yes', '1 year', 8.2, '88% match', 88, 'CONSIDER', 'Battery + app EQ', 'Extra long 70-hour battery life and custom app EQ at a lower budget.', '', '2026-10-28 04:21:12');

-- --------------------------------------------------------

--
-- Table structure for table `product_needs`
--

CREATE TABLE `product_needs` (
  `id` int(11) NOT NULL,
  `product_id` varchar(50) NOT NULL,
  `need_tag` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_needs`
--

INSERT INTO `product_needs` (`id`, `product_id`, `need_tag`) VALUES
(1, 'jbl_510bt', 'Clear calls'),
(2, 'jbl_510bt', 'Under Ôé╣3,000'),
(3, 'jbl_510bt', 'Work-from-home'),
(4, 'soundcore_h30i', 'Long battery'),
(5, 'soundcore_h30i', 'Under Ôé╣3,000'),
(6, 'soundcore_h30i', 'Work-from-home'),
(7, 'boat_450', 'Under Ôé╣3,000'),
(8, 'boat_450', 'Travel audio'),
(9, 'boat_stone_350', 'Under Ôé╣3,000'),
(10, 'boat_stone_350', 'Travel audio'),
(11, 'sony_ch520', 'Clear calls'),
(12, 'sony_ch520', 'Long battery'),
(13, 'sony_ch520', 'Work-from-home'),
(14, 'realme_buds3', 'Travel audio'),
(15, 'realme_buds3', 'Under Ôé╣3,000'),
(16, 'mob_s23', 'Mobiles'),
(17, 'mob_s23', 'Camera'),
(18, 'mob_s23', 'Flagship'),
(19, 'mob_ip15', 'Mobiles'),
(20, 'mob_ip15', 'Camera'),
(21, 'mob_ip15', 'iOS'),
(22, 'mob_op12r', 'Mobiles'),
(23, 'mob_op12r', 'Fast charging'),
(24, 'mob_op12r', 'Gaming'),
(25, 'mob_redmi13p', 'Mobiles'),
(26, 'mob_redmi13p', 'Under Ôé╣25,000'),
(27, 'mob_redmi13p', 'Camera'),
(28, 'mob_realme12p', 'Mobiles'),
(29, 'mob_realme12p', 'Under Ôé╣25,000'),
(30, 'lap_macm2', 'Laptops'),
(31, 'lap_macm2', 'Long battery'),
(32, 'lap_macm2', 'Study'),
(33, 'lap_macm2', 'Work'),
(34, 'lap_dellxps', 'Laptops'),
(35, 'lap_dellxps', 'Business'),
(36, 'lap_dellxps', 'Work'),
(37, 'lap_asusrog', 'Laptops'),
(38, 'lap_asusrog', 'Gaming'),
(39, 'lap_hppavilion', 'Laptops'),
(40, 'lap_hppavilion', 'Student'),
(41, 'lap_hppavilion', 'Study'),
(42, 'lap_lenovoidea', 'Laptops'),
(43, 'lap_lenovoidea', 'Student'),
(44, 'fas_levis_jacket', 'Fashion'),
(45, 'fas_levis_jacket', 'Men'),
(46, 'fas_zara_dress', 'Fashion'),
(47, 'fas_zara_dress', 'Women'),
(48, 'fas_hm_hoodie', 'Fashion'),
(49, 'fas_hm_hoodie', 'Boys'),
(50, 'fas_max_frock', 'Fashion'),
(51, 'fas_max_frock', 'Girls'),
(52, 'fas_fossil_watch', 'Fashion'),
(53, 'fas_fossil_watch', 'Accessories'),
(54, 'fas_rayban_sunglasses', 'Fashion'),
(55, 'fas_rayban_sunglasses', 'Accessories'),
(56, 'app_dyson_purifier', 'Home Appliances'),
(57, 'app_dyson_purifier', 'Air Purifier'),
(58, 'app_lg_washing', 'Home Appliances'),
(59, 'app_lg_washing', 'Washing Machine'),
(60, 'app_philips_airfryer', 'Home Appliances'),
(61, 'app_philips_airfryer', 'Kitchen'),
(62, 'app_samsung_fridge', 'Home Appliances'),
(63, 'app_samsung_fridge', 'Refrigerator'),
(64, 'app_mi_tv', 'Home Appliances'),
(65, 'app_mi_tv', 'Televisions');

-- --------------------------------------------------------

--
-- Table structure for table `saved_comparisons`
--

CREATE TABLE `saved_comparisons` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product1_id` varchar(50) NOT NULL,
  `product2_id` varchar(50) NOT NULL,
  `winner_product_id` varchar(50) DEFAULT NULL,
  `comparison_title` varchar(150) DEFAULT 'Online classes comparison',
  `summary` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `saved_comparisons`
--

INSERT INTO `saved_comparisons` (`id`, `user_id`, `product1_id`, `product2_id`, `winner_product_id`, `comparison_title`, `summary`, `created_at`) VALUES
(1, 10, 'jbl_510bt', 'soundcore_h30i', 'jbl_510bt', '1 comparison · Online classes', 'JBL Tune 510BT is rated higher for your priority use case (Clear calls + lighter fit).', '2026-10-28 06:58:25'),
(2, 10, 'jbl_510bt', 'soundcore_h30i', 'jbl_510bt', '1 comparison · Online classes', 'JBL Tune 510BT is rated higher for your priority use case (Clear calls + lighter fit).', '2026-10-28 07:23:08');

-- --------------------------------------------------------

--
-- Table structure for table `shopping_history`
--

CREATE TABLE `shopping_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` varchar(50) NOT NULL,
  `viewed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `created_at`, `profile_photo`) VALUES
(1, 'jayaprakesh', 'jayaprakash2@gmail.com', '$2y$10$rbmNMydRkwNkabPSYJga5eHxdc3lS4ghL/XU3cGQEHfCncV3zzYvG', '2026-10-26 07:37:00', NULL),
(4, 'jayaprakesh', 'jayaprakash2000000@gmail.com', '$2y$10$Bx7CBnKEl9nxZeqWcpFzi.wtlrzv8wIjkUIhh3jT077MY3AC3Ak1G', '2026-10-26 07:37:44', NULL),
(7, 'John Doe', 'john.doe@example.com', '$2y$10$iD9HerESOYTZptob70ap7uGOfqnIMK6TNIIk1vbR76AnWYbXqcele', '2026-10-26 07:44:22', NULL),
(9, 'nitesh', 'niteshghost444@gmail.com', '$2y$10$FENVtfDdYkBzvzQYkHavlO0tjnkNi5IpS.6cveiAdWpJksKDoF41S', '2026-10-27 02:45:43', 'uploads/373be0b6a6bf25a05bf5547fefd44959.jpg'),
(10, 'Aarav Mehta Updated', 'aarav.mehta@example.com', '$2y$10$hYF3DBGIAaR.XEaR5iUvpO2hnxi8POBg1CsA88k7XWGRYzrUQ/huW', '2026-10-28 03:53:04', 'uploads/2b1af12346ede4a558fff186aac855af.png'),
(14, 'Aarav Mehta', 'aarav.madesh@example.com', '$2y$10$dt7TMgyuSOKiwbrl.pFHNub5.SG3NGsMQSu7MbuHP4QqWJlGZTr.y', '2026-10-29 03:15:47', NULL),
(17, 'Jayaprakash G', 'jayaprakashgiriraj@gmail.com', '$2y$10$NYeD48QF3bww95fTk7xFiupjp6eb8nDuKj6l5Z4LrOApL0nDXTy06', '2026-10-30 03:25:26', 'uploads/a17c34845e82355d7a1f435b4b86b8b7.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `user_preferences`
--

CREATE TABLE `user_preferences` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `categories` text DEFAULT NULL,
  `audio_budget` decimal(10,2) DEFAULT 3000.00,
  `feature_priorities` text DEFAULT NULL,
  `price_drop_alerts` tinyint(1) DEFAULT 1,
  `new_recommendations` tinyint(1) DEFAULT 0,
  `use_shopping_history` tinyint(1) DEFAULT 1,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_preferences`
--

INSERT INTO `user_preferences` (`id`, `user_id`, `categories`, `audio_budget`, `feature_priorities`, `price_drop_alerts`, `new_recommendations`, `use_shopping_history`, `updated_at`) VALUES
(1, 10, '[\"Audio\",\"Mobiles\",\"Laptops\"]', 3000.00, '[\"Clear mic\",\"Comfort\",\"Battery\"]', 1, 0, 1, '2026-10-28 04:45:22');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` varchar(50) NOT NULL,
  `price_drop_alert` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ai_reviews`
--
ALTER TABLE `ai_reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_id` (`product_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_product_cart` (`user_id`,`product_id`),
  ADD KEY `fk_cart_product` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `price_history`
--
ALTER TABLE `price_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_price_history_prod` (`product_id`);

--
-- Indexes for table `price_tracking`
--
ALTER TABLE `price_tracking`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_product_tracking` (`user_id`,`product_id`),
  ADD KEY `fk_tracking_product` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category_name`),
  ADD KEY `idx_numeric_price` (`numeric_price`);

--
-- Indexes for table `product_needs`
--
ALTER TABLE `product_needs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Indexes for table `saved_comparisons`
--
ALTER TABLE `saved_comparisons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_comp_user` (`user_id`),
  ADD KEY `fk_comp_p1` (`product1_id`),
  ADD KEY `fk_comp_p2` (`product2_id`);

--
-- Indexes for table `shopping_history`
--
ALTER TABLE `shopping_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_history_user` (`user_id`),
  ADD KEY `fk_history_product` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_product_wishlist` (`user_id`,`product_id`),
  ADD KEY `fk_wishlist_product` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ai_reviews`
--
ALTER TABLE `ai_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `price_history`
--
ALTER TABLE `price_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `price_tracking`
--
ALTER TABLE `price_tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `product_needs`
--
ALTER TABLE `product_needs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `saved_comparisons`
--
ALTER TABLE `saved_comparisons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `shopping_history`
--
ALTER TABLE `shopping_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `user_preferences`
--
ALTER TABLE `user_preferences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ai_reviews`
--
ALTER TABLE `ai_reviews`
  ADD CONSTRAINT `fk_review_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `price_history`
--
ALTER TABLE `price_history`
  ADD CONSTRAINT `fk_ph_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `price_tracking`
--
ALTER TABLE `price_tracking`
  ADD CONSTRAINT `fk_tracking_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tracking_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_needs`
--
ALTER TABLE `product_needs`
  ADD CONSTRAINT `fk_needs_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `saved_comparisons`
--
ALTER TABLE `saved_comparisons`
  ADD CONSTRAINT `fk_comp_p1` FOREIGN KEY (`product1_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_comp_p2` FOREIGN KEY (`product2_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_comp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shopping_history`
--
ALTER TABLE `shopping_history`
  ADD CONSTRAINT `fk_history_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_history_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD CONSTRAINT `fk_pref_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `fk_wishlist_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_wishlist_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
