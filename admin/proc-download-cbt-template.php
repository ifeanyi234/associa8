<?php
require_once "inc/auth.php";

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="cbt-question-template.csv"');
header('X-Content-Type-Options: nosniff');

$output = fopen('php://output', 'wb');
if ($output === false) {
    error_log('CBT CSV template output could not be opened.');
    http_response_code(500);
    exit('The CBT template could not be generated.');
}

fputcsv($output, [
    'Question',
    'Option A',
    'Option B',
    'Option C',
    'Option D',
    'Option E (optional)',
    'Correct Option (A-E)',
    'Image Filename (optional)',
]);
fputcsv($output, [
    'What is the capital of France?',
    'Paris',
    'Lyon',
    'Marseille',
    'Nice',
    '',
    'A',
    '',
]);
fclose($output);
exit;
