-- Support independent, disableable staff logins with random temporary passwords.
ALTER TABLE `acc-info`
  MODIFY COLUMN `password` varchar(255) NOT NULL,
  ADD COLUMN `status` enum('active','disabled') NOT NULL DEFAULT 'active' AFTER `otp`,
  ADD COLUMN `created_at` datetime NOT NULL DEFAULT current_timestamp() AFTER `status`;

ALTER TABLE `admin-info`
  MODIFY COLUMN `phone` bigint(20) DEFAULT NULL;
