ALTER TABLE `titles`
  ADD COLUMN `scope` ENUM('hq','zone','subzone') NOT NULL DEFAULT 'hq' AFTER `org_id`,
  DROP INDEX `org_level_unique`,
  ADD UNIQUE KEY `titles_org_scope_level_unique` (`org_id`,`scope`,`level`),
  ADD UNIQUE KEY `titles_org_scope_title_unique` (`org_id`,`scope`,`title`);
