-- phpMyAdmin SQL Dump
-- version 4.1.6
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Sep 08, 2026 at 04:34 AM
-- Server version: 5.6.16
-- PHP Version: 5.5.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `private_patient_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `role` varchar(30) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `description` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=46 ;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `username`, `full_name`, `role`, `action`, `description`, `created_at`) VALUES
(26, 1, 'admin', 'System Administrator', 'Administrator', 'LOGOUT', 'User logged out of the system.', '2026-09-08 00:12:55'),
(27, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'LOGIN', 'User logged into the system.', '2026-09-08 00:13:05'),
(28, 1, 'admin', 'System Administrator', 'Administrator', 'LOGIN', 'User logged into the system.', '2026-09-08 00:13:55'),
(29, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'CONSULTATION', 'Added consultation: 0010 - CLARA DELA CRUZ PELAGIOSSS - Consultation #2', '2026-09-08 00:17:36'),
(30, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'PRESCRIPTION', 'Added prescription: 0010 - CLARA DELA CRUZ PELAGIOSSS - 3 medicine(s) - 2026-09-08', '2026-09-08 00:18:58'),
(31, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'LABORATORY', 'Added laboratory record: 0010 - CLARA DELA CRUZ PELAGIOSSS - 9 test(s) - 2026-09-08', '2026-09-08 00:22:39'),
(32, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'PRESCRIPTION', 'Added prescription: 0010 - CLARA DELA CRUZ PELAGIOSSS - 1 medicine(s) - 2026-09-08', '2026-09-08 00:41:11'),
(33, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'PRESCRIPTION', 'Deleted prescription medicine: 0010 - CLARA DELA CRUZ PELAGIOSSS - DSFDSDSFDJFJJGGJJFDJFDJFDSJFHSDSDFHSDHFJSDHFDJHFSDJHFSIDFSDHFSFSDFSF - 2026-09-08', '2026-09-08 00:42:01'),
(34, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'PRESCRIPTION', 'Updated prescription medicine: 0010 - CLARA DELA CRUZ PELAGIOSSS - PARACETAMOL - 2026-09-08', '2026-09-08 00:49:37'),
(35, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'DELETE_PRESCRIPTION', 'Deleted prescription medicine: 0010 - CLARA DELA CRUZ PELAGIOSSS - PARACETAMOL - 2026-09-08', '2026-09-08 00:49:49'),
(36, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'LOGOUT', 'User logged out of the system.', '2026-09-08 01:59:27'),
(37, 1, 'admin', 'System Administrator', 'Administrator', 'LOGIN', 'User logged into the system.', '2026-09-08 01:59:32'),
(38, 1, 'admin', 'System Administrator', 'Administrator', 'LOGOUT', 'User logged out of the system.', '2026-09-08 02:00:41'),
(39, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'LOGIN', 'User logged into the system.', '2026-09-08 02:00:45'),
(40, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'ADD_PATIENT', 'Added patient: 0001 - JUAN DELA CRUZ TEST', '2026-09-08 02:07:38'),
(41, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'CONSULTATION', 'Added consultation: 0001 - JUAN DELA CRUZ TEST - Consultation #1', '2026-09-08 02:09:02'),
(42, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'PRESCRIPTION', 'Added prescription: 0001 - JUAN DELA CRUZ TEST - 1 medicine(s) - 2026-09-08', '2026-09-08 02:17:47'),
(43, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'LABORATORY', 'Added laboratory record: 0001 - JUAN DELA CRUZ TEST - 20 test(s) - 2026-09-08', '2026-09-08 02:21:58'),
(44, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'LABORATORY', 'Updated laboratory record: 0001 - JUAN DELA CRUZ TEST - APPEARANCE - 2026-09-08', '2026-09-08 02:22:25'),
(45, 1, 'admin', 'System Administrator', 'Administrator', 'LOGIN', 'User logged into the system.', '2026-09-08 02:25:29');

-- --------------------------------------------------------

--
-- Table structure for table `consultations`
--

CREATE TABLE IF NOT EXISTS `consultations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `visit_date` date NOT NULL,
  `chief_complaint` text,
  `history_illness` text,
  `blood_pressure` varchar(20) DEFAULT NULL,
  `temperature` decimal(4,1) DEFAULT NULL,
  `pulse_rate` int(11) DEFAULT NULL,
  `respiratory_rate` int(11) DEFAULT NULL,
  `oxygen_saturation` int(11) DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `height` decimal(5,2) DEFAULT NULL,
  `assessment` text,
  `medication` text,
  `management` text,
  `follow_up_date` date DEFAULT NULL,
  `remarks` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `follow_up_status` varchar(30) NOT NULL DEFAULT 'PENDING',
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=33 ;

