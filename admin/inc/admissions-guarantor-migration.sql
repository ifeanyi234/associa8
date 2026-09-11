ALTER TABLE `admissions`
  ADD `guarantor_name` varchar(100) DEFAULT NULL AFTER `phone`,
  ADD `guarantor_email` varchar(100) DEFAULT NULL AFTER `guarantor_name`,
  ADD `guarantor_phone` varchar(20) DEFAULT NULL AFTER `guarantor_email`,
  ADD `guarantor_relationship` varchar(50) DEFAULT NULL AFTER `guarantor_phone`;