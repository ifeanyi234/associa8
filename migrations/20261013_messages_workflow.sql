ALTER TABLE `message_threads`
  ADD COLUMN `org_id` int(15) DEFAULT NULL AFTER `member_id`,
  ADD COLUMN `member_last_read_at` datetime(6) DEFAULT NULL AFTER `is_read`,
  ADD COLUMN `staff_last_read_at` datetime(6) DEFAULT NULL AFTER `member_last_read_at`;

UPDATE `message_threads` t
INNER JOIN `members` m ON m.id = t.member_id
SET t.org_id = m.org_id;

ALTER TABLE `message_threads`
  MODIFY COLUMN `org_id` int(15) NOT NULL,
  MODIFY COLUMN `sent_at` datetime(6) DEFAULT current_timestamp(6),
  ADD KEY `message_threads_org_sent` (`org_id`,`sent_at`),
  ADD CONSTRAINT `message_threads_org_fk`
    FOREIGN KEY (`org_id`) REFERENCES `org-info` (`id`) ON DELETE CASCADE;

ALTER TABLE `message_replies`
  MODIFY COLUMN `member_id` int(10) UNSIGNED DEFAULT NULL,
  MODIFY COLUMN `sent_at` datetime(6) DEFAULT current_timestamp(6),
  ADD COLUMN `admin_account_id` int(30) DEFAULT NULL AFTER `member_id`,
  ADD COLUMN `sender_type` enum('member','staff') NOT NULL DEFAULT 'member' AFTER `admin_account_id`,
  ADD COLUMN `sender_name` varchar(150) DEFAULT NULL AFTER `sender_type`,
  ADD KEY `message_replies_thread_sent` (`thread_id`,`sent_at`),
  ADD KEY `message_replies_admin_account` (`admin_account_id`),
  ADD CONSTRAINT `message_replies_admin_account_fk`
    FOREIGN KEY (`admin_account_id`) REFERENCES `acc-info` (`id`) ON DELETE SET NULL;
