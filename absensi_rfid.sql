-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 07, 2025 at 03:42 PM
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
(89, 21, 'E34FCBEE', 'ESP8266-1', '2025-10-07 16:50:06', 'On Time', '', NULL),
(90, 25, 'F34DCBEE', 'ESP8266-1', '2025-10-07 17:11:40', 'On Time', '', NULL),
(91, 23, '534BCEEE', 'ESP8266-1', '2025-10-07 17:14:40', 'On Time', '', NULL),
(92, 12, '038FD9EE', 'ESP8266-1', '2025-10-07 17:15:14', 'On Time', '', NULL),
(93, 2, '6E283206', 'ESP8266-1', '2025-10-07 20:22:29', 'On Time', '', NULL);

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
(1, 0, 1);

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
(12, 'NURUL ZAHRA', 'XII IPA 2', '038FD9EE', 'active', '2025-10-06 15:39:25', 'default.png'),
(21, 'EKA SRI MULYANI', 'XII IPA 2', 'E34FCBEE', 'active', '2025-10-06 16:42:36', 'default.png'),
(23, 'DAMAR MAYA', 'XII IPA 2', '534BCEEE', 'active', '2025-10-06 16:42:46', 'default.png'),
(25, 'MUHTADIN', 'XII IPA 2', 'F34DCBEE', 'active', '2025-10-06 16:42:50', 'default.png');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

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
