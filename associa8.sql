-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 11, 2026 at 01:34 PM
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
-- Database: `associa8`
--

-- --------------------------------------------------------

--
-- Table structure for table `acc-info`
--

CREATE TABLE `acc-info` (
  `id` int(30) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(150) NOT NULL,
  `otp` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `acc-info`
--

INSERT INTO `acc-info` (`id`, `username`, `password`, `otp`) VALUES
(1, 'ifeanyi', '$2y$10$y13pYMPQX9gxSV6Bi/e4.uE6FfVLZUjuzKicqwdO1G.ROkRLU99nS', '888888');

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_id` int(10) UNSIGNED NOT NULL,
  `action_type` varchar(50) DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin-info`
--

CREATE TABLE `admin-info` (
  `id` int(30) NOT NULL,
  `first-name` varchar(100) NOT NULL,
  `last-name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` int(20) NOT NULL,
  `job-title` varchar(200) NOT NULL,
  `role` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin-info`
--

INSERT INTO `admin-info` (`id`, `first-name`, `last-name`, `email`, `phone`, `job-title`, `role`) VALUES
(1, '23kj23kj2', 'k3j4jk3434', 'heekwe@sfkdfmd', 232443434, 'eerrtrt', 'super_admin'),
(2, '23kj23kj2', 'k3j4jk3434', 'heekwe@rad', 232443434, 'eerrtrt', 'super_admin'),
(3, 'ghhhhhh', 'hdfdkhf', 'eiojerk@keruier', 2147483647, 'eerrtrt', 'super_admin'),
(4, 'Ifeanyi', 'whejwje', 'eowoiwe@djksdl', 903232434, 'title', 'super_admin'),
(5, 'Ifeanyi', 'Ezeh', 'ei711283@gmail.com', 2147483647, 'doctor', 'manager'),
(9, 'Ifeanyi', 'Ezeh', 'eifeanyi320@gmail.com', 2147483647, 'doctor', 'admin'),
(14, 'Ifeanyi', 'eze', 'ifevnyi@yahoo.com', 777777777, 'gamer', 'admin'),
(16, 'uiewiwe', 'Ezeh', 'kshdfdkf@gmail.com', 2147483647, '4304dkjdfdkf', 'admin'),
(20, 'Ifeanyi', 'Ezeh', 'ggggg@yahoo.com', 2147483647, 'doctor', 'admin'),
(23, 'ffffffff', 'Ezeh', 'qqqqqqqqqqq@dsds', 2147483647, 'doctor', 'manager'),
(24, 'Ifeanyi', 'k3j4jk3434', 'luobikay@yahoo.com', 2147483647, 'ddddddddd', 'super_admin');

-- --------------------------------------------------------

--
-- Table structure for table `admissions`
--

CREATE TABLE `admissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `application_number` varchar(30) NOT NULL,
  `applicant_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `guarantor_name` varchar(100) NOT NULL,
  `guarantor_email` varchar(100) DEFAULT NULL,
  `guarantor_phone` varchar(20) DEFAULT NULL,
  `guarantor_relationship` varchar(50) DEFAULT NULL,
  `status` enum('pending','under_review','approved','rejected') DEFAULT 'pending',
  `reviewed_by` int(10) UNSIGNED DEFAULT NULL,
  `applied_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admissions`
--

INSERT INTO `admissions` (`id`, `application_number`, `applicant_name`, `email`, `phone`, `guarantor_name`, `guarantor_email`, `guarantor_phone`, `guarantor_relationship`, `status`, `reviewed_by`, `applied_at`) VALUES
(1, 'APP-2026-001', 'Chioma Ihugba', 'chiomaihugba@gmail.com', '04033333333', '', NULL, NULL, NULL, 'pending', NULL, '2026-09-06 18:58:00');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_logs`
--

CREATE TABLE `attendance_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `member_id` int(10) UNSIGNED DEFAULT NULL,
  `check_in` datetime NOT NULL,
  `check_out` datetime DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `status` enum('present','late','absent') DEFAULT 'present',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cbt_exams`
--

CREATE TABLE `cbt_exams` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(150) NOT NULL,
  `duration_minutes` int(11) NOT NULL DEFAULT 60,
  `pass_mark` int(11) NOT NULL DEFAULT 50,
  `status` enum('draft','active','closed') DEFAULT 'draft',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cbt_exams`
--

INSERT INTO `cbt_exams` (`id`, `title`, `duration_minutes`, `pass_mark`, `status`, `created_at`) VALUES
(1, 'Roll up', 60, 50, 'active', '2026-09-09 23:52:04');

-- --------------------------------------------------------

--
-- Table structure for table `cbt_questions`
--

CREATE TABLE `cbt_questions` (
  `id` int(10) UNSIGNED NOT NULL,
  `exam_id` int(10) UNSIGNED NOT NULL,
  `question_text` text NOT NULL,
  `option_a` varchar(255) NOT NULL,
  `option_b` varchar(255) NOT NULL,
  `option_c` varchar(255) NOT NULL,
  `option_d` varchar(255) NOT NULL,
  `option_e` varchar(255) DEFAULT NULL,
  `correct_option` enum('A','B','C','D','E') NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cbt_questions`
--

INSERT INTO `cbt_questions` (`id`, `exam_id`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `option_e`, `correct_option`, `created_at`) VALUES
(1, 1, 'Roll it up', 'Yes', 'No', 'IDGAF', 'No I\'m Gay', '', 'D', '2026-09-09 23:59:36');

-- --------------------------------------------------------

--
-- Table structure for table `cbt_results`
--

CREATE TABLE `cbt_results` (
  `id` int(10) UNSIGNED NOT NULL,
  `exam_id` int(10) UNSIGNED NOT NULL,
  `member_id` int(10) UNSIGNED NOT NULL,
  `score` int(11) NOT NULL DEFAULT 0,
  `status` enum('passed','failed') NOT NULL,
  `taken_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(150) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `file_size` varchar(20) DEFAULT NULL,
  `category` varchar(50) DEFAULT 'General',
  `uploaded_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `event_type` varchar(50) DEFAULT NULL,
  `event_date` date NOT NULL,
  `event_time` time DEFAULT NULL,
  `venue` varchar(255) DEFAULT NULL,
  `capacity` int(10) UNSIGNED DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_attendance`
--

CREATE TABLE `event_attendance` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_id` int(10) UNSIGNED NOT NULL,
  `event_id` int(10) UNSIGNED NOT NULL,
  `status` enum('attended','missed','excused') DEFAULT 'missed',
  `recorded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_rsvps`
--

CREATE TABLE `event_rsvps` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_id` int(10) UNSIGNED NOT NULL,
  `event_id` int(10) UNSIGNED NOT NULL,
  `status` enum('booked','waitlisted','cancelled') DEFAULT 'booked',
  `booked_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_transactions`
--

CREATE TABLE `finance_transactions` (
  `id` int(10) UNSIGNED NOT NULL,
  `reference` varchar(50) NOT NULL,
  `member_id` int(10) UNSIGNED DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `type` enum('membership_dues','admission_fee','event','other') NOT NULL,
  `status` enum('pending','successful','failed') DEFAULT 'pending',
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_code` varchar(30) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `code` varchar(15) NOT NULL,
  `zone_id` int(10) UNSIGNED NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `state_of_origin` varchar(100) DEFAULT NULL,
  `home_address` text DEFAULT NULL,
  `profile_photo_path` varchar(255) DEFAULT NULL,
  `tier` varchar(50) DEFAULT NULL,
  `membership_valid_until` date DEFAULT NULL,
  `emergency_contact_name` varchar(150) DEFAULT NULL,
  `emergency_contact_phone` varchar(30) DEFAULT NULL,
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `language` varchar(50) DEFAULT 'English (Nigeria)',
  `timezone` varchar(50) DEFAULT 'Africa/Lagos',
  `date_format` varchar(20) DEFAULT 'DD/MM/YYYY',
  `currency_display` varchar(20) DEFAULT 'Nigeria/Naira',
  `status` enum('active','pending','suspended') DEFAULT 'active',
  `joined_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `title_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `member_code`, `first_name`, `last_name`, `email`, `password`, `phone`, `code`, `zone_id`, `date_of_birth`, `occupation`, `state_of_origin`, `home_address`, `profile_photo_path`, `tier`, `membership_valid_until`, `emergency_contact_name`, `emergency_contact_phone`, `two_factor_enabled`, `language`, `timezone`, `date_format`, `currency_display`, `status`, `joined_date`, `created_at`, `updated_at`, `title_id`) VALUES
(1, 'ASC-001', 'Ezeh', 'Ifeanyi', 'ei711283@gmail.com', NULL, '07043277337', 'ASC-001', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'English (Nigeria)', 'Africa/Lagos', 'DD/MM/YYYY', 'Nigeria/Naira', 'suspended', '2026-09-06', '2026-09-06 18:08:33', '2026-09-06 18:25:19', 1),
(2, 'ASC-002', 'Daniel', 'Solomon', 'danielsolomon@gmail.com', NULL, '08099999999', 'ASC-002', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'English (Nigeria)', 'Africa/Lagos', 'DD/MM/YYYY', 'Nigeria/Naira', 'active', '2026-09-06', '2026-09-06 18:40:56', '2026-09-06 18:40:56', 2);

-- --------------------------------------------------------

--
-- Table structure for table `member_notification_preferences`
--

CREATE TABLE `member_notification_preferences` (
  `member_id` int(10) UNSIGNED NOT NULL,
  `email_due_reminder` tinyint(1) NOT NULL DEFAULT 1,
  `email_event_reminder` tinyint(1) NOT NULL DEFAULT 1,
  `email_general_announcement` tinyint(1) NOT NULL DEFAULT 1,
  `sms_reminder` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `member_sessions`
--

CREATE TABLE `member_sessions` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_id` int(10) UNSIGNED NOT NULL,
  `device_label` varchar(150) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `last_active_at` datetime DEFAULT current_timestamp(),
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `message_replies`
--

CREATE TABLE `message_replies` (
  `id` int(10) UNSIGNED NOT NULL,
  `thread_id` int(10) UNSIGNED NOT NULL,
  `member_id` int(10) UNSIGNED NOT NULL,
  `body` text NOT NULL,
  `sent_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `message_threads`
--

CREATE TABLE `message_threads` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_id` int(10) UNSIGNED NOT NULL,
  `sender_name` varchar(150) NOT NULL,
  `sender_avatar_initials` varchar(5) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `tag` varchar(50) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `sent_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_id` int(10) UNSIGNED NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` varchar(255) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `org-info`
--

CREATE TABLE `org-info` (
  `id` int(15) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` varchar(80) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` int(20) DEFAULT NULL,
  `country` varchar(50) NOT NULL,
  `state` varchar(40) NOT NULL,
  `pricing` varchar(50) NOT NULL,
  `total-members` int(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `org-info`
--

INSERT INTO `org-info` (`id`, `name`, `type`, `email`, `phone`, `country`, `state`, `pricing`, `total-members`) VALUES
(1, 'mdnsddsddf', 'church', 'sdfdf@eereref', 2147483647, 'erk/ejrjker', 'state1', 'professional', 2),
(2, 'mdnsddsddf', 'church', 'sdfd@gmail.com', 2147483647, 'erk/ejrjker', 'state1', 'professional', 2),
(3, 'ksdhskdshkd', 'association', 'errrrrrrr@kjfdf', 2147483647, 'nigeria', 'state2', 'professional', 3),
(4, 'eiueureur', 'association', 'sjkeukjwe@gma.com', 2147483647, 'nigeria', 'state2', 'professional', 6),
(5, 'games', 'club', 'fffffffff@gmail.com', 2147483647, 'Nigeria', 'state3', 'professional', 1031),
(10, 'iukerjkher', 'ngo', 'eiamd@gsnd', 9999, 'jsdjksjkd', 'state2', 'basic', 3),
(14, 'ddddddddd', 'association', 'ifevnyi@yahoo.com', 2147483647, 'Kenya', 'state3', 'elite', 3),
(16, 'new', 'ngo', 'rat@gmail.com', 2147483647, 'Kenya', 'state2', 'professional', 2),
(20, 'ksdhskdshkd', 'ngo', 'ei711283@gmail.com', 2147483647, 'oilkerljerjler', 'state1', 'professional', 2),
(23, 'mdnsddsddf', 'ngo', 'eifeanyi320@gmail.com', 2147483647, 'Botswana', 'state2', 'elite', 5),
(24, 'mdnsddsddf', 'church', 'luobikay@yahoo.com', 2147483647, 'Mozambique', 'state2', 'professional', 9);

-- --------------------------------------------------------

--
-- Table structure for table `portal_settings`
--

CREATE TABLE `portal_settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `portal_key` varchar(30) NOT NULL,
  `start_at` datetime DEFAULT NULL,
  `end_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `portal_settings`
--

INSERT INTO `portal_settings` (`id`, `portal_key`, `start_at`, `end_at`, `updated_at`) VALUES
(1, 'admission', '2026-09-30 19:49:00', '2026-10-09 19:49:00', '2026-09-06 19:58:10'),
(2, 'cbt', NULL, NULL, '2026-09-06 19:48:50');

-- --------------------------------------------------------

--
-- Table structure for table `subzones`
--

CREATE TABLE `subzones` (
  `id` int(10) UNSIGNED NOT NULL,
  `zone_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `coordinator_name` varchar(150) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subzones`
--

INSERT INTO `subzones` (`id`, `zone_id`, `name`, `coordinator_name`, `created_at`) VALUES
(1, 1, 'New york city', 'Barry Allen', '2026-09-06 12:30:19');

-- --------------------------------------------------------

--
-- Table structure for table `suspensions`
--

CREATE TABLE `suspensions` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_id` int(10) UNSIGNED NOT NULL,
  `reason` varchar(255) NOT NULL,
  `action_type` enum('suspension','reinstatement') NOT NULL DEFAULT 'suspension',
  `status` enum('active','under_review','completed') NOT NULL DEFAULT 'active',
  `action_date` date NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suspensions`
--

INSERT INTO `suspensions` (`id`, `member_id`, `reason`, `action_type`, `status`, `action_date`, `created_at`) VALUES
(1, 1, 'yowa', 'suspension', 'active', '2026-09-06', '2026-09-06 18:25:19');

-- --------------------------------------------------------

--
-- Table structure for table `titles`
--

CREATE TABLE `titles` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(100) NOT NULL,
  `level` tinyint(3) UNSIGNED NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `titles`
--

INSERT INTO `titles` (`id`, `title`, `level`, `description`, `created_at`) VALUES
(1, 'Odogwu', 1, 'LOL', '2026-09-03 12:26:42'),
(2, 'Scorpion', 2, 'number 2 in command, number 1 assassinator', '2026-09-03 12:42:37');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','manager','staff') NOT NULL DEFAULT 'staff',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_module_permissions`
--

CREATE TABLE `user_module_permissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `module_key` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `zones`
--

CREATE TABLE `zones` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `coordinator_name` varchar(150) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `zones`
--

INSERT INTO `zones` (`id`, `name`, `coordinator_name`, `created_at`) VALUES
(1, 'U.S.A', 'Ezeh Ifeanyi', '2026-09-06 10:06:47');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `acc-info`
--
ALTER TABLE `acc-info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_username` (`username`);

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `admin-info`
--
ALTER TABLE `admin-info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `admissions`
--
ALTER TABLE `admissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `application_number` (`application_number`),
  ADD KEY `reviewed_by` (`reviewed_by`);

--
-- Indexes for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `cbt_exams`
--
ALTER TABLE `cbt_exams`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cbt_questions`
--
ALTER TABLE `cbt_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exam_id` (`exam_id`);

--
-- Indexes for table `cbt_results`
--
ALTER TABLE `cbt_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exam_id` (`exam_id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `event_attendance`
--
ALTER TABLE `event_attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_member_event` (`member_id`,`event_id`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `event_rsvps`
--
ALTER TABLE `event_rsvps`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_member_event_rsvp` (`member_id`,`event_id`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `finance_transactions`
--
ALTER TABLE `finance_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference` (`reference`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `member_code` (`member_code`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `members_ibfk_zone` (`zone_id`),
  ADD KEY `title_id` (`title_id`);

--
-- Indexes for table `member_notification_preferences`
--
ALTER TABLE `member_notification_preferences`
  ADD PRIMARY KEY (`member_id`);

--
-- Indexes for table `member_sessions`
--
ALTER TABLE `member_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `message_replies`
--
ALTER TABLE `message_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `thread_id` (`thread_id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `message_threads`
--
ALTER TABLE `message_threads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `org-info`
--
ALTER TABLE `org-info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `portal_settings`
--
ALTER TABLE `portal_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `portal_key` (`portal_key`);

--
-- Indexes for table `subzones`
--
ALTER TABLE `subzones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `zone_subzone_name` (`zone_id`,`name`),
  ADD KEY `zone_id` (`zone_id`);

--
-- Indexes for table `suspensions`
--
ALTER TABLE `suspensions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `titles`
--
ALTER TABLE `titles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `level` (`level`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_module_permissions`
--
ALTER TABLE `user_module_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_module_unique` (`user_id`,`module_key`);

--
-- Indexes for table `zones`
--
ALTER TABLE `zones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `acc-info`
--
ALTER TABLE `acc-info`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin-info`
--
ALTER TABLE `admin-info`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `admissions`
--
ALTER TABLE `admissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cbt_exams`
--
ALTER TABLE `cbt_exams`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cbt_questions`
--
ALTER TABLE `cbt_questions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cbt_results`
--
ALTER TABLE `cbt_results`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_attendance`
--
ALTER TABLE `event_attendance`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_rsvps`
--
ALTER TABLE `event_rsvps`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `finance_transactions`
--
ALTER TABLE `finance_transactions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `member_sessions`
--
ALTER TABLE `member_sessions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `message_replies`
--
ALTER TABLE `message_replies`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `message_threads`
--
ALTER TABLE `message_threads`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `org-info`
--
ALTER TABLE `org-info`
  MODIFY `id` int(15) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `portal_settings`
--
ALTER TABLE `portal_settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `subzones`
--
ALTER TABLE `subzones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `suspensions`
--
ALTER TABLE `suspensions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `titles`
--
ALTER TABLE `titles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_module_permissions`
--
ALTER TABLE `user_module_permissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `zones`
--
ALTER TABLE `zones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `admissions`
--
ALTER TABLE `admissions`
  ADD CONSTRAINT `admissions_ibfk_1` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  ADD CONSTRAINT `attendance_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_logs_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cbt_questions`
--
ALTER TABLE `cbt_questions`
  ADD CONSTRAINT `cbt_questions_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `cbt_exams` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cbt_results`
--
ALTER TABLE `cbt_results`
  ADD CONSTRAINT `cbt_results_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `cbt_exams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cbt_results_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `event_attendance`
--
ALTER TABLE `event_attendance`
  ADD CONSTRAINT `event_attendance_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_attendance_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_rsvps`
--
ALTER TABLE `event_rsvps`
  ADD CONSTRAINT `event_rsvps_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_rsvps_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `finance_transactions`
--
ALTER TABLE `finance_transactions`
  ADD CONSTRAINT `finance_transactions_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `members`
--
ALTER TABLE `members`
  ADD CONSTRAINT `members_ibfk_title` FOREIGN KEY (`title_id`) REFERENCES `titles` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `members_ibfk_zone` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`id`);

--
-- Constraints for table `member_notification_preferences`
--
ALTER TABLE `member_notification_preferences`
  ADD CONSTRAINT `member_notification_preferences_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `member_sessions`
--
ALTER TABLE `member_sessions`
  ADD CONSTRAINT `member_sessions_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `message_replies`
--
ALTER TABLE `message_replies`
  ADD CONSTRAINT `message_replies_ibfk_1` FOREIGN KEY (`thread_id`) REFERENCES `message_threads` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `message_replies_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `message_threads`
--
ALTER TABLE `message_threads`
  ADD CONSTRAINT `message_threads_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subzones`
--
ALTER TABLE `subzones`
  ADD CONSTRAINT `subzones_ibfk_zone` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `suspensions`
--
ALTER TABLE `suspensions`
  ADD CONSTRAINT `suspensions_ibfk_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_module_permissions`
--
ALTER TABLE `user_module_permissions`
  ADD CONSTRAINT `user_module_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
