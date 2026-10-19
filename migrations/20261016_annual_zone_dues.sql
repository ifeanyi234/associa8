ALTER TABLE `zones`
  ADD COLUMN `annual_dues` DECIMAL(12,2) UNSIGNED NOT NULL DEFAULT 0.00 AFTER `coordinator_name`;

ALTER TABLE `subzones`
  ADD COLUMN `annual_dues` DECIMAL(12,2) UNSIGNED NOT NULL DEFAULT 0.00 AFTER `coordinator_name`;
