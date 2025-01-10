<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\ConfigParser;
use App\Services\RequestService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\CacheInterface;
use Slim\Routing\RouteContext;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class RateLimitingMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly RequestService $requestService,
        private readonly ConfigParser $configParser,
        private readonly RateLimiterFactory $rateLimiterFactory
    ) {

    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $clientIpAddress = str_replace(
            ['{', '}', '(', ')', '/', '\\', ':', '@'],
            '_',
            $this->requestService->getIpAddress(
                $request,
                $this->configParser->get('trusted_proxies')
            )
        );

        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();

        $rateLimiter = $this->rateLimiterFactory->create($route->getName() . '' . $clientIpAddress);

        if ($rateLimiter->consume()->isAccepted() === false) {
            return $this->responseFactory->createResponse(429, 'Too many requests');
        }


        return $handler->handle($request);
    }
}