-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 24, 2026 at 09:13 PM
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
-- Database: `byteclass_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `target_type` varchar(50) DEFAULT NULL,
  `target_id` int(10) UNSIGNED DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `target_type`, `target_id`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-05 21:06:18'),
(2, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-05 21:06:36'),
(3, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-05 21:06:40'),
(4, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-05 21:06:42'),
(5, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-05 21:06:49'),
(6, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-05 21:07:27'),
(7, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-05 21:07:45'),
(8, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-05 21:08:00'),
(9, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-05 21:08:01'),
(10, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-05 21:08:14'),
(11, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-05 21:08:17'),
(12, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-05 21:08:19'),
(13, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-05 21:09:18'),
(14, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-05 21:09:19'),
(15, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-05 21:09:21'),
(16, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-05 21:11:20'),
(17, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-05 21:11:35'),
(18, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-05 21:12:47'),
(19, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-05 21:12:49'),
(20, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-05 21:12:50'),
(21, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-05 21:13:26'),
(22, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-06 10:07:13'),
(23, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-06 10:07:19'),
(24, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-06 10:08:10'),
(25, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-06 10:12:40'),
(26, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-06 10:12:43'),
(27, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-06 10:12:45'),
(28, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-06 10:12:46'),
(29, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-06 10:12:48'),
(30, NULL, 'register', NULL, NULL, 'New student registered', '::1', NULL, '2026-04-06 10:14:46'),
(31, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-06 20:26:13'),
(32, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-06 20:27:44'),
(33, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-06 20:27:51'),
(34, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-06 20:27:52'),
(35, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-06 20:27:54'),
(36, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-06 20:35:20'),
(37, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-06 20:35:22'),
(38, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-06 20:35:23'),
(39, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-06 20:35:24'),
(40, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-06 20:35:26'),
(41, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-06 20:36:24'),
(42, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-06 20:37:12'),
(43, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-06 20:37:37'),
(44, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-06 20:37:40'),
(45, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 11:04:23'),
(46, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:42:21'),
(47, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:42:27'),
(48, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:42:29'),
(49, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:42:30'),
(50, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:42:32'),
(51, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:43:13'),
(52, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:45:26'),
(53, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:45:31'),
(54, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:45:32'),
(55, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:45:33'),
(56, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:45:34'),
(57, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:45:35'),
(58, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:45:36'),
(59, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:45:37'),
(60, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:45:38'),
(61, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:45:39'),
(62, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:45:40'),
(63, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:45:52'),
(64, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:45:54'),
(65, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:45:59'),
(66, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:46:01'),
(67, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:46:25'),
(68, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:48:53'),
(69, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:48:55'),
(70, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:48:59'),
(71, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:49:01'),
(72, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:49:29'),
(73, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:49:31'),
(74, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:52:34'),
(75, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:52:36'),
(76, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:53:13'),
(77, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:53:16'),
(78, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:53:17'),
(79, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:53:18'),
(80, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:53:19'),
(81, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:53:20'),
(82, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:53:20'),
(83, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:53:21'),
(84, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:54:04'),
(85, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:54:05'),
(86, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:54:24'),
(87, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:54:25'),
(88, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:54:26'),
(89, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:54:27'),
(90, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:54:29'),
(91, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:54:30'),
(92, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-07 17:56:17'),
(93, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-11 22:07:48'),
(94, 5, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-11 22:13:11'),
(95, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-12 20:57:53'),
(96, 5, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-12 21:03:56'),
(97, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-13 10:32:34'),
(98, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-13 16:42:40'),
(99, 5, 'create_lecturer', 'user', 8, 'Created lecturer: Emmanuel M Jesse', '::1', NULL, '2026-04-13 16:57:48'),
(100, 5, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-13 17:00:27'),
(101, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-13 17:01:04'),
(102, 5, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-13 17:06:54'),
(103, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-13 21:20:18'),
(104, 5, 'create_lecturer', 'user', 9, 'Created lecturer: Emmanuel  Jesse', '::1', NULL, '2026-04-13 21:21:21'),
(105, 5, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-13 21:27:57'),
(106, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-13 21:31:59'),
(107, 5, 'create_lecturer', 'user', 10, 'Created lecturer: Emmanuel  Jesse', '::1', NULL, '2026-04-13 21:36:22'),
(108, NULL, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-13 21:37:38'),
(109, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-13 21:38:28'),
(110, 5, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-13 21:40:03'),
(111, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-14 13:00:36'),
(112, 5, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-14 13:06:23'),
(113, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-14 13:06:43'),
(114, 5, 'create_lecturer', 'user', 11, 'Created lecturer: Emmanuel M Jesse', '::1', NULL, '2026-04-14 13:11:43'),
(115, 5, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-14 13:14:32'),
(116, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-14 20:13:42'),
(117, 5, 'create_lecturer', 'user', 12, 'Created lecturer: John Kamau', '::1', NULL, '2026-04-14 20:15:35'),
(118, 5, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-14 20:20:53'),
(119, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-14 20:31:31'),
(120, NULL, 'email_sent', NULL, NULL, 'Email sent to: codetechhub1@gmail.com — Subject: Welcome to ByteClass — Your Lecturer Account is Ready', '::1', NULL, '2026-04-14 20:32:19'),
(121, 5, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-14 20:33:10'),
(122, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-15 08:58:55'),
(123, 5, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-15 09:04:22'),
(124, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-15 09:04:53'),
(125, 5, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-15 09:11:40'),
(126, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-15 09:14:31'),
(127, NULL, 'email_sent', NULL, NULL, 'Email sent to: codetechhub1@gmail.com — Subject: Welcome to ByteClass — Your Lecturer Account is Ready', '::1', NULL, '2026-04-15 09:19:38'),
(128, 5, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-15 09:20:55'),
(129, 6, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-15 09:21:12'),
(130, 6, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-15 09:22:23'),
(131, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-15 15:58:05'),
(132, NULL, 'email_sent', NULL, NULL, 'Email sent to: codetechhub1@gmail.com — Subject: Welcome to ByteClass — Your Lecturer Account is Ready', '::1', NULL, '2026-04-15 15:58:33'),
(133, 5, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-15 15:58:38'),
(134, 6, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-15 15:58:46'),
(135, 6, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-15 16:07:51'),
(136, 6, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-15 16:09:29'),
(137, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-15 20:34:36'),
(138, 5, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-15 20:34:49'),
(139, 6, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-15 20:34:58'),
(140, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-15 20:52:27'),
(141, 5, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-15 20:53:03'),
(142, 6, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-15 20:53:20'),
(143, 6, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-15 20:53:53'),
(144, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-15 20:53:56'),
(145, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-15 20:55:20'),
(146, 5, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-15 20:55:56'),
(147, 5, 'login', NULL, NULL, 'User logged in', '127.0.0.1', NULL, '2026-04-16 12:14:13'),
(148, 5, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-16 12:33:26'),
(149, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-16 23:28:26'),
(150, 5, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-16 23:28:53'),
(151, NULL, 'email_sent', NULL, NULL, 'Email sent to: magachi.emmanuel@students.jkuat.ac.ke — Subject: Please verify your ByteClass email address', '::1', NULL, '2026-04-16 23:30:07'),
(152, 13, 'register', NULL, NULL, 'Student registered', '::1', NULL, '2026-04-16 23:30:07'),
(153, 13, 'email_verified', NULL, NULL, 'Email verified', '::1', NULL, '2026-04-16 23:30:43'),
(154, 13, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-16 23:30:59'),
(155, 13, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-16 23:31:42'),
(156, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-16 23:31:47'),
(157, 5, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-16 23:31:59'),
(158, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-17 06:12:28'),
(159, 5, 'create_course', 'course', 1, 'Created course: IT Support Fundamentals', '::1', NULL, '2026-04-17 06:27:34'),
(160, 5, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-17 06:28:13'),
(161, 13, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-17 06:28:27'),
(162, 13, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-17 06:40:46'),
(163, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-17 11:44:44'),
(164, 5, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-17 11:55:53'),
(165, 13, 'points_awarded', NULL, NULL, 'Awarded 50 points: Daily login bonus', '::1', NULL, '2026-04-17 11:58:51'),
(166, 13, 'daily_login_points', NULL, NULL, 'Daily login points awarded', '::1', NULL, '2026-04-17 11:58:51'),
(167, 13, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-17 11:58:51'),
(168, 13, 'points_awarded', NULL, NULL, 'Awarded 500 points: Enrolled in: Introduction to Computer Basics', '::1', NULL, '2026-04-17 11:59:21'),
(169, 13, 'enroll', 'course', 2, 'Student enrolled in: Introduction to Computer Basics', '::1', NULL, '2026-04-17 11:59:22'),
(170, 13, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-17 12:00:45'),
(171, 13, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-17 12:00:58'),
(172, 13, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-17 12:01:07'),
(173, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-17 16:23:02'),
(174, 5, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-17 17:07:41'),
(175, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-17 17:15:57'),
(176, 5, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-17 17:21:37'),
(177, 13, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-17 17:21:52'),
(178, 5, 'login', NULL, NULL, 'User logged in', '127.0.0.1', NULL, '2026-04-17 21:29:19'),
(179, 5, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-17 21:39:10'),
(180, 13, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-17 21:52:16'),
(181, 13, 'points_awarded', NULL, NULL, 'Awarded 500 points: Enrolled in: IT Support Fundamentals', '::1', NULL, '2026-04-17 21:53:30'),
(182, 13, 'payment_success', 'course', 1, 'Payment successful via mpesa', '::1', NULL, '2026-04-17 21:53:30'),
(183, 13, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-17 21:53:47'),
(184, 13, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-17 21:54:01'),
(185, 13, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-17 21:54:57'),
(186, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-17 21:55:08'),
(187, 5, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-17 21:57:50'),
(188, 13, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-17 21:58:03'),
(189, 13, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-17 21:59:28'),
(190, 13, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-17 22:00:35'),
(191, 13, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-17 22:00:43'),
(192, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-18 07:45:56'),
(193, NULL, 'email_sent', NULL, NULL, 'Email sent to: codetechhub1@gmail.com — Subject: Welcome to ByteClass — Your Lecturer Account is Ready', '::1', NULL, '2026-04-18 07:46:12'),
(194, 5, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-18 07:46:16'),
(195, 12, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-18 07:47:31'),
(196, 12, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-18 08:02:40'),
(197, 13, 'points_awarded', NULL, NULL, 'Awarded 50 points: Daily login bonus', '::1', NULL, '2026-04-18 08:02:54'),
(198, 13, 'daily_login_points', NULL, NULL, 'Daily login points awarded', '::1', NULL, '2026-04-18 08:02:54'),
(199, 13, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-18 08:02:54'),
(200, 13, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-18 08:03:22'),
(201, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-18 08:03:28'),
(202, 5, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-18 08:05:19'),
(203, 12, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-18 08:05:34'),
(204, 12, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-18 08:11:00'),
(205, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-18 13:46:12'),
(206, 5, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-18 13:54:45'),
(207, 12, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-18 13:58:29'),
(208, 12, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-18 14:04:13'),
(209, 12, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-18 14:05:25'),
(210, 12, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-18 14:14:38'),
(211, 13, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-18 14:14:51'),
(212, 13, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-18 14:19:04'),
(213, 5, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-18 14:21:03'),
(214, 5, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-18 14:21:37'),
(215, 12, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-20 11:03:04'),
(216, 12, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-20 11:09:37'),
(217, 12, 'login', NULL, NULL, 'User logged in', '::1', NULL, '2026-04-21 10:03:27'),
(218, 12, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2026-04-21 10:03:44');

-- --------------------------------------------------------

--
-- Table structure for table `admin_profiles`
--

CREATE TABLE `admin_profiles` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `signature_image` varchar(255) DEFAULT NULL,
  `title` varchar(100) DEFAULT 'Principal',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_profiles`
