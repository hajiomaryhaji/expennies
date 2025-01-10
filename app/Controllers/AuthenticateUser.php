<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Contracts\AuthInterface;
use App\Contracts\RequestValidatorFactoryInterface;
use App\Contracts\UserProviderServiceInterface;
use App\Entities\User;
use App\Enums\AuthAttemptStatus;
use App\Exceptions\FormValidationException;
use App\Helpers\ResponseFormatter;
use App\Validators\RequestValidators\AuthenticateUserRequestValidator;
use App\Validators\RequestValidators\EnableTwoFactorRequestValidator;
use App\Validators\RequestValidators\UserLoginCodeRequestValidator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Valitron\Validator;

class AuthenticateUser
{
    public function __construct(
        private readonly Twig $twig,
        private readonly AuthInterface $auth,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly UserProviderServiceInterface $userProviderService
    ) {

    }

    public function create(Request $request, Response $response): Response
    {
        return $this->twig->render($response, 'auth/authenticate.html.twig');
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(AuthenticateUserRequestValidator::class)->validate($request->getParsedBody());

        $status = $this->auth->attemptLogin($data);

        if ($status === AuthAttemptStatus::FAILED) {
            throw new FormValidationException(['password' => ['You have entered invalid email or password']]);
        }

        if ($status === AuthAttemptStatus::TWO_FACTOR_AUTH) {
            return ResponseFormatter::json($response, ['two_factor_authentication' => true]);
        }

        return ResponseFormatter::json($response, []);
    }

    public function enableTwoFactor(Request $request, Response $response, User $user): Response
    {
        $data = $this->requestValidatorFactory->make(EnableTwoFactorRequestValidator::class)->validate($request->getParsedBody());

        if ($data['check']) {
            $this->userProviderService->enableTwoFactorAuthentication($data['check'], $user);
        } else {
            $this->userProviderService->enableTwoFactorAuthentication($data['check'], $user);
        }

        return $response->withStatus(200);
    }

    public function getTwoFactor(Response $response, User $user): Response
    {
        return ResponseFormatter::json($response, ['enabled' => $user->hasTwoFactorAuthEnabled()]);
    }

    public function twoFactorAuthenticate(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(UserLoginCodeRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        $this->auth->attemptTwoFactorLogin($data);

        return $response;
    }

    public function destroy(Request $request, Response $response): Response
    {
        $this->auth->logOut();

        return $response->withHeader('Location', '/authenticate')->withStatus(302);
    }
}