<?php

$page = file_get_contents(__DIR__ . '/../admin/user-controls.php');
$processor = file_get_contents(__DIR__ . '/../admin/process-user.php');
$roleProcessor = file_get_contents(__DIR__ . '/../admin/proc-user-action.php');

if ($page === false || strpos($page, 'name="zone_id"') !== false || strpos($page, 'name="subzone_id"') !== false) {
    fwrite(STDERR, "FAIL: user control still asks for a zone or sub-zone\n");
    exit(1);
}
if (
    $processor === false
    || strpos($processor, 'FROM members m') === false
    || strpos($processor, 'LOWER(m.email) = ?') === false
    || strpos($processor, 'valid_zone_id') === false
    || strpos($processor, 'valid_subzone_id') === false
) {
    fwrite(STDERR, "FAIL: admin area is not derived from the matching member record\n");
    exit(1);
}
if (
    $roleProcessor === false
    || strpos($roleProcessor, 'LOWER(m.email) = ?') === false
    || strpos($roleProcessor, 'name="zone_id"') !== false
    || strpos($roleProcessor, 'name="subzone_id"') !== false
) {
    fwrite(STDERR, "FAIL: role changes still request a manual zone or sub-zone\n");
    exit(1);
}

echo "User control derives admin areas from the member email.\n";
