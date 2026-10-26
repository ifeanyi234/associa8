<?php
require_once 'inc/auth.php';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="cbt-applicant-template.csv"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');
if ($output === false) {
    http_response_code(500);
    exit('The CSV template could not be prepared.');
}

fputcsv($output, ['applicant_name', 'email', 'phone', 'guarantor_name', 'guarantor_email', 'guarantor_phone', 'guarantor_relationship']);
fputcsv($output, ['Jane Doe', 'jane@example.com', '08012345678', 'John Doe', 'john@example.com', '08087654321', 'Parent']);

fclose($output);
exit;
