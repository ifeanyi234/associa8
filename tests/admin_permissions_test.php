<?php

require_once __DIR__ . '/../admin/inc/permissions.php';

$cases = [
    [
        'label' => 'super admin can manage admin accounts in the same org',
        'result' => admin_can_manage_staff_account(['account_id' => 2, 'role' => 'admin'], 1, true),
        'expected' => true,
    ],
    [
        'label' => 'admin cannot manage another admin account',
        'result' => admin_can_manage_staff_account(['account_id' => 2, 'role' => 'admin'], 1, false),
        'expected' => false,
    ],
    [
        'label' => 'non-admin staff can be managed by admin level role',
        'result' => admin_can_manage_staff_account(['account_id' => 2, 'role' => 'subzonal_admin'], 1, false),
        'expected' => true,
    ],
    [
        'label' => 'self cannot be managed',
        'result' => admin_can_manage_staff_account(['account_id' => 1, 'role' => 'subzonal_admin'], 1, false),
        'expected' => false,
    ],
];

$failures = 0;
foreach ($cases as $case) {
    if ($case['result'] !== $case['expected']) {
        fwrite(STDERR, "FAIL: {$case['label']}\nExpected: " . var_export($case['expected'], true) . "\nActual: " . var_export($case['result'], true) . "\n");
        $failures++;
    }
}

if ($failures > 0) {
    exit(1);
}

echo "All admin permission checks passed.\n";
