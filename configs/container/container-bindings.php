<?php

declare(strict_types=1);

use App\ConfigParser;
use App\Enums\AppEnvironment;
use App\ViteExtension;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Twig\Extra\Intl\IntlExtension;
use function DI\create;

return [
    App::class => function (ContainerInterface $container): App {
        AppFactory::setContainer($container);
        $app = AppFactory::create();

        $middlewares = require CONFIGS_PATH . '/middlewares.php';

        $middlewares($app);

        return $app;
    },
    ConfigParser::class => create(ConfigParser::class)->constructor(require CONFIGS_PATH . '../app-settings.php'),
    EntityManager::class => function (ConfigParser $configParser): EntityManager {
        $config = ORMSetup::createAttributeMetadataConfiguration(
            [$configParser->get('doctrine.entities_dir')],
            $configParser->get('doctrine.dev_mode'),
            $configParser->get('doctrine.cache_dir')
        );

        $connection = DriverManager::getConnection(
            $configParser->get('doctrine.connection'),
            $config
        );

        return new EntityManager($connection, $config);
    },
    Twig::class => function (ContainerInterface $container, ConfigParser $configParser): Twig {
        $twig = Twig::create(
            VIEWS_PATH,
            [
                'cache' => $configParser->get('twig.cache_dir'),
                'auto_reload' => $configParser->get('twig.reload')
            ]
        );

        $twig->addExtension(new IntlExtension());
        $twig->addExtension(new ViteExtension(BUILD_PATH . '/.vite/manifest.json'));


        return $twig;
    }

];