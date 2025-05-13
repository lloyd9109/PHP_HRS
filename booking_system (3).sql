-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 18, 2024 at 05:11 PM
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
-- Database: `booking_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `lastname` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `firstname`, `password`, `created_at`, `lastname`) VALUES
(1, 'Admin', 'John Lloyd', '$2y$10$488i1RecheL8P0eXEq6Hw.PwhHHGokuk9rN7i6l1/Ap/AHngDnj1.', '2024-09-30 15:24:14', 'Villajuan');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `room_number` varchar(10) NOT NULL,
  `room_name` varchar(255) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone_number` varchar(15) NOT NULL,
  `preferred_date` varchar(255) DEFAULT NULL,
  `preferred_time` varchar(255) DEFAULT NULL,
  `guests` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `user_id` int(11) DEFAULT NULL,
  `notified` tinyint(1) DEFAULT 0,
  `status_updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `price` decimal(10,2) NOT NULL,
  `total_payment` decimal(10,2) NOT NULL,
  `preferred_date_start` date DEFAULT NULL,
  `preferred_date_end` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `room_number`, `room_name`, `full_name`, `email`, `phone_number`, `preferred_date`, `preferred_time`, `guests`, `created_at`, `status`, `user_id`, `notified`, `status_updated_at`, `price`, `total_payment`, `preferred_date_start`, `preferred_date_end`) VALUES
(484, 'DX01', 'Deluxe', 'John Lloyd Villajuan', 'lloydvillajuan@gmail.com', '998-978-6768', NULL, '11:11', 4, '2024-12-17 17:31:06', 'pending', 19, 0, '2024-12-17 17:31:06', 2500.00, 7500.00, '2024-12-18', '2024-12-20'),
(485, 'DX02', 'Deluxe', 'John Lloyd Villajuan', 'lloydvillajuan@gmail.com', '998-978-6768', NULL, '11:11', 2, '2024-12-17 17:36:35', 'pending', 19, 0, '2024-12-17 17:36:35', 2500.00, 5000.00, '2024-12-18', '2024-12-19');

-- --------------------------------------------------------

--
-- Table structure for table `booking_history`
--

CREATE TABLE `booking_history` (
  `id` int(11) NOT NULL,
  `room_number` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone_number` varchar(255) NOT NULL,
  `preferred_date` varchar(255) DEFAULT NULL,
  `preferred_time` varchar(255) DEFAULT NULL,
  `guests` int(11) NOT NULL,
  `check_in_out` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total_payment` decimal(10,2) NOT NULL,
  `preferred_date_start` date DEFAULT NULL,
  `preferred_date_end` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking_history`
--

