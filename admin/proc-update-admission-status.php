<?php
require_once "inc/auth.php";
require_once "../inc/db.php";
require_once "../inc/admission-notifications.php";
require_once "../inc/admin-notifications.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admission-management.php');
    exit;
}

$csrfToken = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['admission_status_csrf'] ?? '';
if (
    !is_string($csrfToken)
    || !is_string($sessionToken)
    || $sessionToken === ''
    || !hash_equals($sessionToken, $csrfToken)
) {
    header('Location: admission-management.php?status=error&msg=' . urlencode('Your form expired. Reload the page and try again.'));
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
$admissionOrgId = (int) ($admission['org_id'] ?? 0);
if ($admissionOrgId < 1) {
    header('Location: admission-management.php?status=error&msg=' . urlencode('The selected admission is not linked to an organization.'));
    exit;
}

$currentStatus = $admission['status'];
$validTransition = ($currentStatus === 'pending' && $newStatus === 'under_review')
    || ($currentStatus === 'pending' && $newStatus === 'approved')
    || (in_array($currentStatus, ['under_review', 'cbt_completed'], true) && in_array($newStatus, ['approved', 'rejected'], true));

if (!$validTransition) {
    header('Location: admission-management.php?status=error&msg=' . urlencode('That status transition is not allowed from the current stage.'));
    exit;
}

if ($newStatus === 'approved') {
    $memberOrgId = $admissionOrgId;
    $zoneId = 0;
    $subzoneId = 0;
    $titleId = 0;

    if (!mysqli_begin_transaction($conn)) {
        error_log('Admission approval transaction could not start: ' . mysqli_error($conn));
        header('Location: admission-management.php?status=error&msg=' . urlencode('The member conversion service is unavailable.'));
        exit;
    }
    $memberCheck = mysqli_prepare($conn, 'SELECT id FROM members WHERE email = ? AND org_id = ? LIMIT 1');
    if (!$memberCheck) {
        mysqli_rollback($conn);
        error_log('Admission member duplicate check prepare failed: ' . mysqli_error($conn));
        header('Location: admission-management.php?status=error&msg=' . urlencode('The member conversion service is unavailable.'));
        exit;
    }
    mysqli_stmt_bind_param($memberCheck, 'si', $admission['email'], $memberOrgId);
    if (!mysqli_stmt_execute($memberCheck)) {
        mysqli_rollback($conn);
        error_log('Admission member duplicate check failed: ' . mysqli_stmt_error($memberCheck));
        header('Location: admission-management.php?status=error&msg=' . urlencode('The member conversion service is unavailable.'));
        exit;
    }
    $memberCheckResult = mysqli_stmt_get_result($memberCheck);
    if (!$memberCheckResult) {
        mysqli_rollback($conn);
        error_log('Admission member duplicate check result failed: ' . mysqli_stmt_error($memberCheck));
        header('Location: admission-management.php?status=error&msg=' . urlencode('The member conversion service is unavailable.'));
        exit;
    }
    if (mysqli_num_rows($memberCheckResult) > 0) {
        mysqli_rollback($conn);
        header('Location: admission-management.php?status=error&msg=' . urlencode('A member already exists for this applicant email.'));
        exit;
    }

    $nameParts = preg_split('/\s+/', trim($admission['applicant_name']), 2);
    $firstName = $nameParts[0] ?? $admission['applicant_name'];
    $lastName = $nameParts[1] ?? '';
    $codeResult = mysqli_query($conn, "SELECT MAX(CAST(SUBSTRING(member_code, 5) AS UNSIGNED)) AS last_number FROM members WHERE member_code REGEXP '^ASC-[0-9]+$'");
    if (!$codeResult) {
        error_log('Admission member code lookup failed: ' . mysqli_error($conn));
        mysqli_rollback($conn);
        header('Location: admission-management.php?status=error&msg=' . urlencode('The member conversion service is unavailable.'));
        exit;
    }
    $lastNumber = (int) (mysqli_fetch_assoc($codeResult)['last_number'] ?? 0);
    $memberCode = 'ASC-' . str_pad((string) ($lastNumber + 1), 3, '0', STR_PAD_LEFT);
    while (true) {
        $codeCheck = mysqli_prepare($conn, 'SELECT id FROM members WHERE member_code = ? LIMIT 1');
        if (!$codeCheck) {
            error_log('Admission member code check prepare failed: ' . mysqli_error($conn));
            mysqli_rollback($conn);
            header('Location: admission-management.php?status=error&msg=' . urlencode('The member conversion service is unavailable.'));
            exit;
        }
        mysqli_stmt_bind_param($codeCheck, 's', $memberCode);
        if (!mysqli_stmt_execute($codeCheck)) {
            error_log('Admission member code check failed: ' . mysqli_stmt_error($codeCheck));
            mysqli_rollback($conn);
            header('Location: admission-management.php?status=error&msg=' . urlencode('The member conversion service is unavailable.'));
            exit;
        }
        $codeResult = mysqli_stmt_get_result($codeCheck);
        if (!$codeResult) {
            error_log('Admission member code check result failed: ' . mysqli_stmt_error($codeCheck));
            mysqli_rollback($conn);
            header('Location: admission-management.php?status=error&msg=' . urlencode('The member conversion service is unavailable.'));
            exit;
        }
        if (mysqli_num_rows($codeResult) === 0) {
            break;
        }
        $lastNumber++;
        $memberCode = 'ASC-' . str_pad((string) ($lastNumber + 1), 3, '0', STR_PAD_LEFT);
    }
    $temporaryPassword = strtoupper(substr(bin2hex(random_bytes(5)), 0, 10));
    $passwordHash = password_hash($temporaryPassword, PASSWORD_DEFAULT);
    $joinedDate = date('Y-m-d');
    $memberStatus = 'active';

    $memberStatement = mysqli_prepare($conn, 'INSERT INTO members (org_id, member_code, first_name, last_name, email, password, phone, code, status, joined_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $updateSql = 'UPDATE admissions SET status = ? WHERE id = ? AND status = ? AND org_id = ?';
    $updateStatement = mysqli_prepare($conn, $updateSql);
    if (!$memberStatement || !$updateStatement) {
        error_log('Admission approval statement prepare failed: ' . mysqli_error($conn));
        mysqli_rollback($conn);
        header('Location: admission-management.php?status=error&msg=' . urlencode('The member conversion service is unavailable.'));
        exit;
    }
    mysqli_stmt_bind_param($memberStatement, 'issssssss', $memberOrgId, $memberCode, $firstName, $lastName, $admission['email'], $passwordHash, $admission['phone'], $memberCode, $memberStatus, $joinedDate);
    mysqli_stmt_bind_param($updateStatement, 'sisi', $newStatus, $admissionId, $currentStatus, $admissionOrgId);
    $memberCreated = mysqli_stmt_execute($memberStatement);
    $statusUpdated = mysqli_stmt_execute($updateStatement);
    if (!$memberCreated || !$statusUpdated || mysqli_stmt_affected_rows($updateStatement) !== 1) {
        error_log('Admission approval write failed: ' . mysqli_stmt_error($memberStatement) . ' ' . mysqli_stmt_error($updateStatement));
        mysqli_rollback($conn);
        header('Location: admission-management.php?status=error&msg=' . urlencode('The applicant could not be converted into a member.'));
        exit;
    }
    if (!mysqli_commit($conn)) {
        error_log('Admission approval transaction commit failed: ' . mysqli_error($conn));
        mysqli_rollback($conn);
        header('Location: admission-management.php?status=error&msg=' . urlencode('The applicant could not be converted into a member.'));
        exit;
    }

    notify_admission_status($conn, $admissionId, $newStatus, $admission['applicant_name'], $admission['email']);
    admin_notify_organization($conn, $admissionOrgId, 'admission', 'Admission status updated', $admission['applicant_name'] . '\'s application is now ' . str_replace('_', ' ', $newStatus) . '.', 'admission-management.php');
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
    $updateSql = $newStatus === 'under_review'
        ? 'UPDATE admissions SET status = ?, cbt_response_deadline = DATE_ADD(UTC_TIMESTAMP(), INTERVAL 36 HOUR), cbt_scheduled_at = NULL WHERE id = ? AND status = ? AND org_id = ?'
        : 'UPDATE admissions SET status = ?, cbt_response_deadline = NULL, cbt_scheduled_at = NULL WHERE id = ? AND status = ? AND org_id = ?';
    $updateStatement = mysqli_prepare($conn, $updateSql);
    if (!$updateStatement) {
        header('Location: admission-management.php?status=error&msg=' . urlencode('The admission status service is unavailable.'));
        exit;
    }
    mysqli_stmt_bind_param($updateStatement, 'sisi', $newStatus, $admissionId, $currentStatus, $admissionOrgId);
    $success = mysqli_stmt_execute($updateStatement) && mysqli_stmt_affected_rows($updateStatement) === 1;
    if ($success) {
        notify_admission_status($conn, $admissionId, $newStatus, $admission['applicant_name'], $admission['email']);
        admin_notify_organization($conn, $admissionOrgId, 'admission', 'Admission status updated', $admission['applicant_name'] . '\'s application is now ' . str_replace('_', ' ', $newStatus) . '.', 'admission-management.php');
    }
    $message = $success ? 'Admission moved to ' . ucwords(str_replace('_', ' ', $newStatus)) . '.' : 'The admission status could not be updated.';
}

header('Location: admission-management.php?status=success&msg=' . urlencode($message));
exit;
