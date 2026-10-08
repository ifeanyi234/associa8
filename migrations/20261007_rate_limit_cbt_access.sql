CREATE TABLE IF NOT EXISTS cbt_rate_limits (
    action_key VARCHAR(40) NOT NULL,
    subject_hash CHAR(64) NOT NULL,
    window_started_at DATETIME NOT NULL,
    attempts INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (action_key, subject_hash),
    KEY idx_cbt_rate_limits_window (window_started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