--

INSERT INTO `admin_profiles` (`id`, `user_id`, `signature_image`, `title`, `created_at`, `updated_at`) VALUES
(4, 5, NULL, 'System Administrator', '2026-04-15 20:39:16', '2026-04-15 20:39:16'),
(5, 6, NULL, 'Principal', '2026-04-15 20:39:16', '2026-04-15 20:39:16');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `body` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `audience` enum('students','lecturers','internal_all','external','all') NOT NULL DEFAULT 'all',
  `is_offer` tinyint(1) NOT NULL DEFAULT 0,
  `discount_pct` decimal(5,2) DEFAULT NULL,
  `course_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`course_ids`)),
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` int(10) UNSIGNED NOT NULL,
  `last_edited_by` int(10) UNSIGNED DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `body`, `image`, `audience`, `is_offer`, `discount_pct`, `course_ids`, `is_pinned`, `created_by`, `last_edited_by`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, '🔥 50% OFF ALL COURSES – Limited Time Only!', 'Upgrade your skills for half the price! 🚀\r\nFor a limited time, enjoy 50% OFF all courses across our platform.\r\n\r\nWhether you&amp;#039;re diving into programming, cybersecurity, networking, or full-stack development — now is the perfect time to learn and grow.\r\n\r\n💡 Don’t miss this opportunity to invest in your future at an unbeatable price.\r\n\r\n⏳ Hurry! Offer expires soon.\r\n📚 Start learning today!', 'http://localhost/ByteClass/uploads/announcements/ann_1776509672.png', 'external', 1, 50.00, NULL, 0, 5, 5, '2026-04-20 19:00:00', '2026-04-18 13:54:32', '2026-04-18 14:21:27');

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

