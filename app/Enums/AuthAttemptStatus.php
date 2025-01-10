<?php

declare(strict_types=1);

namespace App\Enums;

enum AuthAttemptStatus
{
    case FAILED;

    case SUCCESS;

    case TWO_FACTOR_AUTH;
}