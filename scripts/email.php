<?php

declare(strict_types=1);

use App\Services\EmailService;

require_once __DIR__ . '/../bootstrap.php';

$container->get(EmailService::class)->send();

