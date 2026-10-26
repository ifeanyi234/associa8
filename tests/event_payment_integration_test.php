<?php
$paystackHelper = __DIR__ . '/../inc/paystack.php';
if (!file_exists($paystackHelper)) {
    fwrite(STDERR, "FAIL: Paystack helper file is missing\n");
    exit(1);
}

$eventForm = file_get_contents(__DIR__ . '/../admin/add-event.php');
if ($eventForm === false) {
    fwrite(STDERR, "FAIL: unable to read admin/add-event.php\n");
    exit(1);
}
if (strpos($eventForm, 'booking_fee') === false && strpos($eventForm, 'Booking fee') === false) {
    fwrite(STDERR, "FAIL: event creation form is missing the booking fee field\n");
    exit(1);
}

$memberEvents = file_get_contents(__DIR__ . '/../admin/member/events.php');
if ($memberEvents === false) {
    fwrite(STDERR, "FAIL: unable to read admin/member/events.php\n");
    exit(1);
}
if (strpos($memberEvents, 'booking fee') === false && strpos($memberEvents, 'NGN') === false) {
    fwrite(STDERR, "FAIL: member events page is not showing event fee information\n");
    exit(1);
}

$rsvpProcessor = file_get_contents(__DIR__ . '/../admin/member/proc-event-rsvp.php');
$paymentCallback = file_get_contents(__DIR__ . '/../admin/member/handle-event-payment.php');
if (
    $rsvpProcessor === false
    || strpos($rsvpProcessor, 'associa8_paystack_initialize_checkout') === false
    || strpos($rsvpProcessor, 'booking_fee') === false
) {
    fwrite(STDERR, "FAIL: paid event booking does not initialize Paystack checkout\n");
    exit(1);
}
if (
    $paymentCallback === false
    || strpos($paymentCallback, "['amount']") === false
    || strpos($paymentCallback, "['reference']") === false
    || strpos($paymentCallback, "['currency']") === false
) {
    fwrite(STDERR, "FAIL: event payment callback does not validate the verified transaction\n");
    exit(1);
}

echo "Paid events initialize Paystack checkout and validate the callback transaction.\n";
