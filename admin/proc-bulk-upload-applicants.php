<?php
require_once 'inc/auth.php';
require_once '../inc/db.php';
require_once '../inc/admission-notifications.php';
require_once '../inc/admin-notifications.php';
require_once 'inc/admission-number.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admission-applicants-bulk-upload.php');
    exit;
}

$adminRole = $_SESSION['admin_role'] ?? 'admin';
$orgId = isset($_SESSION['org_id']) && $_SESSION['org_id'] !== null ? (int) $_SESSION['org_id'] : 0;
if ($adminRole !== 'super_admin' && $orgId < 1) {
    header('Location: admission-applicants-bulk-upload.php?status=error&msg=' . urlencode('Your account is not linked to an association.'));
    exit;
}

$csrfToken = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['cbt_applicants_bulk_upload_csrf'] ?? '';
if (!is_string($csrfToken) || !is_string($sessionToken) || $sessionToken === '' || !hash_equals($sessionToken, $csrfToken)) {
    header('Location: admission-applicants-bulk-upload.php?status=error&msg=' . urlencode('Your form expired. Reload the page and try again.'));
    exit;
}

if (!isset($_FILES['applicants_file']) || $_FILES['applicants_file']['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($_FILES['applicants_file']['tmp_name'])) {
    header('Location: admission-applicants-bulk-upload.php?status=error&msg=' . urlencode('Choose a valid CSV file to import.'));
    exit;
}

$csvPath = $_FILES['applicants_file']['tmp_name'];
$handle = fopen($csvPath, 'r');
if ($handle === false) {
    header('Location: admission-applicants-bulk-upload.php?status=error&msg=' . urlencode('The applicant CSV could not be opened.'));
    exit;
}

$header = fgetcsv($handle);
if ($header === false) {
    fclose($handle);
    header('Location: admission-applicants-bulk-upload.php?status=error&msg=' . urlencode('The uploaded CSV is empty.'));
    exit;
}

$expected = ['applicant_name', 'email', 'phone', 'guarantor_name', 'guarantor_email', 'guarantor_phone', 'guarantor_relationship'];
$normalizedHeader = array_map(static fn($value) => strtolower(trim((string) $value)), $header);
if ($normalizedHeader !== $expected) {
    fclose($handle);
    header('Location: admission-applicants-bulk-upload.php?status=error&msg=' . urlencode('The CSV headers do not match the template. Download a fresh template and keep the header row unchanged.'));
    exit;
}

$statement = mysqli_prepare($conn, 'INSERT INTO admissions (org_id, application_number, applicant_name, email, phone, guarantor_name, guarantor_email, guarantor_phone, guarantor_relationship) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
if (!$statement) {
    fclose($handle);
    error_log('Applicant bulk upload prepare failed: ' . mysqli_error($conn));
    header('Location: admission-applicants-bulk-upload.php?status=error&msg=' . urlencode('The applicant import service is unavailable.'));
    exit;
}

$imported = 0;
$errors = [];
$lineNumber = 1;
while (($row = fgetcsv($handle)) !== false) {
    $lineNumber++;
    if ($row === [null] || count(array_filter($row, static fn($value) => trim((string) $value) !== '')) === 0) {
        continue;
    }

    $values = [
        'applicant_name' => trim((string) ($row[0] ?? '')),
        'email' => trim((string) ($row[1] ?? '')),
        'phone' => trim((string) ($row[2] ?? '')),
        'guarantor_name' => trim((string) ($row[3] ?? '')),
        'guarantor_email' => trim((string) ($row[4] ?? '')),
        'guarantor_phone' => trim((string) ($row[5] ?? '')),
        'guarantor_relationship' => trim((string) ($row[6] ?? '')),
    ];

    if ($values['applicant_name'] === '' || $values['email'] === '' || $values['phone'] === '' || $values['guarantor_name'] === '' || $values['guarantor_phone'] === '' || $values['guarantor_relationship'] === '') {
        $errors[] = 'Row ' . $lineNumber . ': applicant name, email, phone, guarantor name, guarantor phone, and relationship are required.';
        continue;
    }
    if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Row ' . $lineNumber . ': email is not valid.';
        continue;
    }
    if ($values['guarantor_email'] !== '' && !filter_var($values['guarantor_email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Row ' . $lineNumber . ': guarantor email is not valid.';
        continue;
    }

    $existingStatement = mysqli_prepare($conn, 'SELECT id FROM admissions WHERE LOWER(email) = LOWER(?) AND org_id = ? LIMIT 1');
    if (!$existingStatement) {
        fclose($handle);
        error_log('Applicant duplicate check prepare failed: ' . mysqli_error($conn));
        header('Location: admission-applicants-bulk-upload.php?status=error&msg=' . urlencode('The applicant import service is unavailable.'));
        exit;
    }
    mysqli_stmt_bind_param($existingStatement, 'si', $values['email'], $orgId);
    mysqli_stmt_execute($existingStatement);
    $existingResult = mysqli_stmt_get_result($existingStatement);
    if ($existingResult && mysqli_num_rows($existingResult) > 0) {
        mysqli_stmt_close($existingStatement);
        $errors[] = 'Row ' . $lineNumber . ': an admission already exists for this email.';
        continue;
    }
    mysqli_stmt_close($existingStatement);

    $assignedNumber = '';
    for ($attempt = 0; $attempt < 5; $attempt++) {
        try {
            $assignedNumber = admin_generate_application_number($conn);
            break;
        } catch (RuntimeException $exception) {
            error_log('Applicant bulk upload number generation failed: ' . $exception->getMessage());
            if ($attempt === 4) {
                $errors[] = 'Row ' . $lineNumber . ': the application number could not be generated.';
                continue 2;
            }
        }
    }

    mysqli_stmt_bind_param($statement, 'issssssss', $orgId, $assignedNumber, $values['applicant_name'], $values['email'], $values['phone'], $values['guarantor_name'], $values['guarantor_email'], $values['guarantor_phone'], $values['guarantor_relationship']);
    if (!mysqli_stmt_execute($statement)) {
        $errors[] = 'Row ' . $lineNumber . ': ' . mysqli_stmt_error($statement);
        continue;
    }
    $admissionId = (int) mysqli_insert_id($conn);
    notify_admission_status($conn, $admissionId, 'pending', $values['applicant_name'], $values['email']);
    admin_notify_organization($conn, $orgId, 'admission', 'New admission application', $values['applicant_name'] . ' submitted an admission application.', 'admission-management.php');
    $imported++;
}

fclose($handle);

if ($imported === 0 && count($errors) > 0) {
    header('Location: admission-applicants-bulk-upload.php?status=error&msg=' . urlencode(implode(' | ', $errors)));
    exit;
}

if ($imported > 0 && count($errors) > 0) {
    $message = 'Imported ' . $imported . ' applicants with some rows skipped: ' . implode(' | ', $errors);
    header('Location: admission-management.php?status=warning&msg=' . urlencode($message));
    exit;
}

header('Location: admission-management.php?status=success&msg=' . urlencode('Imported ' . $imported . ' applicants successfully.'));
exit;
