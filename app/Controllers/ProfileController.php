<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Contracts\RequestValidatorFactoryInterface;
use App\Contracts\UserProviderServiceInterface;
use App\DTOs\RegisterUserData;
use App\Entities\User;
use App\Helpers\ResponseFormatter;
use App\Validators\RequestValidators\ProfileUpdatePasswordRequestValidator;
use App\Validators\RequestValidators\UpdateProfileInfoRequestValidator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class ProfileController
{
    public function __construct(
        private readonly Twig $twig,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly UserProviderServiceInterface $userProviderService
    ) {

    }

    public function edit(Response $response): Response
    {
        return $this->twig->render(
            $response,
            'profile.html.twig'
        );
    }

    public function show(Response $response, User $user): Response
    {
        return ResponseFormatter::json($response, ['name' => $user->getName(), 'email' => $user->getEmail()]);
    }

    public function update(Request $request, Response $response, User $user): Response
    {
        $data = $this->requestValidatorFactory->make(UpdateProfileInfoRequestValidator::class)->validate($request->getParsedBody());

        $user = $this->userProviderService->updateUserProfile(
            $user,
            new RegisterUserData(
                $data['name'],
                $data['email']
            )
        );

        if ($user) {
            return ResponseFormatter::json($response, ['success' => true, 'message' => 'Profile updated successfully']);
        }

        $response = $response->withStatus(400);

        return ResponseFormatter::json($response, ['success' => false, 'message' => 'Failed to update profile']);
    }

    public function updatePassword(Request $request, Response $response, User $user): Response
    {
        $data = $this->requestValidatorFactory->make(ProfileUpdatePasswordRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        if (!password_verify($data['currentPassword'], $user->getPassword())) {
            $response = $response->withStatus(400);

            return ResponseFormatter::json($response, ['success' => false, 'message' => 'That is not your current password.']);
        }

        $user = $this->userProviderService->updateUserPassword($user, $data['newPassword']);

        if ($user) {
            return ResponseFormatter::json($response, ['success' => true, 'message' => 'Profile updated successfully']);
        }

        $response = $response->withStatus(400);

        return ResponseFormatter::json($response, ['success' => false, 'message' => 'Failed to update profile']);
    }

    public function destroy(Response $response): Response
    {
        return $response;
    }


}