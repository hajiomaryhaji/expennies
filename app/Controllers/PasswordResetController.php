<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Contracts\RequestValidatorFactoryInterface;
use App\Contracts\UserProviderServiceInterface;
use App\Exceptions\FormValidationException;
use App\Services\EmailService;
use App\Services\PasswordResetService;
use App\Validators\RequestValidators\PasswordResetRequestValidator;
use App\Validators\RequestValidators\UpdatePasswordRequestValidator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class PasswordResetController
{
    public function __construct(
        private readonly Twig $twig,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly UserProviderServiceInterface $userProviderService,
        private readonly EmailService $emailService,
        private readonly PasswordResetService $passwordResetService
    ) {

    }

    public function create(Response $response): Response
    {
        return $this->twig->render($response, 'auth/forgot-password.html.twig');
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(PasswordResetRequestValidator::class)->validate($request->getParsedBody());

        $user = $this->userProviderService->getUserEntityByCriteria(['email' => $data['email']]);

        if ($user) {
            $email = $user->getEmail();
            $this->passwordResetService->deactivateTokens($email);
            $this->passwordResetService->generate($email);
            $this->emailService->queue('Your Expennies Password Reset Instructions', 'emails/password-reset.html.twig', $user);
        }

        return $response->withStatus(200);
    }

    public function showPasswordResetForm(Response $response, array $args): Response
    {
        $passwordReset = $this->passwordResetService->find($args['token']);


        if (!$passwordReset) {
            return $response->withHeader('Location', '/');
        }

        return $this->twig->render($response, 'auth/update-password.html.twig', ['token' => $args['token']]);
    }

    public function resetPassword(Request $request, Response $response, array $args): Response
    {
        $data = $this->requestValidatorFactory->make(UpdatePasswordRequestValidator::class)->validate($request->getParsedBody());

        $passwordReset = $this->passwordResetService->find($args['token']);

        if (!$passwordReset) {
            throw new FormValidationException(['confirmPassword' => ['Invalid Token']]);
        }

        $user = $this->userProviderService->getUserEntityByCriteria(['email' => $passwordReset->getEmail()]);

        if (!$user) {
            throw new FormValidationException(['confirmPassword' => ['Invalid Token']]);
        }

        $this->passwordResetService->update($user, $data['password']);

        return $response->withHeader('Location', '/')->withStatus(302);
    }
}