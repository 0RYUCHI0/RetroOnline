-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 17, 2026 at 03:07 PM
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
-- Database: `retrogamehub`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action_type` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `old_data` longtext DEFAULT NULL,
  `new_data` longtext DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `logged_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`log_id`, `user_id`, `action_type`, `description`, `entity_type`, `entity_id`, `old_data`, `new_data`, `ip_address`, `user_agent`, `logged_at`) VALUES
(1, 1, 'login', 'User logged in successfully', 'user', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-10 12:23:55'),
(2, 6, 'login', 'User logged in successfully', 'user', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-10 12:24:42'),
(3, 1, 'login', 'User logged in successfully', 'user', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-10 12:25:00'),
(4, 1, 'login', 'User logged in successfully', 'user', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-10 12:25:53'),
(5, 1, 'login', 'User logged in successfully', 'user', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-10 12:26:17'),
(6, 1, 'login', 'User logged in successfully', 'user', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-10 12:31:59'),
(7, 6, 'login', 'User logged in successfully', 'user', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-10 12:32:40'),
(8, 2, 'login', 'User logged in successfully', 'user', 2, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-10 12:32:59'),
(9, 7, 'login', 'User logged in successfully', 'user', 7, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-10 12:33:10'),
(10, 3, 'login', 'User logged in successfully', 'user', 3, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-10 12:37:43'),
(11, 2, 'product_update', 'Product \'Sonic the Hedgehog\' updated', 'product', 5, '{\"name\":\"Sonic the Hedgehog\",\"console\":\"Sega\",\"category\":\"Action\",\"price\":\"10.00\",\"image_url\":\"\",\"demo_url\":\"https:\\/\\/www.youtube.com\\/embed\\/7Ba1qSpZaFY\"}', '{\"name\":\"Sonic the Hedgehog\",\"console\":\"Sega\",\"category\":\"Action\",\"price\":10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-10 12:38:24'),
(12, 4, 'login', 'User logged in successfully', 'user', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-10 12:38:34'),
(13, 2, 'login', 'User logged in successfully', 'user', 2, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-10 12:42:37'),
(14, 1, 'login', 'User logged in successfully', 'user', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-10 12:44:40'),
(15, 2, 'login', 'User logged in successfully', 'user', 2, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-10 12:46:17'),
(16, 6, 'login', 'User logged in successfully', 'user', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-10 12:46:49'),
(17, 7, 'login', 'User logged in successfully', 'user', 7, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-10 12:46:59'),
(18, 4, 'login', 'User logged in successfully', 'user', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-10 12:51:34'),
(19, 2, 'login', 'User logged in successfully', 'user', 2, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-10 12:54:53'),
(20, 7, 'login', 'User logged in successfully', 'user', 7, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-10 12:57:25'),
(21, 6, 'login', 'User logged in successfully', 'user', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-10 13:02:32'),
(22, 6, 'login', 'User logged in successfully', 'user', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-10 13:07:23'),
(23, 6, 'login', 'User logged in successfully', 'user', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-10 13:10:36'),
(24, 1, 'login', 'User logged in successfully', 'user', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-10 13:10:47'),
(25, 7, 'login', 'User logged in successfully', 'user', 7, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-10 13:11:22'),
(26, 1, 'stock_decrease', 'Stock decreased by 4 for \'Tekken 3\'', 'product', 10, '{\"stock\":4}', '{\"stock\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-10 13:13:02'),
(27, 6, 'login', 'User logged in successfully', 'user', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-10 13:13:11'),
(28, 4, 'login', 'User logged in successfully', 'user', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-10 13:13:22'),
(29, 3, 'login', 'User logged in successfully', 'user', 3, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-10 13:13:32'),
(30, 2, 'login', 'User logged in successfully', 'user', 2, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-10 13:13:44'),
(31, 7, 'login', 'User logged in successfully', 'user', 7, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-10 13:14:03'),
(32, 6, 'login', 'User logged in successfully', 'user', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-17 12:43:38'),
(33, 8, 'login', 'User logged in successfully', 'user', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-17 12:44:30'),
(34, 1, 'stock_decrease', 'Stock decreased by 3 for \'Castlevania\'', 'product', 14, '{\"stock\":10}', '{\"stock\":7}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-17 12:47:40'),
(35, 3, 'login', 'User logged in successfully', 'user', 3, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-17 12:48:20'),
(36, 3, 'login', 'User logged in successfully', 'user', 3, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-17 12:48:29'),
(37, 2, 'login', 'User logged in successfully', 'user', 2, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-17 12:48:39'),
(38, 1, 'login', 'User logged in successfully', 'user', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-17 13:05:49'),
(39, 2, 'login', 'User logged in successfully', 'user', 2, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-17 13:06:32'),
(40, 1, 'login', 'User logged in successfully', 'user', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-17 13:07:47'),
(41, 8, 'login', 'User logged in successfully', 'user', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-17 13:14:53'),
(42, 1, 'login', 'User logged in successfully', 'user', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-17 13:15:17'),
(43, 1, 'login', 'User logged in successfully', 'user', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-17 13:16:54'),
(44, 2, 'login', 'User logged in successfully', 'user', 2, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-17 13:18:00'),
(45, 8, 'login', 'User logged in successfully', 'user', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-17 13:19:34'),
(46, 8, 'login', 'User logged in successfully', 'user', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-17 13:31:27'),
(47, 4, 'stock_decrease', 'Stock decreased by 5 for \'GTA,S\'', 'product', 7, '{\"stock\":15}', '{\"stock\":10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-17 13:51:11'),
(48, 1, 'login', 'User logged in successfully', 'user', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-17 13:51:32'),
(49, 6, 'login', 'User logged in successfully', 'user', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-17 13:51:59'),
(50, 8, 'login', 'User logged in successfully', 'user', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-17 13:52:31'),
(51, 4, 'stock_decrease', 'Stock decreased by 5 for \'GTA,S\'', 'product', 7, '{\"stock\":10}', '{\"stock\":5}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-17 13:56:54'),
(52, 6, 'login', 'User logged in successfully', 'user', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-17 13:57:11'),
(53, 8, 'login', 'User logged in successfully', 'user', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-17 13:57:52'),
(54, 1, 'login', 'User logged in successfully', 'user', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-17 13:58:07');

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `address_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `recipient_name` varchar(100) DEFAULT NULL,
  `address_line1` varchar(255) DEFAULT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`address_id`, `user_id`, `recipient_name`, `address_line1`, `address_line2`, `city`, `state`, `postal_code`, `country`, `phone`, `created_at`) VALUES
(1, 4, 'GamerOne', '123 Game Street', 'Apt 4B', 'Los Angeles', 'CA', '90001', 'USA', '555-0004', '2026-02-01 17:04:35'),
(2, 5, 'GamerTwo', '456 Console Ave', '', 'New York', 'NY', '10001', 'USA', '555-0005', '2026-02-01 17:04:35'),
(3, 7, 'James', 'Kabina Bonbon', 'N/a', 'CAGAYAN DE ORO', 'Misamis Oriental', '9000', 'Philippines', '09655595466', '2026-02-01 17:38:28'),
(4, 8, 'Grechen', 'Kauswagan, Cagayan De Oro', 'Cagayan de Oro City', 'Cagayan De Oro City', 'Misamis Oriental', '9000', 'Philippines', '09655595466', '2026-02-17 12:47:31');

-- --------------------------------------------------------

--
-- Table structure for table `commissions`
--

CREATE TABLE `commissions` (
  `commission_id` int(11) NOT NULL,
  `order_item_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `commission_percent` decimal(5,2) NOT NULL DEFAULT 5.00,
  `commission_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `commissions`
--

INSERT INTO `commissions` (`commission_id`, `order_item_id`, `seller_id`, `commission_percent`, `commission_amount`, `created_at`) VALUES
(1, 1, 1, 5.00, 1.00, '2026-02-01 17:04:35'),
(2, 2, 1, 5.00, 0.75, '2026-02-01 17:04:35'),
(3, 3, 1, 5.00, 0.50, '2026-02-01 17:04:35'),
(4, 4, 2, 5.00, 1.50, '2026-02-01 17:04:35'),
(5, 8, 4, 5.00, 1.40, '2026-02-01 17:39:26'),
(6, 9, 1, 5.00, 2.50, '2026-02-01 19:20:11'),
(7, 10, 4, 5.00, 1.40, '2026-02-01 20:43:02'),
(8, 11, 1, 5.00, 1.25, '2026-02-01 20:43:02'),
(9, 12, 1, 5.00, 1.50, '2026-02-01 20:43:02'),
(10, 13, 1, 5.00, 3.00, '2026-02-10 12:09:32'),
(11, 14, 1, 5.00, 1.25, '2026-02-10 12:09:32'),
(12, 15, 4, 5.00, 1.40, '2026-02-10 12:16:09'),
(13, 16, 2, 5.00, 0.50, '2026-02-10 12:16:09'),
(14, 17, 1, 5.00, 5.00, '2026-02-10 13:13:02'),
(15, 18, 1, 5.00, 9.00, '2026-02-17 12:47:40'),
(16, 19, 4, 5.00, 3.50, '2026-02-17 13:51:11'),
(17, 20, 4, 5.00, 3.50, '2026-02-17 13:56:54');

-- --------------------------------------------------------

--
-- Table structure for table `couriers`
--

CREATE TABLE `couriers` (
  `courier_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `couriers`
--

INSERT INTO `couriers` (`courier_id`, `name`, `phone`, `email`) VALUES
(1, 'FedEx Games', '1-800-FEDEX', 'support@fedex.com'),
(2, 'UPS Express', '1-800-742-5877', 'support@ups.com'),
(3, 'DHL Retro', '1-800-DHL-4444', 'support@dhl.com');

-- --------------------------------------------------------

--
-- Table structure for table `discounts`
--

CREATE TABLE `discounts` (
  `discount_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `discount_percent` decimal(5,2) NOT NULL COMMENT 'Discount percentage (0-100)',
  `start_date` date NOT NULL COMMENT 'Discount start date',
  `end_date` date NOT NULL COMMENT 'Discount end date',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Discount deals for products';

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `shipping_address_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `total_amount`, `status`, `shipping_address_id`, `created_at`, `updated_at`) VALUES
(1, 4, 45.00, 'delivered', 1, '2026-02-01 17:04:35', '2026-02-01 17:04:35'),
(2, 5, 30.00, 'delivered', 2, '2026-02-01 17:04:35', '2026-02-01 19:31:16'),
(6, 7, 28.00, 'delivered', 3, '2026-02-01 17:39:26', '2026-02-01 18:21:14'),
(7, 7, 50.00, 'delivered', 3, '2026-02-01 19:20:11', '2026-02-01 19:34:37'),
(8, 7, 83.00, 'delivered', 3, '2026-02-01 20:43:02', '2026-02-01 20:43:29'),
(9, 7, 85.00, 'delivered', 3, '2026-02-10 12:09:31', '2026-02-10 12:10:43'),
(10, 5, 38.00, 'shipped', 2, '2026-02-10 12:16:09', '2026-02-10 12:16:36'),
(11, 7, 100.00, 'delivered', 3, '2026-02-10 13:13:02', '2026-02-10 13:13:56'),
(12, 8, 180.00, 'delivered', 4, '2026-02-17 12:47:40', '2026-02-17 13:07:08'),
(13, 8, 70.00, 'delivered', 4, '2026-02-17 13:51:11', '2026-02-17 13:52:14'),
(14, 8, 70.00, 'delivered', 4, '2026-02-17 13:56:54', '2026-02-17 13:57:27');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `seller_id`, `quantity`, `price`, `created_at`) VALUES
(1, 1, 1, 1, 1, 20.00, '2026-02-01 17:04:35'),
(2, 1, 2, 1, 1, 15.00, '2026-02-01 17:04:35'),
(3, 1, 3, 1, 1, 10.00, '2026-02-01 17:04:35'),
(4, 2, 4, 2, 1, 30.00, '2026-02-01 17:04:35'),
(8, 6, 7, 4, 2, 14.00, '2026-02-01 17:39:26'),
(9, 7, 3, 1, 2, 25.00, '2026-02-01 19:20:11'),
(10, 8, 7, 4, 2, 14.00, '2026-02-01 20:43:02'),
(11, 8, 10, 1, 1, 25.00, '2026-02-01 20:43:02'),
(12, 8, 15, 1, 1, 30.00, '2026-02-01 20:43:02'),
(13, 9, 1, 1, 3, 20.00, '2026-02-10 12:09:31'),
(14, 9, 3, 1, 1, 25.00, '2026-02-10 12:09:32'),
(15, 10, 7, 4, 2, 14.00, '2026-02-10 12:16:09'),
(16, 10, 5, 2, 1, 10.00, '2026-02-10 12:16:09'),
(17, 11, 10, 1, 4, 25.00, '2026-02-10 13:13:02'),
(18, 12, 14, 1, 3, 60.00, '2026-02-17 12:47:40'),
(19, 13, 7, 4, 5, 14.00, '2026-02-17 13:51:11'),
(20, 14, 7, 4, 5, 14.00, '2026-02-17 13:56:54');

-- --------------------------------------------------------

--
-- Table structure for table `order_tracking`
--

CREATE TABLE `order_tracking` (
  `tracking_id` int(11) NOT NULL,
  `order_item_id` int(11) NOT NULL,
  `courier_name` varchar(50) DEFAULT NULL,
  `tracking_number` varchar(50) DEFAULT NULL,
  `status` enum('pending','shipped','in_transit','delivered') DEFAULT 'pending',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_tracking`
--

INSERT INTO `order_tracking` (`tracking_id`, `order_item_id`, `courier_name`, `tracking_number`, `status`, `updated_at`) VALUES
(1, 1, 'FedEx Games', 'FDX123456789', 'delivered', '2026-02-01 17:04:35'),
(2, 2, 'FedEx', '123456789', 'in_transit', '2026-02-01 17:27:40'),
(3, 3, 'FedEx Games', 'FDX123456791', 'delivered', '2026-02-01 17:04:35'),
(4, 4, 'UPS Express', '1-800-742-5877', 'delivered', '2026-02-01 19:31:16'),
(8, 8, 'DHL Retro', '1-800-DHL-4444', 'delivered', '2026-02-01 18:21:14'),
(9, 9, 'UPS Express', '1-800-742-5877', 'delivered', '2026-02-01 19:34:37'),
(10, 10, NULL, NULL, 'pending', '2026-02-01 20:43:02'),
(11, 11, 'DHL Retro', '1-800-DHL-4444', 'delivered', '2026-02-01 20:43:29'),
(12, 12, NULL, NULL, 'pending', '2026-02-01 20:43:02'),
(13, 13, 'FedEx Games', '1-800-FEDEX', 'delivered', '2026-02-10 12:10:43'),
(14, 14, 'FedEx Games', '1-800-FEDEX', 'shipped', '2026-02-10 12:10:31'),
(15, 15, NULL, NULL, 'pending', '2026-02-10 12:16:09'),
(16, 16, 'FedEx Games', '1-800-FEDEX', 'shipped', '2026-02-10 12:16:36'),
(17, 17, 'UPS Express', '1-800-742-5877', 'delivered', '2026-02-10 13:13:56'),
(18, 18, 'FedEx Games', '1-800-FEDEX', 'delivered', '2026-02-17 13:07:08'),
(19, 19, 'FedEx Games', '1-800-FEDEX', 'delivered', '2026-02-17 13:52:14'),
(20, 20, 'FedEx Games', '1-800-FEDEX', 'delivered', '2026-02-17 13:57:20');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `console` varchar(50) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `condition` enum('Mint','Used','Refurbished') NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `image_url` varchar(255) DEFAULT NULL,
  `demo_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `seller_id`, `name`, `console`, `category`, `condition`, `price`, `stock`, `image_url`, `demo_url`, `created_at`, `updated_at`) VALUES
(1, 1, 'Super Mario Bros', 'NES', 'Action', 'Mint', 20.00, 12, 'uploads/products/product_1769972675_697fa3c343830.png', 'https://www.youtube.com/embed/cjdfqXIM-Ko', '2026-02-01 17:04:35', '2026-02-10 12:09:32'),
(2, 1, 'Pokemon Red', 'Gameboy', 'RPG', 'Used', 15.00, 13, 'uploads/products/product_1769973413_697fa6a5bae66.jpg', 'https://www.youtube.com/embed/kVAbkcdnjVQ', '2026-02-01 17:04:35', '2026-02-01 20:23:35'),
(3, 1, 'The Legend of Zelda', 'NES', 'Adventure', 'Mint', 25.00, 4, 'uploads/products/product_1769972809_697fa449b51a0.jpg', 'https://www.youtube.com/embed/vDFZIUdo764', '2026-02-01 17:04:35', '2026-02-10 12:09:32'),
(4, 2, 'Final Fantasy VII', 'PlayStation', 'RPG', 'Mint', 30.00, 4, 'uploads/products/product_1769973057_697fa5410e230.jpg', 'https://www.youtube.com/embed/utVE4aUKYuY', '2026-02-01 17:04:35', '2026-02-01 19:10:57'),
(5, 2, 'Sonic the Hedgehog', 'Sega', 'Action', 'Used', 10.00, 15, 'uploads/products/product_1770727104_698b26c04ad57.jpg', 'https://www.youtube.com/embed/7Ba1qSpZaFY', '2026-02-01 17:04:35', '2026-02-10 12:38:24'),
(6, 2, 'Street Fighter II', 'SNES', 'Fighting', 'Mint', 22.00, 3, 'uploads/products/product_1769973238_697fa5f62ce21.jpg', 'https://www.youtube.com/embed/k_1D5By-R98', '2026-02-01 17:04:35', '2026-02-01 19:13:58'),
(7, 4, 'GTA,S', 'PlayStation', 'Action', 'Refurbished', 14.00, 55, 'uploads/products/product_1769972245_697fa21563b63.jpg', 'https://www.youtube.com/embed/hvoD7ehZPcM', '2026-02-01 17:36:45', '2026-02-17 13:57:43'),
(8, 1, 'Tetris', 'NES', 'Puzzle', 'Mint', 15.00, 109, 'uploads/products/product_1769979392_697fbe00f3871.jpg', 'https://www.youtube.com/embed/Mr8fVT_Ds4Q', '2026-02-01 20:19:40', '2026-02-01 20:56:33'),
(10, 1, 'Tekken 3', 'NES', 'Action', 'Used', 25.00, 0, 'uploads/products/product_1769977866_697fb80aceba4.jpg', 'https://www.youtube.com/embed/IsvtUxEFQaU', '2026-02-01 20:31:06', '2026-02-10 13:13:02'),
(14, 1, 'Castlevania', 'NES', 'Action', 'Mint', 60.00, 7, 'uploads/products/product_1769978537_697fbaa9758a3.jpg', 'https://www.youtube.com/embed/RGAy6jrRUaY', '2026-02-01 20:40:45', '2026-02-17 12:47:40'),
(15, 1, 'Castlevania', 'NES', 'Action', 'Used', 30.00, 9, 'uploads/products/product_1769978545_697fbab1101ec.jpg', 'https://www.youtube.com/embed/RGAy6jrRUaY', '2026-02-01 20:40:45', '2026-02-01 20:43:02'),
(16, 1, 'Castlevania', 'NES', 'Action', 'Refurbished', 45.00, 5, 'uploads/products/product_1769978551_697fbab78cc80.jpg', 'https://www.youtube.com/embed/RGAy6jrRUaY', '2026-02-01 20:40:45', '2026-02-01 20:42:31');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `product_id`, `user_id`, `seller_id`, `rating`, `comment`, `created_at`) VALUES
(1, 1, 4, 1, 5, 'Excellent condition! Exactly as described. Fast shipping!', '2026-02-01 17:04:35'),
(2, 4, 5, 2, 4, 'Good product, arrived a bit late but in perfect condition.', '2026-02-01 17:04:35'),
(3, 7, 7, 4, 5, 'CHUYA KAAYO', '2026-02-01 18:28:20'),
(4, 14, 8, 1, 5, 'TSADA', '2026-02-17 13:24:13');

-- --------------------------------------------------------

--
-- Table structure for table `sellers`
--

CREATE TABLE `sellers` (
  `seller_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `store_name` varchar(100) NOT NULL,
  `contact_info` varchar(100) DEFAULT NULL,
  `payment_info` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sellers`
--

INSERT INTO `sellers` (`seller_id`, `user_id`, `store_name`, `contact_info`, `payment_info`, `created_at`) VALUES
(1, 2, 'RetroKing Store', 'contact@retroking.com', 'Stripe Account: sk_test_retroking', '2026-02-01 17:04:35'),
(2, 3, 'PixelGames Store', 'contact@pixelgames.com', 'Stripe Account: sk_test_pixelgames', '2026-02-01 17:04:35'),
(4, 6, 'WayBuot', '09655595466', 'Gcash', '2026-02-01 17:25:16');

-- --------------------------------------------------------

--
-- Table structure for table `seller_applications`
--

CREATE TABLE `seller_applications` (
  `application_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `store_name` varchar(100) NOT NULL,
  `contact_info` varchar(100) NOT NULL,
  `payment_info` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `seller_applications`
--

INSERT INTO `seller_applications` (`application_id`, `user_id`, `store_name`, `contact_info`, `payment_info`, `status`, `applied_at`) VALUES
(1, 6, 'WayBuot', '09655595466', 'Gcash', 'approved', '2026-02-01 17:24:11'),
(2, 8, 'WayBuot', '0888', 'PAYPAL', 'rejected', '2026-02-17 13:15:11');

-- --------------------------------------------------------

--
-- Table structure for table `seller_settings`
--

CREATE TABLE `seller_settings` (
  `setting_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `notify_orders` tinyint(4) DEFAULT 1,
  `notify_reviews` tinyint(4) DEFAULT 1,
  `notify_messages` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '["customer"]' CHECK (json_valid(`roles`)),
  `status` enum('active','inactive','banned') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `phone`, `roles`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Admin Retro', 'admin@retrohub.com', '$2y$10$yIEcAbmNa3rtgJctM2FqKug6.ZAyWY3Wsy35opoj8nAQCvES.W1cG', '555-0001', '[\"admin\"]', 'active', '2026-02-01 17:04:35', '2026-02-01 17:04:35'),
(2, 'RetroKing', 'seller1@retrohub.com', '$2y$10$N5d7qA/S3l1rVAxdslQ0O.f8ttHXrLLmu31/9gZ.ORfM8Z1Su1csK', '555-0002', '[\"seller\"]', 'active', '2026-02-01 17:04:35', '2026-02-01 17:04:35'),
(3, 'PixelGames', 'seller2@retrohub.com', '$2y$10$N5d7qA/S3l1rVAxdslQ0O.f8ttHXrLLmu31/9gZ.ORfM8Z1Su1csK', '555-0003', '[\"seller\"]', 'active', '2026-02-01 17:04:35', '2026-02-01 17:04:35'),
(4, 'GamerOne', 'gamer1@retrohub.com', '$2y$10$dQWe6Rgwp9j1fvw0c6y5AOHde2Dgwbx3QWp0cNwhp9g/1zLFNJEWe', '555-0004', '[\"customer\"]', 'active', '2026-02-01 17:04:35', '2026-02-01 17:04:35'),
(5, 'GamerTwo', 'gamer2@retrohub.com', '$2y$10$dQWe6Rgwp9j1fvw0c6y5AOHde2Dgwbx3QWp0cNwhp9g/1zLFNJEWe', '555-0005', '[\"customer\"]', 'active', '2026-02-01 17:04:35', '2026-02-01 17:04:35'),
(6, 'james', 'gojames0867@gmail.com', '$2y$10$F4Gz59TBDkjxPWj6LA0bGuyZrdS.2IJjyZQFqkuabStPe75GZsrvW', '09655595466', '[\"customer\",\"seller\"]', 'active', '2026-02-01 17:23:21', '2026-02-01 17:25:16'),
(7, 'kim', 'kim@gmail.com', '$2y$10$NsAMA3eLmf2WeA1.IUBBMu8zLUxi9Zlrkh8fag/MIRG1v1WgGi2oW', '123123', '[\"customer\"]', 'active', '2026-02-01 17:37:16', '2026-02-01 17:37:16'),
(8, 'Grechen Joy Go', 'grechen@gmail.com', '$2y$10$3AwIU/rT4b4Oe.PRgfYfn.OXmN3y17Mw/UjfxGvKBSALwIBz84XWu', '09655595466', '[\"customer\"]', 'active', '2026-02-17 12:44:21', '2026-02-17 12:44:21');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `wishlist_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`wishlist_id`, `customer_id`, `product_id`, `date_added`) VALUES
(15, 7, 14, '2026-02-10 12:01:06');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action_type` (`action_type`),
  ADD KEY `idx_entity_type_id` (`entity_type`,`entity_id`),
  ADD KEY `idx_logged_at` (`logged_at`),
  ADD KEY `idx_search` (`action_type`,`entity_type`,`logged_at`);

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `commissions`
--
ALTER TABLE `commissions`
  ADD PRIMARY KEY (`commission_id`),
  ADD KEY `order_item_id` (`order_item_id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Indexes for table `couriers`
--
ALTER TABLE `couriers`
  ADD PRIMARY KEY (`courier_id`);

--
-- Indexes for table `discounts`
--
ALTER TABLE `discounts`
  ADD PRIMARY KEY (`discount_id`),
  ADD UNIQUE KEY `uk_product_dates` (`product_id`,`start_date`,`end_date`),
  ADD KEY `idx_product_dates` (`product_id`,`start_date`,`end_date`),
  ADD KEY `idx_active_discounts` (`start_date`,`end_date`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `shipping_address_id` (`shipping_address_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Indexes for table `order_tracking`
--
ALTER TABLE `order_tracking`
  ADD PRIMARY KEY (`tracking_id`),
  ADD KEY `order_item_id` (`order_item_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Indexes for table `sellers`
--
ALTER TABLE `sellers`
  ADD PRIMARY KEY (`seller_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `seller_applications`
--
ALTER TABLE `seller_applications`
  ADD PRIMARY KEY (`application_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `seller_settings`
--
ALTER TABLE `seller_settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `seller_id` (`seller_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`wishlist_id`),
  ADD UNIQUE KEY `unique_customer_product` (`customer_id`,`product_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_customer_id` (`customer_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `commissions`
--
ALTER TABLE `commissions`
  MODIFY `commission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `couriers`
--
ALTER TABLE `couriers`
  MODIFY `courier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `discounts`
--
ALTER TABLE `discounts`
  MODIFY `discount_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `order_tracking`
--
ALTER TABLE `order_tracking`
  MODIFY `tracking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sellers`
--
ALTER TABLE `sellers`
  MODIFY `seller_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `seller_applications`
--
ALTER TABLE `seller_applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `seller_settings`
--
ALTER TABLE `seller_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `wishlist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `commissions`
--
ALTER TABLE `commissions`
  ADD CONSTRAINT `commissions_ibfk_1` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`order_item_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `commissions_ibfk_2` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`seller_id`) ON DELETE CASCADE;

--
-- Constraints for table `discounts`
--
ALTER TABLE `discounts`
  ADD CONSTRAINT `fk_discount_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`shipping_address_id`) REFERENCES `addresses` (`address_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`seller_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_tracking`
--
ALTER TABLE `order_tracking`
  ADD CONSTRAINT `order_tracking_ibfk_1` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`order_item_id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`seller_id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`seller_id`) ON DELETE CASCADE;

--
-- Constraints for table `sellers`
--
ALTER TABLE `sellers`
  ADD CONSTRAINT `sellers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `seller_applications`
--
ALTER TABLE `seller_applications`
  ADD CONSTRAINT `seller_applications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `seller_settings`
--
ALTER TABLE `seller_settings`
  ADD CONSTRAINT `seller_settings_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`seller_id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
