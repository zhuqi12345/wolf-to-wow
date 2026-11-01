<?php
$config = require __DIR__ . '/config.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli(
        $config['db_host'],
        $config['db_user'],
        $config['db_password'],
        $config['db_name'],
        $config['db_port']
    );
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $exception) {
    if (PHP_SAPI === 'cli') {
        throw $exception;
    }
    error_log('Wolf to Wow database connection failed: ' . $exception->getMessage());
    http_response_code(503);
    exit('Database unavailable. Please check the local setup instructions.');
}
