CREATE TABLE `levies` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `org_id` int(15) NOT NULL,
  `title` varchar(150) NOT NULL,
  `amount` decimal(12,2) UNSIGNED NOT NULL,
  `due_date` date NOT NULL,
  `scope_type` enum('organization','zone','subzone') NOT NULL,
  `scope_id` int(10) UNSIGNED DEFAULT NULL,
  `created_by` int(30) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `levies_org_due_date` (`org_id`,`due_date`),
  CONSTRAINT `levies_org_fk` FOREIGN KEY (`org_id`) REFERENCES `org-info` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `member_levies` (
  `levy_id` int(10) UNSIGNED NOT NULL,
  `member_id` int(10) UNSIGNED NOT NULL,
  `amount` decimal(12,2) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`levy_id`,`member_id`),
  KEY `member_levies_member_fk` (`member_id`),
  CONSTRAINT `member_levies_levy_fk` FOREIGN KEY (`levy_id`) REFERENCES `levies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `member_levies_member_fk` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `finance_transactions`
  MODIFY `type` enum('membership_dues','admission_fee','event','levy','other') NOT NULL,
  ADD COLUMN `levy_id` int(10) UNSIGNED DEFAULT NULL AFTER `member_id`,
  ADD KEY `finance_transactions_levy_fk` (`levy_id`),
  ADD CONSTRAINT `finance_transactions_levy_fk` FOREIGN KEY (`levy_id`) REFERENCES `levies` (`id`) ON DELETE SET NULL;
