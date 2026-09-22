-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: db:3306
-- Generation Time: Sep 21, 2026 at 03:46 PM
-- Server version: 8.0.36
-- PHP Version: 8.2.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ecardnai_Klinhr`
--

-- --------------------------------------------------------

--
-- Table structure for table `assessment`
--

CREATE TABLE `assessment` (
  `id` int NOT NULL,
  `company_code` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `job_id` int NOT NULL,
  `assessment_name` varchar(100) NOT NULL,
  `no_of_question` varchar(50) NOT NULL,
  `pass_mark` varchar(50) NOT NULL,
  `duration` varchar(50) NOT NULL,
  `category` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `assessment`
--

INSERT INTO `assessment` (`id`, `company_code`, `job_id`, `assessment_name`, `no_of_question`, `pass_mark`, `duration`, `category`) VALUES
(12, NULL, 8, 'Service Reporting Engineer Analyst', '10', '70', '30', 'Verbal Reasoning'),
(14, NULL, 1, 'Graphic Designer', '5', '50', '5', 'Critical Thinking'),
(21, NULL, 3, 'Teller', '15', '50', '20', 'Critical Thinking,Mathematics'),
(24, NULL, 4, 'Verification Analyst', '30', '70', '20', 'Numerical Reasoning'),
(26, NULL, 9, 'Operation Officer', '10', '60', '9', 'Numerical Reasoning,Verbal Reasoning'),
(27, NULL, 3, 'Web Developer', '10', '50', '10', 'Numerical Reasoning,Verbal Reasoning'),
(28, NULL, 14, 'Human Resources Transformational Consultant', '20', '80', '30', 'Numerical Reasoning,Verbal Reasoning'),
(29, NULL, 16, 'General', '30', '70', '25', 'Computer Compentencies,Numerical Reasoning,Verbal Reasoning'),
(30, NULL, 10, 'real estate manager', '5', '50', '30', 'Numerical Reasoning'),
(33, NULL, 3, 'Web Developer', '3', '10', '10', 'Verbal Reasoning'),
(34, NULL, 2, 'Devops Consultant', '3', '10', '10', 'logicall Reasoning'),
(35, 'ALEDOY', 2, 'Devops Consultant', '4', '10', '30', 'Verbal Reasoning');

-- --------------------------------------------------------

--
-- Table structure for table `exam_result`
--

CREATE TABLE `exam_result` (
  `id` int NOT NULL,
  `company_code` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `fname` varchar(50) NOT NULL,
  `lname` varchar(50) NOT NULL,
  `email` varchar(80) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `exam_code` varchar(20) NOT NULL,
  `candidate_id` varchar(10) NOT NULL,
  `job_applied_id` varchar(10) NOT NULL,
  `job_id` int DEFAULT NULL,
  `job_title` varchar(100) DEFAULT NULL,
  `no_questions` varchar(20) DEFAULT NULL,
  `ans_1` varchar(200) DEFAULT NULL,
  `ans_2` varchar(200) DEFAULT NULL,
  `ans_3` varchar(200) DEFAULT NULL,
  `ans_4` varchar(200) DEFAULT NULL,
  `ans_5` varchar(200) DEFAULT NULL,
  `ans_6` varchar(200) DEFAULT NULL,
  `ans_7` varchar(200) DEFAULT NULL,
  `ans_8` varchar(200) DEFAULT NULL,
  `ans_9` varchar(200) DEFAULT NULL,
  `ans_10` varchar(200) DEFAULT NULL,
  `ans_11` varchar(200) DEFAULT NULL,
  `ans_12` varchar(200) DEFAULT NULL,
  `ans_13` varchar(200) DEFAULT NULL,
  `ans_14` varchar(200) DEFAULT NULL,
  `ans_15` varchar(200) DEFAULT NULL,
  `ans_16` varchar(200) DEFAULT NULL,
  `ans_17` varchar(200) DEFAULT NULL,
  `ans_18` varchar(200) DEFAULT NULL,
  `ans_19` varchar(200) DEFAULT NULL,
  `ans_20` varchar(200) DEFAULT NULL,
  `ans_21` varchar(200) DEFAULT NULL,
  `ans_22` varchar(200) DEFAULT NULL,
  `ans_23` varchar(200) DEFAULT NULL,
  `ans_24` varchar(200) DEFAULT NULL,
  `ans_25` varchar(200) DEFAULT NULL,
  `ans_26` varchar(200) DEFAULT NULL,
  `ans_27` varchar(200) DEFAULT NULL,
  `ans_28` varchar(200) DEFAULT NULL,
  `ans_29` varchar(200) DEFAULT NULL,
  `ans_30` varchar(200) DEFAULT NULL,
  `ans_31` varchar(200) DEFAULT NULL,
  `ans_32` varchar(200) DEFAULT NULL,
  `ans_33` varchar(200) DEFAULT NULL,
  `ans_34` varchar(200) DEFAULT NULL,
  `ans_35` varchar(200) DEFAULT NULL,
  `ans_36` varchar(200) DEFAULT NULL,
  `ans_37` varchar(200) DEFAULT NULL,
  `ans_38` varchar(200) DEFAULT NULL,
  `ans_39` varchar(200) DEFAULT NULL,
  `ans_40` varchar(200) DEFAULT NULL,
  `ans_41` varchar(200) DEFAULT NULL,
  `ans_42` varchar(200) DEFAULT NULL,
  `ans_43` varchar(200) DEFAULT NULL,
  `ans_44` varchar(200) DEFAULT NULL,
  `ans_45` varchar(200) DEFAULT NULL,
  `ans_46` varchar(200) DEFAULT NULL,
  `ans_47` varchar(200) DEFAULT NULL,
  `ans_48` varchar(200) DEFAULT NULL,
  `ans_49` varchar(200) DEFAULT NULL,
  `ans_50` varchar(200) DEFAULT NULL,
  `total_score` varchar(20) DEFAULT NULL,
  `average` varchar(20) DEFAULT NULL,
  `remark` varchar(50) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `archieved` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `exam_result`
--

INSERT INTO `exam_result` (`id`, `company_code`, `fname`, `lname`, `email`, `phone`, `exam_code`, `candidate_id`, `job_applied_id`, `job_id`, `job_title`, `no_questions`, `ans_1`, `ans_2`, `ans_3`, `ans_4`, `ans_5`, `ans_6`, `ans_7`, `ans_8`, `ans_9`, `ans_10`, `ans_11`, `ans_12`, `ans_13`, `ans_14`, `ans_15`, `ans_16`, `ans_17`, `ans_18`, `ans_19`, `ans_20`, `ans_21`, `ans_22`, `ans_23`, `ans_24`, `ans_25`, `ans_26`, `ans_27`, `ans_28`, `ans_29`, `ans_30`, `ans_31`, `ans_32`, `ans_33`, `ans_34`, `ans_35`, `ans_36`, `ans_37`, `ans_38`, `ans_39`, `ans_40`, `ans_41`, `ans_42`, `ans_43`, `ans_44`, `ans_45`, `ans_46`, `ans_47`, `ans_48`, `ans_49`, `ans_50`, `total_score`, `average`, `remark`, `status`, `start_time`, `end_time`, `archieved`) VALUES
(1, NULL, 'WILLIAMS', 'AMUBIAYA', 'williams.amubiaya@kennediaconsulting.net', '+2348095841862', '147989', '14', '23', 9, 'Operation Officer', '10', '50=A', '44=A', '75=C', '84=B', '72=A', '74=B', '88=B', '80=B', '39=A', '56=C', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '3', '30', 'Failed', 'finished', '2022-08-12 11:00:55', '2022-08-12 11:09:55', NULL),
(2, NULL, 'Slimmz', 'Sage', 'muyiwa@aledoy.com', '+2348139662685', '157815', '15', '30', 3, 'Web Developer', '15', '54=D', '', '', '', '46=A', '45=B', '87=C', '64=B', '73=B', '69=A', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', '7', 'Failed', 'finished', '2022-09-01 16:56:03', '2022-09-01 17:16:03', NULL),
(3, NULL, 'Mayowa', 'OLANREWAJU', 'adelusumbo@gmail.com', '+0(811)847-90-43', '848120', '8', '60', 3, 'Teller', '15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'NAN', 'Passed', 'finished', '2024-01-10 18:06:15', '2024-01-10 18:26:15', NULL),
(4, NULL, 'Inemesit ', 'Jones', 'thedesignerq@gmail.com', '08150477523', '163022', '16', '33', 3, 'Web Developer', '15', '47=A', '55=A', '45=B', '68=B', '50=B', '62=A', '87=B', '41=B', '81=A', '48=A', '44=A', '', '67=B', '64=A', '82=B', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '4', '27', 'Failed', 'finished', '2024-01-24 15:33:12', '2024-01-24 15:53:12', NULL),
(5, NULL, 'Lu', 'Abikoye', 'oabikay@yahoo.com', '08038848848', '137679', '13', '37', 3, 'Web Developer', '15', '78=D', '59=A', '48=A', '47=A', '72=A', '49=A', '87=B', '44=A', '66=A', '65=B', '52=B', '67=A', '38=A', '56=A', '81=C', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '4', '27', 'Failed', 'finished', '2024-07-20 14:25:57', '2024-07-20 14:45:57', NULL),
(12, NULL, 'Osareniro', 'Walter', 'sacaffsd@gmail.comr', '08074277621', '127553', '127', '85', 32, 'Executive Manger', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'started', '2024-10-08 08:30:38', '1970-01-01 01:00:00', NULL),
(14, NULL, 'Bayo', 'Atulaje', 'luabikowqeye@yahoo.com', '0804767744', '134927', '134', '93', 32, 'Executive Manger', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'started', '2024-10-08 14:23:21', '1970-01-01 01:00:00', NULL),
(15, NULL, 'Bayo', 'Atulaje', 'luabikoye@aledoy.com', '0804767744', '135209', '135', '94', 32, 'Executive Manger', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'started', '2024-10-08 14:28:00', '1970-01-01 01:00:00', NULL),
(16, NULL, 'OLUMIDE', 'ABIKOYE', 'hugrwwYTqui@aledoy.com', '08023443581', '132202', '132', '91', 32, 'Executive Manger', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'started', NULL, NULL, NULL),
(17, NULL, 'Mayowa', 'OLANREWAJU', 'adelusumbo@gmail.com', '+0(811)847-90-43', '144797', '144', '26', 3, 'Web Developer', '15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'started', '2024-12-17 13:35:01', '2024-12-17 13:55:01', NULL),
(22, NULL, 'Olasunmbo', 'Delu', 'natiqakanji@gmail.com', '8118479043', '150487', '150', '34', 10, 'real estate manager', '10', '45=B', '51=B', '54=A', '47=B', '56=B', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2', '40', 'Passed', 'finished', NULL, NULL, NULL),
(23, NULL, 'mayowa', 'delu', 'deluolasunmbo4@gmail.com', '0908567432', '151155', '151', '36', 10, 'real estate manager', '5', '52=A', '55=A', '47=B', '46=B', '40=B', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '3', '60', 'Passed', 'finished', '2025-01-08 14:03:31', '2025-01-08 14:33:31', NULL),
(24, NULL, 'mayowa', 'delu', 'richtech1589@gmail.com', '8118479043', '500748', '5007', '5009', 3, 'Web Developer', '15', '', '50=A', '55=C', '86=C', '39=B', '67=D', '57=C', '76=B', '48=C', '70=C', '82=C', '65=C', '45=C', '60=D', '85=C', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', '7', 'Passed', 'finished', '2025-08-19 16:19:23', '2025-08-19 16:39:23', NULL),
(25, NULL, 'mayowa', 'delu', 'richtech1589@gmail.com', '8118479043', '500744', '5007', '5009', 3, 'Web Developer', '15', '48=A', '54=D', '75=C', '76=D', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', '7', 'Passed', 'finished', '2025-08-21 10:51:00', '2025-08-21 11:11:00', NULL),
(29, NULL, 'akanji', 'delu', 'mayowadelu@gmail.com', '08118479043', '152829', '152', '5011', 16, 'General', '30', '93=C', '76=B', '38=A', '87=C', '49=C', '84=D', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2', '7', 'Passed', 'finished', '2026-09-21 16:24:35', '2026-09-21 16:49:35', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `participant`
--

CREATE TABLE `participant` (
  `id` int NOT NULL,
  `company_code` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `candidate_id` varchar(10) DEFAULT NULL,
  `job_applied_id` varchar(10) DEFAULT NULL,
  `firstname` varchar(80) DEFAULT NULL,
  `lastname` varchar(80) DEFAULT NULL,
  `email` varchar(80) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `job_id` varchar(10) DEFAULT NULL,
  `job_title` varchar(100) DEFAULT NULL,
  `exam_code` varchar(20) DEFAULT NULL,
  `expire_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `participant`
