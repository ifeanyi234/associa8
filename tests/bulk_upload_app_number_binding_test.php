<?php
$page = file_get_contents(__DIR__ . '/../admin/proc-bulk-upload-applicants.php');
if ($page === false) {
    fwrite(STDERR, "FAIL: unable to read bulk upload handler\n");
    exit(1);
}

if (strpos($page, "mysqli_stmt_bind_param(\$statement, 'issssssss', \$orgId, \$assignedNumber") === false) {
    fwrite(STDERR, "FAIL: bulk upload application number bind type is incorrect\n");
    exit(1);
}

echo "Bulk upload application number bind type is correct.\n";
