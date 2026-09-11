<?php
require_once "../inc/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: suspension.php');
    exit;
}

$memberId = (int) ($_POST['member_id'] ?? 0);
$actionType = $_POST['action_type'] ?? '';
$status = $_POST['status'] ?? '';
$reason = trim($_POST['reason'] ?? '');
$actionDate = $_POST['action_date'] ?? '';

$validStatuses = ['active', 'under_review', 'completed'];
if ($memberId < 1 || !in_array($actionType, ['suspension', 'reinstatement'], true) || !in_array($status, $validStatuses, true) || $reason === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $actionDate)) {
    header('Location: add-suspension.php?type=' . urlencode($actionType) . '&status=error&msg=' . urlencode('Complete all action fields with valid values.'));
    exit;
}

if ($actionType === 'reinstatement' && $status !== 'completed') {
    header('Location: add-suspension.php?type=reinstatement&status=error&msg=' . urlencode('A reinstatement must be completed.'));
    exit;
}
if ($actionType === 'suspension' && !in_array($status, ['active', 'under_review'], true)) {
    header('Location: add-suspension.php?type=suspension&status=error&msg=' . urlencode('Choose a valid suspension status.'));
    exit;
}

mysqli_begin_transaction($conn);
$memberStatement = mysqli_prepare($conn, 'SELECT status FROM members WHERE id = ? FOR UPDATE');
mysqli_stmt_bind_param($memberStatement, 'i', $memberId);
mysqli_stmt_execute($memberStatement);
$memberResult = mysqli_stmt_get_result($memberStatement);
$member = $memberResult ? mysqli_fetch_assoc($memberResult) : null;

$error = '';
if (!$member) {
    $error = 'The selected member could not be found.';
} elseif ($actionType === 'suspension' && $member['status'] === 'suspended') {
    $error = 'This member is already suspended.';
} elseif ($actionType === 'reinstatement' && $member['status'] !== 'suspended') {
    $error = 'Only a suspended member can be reinstated.';
}

if ($error === '') {
    $newMemberStatus = $actionType === 'reinstatement' ? 'active' : ($status === 'under_review' ? 'pending' : 'suspended');
    $updateStatement = mysqli_prepare($conn, 'UPDATE members SET status = ? WHERE id = ?');
    mysqli_stmt_bind_param($updateStatement, 'si', $newMemberStatus, $memberId);
    $updateSuccess = mysqli_stmt_execute($updateStatement);

    $historyStatement = mysqli_prepare($conn, 'INSERT INTO suspensions (member_id, reason, action_type, status, action_date) VALUES (?, ?, ?, ?, ?)');
    mysqli_stmt_bind_param($historyStatement, 'issss', $memberId, $reason, $actionType, $status, $actionDate);
    $historySuccess = mysqli_stmt_execute($historyStatement);

    if (!$updateSuccess || !$historySuccess) {
        $error = 'The member status and history could not be saved.';
    }
}

if ($error !== '') {
    mysqli_rollback($conn);
    header('Location: add-suspension.php?type=' . urlencode($actionType) . '&status=error&msg=' . urlencode($error));
    exit;
}

mysqli_commit($conn);
header('Location: suspension.php?status=success&msg=' . urlencode(ucfirst($actionType) . ' recorded successfully.'));
exit;