INSERT INTO `booking_history` (`id`, `room_number`, `full_name`, `email`, `phone_number`, `preferred_date`, `preferred_time`, `guests`, `check_in_out`, `created_at`, `user_id`, `price`, `total_payment`, `preferred_date_start`, `preferred_date_end`) VALUES
(24, 'FM01', 'John Lloyd Villajuan', 'lloydvillajuan@gmail.com', '096-179-9065', NULL, '11:11', 0, 'complete', '2024-12-15 12:52:31', 19, 3500.00, 35000.00, '2024-12-15', '2024-12-24'),
(25, 'FM01', 'John Lloyd Villajuan', 'lloydvillajuan@gmail.com', '096-179-9065', NULL, '11:11', 3, 'complete', '2024-12-15 15:23:23', 19, 3500.00, 31500.00, '2024-12-15', '2024-12-23'),
(26, 'FM01', 'John Lloyd Villajuan', 'lloydvillajuan@gmail.com', '096-179-9065', NULL, '11:11', 4, 'complete', '2024-12-15 15:39:19', 19, 3500.00, 31500.00, NULL, NULL),
(27, 'DX01', 'John Lloyd Villajuan', 'lloydvillajuan@gmail.com', '096-179-9065', NULL, '01:00', 4, 'complete', '2024-12-16 06:02:54', 19, 2500.00, 10000.00, NULL, NULL),
(28, 'ST01', 'Lyca Sumalpong', 'lyca@gmail.com', '912-345-4345', NULL, '12:12', 3, 'complete', '2024-12-16 07:45:14', 135, 3000.00, 15000.00, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `user_id`, `message`, `is_admin`, `timestamp`) VALUES
(34, 19, '123456', 0, '2024-11-29 20:26:46'),
(35, 19, '1111', 1, '2024-11-29 20:26:52'),
(36, 19, '1111', 1, '2024-11-29 20:34:14'),
(37, 19, 'qq', 1, '2024-11-30 08:33:29'),
(38, 19, 'qq', 1, '2024-11-30 08:33:30'),
(39, 19, 'qq', 1, '2024-11-30 08:33:30'),
(40, 19, 'qq', 1, '2024-11-30 08:33:31'),
(41, 19, 'qq', 1, '2024-11-30 08:33:31'),
(42, 19, 'qq', 1, '2024-11-30 08:33:32'),
(43, 19, 'qq', 0, '2024-11-30 08:33:47'),
(44, 19, '1', 1, '2024-11-30 08:34:45'),
(45, 19, '32r4 324234 234234 234234 234234 23423 4', 0, '2024-11-30 12:38:45'),
(46, 19, '123456789', 0, '2024-12-02 07:34:14'),
(47, 19, 'abcdefghijklmnop', 1, '2024-12-02 07:34:45'),
(48, 19, 'abcdefghijklmnop', 1, '2024-12-02 07:34:46'),
(49, 19, 'abcdefghijklmnop', 1, '2024-12-02 07:34:48'),
(50, 19, 'abcdefghijklmnop', 1, '2024-12-02 07:34:48'),
(51, 19, 'abcdefghijklmnop', 1, '2024-12-02 07:34:48'),
(52, 19, 'abcdefghijklmnop', 1, '2024-12-02 07:34:48'),
(53, 19, '877878', 1, '2024-12-02 07:35:18'),
(54, 19, '877878', 1, '2024-12-02 07:35:19'),
(55, 19, '877878', 1, '2024-12-02 07:35:19'),
(56, 19, '877878', 1, '2024-12-02 07:35:19'),
(57, 19, 'ddgdsdd', 1, '2024-12-02 07:35:36'),
(58, 19, 'ddgdsdd', 1, '2024-12-02 07:35:37'),
(59, 19, 'ddgdsdd', 1, '2024-12-02 07:35:37'),
(60, 19, '12345678', 0, '2024-12-02 09:45:36'),
(61, 19, '123456789', 1, '2024-12-02 09:45:51'),
(62, 88, '12345678', 0, '2024-12-02 09:48:08'),
(63, 88, '1234', 0, '2024-12-02 09:49:05'),
(64, 88, '123456789egfereger', 1, '2024-12-02 09:49:32'),
(65, 88, 'nbgvgjhvkbnvhkjb', 1, '2024-12-02 09:52:05'),
(66, 88, 'nbgvgjhvkbnvhkjb', 1, '2024-12-02 09:52:11'),
(67, 19, '12345678rsgsgweg', 0, '2024-12-09 07:58:46'),
(68, 19, 'abcdefghijkl', 1, '2024-12-09 07:59:10'),
(69, 19, 'abcdefghijkl', 1, '2024-12-09 07:59:12'),
(70, 19, 'abcdefghijkl', 1, '2024-12-09 07:59:13'),
(71, 19, '1111', 1, '2024-12-09 16:29:59'),
(72, 19, '1111', 1, '2024-12-09 16:30:01'),
(73, 19, '1', 1, '2024-12-09 16:32:39'),
(74, 19, '1', 1, '2024-12-09 16:32:40'),
(75, 19, '1', 0, '2024-12-09 16:32:58'),
(76, 19, '1', 1, '2024-12-09 16:37:08'),
(77, 19, '2', 1, '2024-12-09 16:37:18'),
(78, 19, '3', 1, '2024-12-09 16:39:02'),
(79, 19, '1', 1, '2024-12-09 16:39:17'),
(80, 19, '1', 1, '2024-12-09 16:39:27'),
(81, 19, '1', 1, '2024-12-09 16:39:28'),
(82, 19, '1', 1, '2024-12-09 16:39:29'),
(83, 19, '9', 1, '2024-12-09 16:39:38'),
(84, 19, '2', 1, '2024-12-09 16:40:38'),
(85, 19, '1', 1, '2024-12-09 16:40:49'),
(86, 19, '1', 1, '2024-12-09 16:41:11'),
(87, 19, '1', 1, '2024-12-09 16:41:12'),
(88, 19, '1', 1, '2024-12-09 16:42:12'),
(89, 19, '1', 1, '2024-12-09 16:42:24'),
(90, 19, '1', 1, '2024-12-09 16:42:42'),
(91, 19, '1', 1, '2024-12-09 16:43:05'),
(92, 19, '1', 1, '2024-12-09 16:44:33'),
(93, 19, 'm', 1, '2024-12-09 16:44:47'),
(94, 19, 'a', 1, '2024-12-09 16:45:10'),
(95, 19, 'a', 1, '2024-12-09 16:45:12'),
(96, 19, '1', 1, '2024-12-09 16:48:37'),
(97, 19, '1', 1, '2024-12-09 17:13:04'),
(98, 19, 'a', 1, '2024-12-09 17:13:09'),
(99, 19, '11', 1, '2024-12-11 21:08:24'),
(100, 19, 'aaaa', 1, '2024-12-11 21:08:52'),
(101, 19, '1', 1, '2024-12-11 21:14:08'),
(102, 19, 'qq', 1, '2024-12-11 21:14:20'),
(103, 19, '1', 1, '2024-12-11 21:26:31'),
(104, 91, '123', 0, '2024-12-13 11:34:43'),
(105, 19, '11', 0, '2024-12-15 17:02:26'),
(106, 135, 'Hi', 0, '2024-12-16 07:18:43');

