<?php
$host = getenv('ASSOCIA8_DB_HOST') ?: 'localhost';
$db_name = getenv('ASSOCIA8_DB_NAME');
$db_user = getenv('ASSOCIA8_DB_USER');
$db_password = getenv('ASSOCIA8_DB_PASSWORD');

if ($db_name === false || $db_user === false || $db_password === false) {
    $localConfigPath = __DIR__ . '/db.local.php';
    $hasEnvironmentConfig = $db_name !== false || $db_user !== false || $db_password !== false;

    if ($hasEnvironmentConfig || !is_file($localConfigPath)) {
        error_log('Associa8 database configuration is incomplete.');
        http_response_code(500);
        exit('Database configuration is incomplete.');
    }

    $localConfig = require $localConfigPath;
    if (!is_array($localConfig) || !isset($localConfig['name'], $localConfig['user'], $localConfig['password'])) {
        error_log('Associa8 local database configuration is invalid.');
        http_response_code(500);
        exit('Database configuration is invalid.');
    }

    $host = $localConfig['host'] ?? $host;
    $db_name = $localConfig['name'];
    $db_user = $localConfig['user'];
    $db_password = $localConfig['password'];
}

mysqli_report(MYSQLI_REPORT_OFF);
$conn = mysqli_connect($host, $db_user, $db_password, $db_name);

if (!$conn) {
    error_log('Associa8 database connection failed: ' . mysqli_connect_error());
    http_response_code(500);
    exit('Database connection is unavailable.');
}

mysqli_set_charset($conn, 'utf8mb4');