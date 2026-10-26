<?php

$admissionPage = file_get_contents(__DIR__ . '/../admin/admission-management.php');
$cbtPage = file_get_contents(__DIR__ . '/../admin/cbt-applicants.php');
if ($admissionPage === false || $cbtPage === false) {
    fwrite(STDERR, "FAIL: unable to read admission or CBT applicants page\n");
    exit(1);
}

if (strpos($cbtPage, 'CBT Scheduled') !== false) {
    fwrite(STDERR, "FAIL: CBT applicants table still shows the scheduled column\n");
    exit(1);
}

if (strpos($cbtPage, 'Bulk Upload') !== false || strpos($cbtPage, 'cbt-applicants-bulk-upload.php') !== false) {
    fwrite(STDERR, "FAIL: applicant bulk upload is still linked from CBT applicants\n");
    exit(1);
}

if (strpos($admissionPage, 'admission-applicants-bulk-upload.php') === false) {
    fwrite(STDERR, "FAIL: bulk upload link missing from admission management\n");
    exit(1);
}

echo "Applicant bulk upload is linked from admissions and not CBT applicants.\n";
