<?php

declare(strict_types=1);

namespace App;

use App\Services\EntityManagerService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\InvocationStrategyInterface;

class RouteEntityBindingStrategy implements InvocationStrategyInterface
{
    public function __construct(
        private readonly EntityManagerService $entityManagerService,
        private readonly ResponseFactoryInterface $responseFactory
    ) {

    }

    public function __invoke(
        callable $callable,
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $routeArguments
    ): ResponseInterface {
        $ReflectedCallable = $this->createCallableReflection($callable);

        $arguments = [];

        foreach ($ReflectedCallable->getParameters() as $parameter) {
            $type = $parameter->getType();

            if (!$type) {
                continue;
            }

            $typeName = $type->getName();
            $parameterName = $parameter->getName();

            if ($type->isBuiltin()) {
                if ($typeName === 'array' && $parameterName === 'args') {
                    $arguments[] = $routeArguments;
                }
            } else if ($typeName === ServerRequestInterface::class) {
                $arguments[] = $request;
            } else if ($typeName === ResponseInterface::class) {
                $arguments[] = $response;
            } else {
                // var_dump($routeArguments, $typeName, $parameterName);
                $entityId = $routeArguments[$parameterName] ?? null;

                if (!$entityId) {
                    throw new \InvalidArgumentException((string) 'Unable to resolve parameter name . "' . $parameterName . '" in a route callable');
                }

                $entity = $this->entityManagerService->find($typeName, $entityId);

                if (!$entity) {
                    return $this->responseFactory->createResponse(404, 'Oops! Resource Not Found');
                }

                $arguments[] = $entity;
            }
        }

        return $callable(...$arguments);
    }

    public function createCallableReflection(callable|\Closure $callable): \ReflectionFunctionAbstract
    {
        return is_array($callable)
            ? new \ReflectionMethod($callable[0], $callable[1])
            : new \ReflectionFunction($callable);

    }
}