-- --------------------------------------------------------

--
-- Table structure for table `reserved`
--

CREATE TABLE `reserved` (
  `id` int(11) NOT NULL,
  `room_number` varchar(50) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone_number` varchar(15) NOT NULL,
  `guests` int(11) NOT NULL,
  `check_in_out` enum('check-in','check-out','complete','cancelled') NOT NULL DEFAULT 'check-in',
  `preferred_date` varchar(255) DEFAULT NULL,
  `preferred_time` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `total_payment` decimal(10,2) NOT NULL,
  `preferred_date_start` date DEFAULT NULL,
  `preferred_date_end` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reserved`
--

INSERT INTO `reserved` (`id`, `room_number`, `full_name`, `email`, `phone_number`, `guests`, `check_in_out`, `preferred_date`, `preferred_time`, `user_id`, `price`, `total_payment`, `preferred_date_start`, `preferred_date_end`) VALUES
(54, 'FM03', 'John Lloyd Villajuan', 'lloydvillajuan@gmail.com', '096-179-9065', 4, 'cancelled', NULL, '11:01', 19, 3200.00, 28800.00, '2024-12-15', '2024-12-23');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `room_name` varchar(255) DEFAULT NULL,
  `room_size` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `features` text DEFAULT NULL,
  `availability` enum('Available','Unavailable','Booked','Reserved') NOT NULL,
  `room_number` varchar(10) DEFAULT NULL,
  `amenities` text DEFAULT NULL,
  `guests` varchar(10) NOT NULL,
  `category` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `room_name`, `room_size`, `description`, `price`, `image_url`, `features`, `availability`, `room_number`, `amenities`, `guests`, `category`) VALUES
