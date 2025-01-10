<?php

declare(strict_types=1);
use App\Enums\AppEnvironment;
use App\Enums\StorageDriver;

$appEnv = $_ENV['APP_ENV'] ?? AppEnvironment::Production->value;
$appName = str_replace(' ', '_', strtolower($_ENV['APP_NAME']));

return [
    'app_name' => $_ENV['APP_NAME'],
    'app_env' => $appEnv,
    'app_url' => $_ENV['APP_URL'],
    'app_key' => $_ENV['APP_KEY'] ?? '',
    'app_debug' => (bool) $_ENV['APP_DEBUG'] ?? 0,
    'app_version' => $_ENV['APP_VERSION'],
    'display_error_details' => AppEnvironment::isDevelopment($appEnv),
    'log_errors' => true,
    'log_error_details' => true,
    'doctrine' => [
        'dev_mode' => AppEnvironment::isDevelopment($appEnv),
        'cache_dir' => STORAGE_PATH . '/doctrine/cache',
        'entities_dir' => APP_PATH . '/entities',
        'connection' => [
            'driver' => $_ENV['DB_DRIVER'] ?? 'pdo_mysql',
            'host' => $_ENV['DB_HOST'],
            'dbname' => $_ENV['DB_NAME'],
            'user' => $_ENV['DB_USERNAME'],
            'password' => $_ENV['DB_PASSWORD'],
        ]
    ],
    'twig' => [
        'cache_dir' => STORAGE_PATH . '/twig/cache',
        'reload' => AppEnvironment::isDevelopment($appEnv) ?? true
    ],
    'session' => [
        'name' => (string) $appName . '_session',
        'flash' => 'flash',
        'httponly' => true,
        'secure' => true,
        'samesite' => 'lax'
    ],
    'storage' => [
        'driver' => StorageDriver::Local
    ],
    'email' => [
        'mailtrap' => $_ENV['MAILTRAP_MAILER_DSN'],
        'from' => $_ENV['MAILER_FROM']
    ],
    'redis' => [
        'host' => $_ENV['REDIS_HOST'],
        'port' => (int) $_ENV['REDIS_PORT'],
        // 'password' => $_ENV['REDIS_PASSWORD']
    ],
    'trusted_proxies' => []
];