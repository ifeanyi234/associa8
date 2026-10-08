<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../inc/db.php';

$databaseResult = mysqli_query($conn, 'SELECT DATABASE()');
$databaseRow = $databaseResult ? mysqli_fetch_row($databaseResult) : null;
$connectionInfo = mysqli_get_host_info($conn);
if (
    !$databaseRow
    || $databaseRow[0] !== 'associa8'
    || (stripos($connectionInfo, 'localhost') === false && stripos($connectionInfo, '127.0.0.1') === false)
) {
    fwrite(STDERR, "Demo fixtures may only be removed from the local associa8 database.\n");
    exit(1);
}

$sqlPath = __DIR__ . '/fixtures/cleanup_demo_finance_attendance.sql';
$sql = file_get_contents($sqlPath);
if ($sql === false) {
    fwrite(STDERR, "Could not read the demo fixture cleanup SQL file.\n");
    exit(1);
}

if (!mysqli_multi_query($conn, $sql)) {
    fwrite(STDERR, 'Could not remove demo fixtures: ' . mysqli_error($conn) . "\n");
    exit(1);
}

do {
    $result = mysqli_store_result($conn);
    if ($result) {
        mysqli_free_result($result);
    }

    if (!mysqli_more_results($conn)) {
        break;
    }

    if (!mysqli_next_result($conn)) {
        fwrite(STDERR, 'Could not remove demo fixtures: ' . mysqli_error($conn) . "\n");
        exit(1);
    }
} while (true);

if (mysqli_errno($conn)) {
    fwrite(STDERR, 'Could not remove demo fixtures: ' . mysqli_error($conn) . "\n");
    exit(1);
}

fwrite(STDOUT, "Demo finance and attendance fixtures have been removed from local database associa8.\n");