--

INSERT INTO `participant` (`id`, `company_code`, `candidate_id`, `job_applied_id`, `firstname`, `lastname`, `email`, `phone`, `job_id`, `job_title`, `exam_code`, `expire_date`) VALUES
(1, '', '4', '20', 'John', 'Akerele', 'john@aledoy.com', '08130619499', '37', 'Teller', '490529', '2022-06-09 10:18:18'),
(2, '', '3', '18', 'Samuel', 'Akerele', 'akerelejohn6@gmail.com', '08130619499', '37', 'Graphic Designer', '357815', '2022-06-09 10:18:18'),
(3, '', '4', '15', 'John', 'Akerele', 'akerelejohn6@gmail.com', '08130619499', '4', 'Verification Analyst', '419810', '2022-08-06 10:35:08'),
(4, '', '13', '18', 'Lu', 'Abikoye', 'oabikay@yahoo.com', '08038848848', '10', 'Head Chef', '134528', '2022-08-11 10:50:47'),
(5, '', '13', '20', 'Bayo', 'Atere', 'oabikay@yahoo.com', '0371986', '1', 'Graphic Designer', '133976', '2022-08-11 10:56:07'),
(6, '', '14', '23', 'WILLIAMS', 'AMUBIAYA', 'williams.amubiaya@kennediaconsulting.net', '+2348095841862', '9', 'Operation Officer', '147989', '2022-08-14 10:57:52'),
(7, '', '15', '30', 'Slimmz', 'Sage', 'muyiwa@aledoy.com', '+2348139662685', '3', 'Web Developer', '157815', '2022-09-03 04:52:14'),
(8, '', '13', '36', 'Lu', 'Abikoye', 'oabikay@yahoo.com', '08038848848', '14', 'Human Resources Transformational Consultant', '132103', '2022-09-16 11:17:14'),
(9, '', '69', '56', 'Joseph', 'Betiku', 'omowale81@gmail.com', '+2349026639461', '9', 'Operation Officer', '696351', '2023-07-15 03:55:12'),
(10, '', '8', '60', 'Mayowa', 'OLANREWAJU', 'adelusumbo@gmail.com', '+0(811)847-90-43', '3', 'Teller', '848120', '2024-01-11 01:16:00'),
(11, '', '32', '45', 'Omotoyosi', 'Oahiyemi', 'hardebeauty56@gmail.com', '08100807149', '9', 'Operation Officer', '328000', '2024-01-26 03:22:59'),
(12, '', '30', '42', 'Joseph', 'Atodo', 'atodojosephokoliko@gmail.com', '08064857433', '9', 'Operation Officer', '307589', '2024-01-26 03:22:59'),
(13, '', '16', '33', 'Inemesit ', 'Jones', 'thedesignerq@gmail.com', '08150477523', '3', 'Web Developer', '163022', '2024-01-26 03:25:28'),
(14, '', '67', '54', 'Kehinde ', 'Samuel ', 'kennysammy3@gmail.com', '08162443135 ', '16', 'SALES EXECUTIVE', '679869', '2024-07-22 02:08:29'),
(15, '', '13', '37', 'Lu', 'Abikoye', 'oabikay@yahoo.com', '08038848848', '3', 'Web Developer', '137679', '2024-07-22 02:20:05'),
(16, '', '116', '74', 'Osareniro', 'Walter', 'walternirertgo11@gmail.com', '08074277621', '32', 'Executive Manger', '116132', '2024-10-10 07:58:15'),
(17, '', '119', '77', 'Osareniro', 'Walter', 'waltqeffrwernirertgo11@gmail.com', '08074277621', '32', 'Executive Manger', '119980', '2024-10-10 08:06:42'),
(18, '', '120', '78', 'Osareniro', 'Walter', 'sfffrwernirertgo11@gmail.com', '08074277621', '32', 'Executive Manger', '120799', '2024-10-10 08:07:39'),
(19, '', '121', '79', 'Osareniro', 'Walter', 'sfffrwernirerrrrtgo11@gmail.com', '08074277621', '32', 'Executive Manger', '121146', '2024-10-10 08:09:06'),
(20, '', '122', '80', 'Osareniro', 'Walter', 'sacas@gmail.com', '08074277621', '32', 'Executive Manger', '122648', '2024-10-10 08:10:11'),
(21, '', '123', '81', 'Osareniro', 'Walter', 'sacasd@gmail.com', '08074277621', '32', 'Executive Manger', '123240', '2024-10-10 08:11:30'),
(22, '', '125', '83', 'Osareniro', 'Walter', 'sacafsd@gmail.comrr', '08074277621', '32', 'Executive Manger', '125304', '2024-10-10 08:14:12'),
(23, '', '126', '84', 'Osareniro', 'Walter', 'sacafsd@gmail.comr', '08074277621', '32', 'Executive Manger', '126182', '2024-10-10 08:15:51'),
(24, '', '127', '85', 'Osareniro', 'Walter', 'sacaffsd@gmail.comr', '08074277621', '32', 'Executive Manger', '127553', '2024-10-10 08:17:13'),
(25, '', '130', '89', 'OLUMIDE', 'ABIKOYE', 'hugrvvwwqui@aledoy.com', '08023443581', '32', 'Executive Manger', '130850', '2024-10-10 12:36:09'),
(26, '', '131', '90', 'OLUMIDE', 'ABIKOYE', 'shugrwwqui@aledoy.com', '08023443581', '32', 'Executive Manger', '131590', '2024-10-10 12:43:00'),
(27, '', '132', '91', 'OLUMIDE', 'ABIKOYE', 'hugrwwYTqui@aledoy.com', '08023443581', '32', 'Executive Manger', '132202', '2024-10-10 01:18:20'),
(28, '', '134', '93', 'Bayo', 'Atulaje', 'luabikowqeye@yahoo.com', '0804767744', '32', 'Executive Manger', '134927', '2024-10-10 02:21:57'),
(29, '', '135', '94', 'Bayo', 'Atulaje', 'luabikoye@aledoy.com', '0804767744', '32', 'Executive Manger', '135209', '2024-10-10 02:26:43'),
(30, '', '144', '26', 'mayowa', 'delu', 'adelusumbo@gmail.com', '08118479043', '3', 'Web Developer', '144797', '2024-12-19 12:55:30'),
(33, '', '150', '34', 'Olasunmbo', 'Delu', 'natiqakanji@gmail.com', '8118479043', '10', 'real estate manager', '150487', '2025-01-09 02:37:58'),
(34, '', '151', '36', 'mayowa', 'delu', 'deluolasunmbo4@gmail.com', '0908567432', '10', 'real estate manager', '151155', '2025-01-10 01:58:13'),
(35, '', '152', '39', 'akanji', 'delu', 'mayowadelu@gmail.com', '08118479043', '3', 'Web Developer', '152374', '2025-02-12 01:31:19'),
(36, NULL, '5005', '5007', 'mayowa', 'delu', 'mayowadelu@gmail.com111', '08118479043', '3', 'Teller', '219974', '2025-06-07 04:05:57'),
(37, NULL, '5007', '5009', 'mayowa', 'delu', 'richtech1589@gmail.com', '8118479043', '3', 'Web Developer', '500748', '2025-08-21 04:14:51'),
(38, NULL, '5007', '5009', 'mayowa', 'delu', 'richtech1589@gmail.com', '8118479043', '3', 'Web Developer', '500744', '2025-08-23 10:49:25'),
(39, NULL, '152', '5011', 'mayowa', 'delu', 'mayowadelu@gmail.com', '08118479043', '16', 'General', '152829', '2026-10-01 03:34:45'),
(40, NULL, '5011', '5013', 'mayowa', 'delu', 'adelusumbo@icloud.com', '8118479043', '15', 'Head, Internal Audit & Control', '501150', '2026-09-30 02:56:47');

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` int NOT NULL,
  `company_code` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `category` varchar(100) NOT NULL,
  `type` varchar(50) NOT NULL,
  `question` text NOT NULL,
  `media` varchar(200) NOT NULL,
  `option_a` varchar(200) DEFAULT NULL,
  `option_b` varchar(200) DEFAULT NULL,
  `option_c` varchar(200) DEFAULT NULL,
  `option_d` varchar(200) DEFAULT NULL,
  `option_e` varchar(200) DEFAULT NULL,
  `answer` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`id`, `company_code`, `category`, `type`, `question`, `media`, `option_a`, `option_b`, `option_c`, `option_d`, `option_e`, `answer`) VALUES
(38, NULL, 'Numerical Reasoning', 'single', '<p>In percentages, how much more USD will one get when exchanging 1,223,500 JPY compared to exchanging 9,750 EUR?</p>\r\n', 'q2.png', '13.3%', '88.3%', '117.7%', '35.5%', '', 'A'),
(39, NULL, 'Numerical Reasoning', 'single', '<p><strong>Approximately, how much did Company X pay for two-fifths of June&#39;s available storage volume, in GBP?</strong></p>\r\n', 'q3.png', 'ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â£181,635', 'ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â£408,678', 'ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â£363,269', 'ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â£479,720', '', 'B'),
(40, NULL, 'Numerical Reasoning', 'single', '<p><em>Due to an increase in taxes on electronic devices, the price of a 46&rdquo; LED flat TV screen has increased to &pound;845, which is 30% increase of the original price.</em></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p><strong>What was the original price of the TV prior to the increase?</strong></p>\r\n', '', 'ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â£515.45', 'ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â£591.50', 'ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â£650', 'ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â£676', 'ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â£768.95', 'C'),
(41, NULL, 'Numerical Reasoning', 'single', '<p><em>Scott and Rachel are enthusiastic car collectors. The cars they own are either German or Japanese made cars.</em></p>\r\n\r\n<p><em>The German to Japanese ratio in Scott&#39;s collection is 5:2 in favour of the Germans.&nbsp;</em><br />\r\n<em>The German to Japanese ratio in Rachel&#39;s collection is 4:3 in favour of the Germans</em><br />\r\n<em>The number of Japanese cars Scott owns is identical to the number of Japanese cars Rachel owns.</em></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p><strong>What is the ratio between the&nbsp;</strong><em><strong>total amount of cars</strong></em><strong>&nbsp;(German and Japanese) Scott has and the&nbsp;total amount of cars Rachel has?</strong></p>\r\n', '', '15:8', '9:7', '1:1', '3:2', '', 'D'),
(43, NULL, 'Numerical Reasoning', 'single', '<p><em>Scott and Rachel are enthusiastic car collectors. The cars they own are either German or Japanese made cars.</em></p>\r\n\r\n<p><em>The German to Japanese ratio in Scott&#39;s collection is 5:2 in favour of the Germans.&nbsp;</em><br />\r\n<em>The German to Japanese ratio in Rachel&#39;s collection is 4:3 in favour of the Germans</em><br />\r\n<em>The number of Japanese cars Scott owns is identical to the number of Japanese cars Rachel owns.</em></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p><strong>What is the ratio between the&nbsp;</strong><em><strong>total amount of cars</strong></em><strong>&nbsp;(German and Japanese) Scott has and the&nbsp;total amount of cars Rachel has?</strong></p>\r\n', '', '15:8', '9:7', '1:1', '3:2', '', 'D'),
(44, NULL, 'Numerical Reasoning', 'single', '<p><em>A cell phone company offers insurance that covers cases of theft and accidental water damage. According to its policy, the insurance pays 60% or 50%, respectively, of the value of the phone after a $30 deductible. This means the client pays the first $30, after which the insurance pays 60% of the remaining amount in the case of a theft and 50% in the case of accidental water damage.</em>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p><strong>How much will a client pay to get an identical new phone, if her cell phone, worth $1,080, was stolen?</strong></p>\r\n', '', '$420', '$450', '$464', '$660', '', 'B'),
(45, NULL, 'Numerical Reasoning', 'single', '<p>One-tenth of one bag of toys weighs the same as one-seventh of one bag of marbles.</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p><strong>What is the ratio of the weight of 2 bags of toys to 3 bags of marbles?</strong><br />\r\n&nbsp;</p>\r\n', '', '7:15', '20:21', '21:20', '3:2', '', 'B'),
(46, NULL, 'Numerical Reasoning', 'single', '<p>In year 3, how much more did Germany spend on computer imports than Italy?</p>\r\n', 'q4.png', '650 million', '700 million', '750 million', '800 million', '', 'B'),
(47, NULL, 'Numerical Reasoning', 'single', '<p>If the amount spent on computer imports&nbsp;into&nbsp;the United Kingdom in the fifth year was 20% lower than in the fourth year, how much was spent in the fifth year?</p>\r\n', 'q5.png', '1,080 million', '1,120 million', '1,160 million', '1,220 million', '', 'B'),
(48, NULL, 'Numerical Reasoning', 'single', '<p>Which newspaper was read by a higher percentage of women than men in the third year?</p>\r\n', 'screenshot (11).png', 'The Tribune', 'The Herald', 'Daily News', 'Daily Echo', '', 'B'),
(49, NULL, 'Numerical Reasoning', 'single', '<p>From the table in question 11 above, what was the combined readership of the Daily Chronicle, Daily Echo and Tribune in the first year?</p>\r\n', '', '10.6', '8.4', '9.5', '12.2', '', 'C'),
(50, NULL, 'Numerical Reasoning', 'single', '<p>Quantity A: The price of 2 kilograms of sugar at 46 pounds per kilogram.</p>\r\n\r\n<p>Quantity B: The price of 3 kilograms of sugar at 31 pounds per kilogram.</p>\r\n', '', 'Quantity A is greater than quantity B.', 'Quantity B is greater than quantity A.', 'The two quantities are equal.', 'The relationship between the two quantities cannot be determined based on the information provided.', '', 'B'),
(51, NULL, 'Numerical Reasoning', 'single', '<p>The ratio 5:4 expressed as a percent equal</p>\r\n', '', '12.5%', '40%', '80%', '125%', '', 'D'),
(52, NULL, 'Numerical Reasoning', 'single', '<p>Shobha&#39;s Mathematics Test had 75 problems i.e. 10 arithmetic, 30 algebra and 35 geometry problems. Although she answered 70% of the arithmetic, 40% of the algebra and 60% 0f the geometry problems correctly, she did not pass the test because she got less than 60% of the problems right. How many more questions she would have needed to answer correctly to earn a 60% passing grade?</p>\r\n', '', '5', '10', '15', '20', '', 'A'),
(53, NULL, 'Numerical Reasoning', 'single', '<p>Three candidates contested an election and received 1136, 7636 and 11628 votes respectively. What percentage of the total votes did the winning candidate get?</p>\r\n', '', '45%', '57%', '60%', '65%', '', 'B'),
(54, NULL, 'Numerical Reasoning', 'single', '<p>In a competitive examination in State A, 6% candidates got selected from the total appeared candidates. State B had an equal number of candidates appeared and 7% candidates got selected with 80 more candidates got selected than A. What was the number of candidates appeared from each State?</p>\r\n', '', '7600', '8000', '8400', 'Data inadequate', '', 'B'),
(55, NULL, 'Numerical Reasoning', 'single', '<p>860% of 50 + 50% of 860 =?</p>\r\n', '', '430', '516', '660', '860', '', 'D'),
(56, NULL, 'Numerical Reasoning', 'single', '<p>In an examination, the percentage of students qualified to the number of students appeared from school A is 70%. In school B, the number of students appeared is 20% more than the students appeared from school A and the number of students qualified from school B is 50% more than the students qualified from school A. What is the percentage of students qualified to the number of students appeared from school B?</p>\r\n', '', '30%', '70%', '80%', '87.5%', '', 'D'),
(57, NULL, 'Numerical Reasoning', 'single', '<p>In an examination, there are three papers, and a candidate has to get 35% of the total to pass. In one paper, he gets 62 out of 150 and in the second 35 out of 150. How much must he get, out of 180, in the third paper to just qualify for a pass?</p>\r\n', '', '60', '60.5', '70', '71', '', 'D'),
(59, NULL, 'Verbal Reasoning', 'single', '<p><strong>Pick up the correct antonyms for each of the following words.</strong></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>Density</p>\r\n', '', 'Clarity', 'Brightness', 'Intelligence', 'Rarity', '', 'D'),
(60, NULL, 'Verbal Reasoning', 'single', '<p><strong>Pick up the correct antonyms for each of the following words.</strong></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>Passionate</p>\r\n', '', 'Sure', 'Calm', 'Fervent', 'Arrogant', '', 'B'),
(61, NULL, 'Verbal Reasoning', 'single', '<p><strong>Pick up the correct antonyms for each of the following words.</strong></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>Host</p>\r\n', '', 'Spread', 'Enlarged', 'Diffused', 'Accomplice', '', 'C'),
(62, NULL, 'Verbal Reasoning', 'single', '<p><strong>Pick up the correct antonyms for each of the following words.</strong></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>Native</p>\r\n', '', 'Alien', 'Newcomer', 'Stranger', 'Foreigner', '', 'A'),
(64, 'ALEDOY', 'Verbal Reasoning', 'single', '<p><strong>Pick up the correct antonyms for each of the following words.</strong></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>Moderate</p>\r\n', '', 'Anarchist', 'Radical', 'Revolutionary', 'Nihilist', '', 'D'),
(65, NULL, 'Verbal Reasoning', 'single', '<p><strong><em>Pick out the most effective word from the given words to fill in the blank to make the sentence meaningfully complete</em></strong></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>Suganya reached home __________ did not find his mother there</p>\r\n', '', 'and', 'yet', 'but', 'although', '', 'C'),
(66, NULL, 'Verbal Reasoning', 'single', '<p><strong><em>Pick out the most effective word from the given words to fill in the blank to make the sentence meaningfully complete</em></strong></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>Prasanna __________ a plan to escape from jail.</p>\r\n', '', 'hit against', 'hit out', '. hit about', 'hit upon', '', 'D'),
(67, NULL, 'Verbal Reasoning', 'single', '<p><strong><em>Pick out the most effective word from the given words to fill in the blank to make the sentence meaningfully complete</em></strong></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>While travelling to Delhi. I ran __________ a very old friend of mine.</p>\r\n', '', 'up', 'across', 'against', 'into', '', 'D'),
(68, NULL, 'Verbal Reasoning', 'single', '<p><strong><em>Pick out the most effective word from the given words to fill in the blank to make the sentence meaningfully complete</em></strong></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>Arun was unreasonably incensed __________ me.</p>\r\n', '', 'against', 'at', 'on', 'with', '', 'D'),
(69, NULL, 'Verbal Reasoning', 'single', '<p><strong><em>Pick out the most effective word from the given words to fill in the blank to make the sentence meaningfully complete</em></strong></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>He has been invited __________ lunch.</p>\r\n', '', 'for', 'to', 'on', 'over', '', 'B'),
(70, NULL, 'Verbal Reasoning', 'single', '<p><strong>In each question, a part of sentence is printed italics. Below each sentence, some phrases are given which can substitute the italicized part of the sentence.</strong></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>Prasanna has got many friends because he has <strong><em>got much money</em></strong>.</p>\r\n', '', 'bags of money', 'enough money', 'a lot of money', 'no improvement', '', 'C'),
(71, NULL, 'Verbal Reasoning', 'single', '<p><strong>In each question, a part of sentence is printed italics. Below each sentence, some phrases are given which can substitute the italicized part of the sentence.</strong></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>He <strong><em>gave</em></strong> the I.A.S. examination in all seriousness.</p>\r\n', '', 'undertook', 'took', 'appeared', 'no improvement', '', 'B'),
(72, NULL, 'Verbal Reasoning', 'single', '<p><strong>In each question, a part of sentence is printed italics. Below each sentence, some phrases are given which can substitute the italicized part of the sentence.</strong></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>He has very good command <strong><em>on</em></strong> English</p>\r\n', '', 'of', 'in', 'over', 'no improvement', '', 'A'),
(73, NULL, 'Verbal Reasoning', 'single', '<p><strong>In each question, a part of sentence is printed italics. Below each sentence, some phrases are given which can substitute the italicized part of the sentence.</strong></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>I complimented him for his success <strong><em>in</em></strong> the examination.</p>\r\n', '', 'on', 'at', 'about', 'no improvement', '', 'A'),
(74, NULL, 'Verbal Reasoning', 'single', '<p><strong>In each question, a part of sentence is printed italics. Below each sentence, some phrases are given which can substitute the italicized part of the sentence.</strong></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>It is time the six years old <strong><em>is learning</em></strong> how to read and write.</p>\r\n', '', 'was learning', 'learnt', 'has learned', 'no improvement', '', 'B'),
(75, NULL, 'Verbal Reasoning', 'single', '<p><strong><em>Each of the following questions contains a small paragraph followed by a question on it. Read each paragraph carefully and answer the question given below it.</em></strong></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>Throughout the ages the businessman has helped build civilization&rsquo;s great cities, provided people with luxuries and artists with patronage, and lift his fellow citizens to understand the standard of living. In the last few centuries, the businessman has seeded the Industrial Revolution around the world.</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>The passage best supports the statement that the businessman</p>\r\n', '', '.  is accountable to the society', '.  has contributed to the growth of civilization', 'is capable of raising his standard of living', 'is the beneficiary of the Industrial Revolution', '', 'B'),
(76, NULL, 'Verbal Reasoning', 'single', '<p><strong><em>Each of the following questions contains a small paragraph followed by a question on it. Read each paragraph carefully and answer the question given below it.</em></strong></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>There is a shift in our economy from a manufacturing to a service orientation. The increase in service sector will require the managers to work more with people rather than with objects and things from the assembly line.</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>The passage best supports the statement that</p>\r\n', '', 'service organisations will not deal with objects and things.', 'managers should have a balanced mind.', 'manufacturing organisations ignore importance of people.', '. interpersonal skills will become more important in the future workplace.', '', 'D'),
(77, NULL, 'Verbal Reasoning', 'single', '<p><strong><em>Each of the following questions contains a small paragraph followed by a question on it. Read each paragraph carefully and answer the question given below it.</em></strong></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>The only true education comes through the stimulation of the child&#39;s powers by the demands of the social situations in which he finds himself. Through these demands he is stimulated to act as a member of a unity, to emerge from his original narrowness of action and feeling, and to conceive himself from the standpoint of the welfare of the group to which he belongs.</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>The passage best supports the statement that real education</p>\r\n', '', 'will take place if the children imbibe action and feeling', 'will take place if the children are physically strong', 'comes from the self-centered approach of the students', 'comes through the interaction with social situations', '', 'D'),
(78, NULL, 'Verbal Reasoning', 'single', '<p><strong><em>Each of the following questions contains a small paragraph followed by a question on it. Read each paragraph carefully and answer the question given below it.</em></strong></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>Though the waste of time or the expenditure on fashions is very large, yet fashions have come to stay. They will not go, come what may. However, what is now required is that strong efforts should be made to displace the excessive craze for fashion from the minds of these youngsters.</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>The passage best supports the statement that</p>\r\n', '', 'work and other activities should be valued more than the outward appearance.', 'fashion is the need of the day.', 'the excessive craze for fashion is detrimental to one&#039;s personality.', 'the hoard for fashion should be done away with so as not to be let down the constructive development.', '', 'D'),
(79, NULL, 'Verbal Reasoning', 'single', '<p><strong><em>Each of the following questions contains a small paragraph followed by a question on it. Read each paragraph carefully and answer the question given below it.</em></strong></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>Satisfaction with co-workers, promotion opportunities, the nature of work and pay goes with high performance among those with weak growth needs, no such relationship is present and in fact satisfaction with promotion opportunities goes with low performance.</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>The passage best supports the statement that</p>\r\n', '', 'high performance is essential for organizational effectiveness', 'satisfaction is an inevitable organizational variable.', '.  job satisfaction and performance are directly and closely related.', 'relationship between job satisfaction and performance is moderate by growth need.', '', 'D'),
(80, NULL, 'Verbal Reasoning', 'single', '<p>Ten years ago, A was half of B in age. If the ratio of their present ages is 3 : 4, what will be the total of their present ages ?</p>\r\n', '', '20 Years', '30 Years', '45 Years', 'None of the Above', '', 'D'),
(81, NULL, 'Verbal Reasoning', 'single', '<p>A person&rsquo;s present age is two-fifth of the age of his mother. After 8 years, he will be one-half of the age of his mother. How old is the mother at present?</p>\r\n', '', '32 Years', '36 Years', '40 Years', '42 Years', '', 'C'),
(82, NULL, 'Verbal Reasoning', 'single', '<p>The present ages of three persons are in proportions 4:7:9. Eight years ago, the sum of their ages was 56. Find their present ages (in years).</p>\r\n', '', '8, 20, 28', '16, 28, 36', '20, 35, 45', 'None of the above', '', 'B'),
(83, NULL, 'Verbal Reasoning', 'single', '<p>A man is 24 years older than his son. In two tears, his age will be twice the age of his son. The present age of the son is:</p>\r\n', '', '14 Years', '18 Years', '20 Years', '22 Years', '', 'D'),
(84, NULL, 'Verbal Reasoning', 'single', '<p>Pedro goes either hunting or fishing every day. If it is snowing &amp; windy then Pedro goes hunting. If it is sunny and not windy then Pedro goes fishing. Sometimes it can be snowing and sunny. Which of the following statements must be true:</p>\r\n', '', 'If it is not sunny and it is snowing, then Pedro goes hunting', 'If it is windy and Pedro does not go hunting, then it is not snowing.', 'f it is windy and not sunny then Pedro goes hunting.', 'If it is windy and sunny then Pedro goes hunting.', '', 'B'),
(85, NULL, 'Verbal Reasoning', 'single', '<p>In 1695 about 11,400 doctors who had treated plague sufferers died and about 23,670 doctors who had not treated plague sufferers died. On the basis of these figures, it can be concluded that it was more dangerous for doctors not to participate in the treatment of plague sufferers than it was for them to participate in it.&nbsp; Which of the following statements would cast most doubt on the conclusion above?</p>\r\n', '', 'Expressing the difference between the numbers of deaths among doctors who had treated plague sufferers and doctors who had not treated plague suffers as a percentage of the total number of deaths.', 'Examining the death rates for doctors in the years before and after 1695.', 'Separating deaths due to natural causes during the treatment of plague suffers from deaths caused by other causes.', 'Comparing death rates per thousand members of each group rather than comparing total numbers of deaths.', '', 'D'),
(86, NULL, 'Verbal Reasoning', 'single', '<p>There are 900 bottles to be filled. Jim and Molly working independently but at the same time take 30 minutes to fill the bottles. How long should it take Molly working by herself to fill the bottles? Statement 1 - Molly fills half as many bottles as Jim. Statement 2 - Jim would take 45 minutes by himself. Which of the statements above make it possible to answer the question?</p>\r\n', '', 'Statement 1 alone is sufficient, but statement 2 alone is not sufficient.', 'Statement 2 alone is sufficient, but statement 1 alone is not sufficient.', 'Both statements together are sufficient, but neither statement alone is sufficient.', 'Each statement alone is sufficient.', '', 'D'),
(87, NULL, 'Verbal Reasoning', 'single', '<p>If A is B&#39;s brother, B is C&#39;s sister and C is D&#39;s father, D is A&#39;s... &nbsp;</p>\r\n', '', 'Brother', 'Sister', 'Nephew', 'Cannot be determined', '', 'D'),
(88, NULL, 'Verbal Reasoning', 'single', '<p>Aparna&#39;s mother is the daughter of Vishnu&#39;s sister. How is Vishnu&#39;s mother related to Aparna&#39;s mother?</p>\r\n', '', 'Mother', 'Daughter', 'Sister', 'Grandmother', '', 'D'),
(90, NULL, 'Abstract', 'single', '<p>What is your best color?</p>\r\n', '1571489081882.jpg', 'Red', 'Orange', 'Green', 'Purple', '', 'C'),
(91, NULL, 'Verbal Reasoning', '', '<p>This line of code starts a loop that will iterate through the array <code>$result</code>. The loop will run for as long as the value of the variable <code>$i</code> is less than the value of the variable <code>$num</code>. For each iteration of the loop, the code will fetch the next record from the array and store it in the variable <code>$row</code>. The code will then get the value of the <code>id</code> column from the variable <code>$row</code> and store it in the variable <code>$name</code>.</p>\r\n', '', 'YES', 'NO', 're', 're', 're', 'yes'),
(92, NULL, 'Abstract', 'single', '<p>eaaaacadsfd</p>\r\n', '', 'dda', 'nkjn', 'jnjb', 'hbjh', 'jbk', 'dda'),
(93, NULL, 'Verbal Reasoning', 'single', '<p>Pick your favourite of these four</p>\r\n', '', 'PHP', 'JAVA', 'LARAVEL', 'PYTHON', '', 'A');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assessment`
--
ALTER TABLE `assessment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `exam_result`
--
ALTER TABLE `exam_result`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `exam_code` (`exam_code`);

--
-- Indexes for table `participant`
--
ALTER TABLE `participant`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assessment`
--
ALTER TABLE `assessment`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `exam_result`
--
ALTER TABLE `exam_result`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `participant`
--
ALTER TABLE `participant`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