CREATE TABLE `certificates` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `certificate_uid` varchar(20) NOT NULL,
  `pdf_path` varchar(255) DEFAULT NULL,
  `qr_code_path` varchar(255) DEFAULT NULL,
  `skills_content` text DEFAULT NULL,
  `issued_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `class_sessions`
--

CREATE TABLE `class_sessions` (
  `id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `lecturer_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `platform` enum('zoom','google_meet','other') NOT NULL DEFAULT 'zoom',
  `meet_link` varchar(500) NOT NULL,
  `scheduled_at` datetime NOT NULL,
  `duration_min` smallint(5) UNSIGNED NOT NULL DEFAULT 60,
  `status` enum('upcoming','live','ended','cancelled') NOT NULL DEFAULT 'upcoming',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `class_sessions`
--

INSERT INTO `class_sessions` (`id`, `course_id`, `lecturer_id`, `title`, `description`, `platform`, `meet_link`, `scheduled_at`, `duration_min`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 12, 'WEEK 1 INTRODUCTION TO NETWORKING', NULL, 'google_meet', 'https://meet.google.com/urs-ibds-jfu?authuser=0', '2026-04-18 20:00:00', 30, 'ended', '2026-04-18 07:55:15', '2026-04-20 11:03:24');

-- --------------------------------------------------------

--
-- Table structure for table `community_messages`
--

CREATE TABLE `community_messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `delete_at` datetime GENERATED ALWAYS AS (`created_at` + interval 24 hour) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `content_flags`
--

CREATE TABLE `content_flags` (
  `id` int(10) UNSIGNED NOT NULL,
  `lesson_id` int(10) UNSIGNED NOT NULL,
  `flagged_by` int(10) UNSIGNED NOT NULL,
  `note` text NOT NULL,
  `status` enum('open','resolved') NOT NULL DEFAULT 'open',
  `resolved_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(220) NOT NULL,
  `overview` text NOT NULL,
  `category` varchar(100) NOT NULL,
  `difficulty` enum('beginner','intermediate','advanced') NOT NULL DEFAULT 'beginner',
  `thumbnail` varchar(255) DEFAULT NULL,
  `price_kes` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_usd` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('draft','published','unpublished','archived') NOT NULL DEFAULT 'draft',
  `created_by` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `name`, `slug`, `overview`, `category`, `difficulty`, `thumbnail`, `price_kes`, `price_usd`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'IT Support Fundamentals', '-upport-undamentals-1776396454', 'Master the essential skills every IT support professional needs. This course covers computer hardware, operating systems (Windows &amp; Linux), networking basics, troubleshooting methodologies, help desk best practices, and customer service skills. You will learn how to diagnose and resolve common technical problems, set up and configure workstations, manage user accounts, and handle real-world IT support scenarios. Perfect for beginners pursuing CompTIA A+ or a career in tech support.', 'IT Support', 'beginner', 'http://localhost/ByteClass/uploads/course_images/course_1776396454.png', 4500.00, 35.00, 'published', 5, '2026-04-17 06:27:34', '2026-04-17 06:27:34'),
(2, 'Introduction to Computer Basics', 'intro-computer-basics-free', 'A completely free beginner course covering the fundamentals of computers, operating systems, file management, internet basics, and essential software tools. Perfect for anyone new to technology. No prior experience required. By the end of this course you will be comfortable using a computer for everyday tasks including browsing the web, managing files, using email, and installing applications.', 'IT Support', 'beginner', NULL, 0.00, 0.00, 'published', 5, '2026-04-17 11:38:03', '2026-04-17 11:38:03');

-- --------------------------------------------------------

--
-- Table structure for table `course_lecturers`
--

CREATE TABLE `course_lecturers` (
  `id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `lecturer_id` int(10) UNSIGNED NOT NULL,
  `modules_note` varchar(255) DEFAULT NULL,
  `assigned_by` int(10) UNSIGNED NOT NULL,
  `assigned_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `course_lecturers`
--

INSERT INTO `course_lecturers` (`id`, `course_id`, `lecturer_id`, `modules_note`, `assigned_by`, `assigned_at`) VALUES
(1, 1, 12, NULL, 5, '2026-04-17 06:27:34');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `payment_id` int(10) UNSIGNED DEFAULT NULL,
  `enrolled_at` datetime NOT NULL DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `student_id`, `course_id`, `payment_id`, `enrolled_at`, `completed_at`) VALUES