--
-- Dumping data for table `consultations`
--

INSERT INTO `consultations` (`id`, `patient_id`, `visit_date`, `chief_complaint`, `history_illness`, `blood_pressure`, `temperature`, `pulse_rate`, `respiratory_rate`, `oxygen_saturation`, `weight`, `height`, `assessment`, `medication`, `management`, `follow_up_date`, `remarks`, `created_at`, `follow_up_status`) VALUES
(32, 12, '2026-08-31', 'HEADACHE', 'HEADACHE FOR 2 DAYS', '120/80', '36.5', 72, 18, 98, '60.00', '165.00', 'G44.1 - VASCULAR HEADACHE', NULL, 'REST AND HYDRATION', '2026-09-07', '-', '2026-09-08 02:09:02', 'COMPLETED');

-- --------------------------------------------------------

--
-- Table structure for table `laboratory`
--

CREATE TABLE IF NOT EXISTS `laboratory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `test_date` date NOT NULL,
  `test_name` varchar(150) NOT NULL,
  `result` varchar(255) NOT NULL,
  `unit` varchar(50) DEFAULT '',
  `reference_range` varchar(100) DEFAULT '',
  `remarks` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_patient_id` (`patient_id`),
  KEY `idx_test_date` (`test_date`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=47 ;

--
-- Dumping data for table `laboratory`
--

INSERT INTO `laboratory` (`id`, `patient_id`, `test_date`, `test_name`, `result`, `unit`, `reference_range`, `remarks`, `created_at`) VALUES
(27, 12, '2026-09-08', 'HEMOGLOBIN', '14.2', 'G/DL', 'MALE: 13.5-17.5 G/DL | FEMALE: 12.0-16.0 G/DL', '', '2026-09-08 02:21:58'),
(28, 12, '2026-09-08', 'HEMATOCRIT', '43', '%', 'MALE: 41-53% | FEMALE: 36-46%', '', '2026-09-08 02:21:58'),
(29, 12, '2026-09-08', 'WHITE BLOOD CELL COUNT', '7.2', 'K/UL', '4.0-11.0 K/UL', '', '2026-09-08 02:21:58'),
(30, 12, '2026-09-08', 'PLATELET COUNT', '250', 'K/UL', '150-450 K/UL', '', '2026-09-08 02:21:58'),
(31, 12, '2026-09-08', 'RANDOM BLOOD SUGAR', '98', 'MG/DL', '70-140 MG/DL', '', '2026-09-08 02:21:58'),
(32, 12, '2026-09-08', 'CREATININE', '0.9', 'MG/DL', 'MALE: 0.74-1.35 MG/DL | FEMALE: 0.59-1.04 MG/DL', '', '2026-09-08 02:21:58'),
(33, 12, '2026-09-08', 'TOTAL CHOLESTEROL', '180', 'MG/DL', '<200 MG/DL', '', '2026-09-08 02:21:58'),
(34, 12, '2026-09-08', 'TRIGLYCERIDES', '120', 'MG/DL', '<150 MG/DL', '', '2026-09-08 02:21:58'),
(35, 12, '2026-09-08', 'HDL CHOLESTEROL', '55', 'MG/DL', '>=40 MG/DL', '', '2026-09-08 02:21:58'),
(36, 12, '2026-09-08', 'LDL CHOLESTEROL', '101', 'MG/DL', '<100 MG/DL', '', '2026-09-08 02:21:58'),
(37, 12, '2026-09-08', 'COLOR', 'YELLOW', '', 'YELLOW', '', '2026-09-08 02:21:58'),
(38, 12, '2026-09-08', 'APPEARANCE', 'CLEAR', '', 'CLEAR', '', '2026-09-08 02:21:58'),
(39, 12, '2026-09-08', 'SPECIFIC GRAVITY', '1.015', '', '1.005-1.030', '', '2026-09-08 02:21:58'),
(40, 12, '2026-09-08', 'PH', '6.0', '', '5.0-8.0', '', '2026-09-08 02:21:58'),
(41, 12, '2026-09-08', 'PROTEIN', 'NEGATIVE', '', 'NEGATIVE', '', '2026-09-08 02:21:58'),
(42, 12, '2026-09-08', 'GLUCOSE', 'NEGATIVE', '', 'NEGATIVE', '', '2026-09-08 02:21:58'),
(43, 12, '2026-09-08', 'KETONES', 'NEGATIVE', '', 'NEGATIVE', '', '2026-09-08 02:21:58'),
(44, 12, '2026-09-08', 'BLOOD', 'NEGATIVE', '', 'NEGATIVE', '', '2026-09-08 02:21:58'),
(45, 12, '2026-09-08', 'RBC', '0-2', '/HPF', '0-2 /HPF', '', '2026-09-08 02:21:58'),
(46, 12, '2026-09-08', 'WBC', '0-2', '/HPF', '0-5 /HPF', '', '2026-09-08 02:21:58');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE IF NOT EXISTS `patients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` varchar(10) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `birthdate` date NOT NULL,
  `sex` enum('Male','Female') NOT NULL,
  `address` text,
  `contact_no` varchar(20) DEFAULT NULL,
  `emergency_contact` varchar(150) DEFAULT NULL,
  `emergency_contact_no` varchar(20) DEFAULT NULL,
  `date_registered` date NOT NULL,
  `status` enum('Active','Inactive','Deceased') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `patient_id` (`patient_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=13 ;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `patient_id`, `last_name`, `first_name`, `middle_name`, `birthdate`, `sex`, `address`, `contact_no`, `emergency_contact`, `emergency_contact_no`, `date_registered`, `status`, `created_at`) VALUES
(12, '0001', 'TEST', 'JUAN', 'DELA CRUZ', '1990-01-01', 'Male', 'TEST ADDRESS', '09123456789', 'TEST TWO', '09202345678', '2026-09-08', 'Active', '2026-09-08 02:07:38');

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

CREATE TABLE IF NOT EXISTS `prescriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prescription_header_id` int(11) DEFAULT NULL,
  `consultation_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `medicine_name` varchar(150) NOT NULL,
  `strength` varchar(100) DEFAULT '',
  `dosage` varchar(100) DEFAULT '',
  `frequency` varchar(100) DEFAULT '',
  `duration` varchar(100) DEFAULT '',
  `quantity` int(11) DEFAULT NULL,
  `breakfast` varchar(30) NOT NULL DEFAULT '',
  `lunch` varchar(30) NOT NULL DEFAULT '',
  `dinner` varchar(30) NOT NULL DEFAULT '',
  `instructions` text,
  `prescribed_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `consultation_id` (`consultation_id`),
  KEY `patient_id` (`patient_id`),
  KEY `idx_prescription_header_id` (`prescription_header_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=26 ;

--
-- Dumping data for table `prescriptions`
--

INSERT INTO `prescriptions` (`id`, `prescription_header_id`, `consultation_id`, `patient_id`, `medicine_name`, `strength`, `dosage`, `frequency`, `duration`, `quantity`, `breakfast`, `lunch`, `dinner`, `instructions`, `prescribed_date`, `created_at`) VALUES
(25, 8, 32, 12, 'PARACETAMOL', '500 MG', '', '', '', 10, '1 TAB', '1 TAB', '1 TAB', NULL, '2026-09-08', '2026-09-08 02:17:47');

-- --------------------------------------------------------

--
-- Table structure for table `prescription_headers`
--

CREATE TABLE IF NOT EXISTS `prescription_headers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `prescribed_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_patient_date` (`patient_id`,`prescribed_date`),
  KEY `idx_patient_id` (`patient_id`),
  KEY `idx_prescribed_date` (`prescribed_date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `prescription_headers`
--

INSERT INTO `prescription_headers` (`id`, `patient_id`, `prescribed_date`, `created_at`) VALUES
(8, 12, '2026-09-08', '2026-09-08 02:17:47');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` varchar(30) NOT NULL DEFAULT 'Staff',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `role`, `is_active`, `created_at`) VALUES
(1, 'admin', '$2y$10$WrnjGjrhnxSMCxv8Er/tweYim1H9yWSxyzSCFWQlRvm7Yhrzq8nzW', 'System Administrator', 'Administrator', 1, '2026-09-07 00:08:44'),
(2, 'juan', '$2y$10$/2DS8bwaL44kWE32V14rOOEKyE9Vi2227Eb8UnDADT/JelVIwAR3q', 'Juan Dela Cruz', 'Staff', 1, '2026-09-07 01:15:22');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `consultations`
--
ALTER TABLE `consultations`
  ADD CONSTRAINT `consultations_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `laboratory`
--
ALTER TABLE `laboratory`
  ADD CONSTRAINT `fk_laboratory_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
