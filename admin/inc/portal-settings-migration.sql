CREATE TABLE IF NOT EXISTS `portal_settings` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `portal_key` varchar(30) NOT NULL,
  `start_at` datetime DEFAULT NULL,
  `end_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `portal_key` (`portal_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `portal_settings` (`portal_key`)
VALUES ('admission'), ('cbt')
ON DUPLICATE KEY UPDATE `portal_key` = VALUES(`portal_key`);
