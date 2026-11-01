<?php
// Public defaults. Secrets belong in config.local.php or environment variables.
$config = [
    'db_host' => '127.0.0.1',
    'db_port' => 3306,
    'db_name' => 'wolf_to_wow_demo',
    'db_user' => 'root',
    'db_password' => '',
    'session_name' => 'wolf_to_wow_portfolio',
];

$localFile = __DIR__ . '/config.local.php';
if (is_file($localFile)) {
    $local = require $localFile;
    if (!is_array($local)) {
        throw new RuntimeException('config.local.php must return an array.');
    }
    $config = array_replace($config, $local);
}

foreach ([
    'db_host' => 'DB_HOST',
    'db_port' => 'DB_PORT',
    'db_name' => 'DB_NAME',
    'db_user' => 'DB_USER',
    'db_password' => 'DB_PASSWORD',
    'session_name' => 'SESSION_NAME',
] as $key => $environmentName) {
    $value = getenv($environmentName);
    if ($value !== false) {
        $config[$key] = $value;
    }
}
$config['db_port'] = (int) $config['db_port'];
return $config;
