<?php

declare(strict_types=1);

namespace App\Validators\RequestValidators;

use App\Contracts\RequestValidatorFactoryInterface;
use App\Contracts\RequestValidatorInterface;
use Psr\Container\ContainerInterface;

class RequestValidatorFactory implements RequestValidatorFactoryInterface
{
    public function __construct(private readonly ContainerInterface $container)
    {

    }

    public function make(string $className): RequestValidatorInterface
    {
        $validator = $this->container->get($className);

        if ($validator instanceof RequestValidatorInterface) {
            return $validator;
        }

        throw new \RuntimeException('Failed to instantiate class "' . $className . '"');
    }
}