CREATE TABLE IF NOT EXISTS `cbt_questions` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `exam_id` int(10) UNSIGNED NOT NULL,
  `question_text` text NOT NULL,
  `option_a` varchar(255) NOT NULL,
  `option_b` varchar(255) NOT NULL,
  `option_c` varchar(255) NOT NULL,
  `option_d` varchar(255) NOT NULL,
  `option_e` varchar(255) DEFAULT NULL,
  `correct_option` enum('A','B','C','D','E') NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `exam_id` (`exam_id`),
  CONSTRAINT `cbt_questions_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `cbt_exams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;