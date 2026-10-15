ALTER TABLE `member_sessions`
  ADD COLUMN `token_hash` char(64) DEFAULT NULL,
  ADD COLUMN `expires_at` datetime DEFAULT NULL,
  ADD UNIQUE KEY `member_sessions_token_hash` (`token_hash`),
  ADD KEY `member_sessions_expires_at` (`expires_at`);
