<?php
function associa8_rate_limit_retry_after(mysqli $conn, string $action, string $subject, int $limit, int $windowSeconds): int
{
    if ($limit < 1 || $windowSeconds < 1) {
        throw new InvalidArgumentException('Rate limits must have positive limits and windows.');
    }

    if (random_int(1, 100) === 1) {
        if (!mysqli_query($conn, 'DELETE FROM cbt_rate_limits WHERE window_started_at < DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 DAY)')) {
            error_log('Expired rate-limit cleanup failed: ' . mysqli_error($conn));
            throw new RuntimeException('The request limit service is unavailable.');
        }
    }

    $subjectHash = hash('sha256', $subject);
    $statement = mysqli_prepare(
        $conn,
        'INSERT INTO cbt_rate_limits (action_key, subject_hash, window_started_at, attempts)
         VALUES (?, ?, UTC_TIMESTAMP(), 1)
         ON DUPLICATE KEY UPDATE
           attempts = IF(window_started_at <= DATE_SUB(UTC_TIMESTAMP(), INTERVAL ? SECOND), 1, attempts + 1),
           window_started_at = IF(window_started_at <= DATE_SUB(UTC_TIMESTAMP(), INTERVAL ? SECOND), UTC_TIMESTAMP(), window_started_at)'
    );
    if (!$statement) {
        error_log('Rate-limit update prepare failed: ' . mysqli_error($conn));
        throw new RuntimeException('The request limit service is unavailable.');
    }

    mysqli_stmt_bind_param($statement, 'ssii', $action, $subjectHash, $windowSeconds, $windowSeconds);
    if (!mysqli_stmt_execute($statement)) {
        error_log('Rate-limit update failed: ' . mysqli_stmt_error($statement));
        throw new RuntimeException('The request limit service is unavailable.');
    }

    $lookup = mysqli_prepare(
        $conn,
        'SELECT attempts, GREATEST(0, ? - TIMESTAMPDIFF(SECOND, window_started_at, UTC_TIMESTAMP())) AS retry_after
         FROM cbt_rate_limits
         WHERE action_key = ? AND subject_hash = ?'
    );
    if (!$lookup) {
        error_log('Rate-limit lookup prepare failed: ' . mysqli_error($conn));
        throw new RuntimeException('The request limit service is unavailable.');
    }

    mysqli_stmt_bind_param($lookup, 'iss', $windowSeconds, $action, $subjectHash);
    if (!mysqli_stmt_execute($lookup)) {
        error_log('Rate-limit lookup failed: ' . mysqli_stmt_error($lookup));
        throw new RuntimeException('The request limit service is unavailable.');
    }

    $result = mysqli_stmt_get_result($lookup);
    $row = $result ? mysqli_fetch_assoc($result) : null;
    if (!$row) {
        error_log('Rate-limit lookup returned no counter row.');
        throw new RuntimeException('The request limit service is unavailable.');
    }

    if ((int) $row['attempts'] <= $limit) {
        return 0;
    }

    return max(1, (int) $row['retry_after']);
}
