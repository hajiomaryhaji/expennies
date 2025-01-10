<?php

declare(strict_types=1);

namespace App\Helpers;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;



class Debug
{
    public static function debug(mixed $value): void
    {
        (new Logger('Development'))
            ->pushHandler(new StreamHandler(STORAGE_PATH . '/logs/debug.log'))
            ->debug((string) $value);
    }
}