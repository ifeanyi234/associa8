<?php
require_once "inc/auth.php";
require_once "../inc/db.php";
require_once "../inc/admission-notifications.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admission-management.php');
    exit;
}

$admissionId = (int) ($_POST['admission_id'] ?? 0);
$newStatus = $_POST['status'] ?? '';
$allowedStatuses = ['under_review', 'approved', 'rejected'];

if ($admissionId < 1 || !in_array($newStatus, $allowedStatuses, true)) {
    header('Location: admission-management.php?status=error&msg=' . urlencode('Choose a valid admission status.'));
    exit;
}

$orgId = isset($_SESSION['org_id']) && $_SESSION['org_id'] !== null ? (int) $_SESSION['org_id'] : null;
$adminRole = $_SESSION['admin_role'] ?? 'admin';
if ($adminRole !== 'super_admin' && $orgId === null) {
    header('Location: admission-management.php?status=error&msg=' . urlencode('Your account is not linked to an organization.'));
    exit;
}

$findSql = 'SELECT status, org_id, applicant_name, email, phone FROM admissions WHERE id = ?';
if ($adminRole !== 'super_admin') {
    $findSql .= ' AND org_id = ?';
}
$findSql .= ' LIMIT 1';
$findStatement = mysqli_prepare($conn, $findSql);
if (!$findStatement) {
    header('Location: admission-management.php?status=error&msg=' . urlencode('The admission status service is unavailable.'));
    exit;
}

if ($adminRole === 'super_admin') {
    mysqli_stmt_bind_param($findStatement, 'i', $admissionId);
} else {
    mysqli_stmt_bind_param($findStatement, 'ii', $admissionId, $orgId);
}
mysqli_stmt_execute($findStatement);
$admissionResult = mysqli_stmt_get_result($findStatement);
$admission = $admissionResult ? mysqli_fetch_assoc($admissionResult) : null;

if (!$admission) {
    header('Location: admission-management.php?status=error&msg=' . urlencode('The selected admission could not be found.'));
    exit;
}

$currentStatus = $admission['status'];
$validTransition = ($currentStatus === 'pending' && $newStatus === 'under_review')
    || (in_array($currentStatus, ['under_review', 'cbt_completed'], true) && in_array($newStatus, ['approved', 'rejected'], true));

if (!$validTransition) {
    header('Location: admission-management.php?status=error&msg=' . urlencode('That status transition is not allowed from the current stage.'));
    exit;
}

