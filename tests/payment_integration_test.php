<?php

$paymentPage = file_get_contents(__DIR__ . '/../admin/member/payment.php');
$paymentProcessor = file_get_contents(__DIR__ . '/../admin/member/proc-initiate-payment.php');
$paymentCallback = file_get_contents(__DIR__ . '/../admin/member/handle-payment.php');
$offlinePage = file_get_contents(__DIR__ . '/../admin/add-payment.php');

if (
    $paymentPage === false
    || strpos($paymentPage, 'proc-initiate-payment.php') === false
    || strpos($paymentPage, 'Pay annual dues') === false
    || strpos($paymentPage, 'Pay levy') === false
) {
    fwrite(STDERR, "FAIL: member dues and levy payment actions are missing\n");
    exit(1);
}
if (
    $paymentProcessor === false
    || strpos($paymentProcessor, 'associa8_paystack_initialize_checkout') === false
    || strpos($paymentProcessor, 'status') === false
) {
    fwrite(STDERR, "FAIL: online payment initiation is missing\n");
    exit(1);
}
if (
    $paymentCallback === false
    || strpos($paymentCallback, "['amount']") === false
    || strpos($paymentCallback, "['reference']") === false
    || strpos($paymentCallback, "['currency']") === false
) {
    fwrite(STDERR, "FAIL: payment callback does not validate the verified transaction\n");
    exit(1);
}
if ($offlinePage === false || strpos($offlinePage, 'proc-record-payment.php') === false) {
    fwrite(STDERR, "FAIL: offline payment recording is no longer available\n");
    exit(1);
}

echo "Member dues and levies support Paystack while offline recording remains available.\n";