(1, 13, 2, NULL, '2026-04-17 11:59:21', NULL),
(2, 13, 1, NULL, '2026-04-17 21:53:30', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `hr_forms`
--

CREATE TABLE `hr_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `lecturer_id` int(10) UNSIGNED NOT NULL,
  `form_type` enum('it_support','resource_request','complaint','content_update','co_lecturer_change','other') NOT NULL,
  `subject` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `status` enum('pending','in_review','resolved','rejected') NOT NULL DEFAULT 'pending',
  `admin_note` text DEFAULT NULL,
  `handled_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `id` int(10) UNSIGNED NOT NULL,
  `lecturer_id` int(10) UNSIGNED NOT NULL,
  `type` enum('annual','sick','emergency','off_day','other') NOT NULL DEFAULT 'annual',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `decision_note` text DEFAULT NULL,
  `decided_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leave_requests`
--

INSERT INTO `leave_requests` (`id`, `lecturer_id`, `type`, `start_date`, `end_date`, `reason`, `status`, `approved_by`, `decision_note`, `decided_at`, `created_at`) VALUES
(1, 12, 'sick', '2026-04-19', '2026-04-26', 'I have not been feeling okay for the last several days, and I am requesting a sick leave on the above mentioned dates', 'approved', 5, '', '2026-04-18 08:03:56', '2026-04-18 07:58:50');

-- --------------------------------------------------------

--
-- Table structure for table `lecturer_profiles`
--

CREATE TABLE `lecturer_profiles` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `bio` text DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `signature_image` varchar(255) DEFAULT NULL,
  `contract_pdf` varchar(255) DEFAULT NULL,
  `contract_start` date DEFAULT NULL,
  `contract_end` date DEFAULT NULL,
  `contract_notified` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lecturer_profiles`
--

INSERT INTO `lecturer_profiles` (`id`, `user_id`, `bio`, `department`, `signature_image`, `contract_pdf`, `contract_start`, `contract_end`, `contract_notified`, `created_at`, `updated_at`) VALUES
(5, 12, NULL, 'Cyber Security', NULL, NULL, NULL, NULL, 0, '2026-04-14 20:15:35', '2026-04-14 20:15:35');

-- --------------------------------------------------------

--
-- Table structure for table `lecturer_reviews`
--

CREATE TABLE `lecturer_reviews` (
  `id` int(10) UNSIGNED NOT NULL,
  `lecturer_id` int(10) UNSIGNED NOT NULL,
  `reviewed_by` int(10) UNSIGNED NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL DEFAULT 3,
  `strengths` text DEFAULT NULL,
  `improvements` text DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `review_period` varchar(50) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lecturer_reviews`
--

INSERT INTO `lecturer_reviews` (`id`, `lecturer_id`, `reviewed_by`, `rating`, `strengths`, `improvements`, `comment`, `review_period`, `created_at`) VALUES
(1, 12, 5, 4, 'Teaching and Explaining', 'Time Management', 'GOOD', 'Q2 2026', '2026-04-18 08:05:16');

-- --------------------------------------------------------

--
-- Table structure for table `lessons`
--

CREATE TABLE `lessons` (
  `id` int(10) UNSIGNED NOT NULL,
  `module_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` longtext DEFAULT NULL,
  `video_url` varchar(500) DEFAULT NULL,
  `duration_min` int(11) DEFAULT 0,
  `sort_order` int(11) DEFAULT 1,
  `order_index` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `status` enum('draft','published') NOT NULL DEFAULT 'draft',
  `pdf_path` varchar(255) DEFAULT NULL,
  `created_by` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lesson_content_logs`
--

CREATE TABLE `lesson_content_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `lesson_id` int(10) UNSIGNED NOT NULL,
  `changed_by` int(10) UNSIGNED NOT NULL,
  `old_content` longtext DEFAULT NULL,
  `new_content` longtext DEFAULT NULL,
  `change_note` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lesson_media`
--

CREATE TABLE `lesson_media` (
  `id` int(10) UNSIGNED NOT NULL,
  `lesson_id` int(10) UNSIGNED NOT NULL,
  `media_type` enum('video','audio','image','pdf','document','code','other') NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `video_url` varchar(500) DEFAULT NULL,
  `video_source` enum('youtube','vimeo','upload') DEFAULT NULL,
  `duration_sec` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `order_index` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lesson_progress`
--

CREATE TABLE `lesson_progress` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `lesson_id` int(10) UNSIGNED NOT NULL,
  `read_percent` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `video_percent` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `quiz_passed` tinyint(1) NOT NULL DEFAULT 0,
  `is_complete` tinyint(1) NOT NULL DEFAULT 0,
  `completed_at` datetime DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `order_index` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`id`, `course_id`, `title`, `description`, `order_index`, `created_at`, `updated_at`) VALUES
(1, 1, 'Introduction to Computer Basics', NULL, 0, '2026-04-18 14:05:34', '2026-04-18 14:05:34');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'general',
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `link`, `is_read`, `created_at`) VALUES
(1, 13, 'Ticket Created', 'Your support ticket BC-7C047262 has been submitted.', 'support', NULL, 1, '2026-04-17 06:40:08'),
(2, 5, 'New Support Ticket', 'New ticket from Jonathan Kamaku: Class session error', 'support', NULL, 1, '2026-04-17 06:40:08'),
(3, 6, 'New Support Ticket', 'New ticket from Jonathan Kamaku: Class session error', 'support', NULL, 0, '2026-04-17 06:40:09'),
(4, 13, 'Support Ticket Reply', 'Admin replied to your ticket: Class session error', 'support', NULL, 1, '2026-04-17 11:55:33'),
(5, 13, 'Enrolled Successfully!', 'You have enrolled in: Introduction to Computer Basics', 'course', NULL, 1, '2026-04-17 11:59:21'),
(6, 13, 'Payment Successful! 🎉', 'Payment confirmed for IT Support Fundamentals. You have been enrolled!', 'payment', NULL, 1, '2026-04-17 21:53:30'),
(7, 5, 'New Payment Received', 'mpesa payment of KES 4,500.00 for IT Support Fundamentals', 'payment', NULL, 1, '2026-04-17 21:53:30'),
(8, 6, 'New Payment Received', 'mpesa payment of KES 4,500.00 for IT Support Fundamentals', 'payment', NULL, 0, '2026-04-17 21:53:30'),
(9, 13, 'Ticket Created', 'Your support ticket BC-9659B4FF has been submitted.', 'support', NULL, 1, '2026-04-17 21:54:49'),
(10, 5, 'New Support Ticket', 'New ticket from Jonathan Kamaku: Class session error', 'support', NULL, 1, '2026-04-17 21:54:49'),
(11, 6, 'New Support Ticket', 'New ticket from Jonathan Kamaku: Class session error', 'support', NULL, 0, '2026-04-17 21:54:49'),
(12, 13, 'Support Ticket Reply', 'Admin replied to your ticket: Class session error', 'support', NULL, 1, '2026-04-17 21:56:45'),
(13, 13, 'New Class Session Scheduled', 'WEEK 1 INTRODUCTION TO NETWORKING is scheduled. Check your sessions tab.', 'course', NULL, 1, '2026-04-18 07:55:15'),
(14, 5, 'New Leave Request', 'John Kamau submitted a leave request.', 'hr', NULL, 1, '2026-04-18 07:58:50'),
(15, 6, 'New Leave Request', 'John Kamau submitted a leave request.', 'hr', NULL, 0, '2026-04-18 07:58:50'),
(16, 12, 'Leave Request Approved', 'Your leave request has been approved.', 'hr', NULL, 1, '2026-04-18 08:03:56'),
(17, 12, 'New Performance Review', 'A new performance review has been posted for you.', 'review', NULL, 1, '2026-04-18 08:05:16');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `token` varchar(100) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `receipt_id` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` enum('KES','USD') NOT NULL DEFAULT 'KES',
  `gateway` enum('mpesa','stripe','paypal','flutterwave','paystack') NOT NULL,
  `gateway_ref` varchar(200) DEFAULT NULL,
  `payment_method` varchar(100) DEFAULT NULL,
  `masked_account` varchar(50) DEFAULT NULL,
  `status` enum('pending','success','failed','refunded') NOT NULL DEFAULT 'pending',
  `retry_banner_active` tinyint(1) NOT NULL DEFAULT 0,
  `retry_deadline` datetime DEFAULT NULL,
  `admin_notified` tinyint(1) NOT NULL DEFAULT 0,
  `initiated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `confirmed_at` datetime DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `student_id`, `course_id`, `receipt_id`, `amount`, `currency`, `gateway`, `gateway_ref`, `payment_method`, `masked_account`, `status`, `retry_banner_active`, `retry_deadline`, `admin_notified`, `initiated_at`, `confirmed_at`, `updated_at`) VALUES