(301, 'Deluxe', '300', 'Sample', 2500.00, '../assets/img_url/Deluxe.jpg', 'Queen Bed, Sofa, Room Service, Spa, City View', 'Available', 'DX01', 'Free Wi-Fi, Air Conditioning, Bathroom Amenities, Coffee Kit, Beverage and Dining, Entertainment, Comfort Items, Housekeeping Service', '2', NULL),
(302, 'Deluxe', '300', 'Sample', 2500.00, '../assets/img_url/Deluxe.jpg', 'Queen Bed, Sofa, Room Service, City View', 'Available', 'DX02', 'Free Wi-Fi, Air Conditioning, Bathroom Amenities, Coffee Kit, Beverage and Dining, Entertainment, Comfort Items, Housekeeping Service', '2', NULL),
(303, 'Deluxe', '300', 'Sample', 2500.00, '../assets/img_url/Deluxe.jpg', 'Queen Bed, Sofa, Room Service, City View', 'Available', 'DX03', 'Free Wi-Fi, Air Conditioning, Bathroom Amenities, Coffee Kit, Beverage and Dining, Entertainment, Comfort Items, Housekeeping Service', '2', NULL),
(304, 'Deluxe', '300', 'Sample', 2800.00, '../assets/img_url/room2.jpg', 'Queen Bed, Sofa, Room Service, Mountain View, Ocean View', 'Available', 'DX04', 'Free Wi-Fi, Air Conditioning, Bathroom Amenities, Coffee Kit, Beverage and Dining, Entertainment, Comfort Items, Housekeeping Service', '2', NULL),
(305, 'Deluxe', '300', 'Sample', 3000.00, '../assets/img_url/room2.jpg', 'Queen Bed, Sofa, Room Service, Mountain View, Ocean View', 'Available', 'SD01', 'Free Wi-Fi, Air Conditioning, Bathroom Amenities, Coffee Kit, Beverage and Dining, Entertainment, Comfort Items, Housekeeping Service', '2', NULL),
(306, 'Standard', '300', 'Sample', 1800.00, '../assets/img_url/room1.jpg', 'Queen Bed, Sofa, Work Desk, Room Service, Spa, City View', 'Available', 'SD01', 'Free Wi-Fi, Air Conditioning, Bathroom Amenities, Coffee Kit, Beverage and Dining, Entertainment, Comfort Items, Housekeeping Service', '1', NULL),
(307, 'Standard', '300', 'Sample', 1800.00, '../assets/img_url/1.jpeg', 'Queen Bed, Sofa, Work Desk, Gym, Room Service, Spa, City View', 'Available', 'SD02', 'Free Wi-Fi, Air Conditioning, Bathroom Amenities, Coffee Kit, Beverage and Dining, Entertainment, Comfort Items, Housekeeping Service', '3', NULL),
(308, 'Standard', '300', 'Sample', 2400.00, '../assets/img_url/Standard.jpg', 'King Bed, Sofa, Work Desk, Gym, Room Service, Mountain View, Ocean View', 'Available', 'SD03', 'Free Wi-Fi, Air Conditioning, Bathroom Amenities, Coffee Kit, Beverage and Dining, Entertainment, Comfort Items, Housekeeping Service', '1', NULL),
(309, 'Standard', '300', 'Sample', 1800.00, '../assets/img_url/room2.jpg', 'Sofa, Work Desk, Gym, Room Service, Spa, City View', 'Available', 'SD04', 'Free Wi-Fi, Air Conditioning, Bathroom Amenities, Coffee Kit, Beverage and Dining, Entertainment, Comfort Items, Housekeeping Service', '1', NULL),
(310, 'Standard', '300', 'Sample', 2400.00, '../assets/img_url/Standard.jpg', 'Queen Bed, Sofa, Work Desk, Gym, Room Service, Spa, Mountain View, Ocean View', 'Available', 'SD05', 'Free Wi-Fi, Air Conditioning, Bathroom Amenities, Coffee Kit, Beverage and Dining, Entertainment, Comfort Items, Housekeeping Service', '1', NULL),
(311, 'Suite', '300', 'Sample', 3000.00, '../assets/img_url/suite3.jpg', 'Sofa, Gym, Room Service, Spa, Mountain View, City View', 'Available', 'ST01', 'Free Wi-Fi, Air Conditioning, Bathroom Amenities, Coffee Kit, Beverage and Dining, Entertainment, Comfort Items, Housekeeping Service', '3', NULL),
(312, 'Suite', '300', 'Sample', 3000.00, '../assets/img_urlsuite1.jpeg', 'Queen Bed, Sofa, Gym, Spa, Mountain View, Ocean View', 'Available', 'ST02', 'Free Wi-Fi, Air Conditioning, Bathroom Amenities, Coffee Kit, Beverage and Dining, Entertainment, Comfort Items, Housekeeping Service', '3', NULL),
(313, 'Superior', '300', 'Sample', 3800.00, '../assets/img_url/Superior.jpg', 'Queen Bed, Sofa, Work Desk, Gym, Room Service, Spa, Mountain View, Ocean View, City View', 'Available', 'SPR01', 'Free Wi-Fi, Air Conditioning, Bathroom Amenities, Coffee Kit, Beverage and Dining, Entertainment, Comfort Items, Housekeeping Service', '3', NULL),
(314, 'Superior', '300', 'Sample', 3500.00, '../assets/img_url/Superior 2.jpg', 'Queen Bed, Sofa, Work Desk, Room Service, Spa, Ocean View', 'Available', 'SPR02', 'Free Wi-Fi, Air Conditioning, Bathroom Amenities, Coffee Kit, Beverage and Dining, Entertainment, Comfort Items, Housekeeping Service', '3', NULL),
(315, 'Superior', '300', 'Sample', 3500.00, '../assets/img_url/Superior 2.jpg', 'Queen Bed, Sofa, Work Desk, Gym, Room Service, Spa, Ocean View', 'Available', 'SPR03', 'Free Wi-Fi, Air Conditioning, Bathroom Amenities, Coffee Kit, Beverage and Dining, Entertainment, Comfort Items, Housekeeping Service', '3', NULL),
(316, 'Suite', '300', 'Sample', 3000.00, '../assets/img_url/room.jpg', 'Sofa, Gym, Room Service, Spa, Mountain View', 'Available', 'ST03', 'Free Wi-Fi, Air Conditioning, Bathroom Amenities, Coffee Kit, Beverage and Dining, Entertainment, Comfort Items, Housekeeping Service', '3', NULL),
(317, 'Family', '300', 'Sample', 3000.00, '../assets/img_url/room5.jpg', 'Queen Bed, Sofa, Room Service, Spa, Pet-Friendly Rooms, Mountain View', 'Available', 'FM01', 'Free Wi-Fi, Air Conditioning, Bathroom Amenities, Coffee Kit, Beverage and Dining, Entertainment, Comfort Items, Housekeeping Service', '4', NULL),
(318, 'Family', '300', 'Sample', 3000.00, '../assets/img_url/Family.jpg', 'Queen Bed, Sofa, Room Service, Spa, Pet-Friendly Rooms, Ocean View', 'Available', 'FM02', 'Free Wi-Fi, Air Conditioning, Bathroom Amenities, Coffee Kit, Beverage and Dining, Entertainment, Comfort Items, Housekeeping Service', '4', NULL),
(319, 'Family', '300', 'Sample', 2600.00, '../assets/img_url/fm1.jpeg', 'Sofa, Room Service, Spa, Pet-Friendly Rooms, Mountain View, City View', 'Available', 'FM03', 'Free Wi-Fi, Air Conditioning, Bathroom Amenities, Coffee Kit, Beverage and Dining, Entertainment, Comfort Items, Housekeeping Service', '4', NULL),
(320, 'Standard', '300', 'Sample', 10000.00, '../assets/img_url/1.jpeg', 'Sofa, Work Desk, Room Service, Spa, City View', 'Available', 'SD06', 'Free Wi-Fi, Air Conditioning, Bathroom Amenities, Coffee Kit, Beverage and Dining, Comfort Items', '1', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `first_name`, `last_name`, `email`, `password`, `created_at`) VALUES
(26, 'John Lloyd', 'Villajuan', 'lloydvillajuan@gmail.com', '$2y$10$t5XCdxYHNCwIlzoRJ3obq..TiQcCORg9q7U4Jg6DWnAvtypf9CHYy', '2024-12-11 05:16:35'),
(44, 'John Lloyd', 'Villajuan', 'villajuan@gmail.com', '$2y$10$L7Fhr7chc6kOt3s5Uua8oOBAaHCCnrxv2M53wYfeLl0Iwq8HXzG5S', '2024-12-14 06:01:38'),
(45, 'Jose', 'Rizal', 'jose@gmail.com', '$2y$10$7Z4bdegRqo4uvnCHiMsPU.p2IRZTtWjtEGpljUkdgPn4rlRpYKBlC', '2024-12-14 14:56:08'),
(46, '123', '123', '123@gmail.com', '$2y$10$.Bxk3FnUNyQVnwZMk2j8cOzq/YhVyNKvElCxxpp63OoI3adFBRxQG', '2024-12-14 14:56:23'),
(47, 'john', 'john', 'john@gmail.com', '$2y$10$01PPkYlXDVNpFeT8zH6lceWrnR1oKA8BHa4LBdh2h70LE26vEkgJG', '2024-12-14 15:07:02'),
(48, 'lloyd', 'lloyd', 'lloyd@gmail.com', '$2y$10$zI7VqCTdNHt7gIk3dbG9bOGgMWtaMUsd.Dx98mbGzMo7nPsmZrvXS', '2024-12-14 15:07:16'),
(49, 'lloyd', 'john', 'vill@gmail.com', '$2y$10$19rcTf1nqgeeaH1EIXjFiOq0rKmnyYhYkTmDG2NrpeLBCZQTCo0z.', '2024-12-14 15:07:34'),
(50, '456', '456', '456@gmail.com', '$2y$10$51JtYHzy99ED5ME7LLVNL.1PSM4brh3Tjx333Ns.cKAJLOm0WLley', '2024-12-14 15:15:43'),
(51, 'abc', 'def', 'abc@gmail.com', '$2y$10$GRkipRIr3Bm5XXH.a1F6AeFG0ur3q74bBNo7vO4TqrLmj3YbJxHDS', '2024-12-14 15:37:36'),
(52, 'def', 'def', 'def@gmail.com', '$2y$10$MNhTG.Gsrs87KJRgy0jRXuZh5q1XSQCVG.keUUJc.d2dkHLmjc7M.', '2024-12-14 15:37:50'),
(54, 'john', 'john', 'll@gmail.com', '$2y$10$m9AZIDUncg.P5.5fG/o.h.D8LwlSIEPQoYMpCOmyN.zliwlIvgtGe', '2024-12-14 15:41:54'),
(55, '123', '123', '1@gmail.com', '$2y$10$38zOedLoNbdycPtZVdt4oOno9VhMhNPT6u6zj/1nMWs6PoNhjAW.q', '2024-12-15 07:08:34'),
(56, '1213', '123', '2@gmail.com', '$2y$10$bWlYOUqcdWqz6OP1gYIdJe/b49HrCchMXyrS8y5mJy4/QUwiNRdom', '2024-12-15 07:08:53'),
(57, 'abcdef', '123', '3@gmail.com', '$2y$10$N2dSvCUAHD9Gds8YHRxOTuFsrLm2kEvsFQX0Sw65mRT73fR4s2M22', '2024-12-15 07:11:18'),
(58, 'Denis', 'Boang', 'denis@gmail.com', '$2y$10$dOS0L4gJVo3DgxXHbkJ0bu7rRsBZXpkx1lbr/nJlcPkKWjwWWGxLW', '2024-12-16 07:42:23');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `cellphone` varchar(15) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `agree_to_conditions` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_image` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `cellphone`, `password_hash`, `agree_to_conditions`, `created_at`, `profile_image`, `address`, `age`, `gender`) VALUES
(19, 'John Lloyd', 'Villajuan', 'lloydvillajuan@gmail.com', '998-978-6768', '$2y$10$1dTvGR4cjVOPkJFhYAGO0uXZ6gmJNdrkcg28725aOslbaHviDWQoS', 1, '2024-10-02 13:18:25', '../assets/img_url/19-profile-image.png', 'Tipolo Mandaue City', 20, 'male'),
(124, 'Joseaa', 'Rizala', 'jose@gmail.com', '976-587-5876', '$2y$10$amGWE5eCQwKBu7ndfczoLuwvS/d7rKGHO0xELvWGtQX.x1c6aPOla', 0, '2024-12-14 05:45:09', '../assets/img_url/124-profile-image.png', '201221', 1111111111, 'male'),
(125, 'Andres', 'Bonifacio', 'andres@gmail.com', '987-968-7587', '$2y$10$YS4PexsAO1uD32QHhKjTauWVQDH3sWUGayyzmm2p0NYD/wA9dAu.u', 0, '2024-12-14 05:50:04', NULL, NULL, NULL, NULL),
(126, 'abc', 'def', '123@gmail.com', '977-858-7587', '$2y$10$8bHGonQ0aNI591cRNNebF.kspe9MV5wj13nBKItSqMKqUcpgxtwHO', 0, '2024-12-14 19:30:23', NULL, NULL, NULL, NULL),
(127, 'john', 'john', 'll@gmail.com', '987-587-5878', '$2y$10$XhsbovmUKOCDTlvAgVZuK.eQCnC1skTKUPnejrRieX4OrU5VeIahe', 0, '2024-12-14 19:33:26', NULL, NULL, NULL, NULL),
(128, 'aa', 'bb', 'aabb@gmail.com', '987-687-6868', '$2y$10$vJHHDMyi0OEuQxSY1I5r0ejznvor6k7yiNeAcJ.CWz9PG9EvrdJI2', 0, '2024-12-14 19:34:12', NULL, NULL, NULL, NULL),
(129, 'aa', 'aa', 'aa@gmail.com', '987-689-6868', '$2y$10$StqY/xUNzoi4QJKAzSciY.4US9JHcshE8HVBh04PD/D.yiLZU8Kj2', 0, '2024-12-14 19:35:05', NULL, NULL, NULL, NULL),
(130, 'bb', 'bb', 'bb@gmail.com', '957-546-3464', '$2y$10$BXWMTnraQjVeENUhG3e/3Og9PVUZpI16S47xhc35vW9NpoacqR6S6', 0, '2024-12-14 19:36:06', NULL, NULL, NULL, NULL),
(131, 'cc', 'cc', 'cc@gmail.com', '978-686-8687', '$2y$10$YoSm3mr2.JUrcZZgsjXMVOebydPvVS/t/wVCS.PvoftFdKdxoswnq', 0, '2024-12-14 19:36:35', NULL, NULL, NULL, NULL),
(132, 'dd', 'dd', 'dd@gmail.com', '944-444-4444', '$2y$10$Epaiv5Gr3s7AMop4mqC7ru2.LiVE45O/9gCkQIoNUHVU4JEY7w8lC', 0, '2024-12-14 19:36:58', '../assets/img_url/APP-DEV Reseach Paper - VILLAJUAN, JL.pdf', '1', 20, 'other'),
(133, 'john', 'lloyd', 'abcdefg@gmail.com', '932-421-4124', '$2y$10$hrjN3u2B.4IlfZG9q/mlVOmg9vV/3OE1P9uY8jTpeKOxOuMdABKo.', 0, '2024-12-14 22:45:54', NULL, NULL, NULL, NULL),
(134, 'abc', 'DEF', '12345@gmail.com', '986-875-7654', '$2y$10$H/NjTkIN/Sq2vIcj5dhFoOifom6jGOz09Ce7iF3Ww9E44pR5hBL4K', 0, '2024-12-16 05:57:38', NULL, NULL, NULL, NULL),
(135, 'Lyca', 'Sumalpong', 'lyca@gmail.com', '912-345-4345', '$2y$10$n3oGIIBOcALX7.q3/6FpPuy8F0iydzaBuyplq6yXEF6dWD6BYBfSy', 0, '2024-12-16 07:07:01', NULL, NULL, NULL, NULL),
(136, 'rio', 'Rio', 'rio@gmail.com', '999-687-5765', '$2y$10$tQoZup/xDdLkk2J4bABoC.4XSnGvmSzxRKMr1KWR4.P5cAbqYBqeu', 0, '2024-12-16 07:52:43', NULL, NULL, NULL, NULL),
(137, 'John Lloyd', 'Villajuan', 'test9@gmail.com', '998-978-9687', '$2y$10$NaxefT8dWiJnJejnkb4tCe2M8YMON63wtBKqfAg/YfdqjLJBZT2va', 0, '2024-12-16 21:08:45', NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `booking_history`
--
ALTER TABLE `booking_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reserved`
--
ALTER TABLE `reserved`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `email_2` (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=486;

--
-- AUTO_INCREMENT for table `booking_history`
--
ALTER TABLE `booking_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT for table `reserved`
--
ALTER TABLE `reserved`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=321;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=138;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
