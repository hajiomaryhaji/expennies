<?php

declare(strict_types=1);

use DI\ContainerBuilder;

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions(CONFIGS_PATH . '/container/container-bindings.php');

return $containerBuilder->build();