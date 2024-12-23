<?php

declare(strict_types=1);
use App\ConfigParser;
use Slim\App;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

return function (App $app): void {
    $container = $app->getContainer();

    $app->add(TwigMiddleware::create($app, $container->get(Twig::class)));

    $configParser = $container->get(ConfigParser::class);

    $app->addErrorMiddleware(
        (bool) $configParser->get('display_error_details'),
        (bool) $configParser->get('log_errors'),
        (bool) $configParser->get('log_error_details')
    );
};