(1, 13, 1, 'MPESA-DD401D62A7', 4500.00, 'KES', 'mpesa', NULL, NULL, NULL, 'success', 0, NULL, 0, '2026-04-17 21:53:15', '2026-04-17 21:53:30', '2026-04-17 21:53:30');

-- --------------------------------------------------------

--
-- Table structure for table `payslips`
--

CREATE TABLE `payslips` (
  `id` int(10) UNSIGNED NOT NULL,
  `lecturer_id` int(10) UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency` enum('KES','USD') NOT NULL DEFAULT 'KES',
  `period_from` date NOT NULL,
  `period_to` date NOT NULL,
  `payment_method` varchar(100) DEFAULT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `pdf_path` varchar(255) DEFAULT NULL,
  `paid_by` int(10) UNSIGNED NOT NULL,
  `paid_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `public_chat_sessions`
--

CREATE TABLE `public_chat_sessions` (
  `id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `message` text NOT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `sender_name` varchar(100) DEFAULT 'Visitor',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` int(10) UNSIGNED NOT NULL,
  `quiz_id` int(10) UNSIGNED NOT NULL,
  `question_text` text NOT NULL,
  `question_type` enum('mcq','true_false','short_answer') NOT NULL DEFAULT 'mcq',
  `order_index` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `points` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question_options`
--

CREATE TABLE `question_options` (
  `id` int(10) UNSIGNED NOT NULL,
  `question_id` int(10) UNSIGNED NOT NULL,
  `option_text` text NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0,
  `order_index` smallint(5) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `id` int(10) UNSIGNED NOT NULL,
  `lesson_id` int(10) UNSIGNED DEFAULT NULL,
  `module_id` int(10) UNSIGNED DEFAULT NULL,
  `quiz_type` enum('lesson','module','survey') NOT NULL DEFAULT 'lesson',
  `title` varchar(200) NOT NULL,
  `passmark` tinyint(3) UNSIGNED NOT NULL DEFAULT 75,
  `randomise` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_attempts`
--

CREATE TABLE `quiz_attempts` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `quiz_id` int(10) UNSIGNED NOT NULL,
  `score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `passed` tinyint(1) NOT NULL DEFAULT 0,
  `attempt_number` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `answers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`answers`)),
  `attempted_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_profiles`
--

CREATE TABLE `student_profiles` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `streak_count` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `last_activity` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_profiles`
--

INSERT INTO `student_profiles` (`id`, `user_id`, `streak_count`, `last_activity`, `created_at`, `updated_at`) VALUES
(2, 13, 0, NULL, '2026-04-16 23:30:03', '2026-04-16 23:30:03');

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

CREATE TABLE `support_tickets` (
  `id` int(10) UNSIGNED NOT NULL,
  `ticket_uid` varchar(20) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `subject` varchar(200) NOT NULL,
  `category` enum('technical','payment','course_content','account','lecturer_support','other') NOT NULL DEFAULT 'other',
  `description` text NOT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `status` enum('open','in_progress','resolved','closed') NOT NULL DEFAULT 'open',
  `priority` enum('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `assigned_to` int(10) UNSIGNED DEFAULT NULL,
  `satisfaction` tinyint(3) UNSIGNED DEFAULT NULL,
  `satisfaction_note` text DEFAULT NULL,
  `auto_close_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `support_tickets`
--

