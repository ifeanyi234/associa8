-- Add organization ownership to events on databases created before this feature.
-- Existing events remain unassigned until an administrator maps them to an organization.
-- Application pages intentionally exclude unassigned events.
ALTER TABLE `events`
  ADD COLUMN `org_id` int(15) DEFAULT NULL AFTER `id`,
  ADD COLUMN `status` enum('scheduled','cancelled') NOT NULL DEFAULT 'scheduled' AFTER `description`,
  ADD KEY `events_org_date` (`org_id`,`event_date`,`status`),
  ADD CONSTRAINT `events_org_fk` FOREIGN KEY (`org_id`) REFERENCES `org-info` (`id`) ON DELETE CASCADE;
