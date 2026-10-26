ALTER TABLE `staff_invitations`
  ADD COLUMN `zone_id` int(10) unsigned DEFAULT NULL AFTER `role`,
  ADD COLUMN `subzone_id` int(10) unsigned DEFAULT NULL AFTER `zone_id`,
  ADD KEY `staff_invitations_zone_fk` (`zone_id`),
  ADD KEY `staff_invitations_subzone_fk` (`subzone_id`),
  ADD CONSTRAINT `staff_invitations_zone_fk` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `staff_invitations_subzone_fk` FOREIGN KEY (`subzone_id`) REFERENCES `subzones` (`id`) ON DELETE SET NULL;
