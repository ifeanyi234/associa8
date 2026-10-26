ALTER TABLE `events`
  ADD COLUMN `booking_fee` DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER `capacity`;
