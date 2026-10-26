CREATE TABLE admin_notifications (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  org_id INT NOT NULL,
  account_id INT NOT NULL,
  type VARCHAR(50) NOT NULL,
  title VARCHAR(150) NOT NULL,
  message VARCHAR(255) NOT NULL,
  link VARCHAR(255) NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_admin_notifications_account_unread (org_id, account_id, is_read, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
