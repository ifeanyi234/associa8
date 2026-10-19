<?php
require_once 'inc/auth.php';
require_once '../inc/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: zones.php');
    exit;
}

$csrfToken = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['subzone_edit_csrf'] ?? '';
if (!is_string($csrfToken) || !is_string($sessionToken) || $sessionToken === ''
    || !hash_equals($sessionToken, $csrfToken)) {
    header('Location: zones.php?status=error&msg=' . urlencode('Your form expired. Reload the page and try again.'));
    exit;
}

$orgId = (int) ($_SESSION['org_id'] ?? 0);
$subzoneId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$name = trim((string) ($_POST['name'] ?? ''));
$coordinatorName = trim((string) ($_POST['coordinator_name'] ?? ''));
$annualDuesInput = trim((string) ($_POST['annual_dues'] ?? ''));
$annualDuesIsValid = $annualDuesInput === ''
    || (preg_match('/^\d{1,10}(?:\.\d{1,2})?$/', $annualDuesInput) === 1
        && (float) $annualDuesInput <= 9999999999.99);

if (
    $orgId < 1 || !$subzoneId || $subzoneId < 1
    || $name === '' || strlen($name) > 100 || strlen($coordinatorName) > 150
    || !$annualDuesIsValid
) {
    $message = $annualDuesIsValid
        ? 'Enter valid sub-zone details before saving.'
        : 'Annual dues must be a non-negative amount with no more than two decimal places.';
    header('Location: zones.php?status=error&msg=' . urlencode($message));
    exit;
}

$annualDues = $annualDuesInput === '' ? '0.00' : $annualDuesInput;
$statement = mysqli_prepare(
    $conn,
    'UPDATE subzones sz
     INNER JOIN zones z ON z.id = sz.zone_id
     SET sz.name = ?, sz.coordinator_name = NULLIF(?, \'\'), sz.annual_dues = ?
     WHERE sz.id = ? AND z.org_id = ?'
);
if (!$statement) {
    error_log('Sub-zone update could not be prepared: ' . mysqli_error($conn));
    header('Location: zones.php?status=error&msg=' . urlencode('The sub-zone could not be updated. Please try again.'));
    exit;
}

mysqli_stmt_bind_param($statement, 'sssii', $name, $coordinatorName, $annualDues, $subzoneId, $orgId);
$updated = mysqli_stmt_execute($statement);
if (!$updated) {
    error_log('Sub-zone update failed: ' . mysqli_stmt_error($statement));
}
mysqli_stmt_close($statement);

if (!$updated) {
    header('Location: zones.php?status=error&msg=' . urlencode('The sub-zone could not be updated. Please try again.'));
    exit;
}

unset($_SESSION['subzone_edit_csrf']);
header('Location: zones.php?status=success&msg=' . urlencode('Sub-zone updated successfully.'));
exit;
