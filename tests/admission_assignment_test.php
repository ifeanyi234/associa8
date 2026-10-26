<?php

require_once __DIR__ . '/../admin/inc/title-assignment.php';

if (admission_approval_requires_assignment() !== false) {
    fwrite(STDERR, "FAIL: admission approval should not require zone/sub-zone/title assignment.\n");
    exit(1);
}

echo "Admission approval assignment requirement is disabled.\n";
