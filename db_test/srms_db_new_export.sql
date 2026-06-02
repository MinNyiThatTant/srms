-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 18, 2026 at 02:02 PM
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
-- Database: `srms_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `main_category` varchar(100) NOT NULL,
  `sub_category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `main_category`, `sub_category_name`) VALUES
(1, 'Food', 'Thai Food'),
(2, 'Food', 'Traditional Food'),
(3, 'Food', 'Lunch'),
(4, 'Drink', 'cool'),
(5, 'Drink', 'hot drink'),
(6, 'Snack', 'potato fired'),
(9, 'ထိုင်းအစားအစာ', 'ယိုးဒယားထမင်း'),
(11, 'Dessert', 'ice-cream'),
(12, 'Drink', 'hot drink');

-- --------------------------------------------------------

--
-- Table structure for table `main_categories`
--

CREATE TABLE `main_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `main_categories`
--

INSERT INTO `main_categories` (`id`, `name`) VALUES
(3, 'Dessert'),
(2, 'Drink'),
(1, 'Food'),
(4, 'Snack'),
(6, 'တရုတ်အစားအစာ'),
(5, 'ထိုင်းအစားအစာ');

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `price` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT 'default.jpg',
  `category` varchar(100) DEFAULT NULL,
  `main_category` varchar(100) NOT NULL,
  `sub_category_id` int(11) DEFAULT NULL,
  `main_category_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`id`, `name`, `price`, `image`, `category`, `main_category`, `sub_category_id`, `main_category_id`) VALUES
(48, 'ထမင်းဖြူ ', 1000, 'default.jpg', 'Traditional Food', 'Food', NULL, NULL),
(49, 'Bk food', 1500, 'default.jpg', 'ယိုးဒယားထမင်း', 'ထိုင်းအစားအစာ', NULL, NULL),
(50, 'berry ice-cream', 3000, 'default.jpg', 'ice-cream', 'Dessert', NULL, NULL),
(51, 'strawberry ice-cream', 3000, 'default.jpg', 'ice-cream', 'Dessert', NULL, NULL),
(52, 'မြန်မာကြက်', 1500, 'default.jpg', NULL, '', 1, 3),
(53, 'အမည်း', 1500, '1779090965_qr_web.png', NULL, '', 2, 2),
(54, 'မြန်မာထမင်း', 1500, 'default.jpg', NULL, '', 3, 1),
(55, 'လက်လုပ်ခေါက်ဆွဲ', 2000, 'default.jpg', NULL, '', 4, 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `table_no` varchar(50) DEFAULT NULL,
  `total_price` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `table_no`, `total_price`, `status`, `created_at`) VALUES
