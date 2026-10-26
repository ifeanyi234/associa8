<?php

function admin_notify_organization(mysqli $conn, int $orgId, string $type, string $title, string $message, string $link): bool
{
    if ($orgId < 1 || $title === '' || $message === '' || !preg_match('/^[A-Za-z0-9_-]+\.php(?:\?[A-Za-z0-9_=&%-]*)?$/', $link)) {
        error_log('Admin notification was not created because its values were invalid.');
        return false;
    }

    $statement = mysqli_prepare(
        $conn,
        "INSERT INTO admin_notifications (org_id, account_id, type, title, message, link)
         SELECT ?, a.id, ?, ?, ?, ?
         FROM `acc-info` a
         INNER JOIN `admin-info` ai ON ai.acc_id = a.id AND ai.org_id = a.org_id
         WHERE a.org_id = ? AND a.status = 'active' AND ai.role IN ('admin', 'super_admin')"
    );
    if (!$statement) {
        error_log('Admin notification could not be prepared: ' . mysqli_error($conn));
        return false;
    }
    mysqli_stmt_bind_param($statement, 'issssi', $orgId, $type, $title, $message, $link, $orgId);
    $success = mysqli_stmt_execute($statement);
    if (!$success) {
        error_log('Admin notification insert failed: ' . mysqli_stmt_error($statement));
    }
    mysqli_stmt_close($statement);
    return $success;
}
