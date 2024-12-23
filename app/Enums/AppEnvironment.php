<?php

declare(strict_types=1);

namespace App\Enums;

enum AppEnvironment: string
{
    case Development = 'development';

    case Production = 'production';

    public static function isDevelopment(string $appEnvironment): bool
    {
        return self::Development === self::tryFrom($appEnvironment);
    }

    public static function isProduction(string $appEnvironment): bool
    {
        return self::Production === self::tryFrom($appEnvironment);
    }
}