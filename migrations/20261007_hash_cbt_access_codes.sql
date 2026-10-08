-- Safe to run more than once on an existing Associa8 database.
-- Existing plaintext access codes are revoked; applicants can request replacements.

SET @migration_sql = IF(
    EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'admissions'
          AND COLUMN_NAME = 'exam_code_hash'
    ),
    'SELECT ''exam_code_hash already exists'' AS migration_status',
    'ALTER TABLE admissions ADD COLUMN exam_code_hash VARCHAR(255) DEFAULT NULL AFTER exam_code'
);
PREPARE migration_statement FROM @migration_sql;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;

SET @migration_sql = IF(
    EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'admissions'
          AND COLUMN_NAME = 'cbt_attempt_started_at'
    ),
    'SELECT ''cbt_attempt_started_at already exists'' AS migration_status',
    'ALTER TABLE admissions ADD COLUMN cbt_attempt_started_at DATETIME DEFAULT NULL AFTER exam_expires_at'
);
PREPARE migration_statement FROM @migration_sql;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;

UPDATE admissions
SET cbt_attempt_started_at = UTC_TIMESTAMP()
WHERE exam_code IS NULL
  AND exam_expires_at IS NOT NULL
  AND cbt_attempt_started_at IS NULL
  AND status = 'under_review';

UPDATE admissions
SET exam_code = NULL,
    exam_code_hash = NULL
WHERE exam_code IS NOT NULL;
