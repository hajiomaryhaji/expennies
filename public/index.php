<?php

declare(strict_types=1);

use Doctrine\ORM\EntityManager;
use Slim\App;

require __DIR__ . '/../bootstrap.php';

$app = $container->get(App::class);

$app->run();
