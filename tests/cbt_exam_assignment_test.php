<?php

$source = file_get_contents(__DIR__ . '/../proc-validate-cbt-email.php');
if ($source === false) {
    fwrite(STDERR, "FAIL: unable to read proc-validate-cbt-email.php\n");
    exit(1);
}

if (strpos($source, 'ORDER BY e.id LIMIT 1') !== false) {
    fwrite(STDERR, "FAIL: fallback exam selection still auto-picks a generic active exam\n");
    exit(1);
}

if (strpos($source, 'This applicant has no exam assigned yet') === false) {
    fwrite(STDERR, "FAIL: applicant exam assignment guard missing\n");
    exit(1);
}

echo "Exam assignment guard prevents auto-assigning the wrong CBT exam.\n";
