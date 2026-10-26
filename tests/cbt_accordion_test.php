<?php

$page = file_get_contents(__DIR__ . '/../admin/cbt-questions.php');
if ($page === false) {
    fwrite(STDERR, "FAIL: unable to read cbt-questions.php\n");
    exit(1);
}

$checks = [
    'exam-accordion',
    '<details class="exam-accordion-item"',
    '<summary>',
    'count($examQuestions); ?> questions)',
    'justify-content: flex-start;',
    'gap: 8px;',
    'exam-list-item',
    'grid-template-columns: minmax(0, 1fr) auto;',
    '@media (max-width: 768px)'
];

foreach ($checks as $check) {
    if (strpos($page, $check) === false) {
        fwrite(STDERR, "FAIL: missing accordion markup: {$check}\n");
        exit(1);
    }
}

echo "CBT exam accordion markup present.\n";