if ($newStatus === 'approved') {
    $memberCheck = mysqli_prepare($conn, 'SELECT id FROM members WHERE email = ? LIMIT 1');
    mysqli_stmt_bind_param($memberCheck, 's', $admission['email']);
    mysqli_stmt_execute($memberCheck);
    $memberCheckResult = mysqli_stmt_get_result($memberCheck);
    if ($memberCheckResult && mysqli_num_rows($memberCheckResult) > 0) {
        header('Location: admission-management.php?status=error&msg=' . urlencode('A member already exists for this applicant email.'));
        exit;
    }

    $memberOrgId = $admission['org_id'] !== null ? (int) $admission['org_id'] : $orgId;
    if ($memberOrgId < 1) {
        header('Location: admission-management.php?status=error&msg=' . urlencode('The admission is not linked to an organization.'));
        exit;
    }

    $nameParts = preg_split('/\s+/', trim($admission['applicant_name']), 2);
    $firstName = $nameParts[0] ?? $admission['applicant_name'];
    $lastName = $nameParts[1] ?? '';
    $codeResult = mysqli_query($conn, "SELECT MAX(CAST(SUBSTRING(member_code, 5) AS UNSIGNED)) AS last_number FROM members WHERE member_code REGEXP '^ASC-[0-9]+$'");
    $lastNumber = $codeResult ? (int) (mysqli_fetch_assoc($codeResult)['last_number'] ?? 0) : 0;
    $memberCode = 'ASC-' . str_pad((string) ($lastNumber + 1), 3, '0', STR_PAD_LEFT);
    while (true) {
        $codeCheck = mysqli_prepare($conn, 'SELECT id FROM members WHERE member_code = ? LIMIT 1');
        mysqli_stmt_bind_param($codeCheck, 's', $memberCode);
        mysqli_stmt_execute($codeCheck);
        $codeResult = mysqli_stmt_get_result($codeCheck);
        if (!$codeResult || mysqli_num_rows($codeResult) === 0) {
            break;
        }
        $lastNumber++;
        $memberCode = 'ASC-' . str_pad((string) ($lastNumber + 1), 3, '0', STR_PAD_LEFT);
    }
    $temporaryPassword = strtoupper(substr(bin2hex(random_bytes(5)), 0, 10));
    $passwordHash = password_hash($temporaryPassword, PASSWORD_DEFAULT);
    $joinedDate = date('Y-m-d');
    $memberStatus = 'active';

    mysqli_begin_transaction($conn);
    $memberStatement = mysqli_prepare($conn, 'INSERT INTO members (org_id, member_code, first_name, last_name, email, password, phone, code, zone_id, title_id, status, joined_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NULL, NULL, ?, ?)');
    $updateStatement = mysqli_prepare($conn, 'UPDATE admissions SET status = ? WHERE id = ?');
    if (!$memberStatement || !$updateStatement) {
        mysqli_rollback($conn);
        header('Location: admission-management.php?status=error&msg=' . urlencode('The member conversion service is unavailable.'));
        exit;
    }
    mysqli_stmt_bind_param($memberStatement, 'isssssssss', $memberOrgId, $memberCode, $firstName, $lastName, $admission['email'], $passwordHash, $admission['phone'], $memberCode, $memberStatus, $joinedDate);
    mysqli_stmt_bind_param($updateStatement, 'si', $newStatus, $admissionId);
    $memberCreated = mysqli_stmt_execute($memberStatement);
    $statusUpdated = mysqli_stmt_execute($updateStatement);
    if (!$memberCreated || !$statusUpdated || mysqli_stmt_affected_rows($updateStatement) !== 1) {
        mysqli_rollback($conn);
        header('Location: admission-management.php?status=error&msg=' . urlencode('The applicant could not be converted into a member.'));
        exit;
    }
    mysqli_commit($conn);

    notify_admission_status($conn, $admissionId, $newStatus, $admission['applicant_name'], $admission['email']);
    $approvalBody = '<p>Hello ' . htmlspecialchars($admission['applicant_name'], ENT_QUOTES, 'UTF-8') . ',</p>'
        . '<p>Your Associa8 application has been approved and your member account is ready.</p>'
        . '<p><strong>Email:</strong> ' . htmlspecialchars($admission['email'], ENT_QUOTES, 'UTF-8') . '<br><strong>Temporary password:</strong> ' . htmlspecialchars($temporaryPassword, ENT_QUOTES, 'UTF-8') . '</p>'
        . '<p><a href="' . htmlspecialchars((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\') . '/member-login.php', ENT_QUOTES, 'UTF-8') . '">Sign in to the member portal</a></p>'
        . '<p>Please sign in and change your password after your first login.</p>';
    if (!send_app_mail($admission['email'], $admission['applicant_name'], 'Your Associa8 membership is approved', $approvalBody)) {
        error_log('Member approval email failed for admission ' . $admissionId . '.');
    }
    $message = 'Admission approved and member account created.';
} else {
    $updateStatement = $newStatus === 'under_review'
        ? mysqli_prepare($conn, 'UPDATE admissions SET status = ?, cbt_response_deadline = DATE_ADD(UTC_TIMESTAMP(), INTERVAL 36 HOUR) WHERE id = ?')
        : mysqli_prepare($conn, 'UPDATE admissions SET status = ?, cbt_response_deadline = NULL WHERE id = ?');
    if (!$updateStatement) {
        header('Location: admission-management.php?status=error&msg=' . urlencode('The admission status service is unavailable.'));
        exit;
    }
    mysqli_stmt_bind_param($updateStatement, 'si', $newStatus, $admissionId);
    $success = mysqli_stmt_execute($updateStatement);
    if ($success) {
        notify_admission_status($conn, $admissionId, $newStatus, $admission['applicant_name'], $admission['email']);
    }
    $message = $success ? 'Admission moved to ' . ucwords(str_replace('_', ' ', $newStatus)) . '.' : 'The admission status could not be updated.';
}

header('Location: admission-management.php?status=success&msg=' . urlencode($message));
exit;