(1, '3', 4000, 'ready', '2026-04-16 21:39:09'),
(2, '5', 4000, 'ready', '2026-04-16 21:58:47'),
(3, '5', 3000, 'ready', '2026-04-16 21:59:11'),
(4, '5', 3000, 'ready', '2026-04-16 21:59:35'),
(5, '5', 2, 'ready', '2026-04-16 22:02:04'),
(6, '5', 2, 'ready', '2026-04-16 22:02:42'),
(7, '5', 2, 'ready', '2026-04-16 22:02:52'),
(8, '5', 4, 'ready', '2026-04-16 22:03:14'),
(9, '5', 3000, 'ready', '2026-04-16 22:03:20'),
(10, '1', 4000, 'ready', '2026-04-16 22:13:37'),
(11, '1', 4000, 'ready', '2026-04-16 22:18:21'),
(12, '3', 2006, 'ready', '2026-04-16 22:19:39'),
(13, '3', 4000, 'ready', '2026-04-16 22:19:44'),
(14, '3', 3, 'ready', '2026-04-16 22:20:59'),
(15, '3', 2501, 'ready', '2026-04-16 22:21:10'),
(16, '3', 5002, 'ready', '2026-04-16 22:24:21'),
(17, '3', 25010, 'ready', '2026-04-16 22:24:38'),
(18, '3', 4000, 'ready', '2026-04-16 22:49:23'),
(19, '3', 4500, 'ready', '2026-04-16 22:49:50'),
(20, '3', 5000, 'ready', '2026-04-16 22:51:10'),
(21, '3', 3500, 'ready', '2026-04-16 22:56:50'),
(22, '3', 6000, 'ready', '2026-04-16 22:56:59'),
(23, '3', 3000, 'ready', '2026-04-16 23:03:19'),
(24, '3', 3000, 'ready', '2026-04-16 23:03:53'),
(26, '3', 1500, 'ready', '2026-04-16 23:07:24'),
(27, '3', 5000, 'ready', '2026-04-16 23:19:05'),
(28, '3', 21000, 'ready', '2026-04-17 00:35:45'),
(29, '3', 3000, 'ready', '2026-04-17 00:36:19'),
(30, '3', 8500, 'ready', '2026-04-17 00:46:08'),
(31, '5', 3500, 'ready', '2026-04-17 00:48:22'),
(32, '3', 10500, 'ready', '2026-04-17 01:56:53'),
(37, '3', 8000, 'ready', '2026-04-17 04:21:37'),
(38, '3', 3000, 'ready', '2026-04-17 04:30:21'),
(39, '3', 4500, 'ready', '2026-04-17 04:35:46'),
(41, '5', 4500, 'ready', '2026-04-17 04:51:09'),
(42, '3', 5000, 'ready', '2026-04-17 04:53:30'),
(43, '3', 28000, 'ready', '2026-04-17 04:54:05'),
(44, '3', 5000, 'ready', '2026-04-17 06:11:13'),
(45, '3', 3000, 'ready', '2026-04-17 06:12:17'),
(46, '3', 4500, 'ready', '2026-04-17 06:13:22'),
(47, '5', 4000, 'ready', '2026-04-17 06:23:46'),
(48, '3', 3000, 'ready', '2026-04-17 07:25:24'),
(50, '3', 1500, 'ready', '2026-04-17 09:37:33'),
(52, '3', 9000, 'ready', '2026-04-17 10:03:00'),
(55, '3', 18000, 'ready', '2026-04-17 10:34:37'),
(56, '3', 9000, 'ready', '2026-04-17 10:36:25'),
(57, '3', 4500, 'ready', '2026-04-17 10:36:33'),
(58, '3', 3000, 'ready', '2026-04-17 10:36:53'),
(60, '3', 1000, 'ready', '2026-05-15 18:17:41'),
(61, '3', 3000, 'ready', '2026-05-17 08:18:27'),
(62, '3', 1000, 'ready', '2026-05-17 08:18:49'),
(63, '5', 3000, 'ready', '2026-05-18 04:39:53'),
(64, '1', 6000, 'ready', '2026-05-18 05:55:05'),
(65, '1', 18000, 'ready', '2026-05-18 06:11:26'),
(66, '1', 4500, 'ready', '2026-05-18 06:18:20'),
(67, '3', 15000, 'ready', '2026-05-18 11:31:44'),
(68, '5', 6000, 'ready', '2026-05-18 11:55:19'),
(69, '3', 4500, 'pending', '2026-05-18 11:59:42'),
(70, '3', 1500, 'pending', '2026-05-18 11:59:47'),
(71, '3', 8000, 'pending', '2026-05-18 11:59:50');

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `price` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_details`
--

INSERT INTO `order_details` (`id`, `order_id`, `item_name`, `price`) VALUES
(1, 1, 'dkfj', 1000),
(2, 1, 'mont hin gar', 1500),
(3, 1, 'mont hin gar', 1500),
(4, 2, 'coca', 1500),
(5, 2, 'mont hin gar', 1500),
(6, 2, 'coffee', 1000),
(7, 3, 'coffee', 1000),
(8, 3, 'coffee', 1000),
(9, 3, 'coffee', 1000),
(10, 4, 'coffee', 1000),
(11, 4, 'coffee', 1000),
(12, 4, 'coffee', 1000),
(13, 5, 'လက်ဖက်ရည်', 2),
(14, 6, 'လက်ဖက်ရည်', 2),
(15, 7, 'လက်ဖက်ရည်', 2),
(16, 8, 'လက်ဖက်ရည်', 2),
(17, 8, 'လက်ဖက်ရည်', 2),
(18, 9, 'coca', 1500),
(19, 9, 'coca', 1500),
(20, 10, 'dkfj', 1000),
(21, 10, 'coca', 1500),
(22, 10, 'mont hin gar', 1500),
(23, 11, 'dkfj', 1000),
(24, 11, 'coca', 1500),
(25, 11, 'mont hin gar', 1500),
(26, 12, 'dkfj', 1000),
(27, 12, 'dkfj', 1000),
(28, 12, 'coffee', 1),
(29, 12, 'coffee', 1),
(30, 12, 'လက်ဖက်ရည်', 2),
(31, 12, 'လက်ဖက်ရည်', 2),
(32, 13, 'dkfj', 1000),
(33, 13, 'dkfj', 1000),
(34, 13, 'coffee', 1000),
(35, 13, 'coffee', 1000),
(36, 14, 'coffee', 1),
(37, 14, 'coffee', 1),
(38, 14, 'coffee', 1),
(39, 15, 'coca', 1500),
(40, 15, 'coffee', 1000),
(41, 15, 'coffee', 1),
(42, 16, 'dkfj', 1000),
(43, 16, 'coca', 1500),
(44, 16, 'mont hin gar', 1500),
(45, 16, 'လက်ဖက်ရည်', 2),
(46, 16, 'coffee', 1000),
(47, 17, 'coffee', 1000),
(48, 17, 'coffee', 1000),
(49, 17, 'coffee', 1000),
(50, 17, 'coffee', 1000),
(51, 17, 'coffee', 1000),
(52, 17, 'dkfj', 1000),
(53, 17, 'dkfj', 1000),
(54, 17, 'dkfj', 1000),
(55, 17, 'dkfj', 1000),
(56, 17, 'dkfj', 1000),
(57, 17, 'coca', 1500),
(58, 17, 'coca', 1500),
(59, 17, 'coca', 1500),
(60, 17, 'coca', 1500),
(61, 17, 'coca', 1500),
(62, 17, 'လက်ဖက်ရည်', 2),
(63, 17, 'လက်ဖက်ရည်', 2),
(64, 17, 'လက်ဖက်ရည်', 2),
(65, 17, 'လက်ဖက်ရည်', 2),
(66, 17, 'လက်ဖက်ရည်', 2),
(67, 17, 'mont hin gar', 1500),
(68, 17, 'mont hin gar', 1500),
(69, 17, 'mont hin gar', 1500),
(70, 17, 'mont hin gar', 1500),
(71, 17, 'mont hin gar', 1500),
(72, 18, 'ထမင်းဖြူ', 1000),
(73, 18, 'CocaCola', 1500),
(74, 18, ' မုန့်ဟင်းခါး', 1500),
(75, 19, 'ထမင်းဖြူ', 1000),
(76, 19, 'လက်ဖက်ရည်', 2000),
(77, 19, 'CocaCola', 1500),
(78, 20, 'ကော်ဖီ', 1000),
(79, 20, 'ကော်ဖီ', 1000),
(80, 20, 'ကော်ဖီ', 1000),
(81, 20, 'လက်ဖက်ရည်', 2000),
(82, 21, 'ကော်ဖီ', 1000),
(83, 21, 'ထမင်းဖြူ', 1000),
(84, 21, 'CocaCola', 1500),
(85, 22, 'လက်ဖက်ရည်', 2000),
(86, 22, 'လက်ဖက်ရည်', 2000),
(87, 22, 'လက်ဖက်ရည်', 2000),
(88, 23, 'ကော်ဖီ', 1000),
(89, 23, 'ကော်ဖီ', 1000),
(90, 23, 'ကော်ဖီ', 1000),
(91, 24, 'ကော်ဖီ', 1000),
(92, 24, 'ကော်ဖီ', 1000),
(93, 24, 'ကော်ဖီ', 1000),
(100, 26, 'CocaCola', 1500),
(101, 27, 'လက်ဖက်ရည်', 2000),
(102, 27, 'CocaCola', 1500),
(103, 27, ' မုန့်ဟင်းခါး', 1500),
(104, 28, 'ထမင်းဖြူ', 1000),
(105, 28, 'ထမင်းဖြူ', 1000),
(106, 28, 'ထမင်းဖြူ', 1000),
(107, 28, 'CocaCola', 1500),
(108, 28, 'CocaCola', 1500),
(109, 28, 'CocaCola', 1500),
(110, 28, ' မုန့်ဟင်းခါး', 1500),
(111, 28, ' မုန့်ဟင်းခါး', 1500),
(112, 28, ' မုန့်ဟင်းခါး', 1500),
(113, 28, 'ကော်ဖီ', 1000),
(114, 28, 'ကော်ဖီ', 1000),
(115, 28, 'ကော်ဖီ', 1000),
(116, 28, 'လက်ဖက်ရည်', 2000),
(117, 28, 'လက်ဖက်ရည်', 2000),
(118, 28, 'လက်ဖက်ရည်', 2000),
(119, 29, 'ကော်ဖီ', 1000),
(120, 29, 'ကော်ဖီ', 1000),
(121, 29, 'ကော်ဖီ', 1000),
(122, 30, 'CocaCola', 1500),
(123, 30, ' မုန့်ဟင်းခါး', 1500),
(124, 30, ' မုန့်ဟင်းခါး', 1500),
(125, 30, 'လက်ဖက်ရည်', 2000),
(126, 30, 'လက်ဖက်ရည်', 2000),
(127, 31, 'CocaCola', 1500),
(128, 31, 'ထမင်းဖြူ', 1000),
(129, 31, 'ထမင်းဖြူ', 1000),
(130, 32, 'ထမင်းဖြူ', 1000),
(131, 32, 'ထမင်းဖြူ', 1000),
(132, 32, 'ထမင်းဖြူ', 1000),
(133, 32, 'CocaCola', 1500),
(134, 32, 'CocaCola', 1500),
(135, 32, 'CocaCola', 1500),
(136, 32, ' မုန့်ဟင်းခါး', 1500),
(137, 32, ' မုန့်ဟင်းခါး', 1500),
(147, 37, 'ကော်ဖီ', 1000),
(148, 37, 'လက်ဖက်ရည်', 2000),
(149, 37, 'coffee latte', 5000),
(150, 38, 'ထမင်းဖြူ', 1000),
(151, 38, 'ထမင်းဖြူ', 1000),
(152, 38, 'ထမင်းဖြူ', 1000),
(153, 39, 'CocaCola', 1500),
(154, 39, 'CocaCola', 1500),
(155, 39, 'CocaCola', 1500),
(157, 41, ' မုန့်ဟင်းခါး', 1500),
(158, 41, 'လက်ဖက်ရည်', 2000),
(159, 41, 'ကော်ဖီ', 1000),
(160, 42, 'CocaCola', 1500),
(161, 42, ' မုန့်ဟင်းခါး', 1500),
(162, 42, 'လက်ဖက်ရည်', 2000),
(163, 43, 'ထမင်းဖြူ', 1000),
(164, 43, 'CocaCola', 1500),
(165, 43, ' မုန့်ဟင်းခါး', 1500),
(166, 43, 'coffee latte', 5000),
(167, 43, ' မုန့်ဟင်းခါး', 1500),
(168, 43, 'လက်ဖက်ရည်', 2000),
(169, 43, 'coffee latte', 5000),
(170, 43, 'ကော်ဖီ', 1000),
(171, 43, 'ထမင်းဖြူ', 1000),
(172, 43, 'CocaCola', 1500),
(173, 43, 'sfgsdf', 3000),
(174, 43, 'max', 1000),
(175, 43, ' မုန့်ဟင်းခါး', 1500),
(176, 43, ' မုန့်ဟင်းခါး', 1500),
(177, 44, 'ထမင်းဖြူ', 1000),
(178, 44, 'ထမင်းဖြူ', 1000),
(179, 44, 'ထမင်းဖြူ', 1000),
(180, 44, 'ထမင်းဖြူ', 1000),
(181, 44, 'ထမင်းဖြူ', 1000),
(182, 45, 'ထမင်းဖြူ', 1000),
(183, 45, 'ထမင်းဖြူ', 1000),
(184, 45, 'ထမင်းဖြူ', 1000),
(185, 46, 'CocaCola', 1500),
(186, 46, 'CocaCola', 1500),
(187, 46, 'CocaCola', 1500),
(188, 47, 'CocaCola', 1500),
(189, 47, ' မုန့်ဟင်းခါး', 1500),
(190, 47, 'ထမင်းဖြူ', 1000),
(191, 48, 'ထမင်းဖြူ', 1000),
(192, 48, 'ထမင်းဖြူ', 1000),
(193, 48, 'ထမင်းဖြူ', 1000),
(195, 50, 'Bkd', 1500),
(197, 52, 'berry ice-cream', 3000),
(198, 52, 'berry ice-cream', 3000),
(199, 52, 'berry ice-cream', 3000),
(210, 55, 'berry ice-cream', 3000),
(211, 55, 'berry ice-cream', 3000),
(212, 55, 'berry ice-cream', 3000),
(213, 55, 'strawberry ice-cream', 3000),
(214, 55, 'strawberry ice-cream', 3000),
(215, 55, 'strawberry ice-cream', 3000),
(216, 56, 'berry ice-cream', 3000),
(217, 56, 'berry ice-cream', 3000),
(218, 56, 'berry ice-cream', 3000),
(219, 57, 'Bk food', 1500),
(220, 57, 'Bk food', 1500),
(221, 57, 'Bk food', 1500),
(222, 58, 'ထမင်းဖြူ ', 1000),
(223, 58, 'ထမင်းဖြူ ', 1000),
(224, 58, 'ထမင်းဖြူ ', 1000),
(230, 60, 'ထမင်းဖြူ ', 1000),
(231, 61, 'berry ice-cream', 3000),
(232, 62, 'ထမင်းဖြူ ', 1000),
(233, 63, 'berry ice-cream', 3000),
(234, 64, 'strawberry ice-cream', 3000),
(235, 64, 'berry ice-cream', 3000),
(236, 65, 'berry ice-cream', 3000),
(237, 65, 'berry ice-cream', 3000),
(238, 65, 'berry ice-cream', 3000),
(239, 65, 'strawberry ice-cream', 3000),
(240, 65, 'strawberry ice-cream', 3000),
(241, 65, 'strawberry ice-cream', 3000),
(242, 66, 'မြန်မာကြက်', 1500),
(243, 66, 'မြန်မာကြက်', 1500),
(244, 66, 'မြန်မာကြက်', 1500),
(245, 67, 'လက်လုပ်ခေါက်ဆွဲ', 2000),
(246, 67, 'လက်လုပ်ခေါက်ဆွဲ', 2000),
(247, 67, 'လက်လုပ်ခေါက်ဆွဲ', 2000),
(248, 67, 'အမည်း', 1500),
(249, 67, 'အမည်း', 1500),
(250, 67, 'အမည်း', 1500),
(251, 67, 'မြန်မာထမင်း', 1500),
(252, 67, 'မြန်မာထမင်း', 1500),
(253, 67, 'မြန်မာထမင်း', 1500),
(254, 68, 'လက်လုပ်ခေါက်ဆွဲ', 2000),
(255, 68, 'လက်လုပ်ခေါက်ဆွဲ', 2000),
(256, 68, 'လက်လုပ်ခေါက်ဆွဲ', 2000),
(257, 69, 'မြန်မာကြက်', 1500),
(258, 69, 'မြန်မာကြက်', 1500),
(259, 69, 'မြန်မာကြက်', 1500),
(260, 70, 'အမည်း', 1500),
(261, 71, 'လက်လုပ်ခေါက်ဆွဲ', 2000),
(262, 71, 'လက်လုပ်ခေါက်ဆွဲ', 2000),
(263, 71, 'လက်လုပ်ခေါက်ဆွဲ', 2000),
(264, 71, 'လက်လုပ်ခေါက်ဆွဲ', 2000);

-- --------------------------------------------------------

--
-- Table structure for table `sub_categories`
--

CREATE TABLE `sub_categories` (
  `id` int(11) NOT NULL,
  `main_category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sub_categories`
--

INSERT INTO `sub_categories` (`id`, `main_category_id`, `name`, `created_at`) VALUES
(1, 3, 'ကြက်သား', '2026-05-18 06:17:30'),
(2, 2, 'ကိုကာကိုလာ', '2026-05-18 07:54:46'),
(3, 1, 'မြန်မာအစားအစာမျိုးစုံ', '2026-05-18 11:27:35'),
(4, 1, 'ခေါက်ဆွဲများ', '2026-05-18 11:28:25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `main_categories`
--
ALTER TABLE `main_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sub_category_id` (`sub_category_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sub_categories`
--
ALTER TABLE `sub_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_sub_category` (`main_category_id`,`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `main_categories`
--
ALTER TABLE `main_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=265;

--
-- AUTO_INCREMENT for table `sub_categories`
--
ALTER TABLE `sub_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `menu`
--
ALTER TABLE `menu`
  ADD CONSTRAINT `menu_ibfk_1` FOREIGN KEY (`sub_category_id`) REFERENCES `sub_categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sub_categories`
--
ALTER TABLE `sub_categories`
  ADD CONSTRAINT `sub_categories_ibfk_1` FOREIGN KEY (`main_category_id`) REFERENCES `main_categories` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
