<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../inc/db.php';

$databaseResult = mysqli_query($conn, 'SELECT DATABASE()');
$databaseRow = $databaseResult ? mysqli_fetch_row($databaseResult) : null;
if (
    !$databaseRow
    || $databaseRow[0] !== 'associa8'
    || stripos(mysqli_get_host_info($conn), 'localhost') === false
        && stripos(mysqli_get_host_info($conn), '127.0.0.1') === false
) {
    fwrite(STDERR, "Demo fixtures may only be loaded into the local associa8 database.\n");
    exit(1);
}

$sqlPath = __DIR__ . '/fixtures/demo_finance_attendance.sql';
$sql = file_get_contents($sqlPath);
if ($sql === false) {
    fwrite(STDERR, "Could not read the demo fixture SQL file.\n");
    exit(1);
}

if (!mysqli_multi_query($conn, $sql)) {
    fwrite(STDERR, 'Could not apply demo fixtures: ' . mysqli_error($conn) . "\n");
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
        fwrite(STDERR, 'Could not apply demo fixtures: ' . mysqli_error($conn) . "\n");
        exit(1);
    }
} while (true);

if (mysqli_errno($conn)) {
    fwrite(STDERR, 'Could not apply demo fixtures: ' . mysqli_error($conn) . "\n");
    exit(1);
}

$checks = [
    'members' => "SELECT COUNT(*) FROM members WHERE org_id = 1 AND member_code IN ('DEMO-FIN-001', 'DEMO-FIN-002', 'DEMO-FIN-003')",
    'transactions' => "SELECT COUNT(*) FROM finance_transactions WHERE reference LIKE 'DEMO-FIN-PAY-%'",
    'attendance records' => "SELECT COUNT(*) FROM attendance_logs al INNER JOIN members m ON m.id = al.member_id WHERE m.org_id = 1 AND m.member_code LIKE 'DEMO-FIN-%'",
];
foreach ($checks as $label => $query) {
    $result = mysqli_query($conn, $query);
    if (!$result) {
        fwrite(STDERR, 'Could not verify demo fixtures: ' . mysqli_error($conn) . "\n");
        exit(1);
    }
    $row = mysqli_fetch_row($result);
    fwrite(STDOUT, ucfirst($label) . ': ' . (int) ($row[0] ?? 0) . "\n");
}
