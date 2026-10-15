CREATE TABLE `admin_remember_tokens` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_id` int(30) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `last_active_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_remember_tokens_token_hash` (`token_hash`),
  KEY `admin_remember_tokens_account_id` (`account_id`),
  KEY `admin_remember_tokens_expires_at` (`expires_at`),
  CONSTRAINT `admin_remember_tokens_account_fk`
    FOREIGN KEY (`account_id`) REFERENCES `acc-info` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
