<?php
function admin_generate_application_number(mysqli $conn): string
{
    $year = (new DateTimeImmutable('now', new DateTimeZone('Africa/Lagos')))->format('Y');
    $statement = mysqli_prepare($conn, 'SELECT id FROM admissions WHERE application_number = ? LIMIT 1');
    if (!$statement) {
        error_log('Application number lookup could not be prepared: ' . mysqli_error($conn));
        throw new RuntimeException('An application number could not be generated.');
    }

    for ($sequence = 1; $sequence <= 999999; $sequence++) {
        $number = sprintf('APP-%s-%03d', $year, $sequence);
        mysqli_stmt_bind_param($statement, 's', $number);
        if (!mysqli_stmt_execute($statement)) {
            error_log('Application number lookup failed: ' . mysqli_stmt_error($statement));
            mysqli_stmt_close($statement);
            throw new RuntimeException('An application number could not be generated.');
        }
        if (!mysqli_stmt_store_result($statement)) {
            error_log('Application number lookup result could not be stored: ' . mysqli_stmt_error($statement));
            mysqli_stmt_close($statement);
            throw new RuntimeException('An application number could not be generated.');
        }
        $isTaken = mysqli_stmt_num_rows($statement) > 0;
        mysqli_stmt_free_result($statement);

        if (!$isTaken) {
            mysqli_stmt_close($statement);
            return $number;
        }
    }

    mysqli_stmt_close($statement);
    throw new RuntimeException('No application numbers are available for this year.');
}
