<?php

declare(strict_types=1);

$config = [
    'database' => [
        'driver' => getenv('DB_DRIVER') ?: 'mysql',
        'dsn' => getenv('DB_DSN') ?: 'mysql:host=127.0.0.1;dbname=minecraft_panel;charset=utf8mb4',
        'username' => getenv('DB_USERNAME') ?: 'minecraft_panel',
        'password' => getenv('DB_PASSWORD') ?: '',
    ],
    'daemon_token' => getenv('DAEMON_TOKEN') ?: '',
    'daemon_url' => getenv('DAEMON_URL') ?: 'http://127.0.0.1:8765',
    'uploads' => [
        'mode' => getenv('UPLOAD_MODE') ?: 'hardened',
        'root' => getenv('UPLOAD_ROOT') ?: dirname(__DIR__) . '/storage/uploads',
    ],
];

$runtimeConfigPath = dirname(__DIR__) . '/storage/config.php';
if (is_readable($runtimeConfigPath)) {
    $runtimeConfig = require $runtimeConfigPath;
    if (is_array($runtimeConfig)) {
        $config = array_replace_recursive($config, $runtimeConfig);
    }
}

return $config;
