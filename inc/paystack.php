<?php

function associa8_paystack_secret(): string
{
    $secret = getenv('ASSOCIA8_PAYSTACK_SECRET') ?: getenv('PAYSTACK_SECRET_KEY') ?: '';
    if ($secret === '' && isset($_ENV['ASSOCIA8_PAYSTACK_SECRET'])) {
        $secret = (string) $_ENV['ASSOCIA8_PAYSTACK_SECRET'];
    }

    return trim($secret);
}

function associa8_paystack_public_key(): string
{
    $publicKey = getenv('ASSOCIA8_PAYSTACK_PUBLIC') ?: getenv('PAYSTACK_PUBLIC_KEY') ?: '';
    if ($publicKey === '' && isset($_ENV['ASSOCIA8_PAYSTACK_PUBLIC'])) {
        $publicKey = (string) $_ENV['ASSOCIA8_PAYSTACK_PUBLIC'];
    }

    return trim($publicKey);
}

function associa8_paystack_enabled(): bool
{
    return associa8_paystack_secret() !== '';
}

function associa8_paystack_base_url(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) === '443'
        ? 'https'
        : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    if (!preg_match('/^(?:[A-Za-z0-9.-]+|\[[A-Fa-f0-9:]+\])(?::[0-9]{1,5})?$/', (string) $host)) {
        error_log('Paystack callback URL used an invalid request host.');
        $host = 'localhost';
    }
    $scriptPath = $_SERVER['SCRIPT_NAME'] ?? '/associa8/admin/member/proc-event-rsvp.php';
    $basePath = dirname($scriptPath, 3);
    if ($basePath === '.' || $basePath === '/' || $basePath === '\\') {
        $basePath = '';
    }

    return $scheme . '://' . $host . $basePath;
}

function associa8_paystack_request(string $path, array $payload = [], string $method = 'POST'): ?array
{
    $secretKey = associa8_paystack_secret();
    if ($secretKey === '') {
        return null;
    }

    $url = 'https://api.paystack.co/' . ltrim($path, '/');
    $body = json_encode($payload, JSON_UNESCAPED_SLASHES);
    $headers = [
        'Authorization: Bearer ' . $secretKey,
        'Content-Type: application/json',
        'Accept: application/json',
    ];

    if (function_exists('curl_init')) {
        $ch = curl_init();
        if ($ch === false) {
            return null;
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || $curlError !== '') {
            error_log('Paystack request failed for ' . $path . ': ' . $curlError);
            return null;
        }

        $decoded = json_decode($response, true);
        return is_array($decoded) ? $decoded : null;
    }

    $contextOptions = [
        'http' => [
            'method' => $method,
            'header' => implode("\r\n", $headers) . "\r\n",
            'content' => $body,
            'ignore_errors' => true,
            'timeout' => 30,
        ],
    ];

    $streamContext = stream_context_create($contextOptions);
    $response = @file_get_contents($url, false, $streamContext);
    if ($response === false) {
        $lastError = error_get_last();
        error_log('Paystack request failed for ' . $path . ': ' . (string) ($lastError['message'] ?? 'stream request failed'));
        return null;
    }

    $decoded = json_decode($response, true);
    return is_array($decoded) ? $decoded : null;
}

function associa8_paystack_initialize_checkout(int $amountKobo, string $email, string $reference, string $callbackUrl, array $metadata = []): ?string
{
    $response = associa8_paystack_request('transaction/initialize', [
        'email' => $email,
        'amount' => (string) $amountKobo,
        'currency' => 'NGN',
        'reference' => $reference,
        'callback_url' => $callbackUrl,
        'metadata' => $metadata,
    ], 'POST');

    if (!is_array($response) || (($response['status'] ?? false) !== true)) {
        error_log('Paystack initialization failed: ' . json_encode($response));
        return null;
    }

    $authorizationUrl = (string) ($response['data']['authorization_url'] ?? '');
    return $authorizationUrl !== '' ? $authorizationUrl : null;
}

function associa8_paystack_verify_reference(string $reference): ?array
{
    $response = associa8_paystack_request('transaction/verify/' . rawurlencode($reference), [], 'GET');
    if (!is_array($response) || (($response['status'] ?? false) !== true)) {
        error_log('Paystack verification failed for reference ' . $reference . ': ' . json_encode($response));
        return null;
    }

    return $response['data'] ?? null;
}