INSERT INTO `support_tickets` (`id`, `ticket_uid`, `user_id`, `subject`, `category`, `description`, `attachment`, `status`, `priority`, `assigned_to`, `satisfaction`, `satisfaction_note`, `auto_close_at`, `created_at`, `updated_at`) VALUES
(1, 'BC-7C047262', 13, 'Class session error', 'course_content', 'Needed to know if i will get a trial module', NULL, 'closed', 'low', NULL, NULL, NULL, NULL, '2026-04-17 06:40:08', '2026-04-17 11:55:33'),
(2, 'BC-9659B4FF', 13, 'Class session error', 'course_content', 'Hello I need help with course content , My courses are empty', NULL, 'resolved', 'medium', NULL, NULL, NULL, '2026-04-24 21:56:45', '2026-04-17 21:54:49', '2026-04-17 21:56:45');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_by` int(10) UNSIGNED DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `updated_by`, `updated_at`) VALUES
(1, 'platform_name', '[\"Programming\",\"Cybersecurity\",\"Networking\",\"Cloud Computing\",\"Data Science\",\"Web Development\",\"Mobile Development\",\"DevOps\",\"Database Administration\",\"IT Support\",\"Linux\",\"Windows Server\",\"Ethical Hacking\",\"Digital Forensics\",\"Artificial Intelligence\"]', NULL, '2026-04-03 22:42:53'),
(2, 'platform_tagline', 'Learn · Build · Grow', NULL, '2026-04-03 22:42:53'),
(3, 'platform_email', 'hello@byteclass.io', NULL, '2026-04-03 22:42:53'),
(4, 'platform_logo', NULL, NULL, '2026-04-03 22:42:53'),
(5, 'platform_favicon', NULL, NULL, '2026-04-03 22:42:53'),
(6, 'primary_color', '#4F46E5', NULL, '2026-04-03 22:42:53'),
(7, 'secondary_color', '#06B6D4', NULL, '2026-04-03 22:42:53'),
(8, 'accent_color', '#F59E0B', NULL, '2026-04-03 22:42:53'),
(9, 'currency_default', 'KES', NULL, '2026-04-03 22:42:53'),
(10, 'currencies_enabled', '[\"KES\",\"USD\"]', NULL, '2026-04-03 22:42:53'),
(11, 'auto_logout_minutes', '5', NULL, '2026-04-03 22:42:53'),
(12, 'max_login_attempts', '5', NULL, '2026-04-03 22:42:53'),
(13, 'max_devices', '2', NULL, '2026-04-03 22:42:53'),
(14, 'password_reset_expiry_hrs', '24', NULL, '2026-04-03 22:42:53'),
(15, 'two_fa_enabled', '1', NULL, '2026-04-03 22:42:53'),
(16, 'two_fa_expiry_minutes', '10', NULL, '2026-04-03 22:42:53'),
(17, 'two_fa_max_attempts', '3', NULL, '2026-04-03 22:42:53'),
(18, 'quiz_lesson_passmark', '75', NULL, '2026-04-03 22:42:53'),
(19, 'quiz_module_passmark', '85', NULL, '2026-04-03 22:42:53'),
(20, 'quiz_attempts_per_day', '3', NULL, '2026-04-03 22:42:53'),
(21, 'video_max_duration_sec', '900', NULL, '2026-04-03 22:42:53'),
(22, 'community_msg_ttl_hrs', '24', NULL, '2026-04-03 22:42:53'),
(23, 'ticket_autoclose_days', '7', NULL, '2026-04-03 22:42:53'),
(24, 'cert_qr_enabled', '1', NULL, '2026-04-03 22:42:53'),
(25, 'maintenance_mode', '0', NULL, '2026-04-03 22:42:53'),
(26, 'maintenance_message', 'ByteClass is undergoing scheduled maintenance. We will be back shortly.', NULL, '2026-04-03 22:42:53'),
(27, 'smtp_host', 'smtp.gmail.com', 5, '2026-04-14 20:32:04'),
(28, 'smtp_port', '587', 5, '2026-04-14 20:32:04'),
(29, 'smtp_user', 'emmanueljesseofficial6@gmail.com', 5, '2026-04-14 20:32:04'),
(30, 'smtp_pass', 'hufa rzzm cjqj tavg', 5, '2026-04-14 20:32:04'),
(31, 'smtp_from_name', 'ByteClass', 5, '2026-04-14 20:32:04'),
(32, 'smtp_from_email', 'emmanueljesseofficial6@gmail.com', 5, '2026-04-14 20:32:04'),
(33, 'mpesa_enabled', '1', 5, '2026-04-13 16:56:21'),
(34, 'stripe_enabled', '1', 5, '2026-04-13 16:56:21'),
(35, 'paypal_enabled', '1', 5, '2026-04-13 16:56:21'),
(37, 'paystack_enabled', '1', 5, '2026-04-13 16:56:21'),
(38, 'mpesa_shortcode', '', 5, '2026-04-13 16:56:21'),
(39, 'mpesa_passkey', '', NULL, '2026-04-03 22:42:53'),
(40, 'mpesa_consumer_key', '', 5, '2026-04-13 16:56:21'),
(41, 'mpesa_consumer_secret', '', 5, '2026-04-13 16:56:21'),
(42, 'stripe_public_key', '', 5, '2026-04-13 16:56:21'),
(43, 'stripe_secret_key', '', 5, '2026-04-13 16:56:21'),
(44, 'paypal_client_id', '', 5, '2026-04-13 16:56:21'),
(45, 'paypal_client_secret', '', 5, '2026-04-13 16:56:21'),
(48, 'paystack_public_key', '', NULL, '2026-04-03 22:42:53'),
(49, 'paystack_secret_key', '', NULL, '2026-04-03 22:42:53'),
(50, 'gemini_api_key', 'AQ.Ab8RN6IZgg0WCDFgj36shNDRmPFtkaprwtwKaLNh9sVLsJiVNg', 5, '2026-04-17 21:57:07'),
(51, 'grok_api_key', 'xai-Hzxo9s6hBf05Ud2xEMIOxejOuNJRU5Mm2Jos8J9EX0UKYqTsTBJnnSuIs35Helahw3L63EXHTzpe2oiW', 5, '2026-04-17 21:57:07'),
(52, 'learnpulse_provider', 'gemini', 5, '2026-04-17 21:57:07'),
(53, 'learnpulse_enabled', '1', 5, '2026-04-17 21:57:07'),
(54, 'contract_expiry_alert_days', '30', NULL, '2026-04-03 22:42:53'),
(55, 'terms_content', 'Terms and Conditions content goes here.', NULL, '2026-04-03 22:42:53'),
(56, 'privacy_content', 'Privacy Policy content goes here.', NULL, '2026-04-03 22:42:53'),
(57, 'course_categories', '[\"Programming\",\"Cybersecurity\",\"Networking\",\"Cloud Computing\",\"Data Science\",\"Web Development\",\"Mobile Development\",\"DevOps\",\"Database Administration\",\"IT Support\",\"Linux\",\"Windows Server\",\"Ethical Hacking\",\"Digital Forensics\",\"Artificial Intelligence\"]', NULL, '2026-04-03 22:42:53'),
(58, 'social_whatsapp', '', NULL, '2026-04-14 20:11:34'),
(59, 'social_telegram', '', NULL, '2026-04-14 20:11:34'),
(60, 'social_twitter', '', NULL, '2026-04-14 20:11:34'),
(61, 'social_website', '', NULL, '2026-04-14 20:11:34'),
(62, 'social_facebook', '', NULL, '2026-04-14 20:11:34'),
(63, 'social_instagram', '', NULL, '2026-04-14 20:11:34');

-- --------------------------------------------------------

--
-- Table structure for table `ticket_replies`
--

CREATE TABLE `ticket_replies` (
  `id` int(10) UNSIGNED NOT NULL,
  `ticket_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ticket_replies`
--

INSERT INTO `ticket_replies` (`id`, `ticket_id`, `user_id`, `message`, `created_at`) VALUES
(1, 1, 5, 'Hello Kamaku your issue has be resolved please check your course dashboard', '2026-04-17 11:55:33'),
(2, 2, 5, 'Hello sir , The content will be available soon, please keep checking', '2026-04-17 21:56:45');

-- --------------------------------------------------------

--
-- Table structure for table `two_fa_codes`
--

CREATE TABLE `two_fa_codes` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `code` char(6) NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(180) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','lecturer','student') NOT NULL DEFAULT 'student',
  `status` enum('active','inactive','locked','pending') NOT NULL DEFAULT 'pending',
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `email_verify_token` varchar(100) DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `dark_mode` tinyint(1) NOT NULL DEFAULT 0,
  `points` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `login_attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `locked_at` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `last_2fa_sent` datetime DEFAULT NULL,
  `two_fa_count_week` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `two_fa_week_start` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `address`, `password_hash`, `role`, `status`, `email_verified`, `email_verify_token`, `profile_photo`, `dark_mode`, `points`, `login_attempts`, `locked_at`, `last_login`, `last_2fa_sent`, `two_fa_count_week`, `two_fa_week_start`, `created_at`, `updated_at`) VALUES
