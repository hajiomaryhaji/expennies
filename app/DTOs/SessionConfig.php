<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\SameSite;

class SessionConfig
{
    public function __construct(
        public readonly string $name,
        public readonly string $flash,
        public readonly bool $httpOnly,
        public readonly bool $secure,
        public readonly SameSite $sameSite
    ) {

    }
}