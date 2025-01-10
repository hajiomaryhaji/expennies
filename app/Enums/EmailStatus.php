<?php

declare(strict_types=1);

namespace App\Enums;

enum EmailStatus: int
{
    case Queued = 0;

    case Sent = 1;

    case Failed = 2;
}