(5, 'Emmanuel Jesse', 'emmanueljesseofficial6@gmail.com', '+254796070811', 'ByteClass HQ, Nairobi, Kenya', '$2y$12$QKOZmVn0j/wPHAiqIi2ejeN6tgrGW8k17.yYKPozhJ5Isde3ItrKy', 'admin', 'active', 1, NULL, 'http://localhost/ByteClass/uploads/profile_photos/admin_5_1776509228_df9c8e92.png', 0, 0, 0, NULL, '2026-04-17 06:12:28', NULL, 0, NULL, '2026-04-07 17:19:42', '2026-04-18 13:47:08'),
(6, 'Victor Karanja', 'admin2@byteclass.io', '+254700000002', 'ByteClass HQ, Nairobi, Kenya', '$2y$12$QKOZmVn0j/wPHAiqIi2ejeN6tgrGW8k17.yYKPozhJ5Isde3ItrKy', 'admin', 'active', 1, NULL, NULL, 0, 0, 0, NULL, '2026-04-15 20:53:20', NULL, 0, NULL, '2026-04-07 17:19:42', '2026-04-15 20:53:20'),
(12, 'John Kamau', 'codetechhub1@gmail.com', '+254796070811', 'Nairobi Kenya', '$2y$12$0hCic5BOv1F13fuOrqZTPePfU239VIb/aup5gjymjLjnjxQtPgZZy', 'lecturer', 'active', 1, NULL, NULL, 0, 0, 0, NULL, NULL, NULL, 0, NULL, '2026-04-14 20:15:35', '2026-04-18 08:02:16'),
(13, 'Jonathan Kamaku', 'magachi.emmanuel@students.jkuat.ac.ke', '+254796070811', 'Nairobi Kenya', '$2y$12$luNVj3cMGv/QM8Mb..D./eJj.mMd9cfYg0QfSwVMqF7BZi4aefROa', 'student', 'active', 1, NULL, 'http://localhost/ByteClass/uploads/profile_photos/student_13_1776397240_43fd2fbb.png', 0, 2100, 0, NULL, '2026-04-17 06:28:27', NULL, 0, NULL, '2026-04-16 23:30:03', '2026-04-18 08:02:54');

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `token` varchar(500) NOT NULL,
  `device_type` enum('laptop','phone','other') NOT NULL DEFAULT 'other',
  `device_name` varchar(100) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `last_active` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`id`, `user_id`, `token`, `device_type`, `device_name`, `ip_address`, `user_agent`, `last_active`, `created_at`) VALUES
