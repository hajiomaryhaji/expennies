<?php

declare(strict_types=1);

use App\Auth;
use App\Authorizers\AuthorizeUser;
use App\ConfigParser;
use App\Contracts\AuthInterface;
use App\Contracts\RequestValidatorFactoryInterface;
use App\Contracts\SessionInterface;
use App\Contracts\UserProviderServiceInterface;
use App\Csrf;
use App\DTOs\SessionConfig;
use App\Enums\SameSite;
use App\Enums\StorageDriver;
use App\RouteEntityBindingStrategy;
use App\Services\EntityManagerService;
use App\Services\UserProviderService;
use App\Session;
use App\Validators\RequestValidators\RequestValidatorFactory;
use App\ViteExtension;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\SimpleCache\CacheInterface;
use Slim\App;
use Slim\Csrf\Guard;
use Slim\Factory\AppFactory;
use Slim\Interfaces\RouteParserInterface;
use Slim\Views\Twig;
use Symfony\Bridge\Twig\Mime\BodyRenderer;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\BodyRendererInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\CacheStorage;
use Twig\Extra\Intl\IntlExtension;
use function DI\create;

return [
    App::class => function (ContainerInterface $container): App {
        AppFactory::setContainer($container);
        $app = AppFactory::create();

        $app->getRouteCollector()->setDefaultInvocationStrategy(
            new RouteEntityBindingStrategy(
                $container->get(EntityManagerService::class),
                $app->getResponseFactory()
            )
        );

        $routes = require CONFIGS_PATH . '/routes/web.php';

        $routes($app);

        $middlewares = require CONFIGS_PATH . '/middlewares.php';

        $middlewares($app);

        return $app;
    },
    ConfigParser::class => create(ConfigParser::class)->constructor(require CONFIGS_PATH . '/app-settings.php'),
    EntityManagerInterface::class => function (ConfigParser $configParser): EntityManager {
        $ormConfig = ORMSetup::createAttributeMetadataConfiguration(
            [$configParser->get('doctrine.entities_dir')],
            $configParser->get('doctrine.dev_mode'),
            $configParser->get('doctrine.cache_dir')
        );

        $ormConfig->addFilter('user', AuthorizeUser::class);

        $connection = DriverManager::getConnection(
            $configParser->get('doctrine.connection'),
            $ormConfig
        );

        return new EntityManager($connection, $ormConfig);
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
    },
    ResponseFactoryInterface::class => fn(App $app): ResponseFactoryInterface => $app->getResponseFactory(),
    AuthInterface::class => fn(ContainerInterface $container): AuthInterface => $container->get(Auth::class),
    UserProviderServiceInterface::class => fn(ContainerInterface $container): UserProviderServiceInterface => $container->get(
        UserProviderService::class
    ),
    SessionInterface::class => fn(ConfigParser $configParser): SessionInterface => new Session(
        new SessionConfig(
            $configParser->get('session.name', 'php'),
            $configParser->get('session.flash'),
            $configParser->get('session.httponly', true),
            $configParser->get('session.secure', true),
            SameSite::tryFrom($configParser->get('session.samesite', 'lax'))
        )
    ),
    RequestValidatorFactoryInterface::class => fn(ContainerInterface $container) => $container->get(RequestValidatorFactory::class),
    'csrf' => fn(ResponseFactoryInterface $responseFactory, Csrf $csrf): Guard => new Guard(
        $responseFactory,
        persistentTokenMode: true,
        failureHandler: $csrf->failureHandler()
    ),
    Filesystem::class => function (ConfigParser $configParser): Filesystem {
        $adapter = match ($configParser->get('storage.driver')) {
            StorageDriver::Local => new LocalFilesystemAdapter(STORAGE_PATH),
        };


        return new Filesystem($adapter);
    },
    MailerInterface::class => function (ConfigParser $configParser): MailerInterface {
        $transport = Transport::fromDsn($configParser->get('email.mailtrap'));

        return new Mailer($transport);
    },
    BodyRendererInterface::class => fn(Twig $twig): BodyRendererInterface => new BodyRenderer($twig->getEnvironment()),
    RouteParserInterface::class => fn(App $app): RouteParserInterface => $app->getRouteCollector()->getRouteParser(),
    RedisAdapter::class => function (ConfigParser $configParser): RedisAdapter {
        // Create a Redis instance
        $redis = new \Redis();
        $redisConfigs = $configParser->get('redis');

        // Connect to Redis server
        $redis->connect($redisConfigs['host'], $redisConfigs['port']);
        $redis->auth($redisConfigs['password']);

        return new RedisAdapter($redis);
    },
    CacheInterface::class => fn(RedisAdapter $redisAdapter): CacheInterface => new Psr16Cache($redisAdapter),
    RateLimiterFactory::class => function (RedisAdapter $redisAdapter): RateLimiterFactory {
        $storage = new CacheStorage($redisAdapter);

        return new RateLimiterFactory(
            ['id' => 'default', 'policy' => 'fixed_window', 'interval' => '1 minute', 'limit' => 3],
            $storage
        );
    }
];