<?php

declare(strict_types=1);

use App\ConfigParser;
use App\Enums\AppEnvironment;
use App\Middlewares\CsrfFieldsMiddleware;
use App\Middlewares\DisplayFormValidationErrorsMiddleware;
use App\Middlewares\FormValidationExceptionMiddleware;
use App\Middlewares\KeepOldValidFormDataMiddleware;
use App\Middlewares\StartSessionMiddleware;
use Clockwork\Clockwork;
use Clockwork\Support\Slim\ClockworkMiddleware;
use Slim\App;
use Slim\Middleware\MethodOverrideMiddleware;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

return function (App $app): void {
    $container = $app->getContainer();
    $configParser = $container->get(ConfigParser::class);

    $app->add(MethodOverrideMiddleware::class);
    $app->add(CsrfFieldsMiddleware::class);
    $app->add('csrf');
    $app->add(TwigMiddleware::create($app, $container->get(Twig::class)));
    $app->add(FormValidationExceptionMiddleware::class);
    $app->add(DisplayFormValidationErrorsMiddleware::class);
    $app->add(KeepOldValidFormDataMiddleware::class);
    $app->add(StartSessionMiddleware::class);
    $app->addBodyParsingMiddleware();
    $app->addErrorMiddleware(
        (bool) $configParser->get('display_error_details'),
        (bool) $configParser->get('log_errors'),
        (bool) $configParser->get('log_error_details')
    );
};