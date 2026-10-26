<?php
require_once 'inc/auth.php';
require_once '../inc/db.php';
require_once __DIR__ . '/inc/finance-levies.php';

function levyRedirect(string $type, string $message): void
{
    $_SESSION['levy_notice'] = ['type' => $type, 'text' => $message];
    header('Location: financial.php?tab=dues-levies');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: financial.php?tab=dues-levies');
    exit;
}
if (!admin_finance_levies_available($conn)) {
    levyRedirect('error', 'One-time levies are not enabled in this database. Apply the levy migration and try again.');
}

$role = (string) ($_SESSION['admin_role'] ?? '');
$orgId = (int) ($_SESSION['org_id'] ?? 0);
if (!in_array($role, ['admin', 'super_admin'], true) || $orgId < 1) {
    admin_render_access_denied('Only an organization administrator can create levies.');
}

$csrfToken = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['finance_csrf'] ?? '';
if (!is_string($csrfToken) || !is_string($sessionToken) || $sessionToken === '' || !hash_equals($sessionToken, $csrfToken)) {
    levyRedirect('error', 'Your form expired. Reload the page and try again.');
}

$title = trim((string) ($_POST['title'] ?? ''));
$amount = trim((string) ($_POST['amount'] ?? ''));
$dueDateInput = (string) ($_POST['due_date'] ?? '');
$scopeTarget = (string) ($_POST['scope_target'] ?? '');
$scopeType = 'organization';
$scopeId = null;
$validScope = $scopeTarget === 'organization';
if (preg_match('/^(zone|subzone):([1-9]\d*)$/', $scopeTarget, $scopeMatch) === 1) {
    $scopeType = $scopeMatch[1];
    $scopeId = (int) $scopeMatch[2];
    $validScope = true;
}
$dueDate = DateTimeImmutable::createFromFormat('!Y-m-d', $dueDateInput, new DateTimeZone('Africa/Lagos'));
$dateErrors = DateTimeImmutable::getLastErrors();
$validDate = $dueDate !== false
    && $dueDate->format('Y-m-d') === $dueDateInput
    && (!$dateErrors || ($dateErrors['warning_count'] === 0 && $dateErrors['error_count'] === 0));

if (
    $title === '' || strlen($title) > 150
    || !preg_match('/^\d{1,10}(?:\.\d{1,2})?$/', $amount)
    || (float) $amount <= 0 || (float) $amount > 9999999999.99
    || !$validDate || !$validScope
) {
    levyRedirect('error', 'Enter a title, positive amount, valid due date, and valid target.');
}

if (!mysqli_begin_transaction($conn)) {
    error_log('Levy creation transaction could not start: ' . mysqli_error($conn));
    levyRedirect('error', 'The levy could not be created. Please try again later.');
}

$scopeName = 'Entire organization';
$targetZoneId = null;
if ($scopeType === 'zone') {
    $targetStmt = mysqli_prepare($conn, 'SELECT id, name FROM zones WHERE id = ? AND org_id = ? FOR UPDATE');
    if (!$targetStmt) {
        mysqli_rollback($conn);
        error_log('Levy zone validation could not be prepared: ' . mysqli_error($conn));
        levyRedirect('error', 'The levy target could not be checked. Please try again later.');
    }
    mysqli_stmt_bind_param($targetStmt, 'ii', $scopeId, $orgId);
} elseif ($scopeType === 'subzone') {
    $targetStmt = mysqli_prepare(
        $conn,
        'SELECT sz.id, sz.name, sz.zone_id, z.name AS zone_name
         FROM subzones sz
         INNER JOIN zones z ON z.id = sz.zone_id
         WHERE sz.id = ? AND z.org_id = ?
         FOR UPDATE'
    );
    if (!$targetStmt) {
        mysqli_rollback($conn);
        error_log('Levy sub-zone validation could not be prepared: ' . mysqli_error($conn));
        levyRedirect('error', 'The levy target could not be checked. Please try again later.');
    }
    mysqli_stmt_bind_param($targetStmt, 'ii', $scopeId, $orgId);
} else {
    $targetStmt = null;
}

if ($targetStmt) {
    if (!mysqli_stmt_execute($targetStmt)) {
        mysqli_rollback($conn);
        error_log('Levy target validation failed: ' . mysqli_stmt_error($targetStmt));
        mysqli_stmt_close($targetStmt);
        levyRedirect('error', 'The levy target could not be checked. Please try again later.');
    }
    $targetResult = mysqli_stmt_get_result($targetStmt);
    $target = $targetResult ? mysqli_fetch_assoc($targetResult) : null;
    mysqli_stmt_close($targetStmt);
    if (!$target) {
        mysqli_rollback($conn);
        levyRedirect('error', 'The selected zone or sub-zone does not belong to your organization.');
    }
    if ($scopeType === 'zone') {
        $scopeName = (string) $target['name'];
        $targetZoneId = (int) $target['id'];
    } else {
        $scopeName = (string) $target['zone_name'] . ' / ' . (string) $target['name'];
        $targetZoneId = (int) $target['zone_id'];
    }
}

