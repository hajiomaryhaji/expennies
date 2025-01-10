<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Contracts\AuthInterface;
use App\Services\EntityManagerService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;

class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly AuthInterface $auth,
        private readonly Twig $twig,
        private readonly EntityManagerService $entityManagerService
    ) {

    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($user = $this->auth->user()) {
            $this->twig->getEnvironment()->addGlobal('auth', ['id' => $user->getId(), 'name' => $user->getName(), 'email' => $user->getEmail()]);
            $this->twig->getEnvironment()->addGlobal(
                'current_route',
                RouteContext::fromRequest($request)->getRoute()->getName()
            );

            $this->entityManagerService->authorizeUser($user->getId());

            return $handler->handle($request->withAttribute('user', $user));
        }
        return $this->responseFactory->createResponse(302)->withHeader('Location', '/authenticate');
    }
}