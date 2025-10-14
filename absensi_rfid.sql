-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 09, 2025 at 10:30 AM
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
-- Database: `absensi_rfid`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance_log`
--

CREATE TABLE `attendance_log` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `card_id` varchar(50) NOT NULL,
  `device_id` varchar(50) DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp(),
  `status` enum('On Time','Late','Holiday') DEFAULT 'On Time',
  `schedule_status` enum('On Time','Late','Holiday') DEFAULT 'On Time',
  `location` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance_log`
--

INSERT INTO `attendance_log` (`id`, `student_id`, `card_id`, `device_id`, `timestamp`, `status`, `schedule_status`, `location`) VALUES
(93, 2, '6E283206', 'ESP8266-1', '2025-10-07 20:22:29', 'On Time', '', NULL),
(94, 26, '038FD9EE', 'ESP8266-1', '2025-10-07 22:22:44', 'On Time', '', NULL),
(95, 27, 'D056675F', 'ESP8266-1', '2025-10-07 22:33:27', 'On Time', '', NULL),
(97, 29, 'E34FCBEE', 'ESP8266-1', '2025-10-07 22:40:05', 'On Time', '', NULL),
(99, 28, '534BCEEE', 'ESP8266-1', '2025-10-07 22:40:10', 'On Time', '', NULL),
(103, 33, 'F34DCBEE', 'ESP8266-1', '2025-10-08 22:49:15', 'On Time', '', NULL),
(104, 34, 'A345CAEE', 'ESP8266-1', '2025-10-08 22:54:44', 'On Time', '', NULL),
(105, 34, 'A345CAEE', 'ESP8266-1', '2025-10-09 00:52:44', 'On Time', 'On Time', NULL),
(106, 27, 'D056675F', 'ESP8266-1', '2025-10-09 00:53:04', 'On Time', 'On Time', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `day` enum('Mon','Tue','Wed','Thu','Fri','Sat','Sun') NOT NULL,
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `grace_period` int(3) DEFAULT NULL,
  `is_holiday` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`id`, `day`, `time_in`, `time_out`, `grace_period`, `is_holiday`) VALUES
(1, 'Mon', '07:00:00', '14:20:00', 10, 0),
(2, 'Tue', '07:00:00', '14:20:00', 10, 0),
(3, 'Wed', '07:00:00', '14:20:00', 10, 0),
(4, 'Thu', '07:00:00', '14:20:00', 10, 0),
(5, 'Fri', '07:00:00', '11:05:00', 10, 0),
(6, 'Sat', NULL, NULL, 0, 1),
(7, 'Sun', NULL, NULL, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `reg_mode` tinyint(1) DEFAULT 0,
  `test_mode` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `reg_mode`, `test_mode`) VALUES
(1, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `class` varchar(50) NOT NULL,
  `card_id` varchar(50) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_pic` varchar(255) DEFAULT 'default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `name`, `class`, `card_id`, `status`, `created_at`, `profile_pic`) VALUES
(2, 'MARCO', 'XII IPA 2', '6E283206', 'active', '2025-10-01 08:08:28', 'p_68dcfb9ed4714.jpg'),
(26, 'NURUL ZAHRA', 'XII IPA 2', '038FD9EE', 'active', '2025-10-07 15:11:38', 'default.png'),
(27, 'MUHTADIN', 'XII IPA 2', 'D056675F', 'active', '2025-10-07 15:25:34', 'default.png'),
(28, 'HAGIA', 'XII IPA 2', '534BCEEE', 'active', '2025-10-07 15:37:31', 'default.png'),
(29, 'EKA SRI MULYANI', 'XII IPA 2', 'E34FCBEE', 'active', '2025-10-07 15:37:32', 'default.png'),
(33, 'DAMAR MAYA', 'XII IPA 2', 'F34DCBEE', 'active', '2025-10-08 15:48:36', 'default.png'),
(34, 'SUCI DEWI AMANDA', 'XII IPA 2', 'A345CAEE', 'active', '2025-10-08 15:54:08', 'default.png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance_log`
--
ALTER TABLE `attendance_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attendance_log_ibfk_1` (`student_id`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `card_id` (`card_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance_log`
--
ALTER TABLE `attendance_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance_log`
--
ALTER TABLE `attendance_log`
  ADD CONSTRAINT `attendance_log_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
