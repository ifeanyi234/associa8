-- Safe to run more than once. If duplicate results exist, this reports them
-- without attempting the unique index; review the duplicates before retrying.
SELECT COUNT(*) INTO @duplicate_admission_results
FROM (
    SELECT admission_id
    FROM cbt_results
    WHERE admission_id IS NOT NULL
    GROUP BY admission_id
    HAVING COUNT(*) > 1
) AS duplicate_results;

SELECT COUNT(*) INTO @unique_index_exists
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'cbt_results'
  AND INDEX_NAME = 'uniq_cbt_admission_attempt';

SET @migration_sql = IF(
    @unique_index_exists > 0,
    'SELECT ''uniq_cbt_admission_attempt already exists'' AS migration_status',
    IF(
        @duplicate_admission_results > 0,
        'SELECT ''Unique index not added: duplicate admission results need review'' AS migration_status',
        'ALTER TABLE cbt_results ADD UNIQUE KEY uniq_cbt_admission_attempt (admission_id)'
    )
);
PREPARE migration_statement FROM @migration_sql;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;
