-- Safe to run more than once on an existing Associa8 database.
-- Scheduled datetimes are stored in UTC.

SET @migration_sql = IF(
    EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'admissions'
          AND COLUMN_NAME = 'cbt_scheduled_at'
    ),
    'SELECT ''cbt_scheduled_at already exists'' AS migration_status',
    'ALTER TABLE admissions ADD COLUMN cbt_scheduled_at DATETIME DEFAULT NULL AFTER cbt_exam_id'
);
PREPARE migration_statement FROM @migration_sql;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;