$insertLevy = $scopeType === 'organization'
    ? mysqli_prepare(
        $conn,
        'INSERT INTO levies (org_id, title, amount, due_date, scope_type, scope_id, created_by)
         VALUES (?, ?, ?, ?, ?, NULL, ?)'
    )
    : mysqli_prepare(
        $conn,
        'INSERT INTO levies (org_id, title, amount, due_date, scope_type, scope_id, created_by)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
if (!$insertLevy) {
    mysqli_rollback($conn);
    error_log('Levy insert could not be prepared: ' . mysqli_error($conn));
    levyRedirect('error', 'The levy could not be created. Please try again later.');
}
$createdBy = (int) ($_SESSION['user_id'] ?? 0);
if ($scopeType === 'organization') {
    mysqli_stmt_bind_param($insertLevy, 'isdssi', $orgId, $title, $amount, $dueDateInput, $scopeType, $createdBy);
} else {
    mysqli_stmt_bind_param($insertLevy, 'isdssii', $orgId, $title, $amount, $dueDateInput, $scopeType, $scopeId, $createdBy);
}
if (!mysqli_stmt_execute($insertLevy)) {
    mysqli_rollback($conn);
    error_log('Levy insert failed: ' . mysqli_stmt_error($insertLevy));
    mysqli_stmt_close($insertLevy);
    levyRedirect('error', 'The levy could not be created. Please try again later.');
}
$levyId = (int) mysqli_insert_id($conn);
mysqli_stmt_close($insertLevy);

$memberSql = "INSERT INTO member_levies (levy_id, member_id, amount)
              SELECT ?, m.id, ? FROM members m
              WHERE m.org_id = ? AND m.status = 'active'";
$memberTypes = 'isi';
$memberParams = [$levyId, $amount, $orgId];
if ($scopeType === 'zone') {
    $memberSql .= ' AND m.zone_id = ?';
    $memberTypes .= 'i';
    $memberParams[] = $scopeId;
} elseif ($scopeType === 'subzone') {
    $memberSql .= ' AND m.subzone_id = ? AND m.zone_id = ?';
    $memberTypes .= 'ii';
    $memberParams[] = $scopeId;
    $memberParams[] = $targetZoneId;
}
$assignMembers = mysqli_prepare($conn, $memberSql);
if (!$assignMembers) {
    mysqli_rollback($conn);
    error_log('Levy member assignment could not be prepared: ' . mysqli_error($conn));
    levyRedirect('error', 'The levy could not be assigned. Please try again later.');
}
// mysqli_stmt_bind_param requires each dynamic value to be passed by reference.
$assignmentParams = [$assignMembers, $memberTypes];
foreach ($memberParams as &$memberParam) {
    $assignmentParams[] = &$memberParam;
}
unset($memberParam);
if (!call_user_func_array('mysqli_stmt_bind_param', $assignmentParams)) {
    mysqli_rollback($conn);
    error_log('Levy member assignment parameters could not be bound: ' . mysqli_stmt_error($assignMembers));
    mysqli_stmt_close($assignMembers);
    levyRedirect('error', 'The levy could not be assigned. Please try again later.');
}
if (!mysqli_stmt_execute($assignMembers)) {
    mysqli_rollback($conn);
    error_log('Levy member assignment failed: ' . mysqli_stmt_error($assignMembers));
    mysqli_stmt_close($assignMembers);
    levyRedirect('error', 'The levy could not be assigned. Please try again later.');
}
$assignedCount = mysqli_stmt_affected_rows($assignMembers);
mysqli_stmt_close($assignMembers);
if ($assignedCount < 1) {
    mysqli_rollback($conn);
    levyRedirect('error', 'There are no active members in the selected target.');
}

if (!mysqli_commit($conn)) {
    error_log('Levy creation transaction could not commit: ' . mysqli_error($conn));
    mysqli_rollback($conn);
    levyRedirect('error', 'The levy could not be created. Please try again later.');
}

levyRedirect('success', 'Levy created for ' . $assignedCount . ' active member' . ($assignedCount === 1 ? '' : 's') . ' in ' . $scopeName . '.');