(116, 5, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjo1LCJyb2xlIjoiYWRtaW4iLCJlbWFpbCI6ImFkbWluMUBieXRlY2xhc3MuaW8iLCJpYXQiOjE3NzYyNzU2MzYsImV4cCI6MTc3NjI3OTIzNn0.tFm1WD59Q-SGdJSHBTRerUD_oQAFkBuPqY7g7jsiiY4', 'laptop', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-15 20:53:56', '2026-04-15 20:53:56'),
(129, 13, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjoxMywicm9sZSI6InN0dWRlbnQiLCJlbWFpbCI6Im1hZ2FjaGkuZW1tYW51ZWxAc3R1ZGVudHMuamt1YXQuYWMua2UiLCJpYXQiOjE3NzY0MzU3MTIsImV4cCI6MTc3NjQzOTMxMn0.3iHSWSgGAut4tqGEAGdBqioBnhCMAi0Af2FPNBIfdTw', 'laptop', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-17 17:21:52', '2026-04-17 17:21:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_al_user` (`user_id`),
  ADD KEY `idx_al_action` (`action`),
  ADD KEY `idx_al_created` (`created_at`);

--
-- Indexes for table `admin_profiles`
--
ALTER TABLE `admin_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_admin_user` (`user_id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ann_audience` (`audience`),
  ADD KEY `idx_ann_expires` (`expires_at`),
  ADD KEY `fk_ann_creator` (`created_by`),
  ADD KEY `fk_ann_editor` (`last_edited_by`);

--
-- Indexes for table `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_cert_uid` (`certificate_uid`),
  ADD UNIQUE KEY `uq_cert_student_course` (`student_id`,`course_id`),
  ADD KEY `idx_cert_course` (`course_id`);

--
-- Indexes for table `class_sessions`
--
ALTER TABLE `class_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cs_course` (`course_id`),
  ADD KEY `idx_cs_lecturer` (`lecturer_id`),
  ADD KEY `idx_cs_scheduled` (`scheduled_at`);

--
-- Indexes for table `community_messages`
--
ALTER TABLE `community_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cm_user` (`user_id`),
  ADD KEY `idx_cm_created` (`created_at`);

--
-- Indexes for table `content_flags`
--
ALTER TABLE `content_flags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cf_lesson` (`lesson_id`),
  ADD KEY `fk_cf_admin` (`flagged_by`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_course_slug` (`slug`),
  ADD KEY `idx_course_status` (`status`),
  ADD KEY `idx_course_category` (`category`),
  ADD KEY `idx_course_created_by` (`created_by`);

--
-- Indexes for table `course_lecturers`
--
ALTER TABLE `course_lecturers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_course_lecturer` (`course_id`,`lecturer_id`),
  ADD KEY `idx_cl_lecturer` (`lecturer_id`),
  ADD KEY `fk_cl_admin` (`assigned_by`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_enrollment` (`student_id`,`course_id`),
  ADD KEY `idx_enroll_course` (`course_id`);

--
-- Indexes for table `hr_forms`
--
ALTER TABLE `hr_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hf_lecturer` (`lecturer_id`),
  ADD KEY `fk_hf_admin` (`handled_by`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lr_lecturer` (`lecturer_id`),
  ADD KEY `idx_lr_status` (`status`),
  ADD KEY `fk_lr_admin` (`approved_by`);

--
-- Indexes for table `lecturer_profiles`
--
ALTER TABLE `lecturer_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_lecturer_user` (`user_id`);

--
-- Indexes for table `lecturer_reviews`
--
ALTER TABLE `lecturer_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lrev_lecturer` (`lecturer_id`),
  ADD KEY `fk_lrev_admin` (`reviewed_by`);

--
-- Indexes for table `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lessons_module` (`module_id`),
  ADD KEY `idx_lessons_status` (`status`),
  ADD KEY `fk_lessons_creator` (`created_by`);

--
-- Indexes for table `lesson_content_logs`
--
ALTER TABLE `lesson_content_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lcl_lesson` (`lesson_id`),
  ADD KEY `fk_lcl_user` (`changed_by`);

--
-- Indexes for table `lesson_media`
--
ALTER TABLE `lesson_media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_media_lesson` (`lesson_id`);

--
-- Indexes for table `lesson_progress`
--
ALTER TABLE `lesson_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_lesson_progress` (`student_id`,`lesson_id`),
  ADD KEY `idx_lp_lesson` (`lesson_id`);

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_modules_course` (`course_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notif_user` (`user_id`),
  ADD KEY `idx_notif_read` (`is_read`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_pr_token` (`token`),
  ADD KEY `idx_pr_user` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_receipt` (`receipt_id`),
  ADD KEY `idx_pay_student` (`student_id`),
  ADD KEY `idx_pay_course` (`course_id`),
  ADD KEY `idx_pay_status` (`status`),
  ADD KEY `idx_pay_gateway` (`gateway`);

--
-- Indexes for table `payslips`
--
ALTER TABLE `payslips`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ps_lecturer` (`lecturer_id`),
  ADD KEY `fk_ps_admin` (`paid_by`);

--
-- Indexes for table `public_chat_sessions`
--
ALTER TABLE `public_chat_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `token` (`token`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_questions_quiz` (`quiz_id`);

--
-- Indexes for table `question_options`
--
ALTER TABLE `question_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_qo_question` (`question_id`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_quiz_lesson` (`lesson_id`),
  ADD KEY `idx_quiz_module` (`module_id`),
  ADD KEY `fk_quiz_creator` (`created_by`);

--
-- Indexes for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_qa_student` (`student_id`),
  ADD KEY `idx_qa_quiz` (`quiz_id`),
  ADD KEY `idx_qa_date` (`attempted_at`);

--
-- Indexes for table `student_profiles`
--
ALTER TABLE `student_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_student_user` (`user_id`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_ticket_uid` (`ticket_uid`),
  ADD KEY `idx_ticket_user` (`user_id`),
  ADD KEY `idx_ticket_status` (`status`),
  ADD KEY `idx_ticket_assigned` (`assigned_to`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_setting_key` (`setting_key`),
  ADD KEY `fk_ss_admin` (`updated_by`);

--
-- Indexes for table `ticket_replies`
--
ALTER TABLE `ticket_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tr_ticket` (`ticket_id`),
  ADD KEY `fk_tr_user` (`user_id`);

--
-- Indexes for table `two_fa_codes`
--
ALTER TABLE `two_fa_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_2fa_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_email` (`email`),
  ADD KEY `idx_users_role` (`role`),
  ADD KEY `idx_users_status` (`status`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sessions_user` (`user_id`),
  ADD KEY `idx_sessions_token` (`token`(100));

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=219;

--
-- AUTO_INCREMENT for table `admin_profiles`
--
ALTER TABLE `admin_profiles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `class_sessions`
--
ALTER TABLE `class_sessions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `community_messages`
--
ALTER TABLE `community_messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `content_flags`
--
ALTER TABLE `content_flags`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `course_lecturers`
--
ALTER TABLE `course_lecturers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `hr_forms`
--
ALTER TABLE `hr_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lecturer_profiles`
--
ALTER TABLE `lecturer_profiles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `lecturer_reviews`
--
ALTER TABLE `lecturer_reviews`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lessons`
--
ALTER TABLE `lessons`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lesson_content_logs`
--
ALTER TABLE `lesson_content_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lesson_media`
--
ALTER TABLE `lesson_media`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lesson_progress`
--
ALTER TABLE `lesson_progress`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payslips`
--
ALTER TABLE `payslips`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `public_chat_sessions`
--
ALTER TABLE `public_chat_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `question_options`
--
ALTER TABLE `question_options`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_profiles`
--
ALTER TABLE `student_profiles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `ticket_replies`
--
ALTER TABLE `ticket_replies`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `two_fa_codes`
--
ALTER TABLE `two_fa_codes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=148;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `fk_al_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `admin_profiles`
--
ALTER TABLE `admin_profiles`
  ADD CONSTRAINT `fk_admin_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `fk_ann_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_ann_editor` FOREIGN KEY (`last_edited_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `certificates`
--
ALTER TABLE `certificates`
  ADD CONSTRAINT `fk_cert_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cert_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `class_sessions`
--
ALTER TABLE `class_sessions`
  ADD CONSTRAINT `fk_cs_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cs_lecturer` FOREIGN KEY (`lecturer_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `community_messages`
--
ALTER TABLE `community_messages`
  ADD CONSTRAINT `fk_cm_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `content_flags`
--
ALTER TABLE `content_flags`
  ADD CONSTRAINT `fk_cf_admin` FOREIGN KEY (`flagged_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_cf_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `fk_course_admin` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `course_lecturers`
--
ALTER TABLE `course_lecturers`
  ADD CONSTRAINT `fk_cl_admin` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_cl_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cl_lecturer` FOREIGN KEY (`lecturer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `fk_enroll_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_enroll_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hr_forms`
--
ALTER TABLE `hr_forms`
  ADD CONSTRAINT `fk_hf_admin` FOREIGN KEY (`handled_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_hf_lecturer` FOREIGN KEY (`lecturer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD CONSTRAINT `fk_lr_admin` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_lr_lecturer` FOREIGN KEY (`lecturer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lecturer_profiles`
--
ALTER TABLE `lecturer_profiles`
  ADD CONSTRAINT `fk_lecturer_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lecturer_reviews`
--
ALTER TABLE `lecturer_reviews`
  ADD CONSTRAINT `fk_lrev_admin` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_lrev_lecturer` FOREIGN KEY (`lecturer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lessons`
--
ALTER TABLE `lessons`
  ADD CONSTRAINT `fk_lessons_module` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lesson_content_logs`
--
ALTER TABLE `lesson_content_logs`
  ADD CONSTRAINT `fk_lcl_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_lcl_user` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `lesson_media`
--
ALTER TABLE `lesson_media`
  ADD CONSTRAINT `fk_media_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lesson_progress`
--
ALTER TABLE `lesson_progress`
  ADD CONSTRAINT `fk_lp_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_lp_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `modules`
--
ALTER TABLE `modules`
  ADD CONSTRAINT `fk_modules_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `fk_pr_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_pay_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  ADD CONSTRAINT `fk_pay_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `payslips`
--
ALTER TABLE `payslips`
  ADD CONSTRAINT `fk_ps_admin` FOREIGN KEY (`paid_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_ps_lecturer` FOREIGN KEY (`lecturer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `fk_questions_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `question_options`
--
ALTER TABLE `question_options`
  ADD CONSTRAINT `fk_qo_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `fk_quiz_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_quiz_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_quiz_module` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD CONSTRAINT `fk_qa_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_qa_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_profiles`
--
ALTER TABLE `student_profiles`
  ADD CONSTRAINT `fk_student_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD CONSTRAINT `fk_ticket_admin` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_ticket_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD CONSTRAINT `fk_ss_admin` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `ticket_replies`
--
ALTER TABLE `ticket_replies`
  ADD CONSTRAINT `fk_tr_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tr_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `two_fa_codes`
--
ALTER TABLE `two_fa_codes`
  ADD CONSTRAINT `fk_2fa_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `fk_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
