<?php

declare(strict_types=1);
use App\Enums\AppEnvironment;

$appEnv = $_ENV['APP_ENV'] ?? AppEnvironment::Production->value;

return [
    'app_name' => $_ENV['APP_NAME'],
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
            'password' => $_ENV['DB_PASSWORD']
        ]
    ],
    'twig' => [
        'cache_dir' => STORAGE_PATH . '/twig/cache',
        'reload' => AppEnvironment::isDevelopment($appEnv) ?? true
    ]
];