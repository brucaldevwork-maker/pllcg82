-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 24, 2026 at 08:51 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pllc_enterprise`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `role`) VALUES
(2, 'admin', '$2y$10$YrjZsZmM20qPiwLmDgG/iebCj8bEvUHE5H8JI6F5nKyn2gWs4pPSe', 'super_admin');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `service_type` varchar(100) DEFAULT NULL,
  `appointment_date` date DEFAULT NULL,
  `appointment_time` time DEFAULT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `user_id`, `service_type`, `appointment_date`, `appointment_time`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 3, 'maintenance', '2026-03-23', '11:00:00', 'pending', 'Bike Model: echo\nBranch: Gumaca\nUrgency: normal\nProblem: Shes', '2026-03-22 01:56:39', '2026-03-22 01:56:39');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `session_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `session_id`, `created_at`) VALUES
(7, NULL, 16, 1, '0r3ibpt31ilqvsl4fe8osi5oh4', '2026-03-22 12:22:59');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `sentiment` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `user_id`, `product_id`, `rating`, `comment`, `sentiment`, `created_at`) VALUES
(1, 3, NULL, 5, 'its good\n\n--- Additional Information ---\nFeedback Type: service\nRecommendation: probably\nName: admin\nEmail: admin@gmail.com', 'Positive', '2026-03-22 01:56:08');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_number` varchar(50) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `shipping_address` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `total_amount`, `status`, `shipping_address`, `payment_method`, `created_at`, `updated_at`) VALUES
(1, 3, 'PLLC-20260321-A4529C', 33499.00, 'processing', 'admin, weqwe, weqwe', 'cod', '2026-03-21 16:40:10', '2026-03-22 01:56:55'),
(2, 3, 'PLLC-20260322-349ADA', 4650.00, 'pending', 'Store Pickup - CSC', 'bank', '2026-03-22 10:30:27', '2026-03-22 10:30:27');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `created_at`) VALUES
(1, 1, 28, 1, 4500.00, '2026-03-21 16:40:10'),
(2, 1, 3, 1, 28999.00, '2026-03-21 16:40:10'),
(3, 2, 28, 1, 4500.00, '2026-03-22 10:30:27');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `subcategory` varchar(50) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `rating` decimal(2,1) DEFAULT NULL,
  `reviews` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `stock` int(11) DEFAULT NULL,
  `min_stock` int(11) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT NULL,
  `sku` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `category`, `subcategory`, `image`, `rating`, `reviews`, `description`, `stock`, `min_stock`, `is_available`, `sku`) VALUES
(1, 'PLLC Raven E-Bike', 25999.00, 'ebikes', 'raven', 'image/raven.jpg', 4.8, 124, 'Premium electric bicycle with long-lasting battery', 15, 5, 1, 'PLLC-RVN-001'),
(2, 'PLLC Echo E-Bike', 22999.00, 'ebikes', 'echo', 'image/echo v1.jpg', 4.7, 98, 'Echo model with advanced features', 8, 3, 1, 'PLLC-ECH-002'),
(3, 'PLLC Supreme E-Bike', 28999.00, 'ebikes', 'supreme', 'image/supreme v1.jpg', 4.9, 156, 'Top-tier electric bicycle with premium components', 2, 2, 1, 'PLLC-SUP-003'),
(4, 'PLLC Skye E-Bike', 19999.00, 'ebikes', 'skye', 'image/skye2.0.jpg', 4.6, 87, 'Lightweight and efficient electric bike', 12, 4, 1, 'PLLC-SKY-004'),
(5, 'PLLC Zhi 18 E-Bike', 17999.00, 'ebikes', 'zhi18', 'image/zhi-018 modified.jpg', 4.5, 73, 'Compact and reliable electric bicycle', 10, 3, 1, 'PLLC-ZHI-005'),
(6, 'PLLC Adventure Plus E-Bike', 23999.00, 'ebikes', 'adventure', 'image/adventure plus.jpg', 4.7, 89, 'Adventure-ready electric bike for off-road terrain', 6, 2, 1, 'PLLC-ADV-006'),
(7, 'PLLC Blaze E-Bike', 21999.00, 'ebikes', 'blaze', 'image/blaze.jpg', 4.6, 67, 'High-performance electric bike with sporty design', 9, 3, 1, 'PLLC-BLZ-007'),
(8, 'PLLC DC V3 E-Bike', 24999.00, 'ebikes', 'dc', 'image/dc v3.jpg', 4.8, 112, 'Direct current powered electric bicycle', 7, 2, 1, 'PLLC-DC-008'),
(9, 'PLLC Dusk V1 E-Bike', 19999.00, 'ebikes', 'dusk', 'image/dusk v1.jpg', 4.5, 78, 'Evening ride optimized electric bike', 11, 4, 1, 'PLLC-DSK-009'),
(10, 'PLLC Mini Cargo V2', 18999.00, 'ebikes', 'cargo', 'image/mini cargo v.2.jpg', 4.4, 54, 'Compact cargo electric bike for urban delivery', 13, 5, 1, 'PLLC-CRG-010'),
(11, 'PLLC P1 Plus V1', 27999.00, 'ebikes', 'p1plus', 'image/p1 plua v.1.jpg', 4.9, 134, 'Premium plus model with enhanced features', 4, 2, 1, 'PLLC-P1P-011'),
(12, 'PLLC Pau V3.2', 20999.00, 'ebikes', 'pau', 'image/pau v3.2.jpg', 4.6, 91, 'Version 3.2 with improved battery life', 8, 3, 1, 'PLLC-PAU-012'),
(13, 'PLLC Ragnar E-Bike', 26999.00, 'ebikes', 'ragnar', 'image/ragnar.jpg', 4.8, 156, 'Warrior-class electric bike for tough terrain', 5, 2, 1, 'PLLC-RAG-013'),
(14, 'PLLC Storm V1', 22999.00, 'ebikes', 'storm', 'image/storm v1.jpg', 4.7, 103, 'Storm-ready electric bike for all weather', 7, 3, 1, 'PLLC-STM-014'),
(15, 'PLLC Summer E-Bike', 17999.00, 'ebikes', 'summer', 'image/summer.jpg', 4.5, 67, 'Summer cruising electric bike', 14, 5, 1, 'PLLC-SUM-015'),
(16, 'PLLC Supreme Plus V2', 32999.00, 'ebikes', 'supremeplus', 'image/supreme plus v2.jpg', 5.0, 89, 'Ultimate premium electric bike with all features', 2, 1, 1, 'PLLC-SPP-016'),
(17, 'PLLC Pau 001 Model', 23999.00, 'ebikes', 'pau', 'image/pau 001.jpg', 4.7, 112, 'Special edition Pau 001 electric bike', 6, 2, 1, 'PLLC-PAU-020'),
(18, 'PLLC 48V Long-Life Battery', 8500.00, 'batteries', '48v', 'image/battery.jpg', 4.7, 89, 'High-capacity 48V lithium battery for extended range', 25, 10, 1, 'PLLC-BAT-006'),
(19, 'PLLC 60V Power Battery', 12500.00, 'batteries', '60v', 'image/battery.avif', 4.8, 67, '60V high-performance battery for maximum power', 18, 8, 1, 'PLLC-BAT-007'),
(20, 'PLLC Lithium Pro Battery', 9800.00, 'batteries', 'lithium', 'image/battery.jpg', 4.9, 124, 'Professional grade lithium battery with smart management', 30, 12, 1, 'PLLC-BAT-008'),
(21, 'PLLC Standard Battery', 6500.00, 'batteries', 'standard', 'image/battery.avif', 4.5, 156, 'Reliable standard battery for everyday use', 40, 15, 1, 'PLLC-BAT-009'),
(22, 'PLLC Heavy Duty Battery', 15000.00, 'batteries', 'heavy-duty', 'image/battery.jpg', 4.8, 78, 'Heavy-duty battery for commercial and industrial use', 12, 5, 1, 'PLLC-BAT-010'),
(23, 'PLLC Tire Set (Front/Rear)', 2500.00, 'spareparts', 'tires', 'image/spareparts.jpg', 4.6, 67, 'High-quality tire set for e-bikes', 40, 12, 1, 'PLLC-SP-TIRE-001'),
(24, 'PLLC Brake Lever Assembly', 1200.00, 'spareparts', 'brakes', 'image/spareparts.jpg', 4.5, 45, 'Complete brake lever assembly', 35, 10, 1, 'PLLC-SP-BRAKE-002'),
(25, 'PLLC Motor Controller', 3200.00, 'spareparts', 'motor', 'image/motortype.jpg', 4.8, 156, 'Advanced motor controller for smooth operation', 20, 8, 1, 'PLLC-SP-MOTOR-003'),
(26, 'PLLC LED Light Kit', 800.00, 'spareparts', 'lighting', 'image/spareparts.jpg', 4.5, 312, 'Bright LED lighting system for safety', 75, 25, 1, 'PLLC-SP-LED-004'),
(27, 'PLLC Accessory Kit', 1200.00, 'spareparts', 'accessories', 'image/spareparts.jpg', 4.4, 267, 'Essential accessories for your e-bike', 60, 20, 1, 'PLLC-SP-ACC-005'),
(28, 'PLLC Motor Type 2.0', 4500.00, 'spareparts', 'motor', 'image/motortype 2.0.jpg', 4.9, 145, 'Advanced motor system with improved efficiency', 13, 6, 1, 'PLLC-MOT-018'),
(29, 'PLLC Adjustable Seat', 1500.00, 'spareparts', 'accessories', 'image/adjustable.jpg', 4.6, 198, 'Comfortable adjustable seat for long rides', 40, 15, 1, 'PLLC-ACC-019'),
(32, 'logo', 30.00, 'ebikes', 'dc', 'image/logo_1774175622.png', 0.0, 0, '32131', 5, 5, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `phone`, `address`) VALUES
(3, 'admin', 'admin@gmail.com', '$2y$10$iJ5GibNgznRtcuQsaJCMxOj9TQphpSbtkloc/Ac5K9oZ1g8HwXHEW', 'admin', 'admin', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
