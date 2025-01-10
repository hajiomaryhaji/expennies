<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Contracts\AuthInterface;
use App\Contracts\RequestValidatorFactoryInterface;
use App\DTOs\RegisterUserData;
use App\Validators\RequestValidators\RegisterUserRequestValidator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class RegisterUser
{
    public function __construct(
        private readonly Twig $twig,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly AuthInterface $auth
    ) {

    }

    public function create(Response $response): Response
    {
        return $this->twig->render($response, 'auth/register.html.twig');
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(RegisterUserRequestValidator::class)->validate($request->getParsedBody());

        $this->auth->register(new RegisterUserData(
            $data['name'],
            $data['email'],
            $data['password']
        ));

        return $response->withHeader('Location', '/authenticate')->withStatus(302);
    }

    public function resend(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('user');

        $this->auth->send($user);

        return $response->withStatus(200);
    }
}