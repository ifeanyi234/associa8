<?php

function admin_finance_levies_available(mysqli $conn): bool
{
    $result = mysqli_query(
        $conn,
        "SELECT
            (SELECT COUNT(*) FROM information_schema.tables
             WHERE table_schema = DATABASE()
               AND table_name IN ('levies', 'member_levies')) AS levy_tables,
            (SELECT COUNT(*) FROM information_schema.columns
             WHERE table_schema = DATABASE()
               AND table_name = 'finance_transactions'
               AND column_name = 'levy_id') AS levy_column,
            (SELECT column_type FROM information_schema.columns
             WHERE table_schema = DATABASE()
               AND table_name = 'finance_transactions'
               AND column_name = 'type') AS transaction_type"
    );
    if (!$result) {
        error_log('Finance levy schema check failed: ' . mysqli_error($conn));
        return false;
    }
    $schema = mysqli_fetch_assoc($result);
    return $schema
        && (int) $schema['levy_tables'] === 2
        && (int) $schema['levy_column'] === 1
        && strpos((string) $schema['transaction_type'], "'levy'") !== false;
}
