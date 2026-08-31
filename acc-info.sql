-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 31, 2026 at 02:34 PM
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
(1, 'ifevnyi', '$2y$10$fvaitFjNvYP1SBGLhFWqfebp7Y1zTq1YZ5eBf78cAUFUvURuNok4q', '000000'),
(2, 'ifevnyi', '$2y$10$qf9OyZD6VYiCSWTD7I4hruagIhhm85l3ShD7GSIwyisRccQyGVWcS', '000000'),
(3, 'newleg', '$2y$10$//Bf08FRNsDh/mRh9auDe.eMIv9pr9DrwWfED.Y/zSRwXqk47dG3a', '222222'),
(4, 'anyi', '$2y$10$vgJPj4WnRh5carj5dDWAweA2TEZcGRBpEtOLG5IuH9AhE.0SeIpvu', '123456'),
(5, 'ezehhh', '$2y$10$MY1hRIyhEoqzQAqGm3qhmu4CYoGsPV1eL/Hb6yCAsEXbwrp1QIoi6', '999999'),
(6, 'ezehhh', '$2y$10$GAtP20u3NMQ0zK86IHK2oeahYcvuQQ52pjH39C74vr4Hm0X0sj1b6', '999999'),
(7, 'ezehhh', '$2y$10$gSoh1NaQwwi3op7nr1BnxOYM8HDAJcAv/zGfeoYRaxhDQcMgXyllm', '999999'),
(8, 'eze', '$2y$10$4U5DrflzOjqHvCATVjqp3.Wc9KeSE6dUBHH3.5Q./eIlt8kpp2ymm', '999999'),
(9, 'finite', '$2y$10$jZY/ib8DGK8Uv1WMfRwdEO8JMlN9Kd.WVNcMMoc5WPZvTFQOQoOy6', '340845'),
(10, 'ifevny', '$2y$10$fOY76esIrxppGfzh4V8HWumsQFbKxVGh7iHudi5/yGB72SODqXBMK', '039434'),
(11, 'ifnyi', '$2y$10$q.zCaCtFikstALhANBofD.fAi4wKY04F04KpzXEm9oPRdtG.Do1Aq', '888888'),
(14, 'gamerboy', '$2y$10$zzXxHARdBBOAMzg/liKlDe3AlbPXndKS.sq6JKiAXf2tFc38NiaSG', '234567'),
(16, 'helloworld', '$2y$10$h2HbDEXrlh5lFOR0q8xVkO1DYWvTe0OeEhGnVxOSQOc4iQlV4d3xu', '123456'),
(20, 'vvvvvvvvvvv', '$2y$10$0dw7RpTCtB8I79nmONTGfeSTWargAB9H9YpH7lrUG/dmYXTMgqOZK', '444444'),
(23, 'gamerboy', '$2y$10$QnXg1hOkiXi.yYcrFR7p3e8X46EbZlHu3wLDYopCyaPfSpt8Oj5ji', '000000');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `acc-info`
--
ALTER TABLE `acc-info`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `acc-info`
--
ALTER TABLE `acc-info`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
