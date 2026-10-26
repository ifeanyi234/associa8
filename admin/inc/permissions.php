<?php

function admin_can_manage_staff_account(array $user, int $currentUserId, bool $isPlatformAdmin): bool
{
    $accountId = (int) ($user['account_id'] ?? $user['id'] ?? 0);
    if ($accountId === $currentUserId) {
        return false;
    }

    $userRole = (string) ($user['role'] ?? '');
    if ($userRole === 'super_admin') {
        return false;
    }

    if ($isPlatformAdmin) {
        return true;
    }

    return $userRole !== 'admin';
}
