<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Contracts\UserProviderServiceInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class VerifyController
{
    public function __construct(
        private readonly Twig $twig,
        private readonly UserProviderServiceInterface $userProviderService
    ) {

    }

    public function index(Response $response): Response
    {
        return $this->twig->render($response, 'emails/verify.html.twig');
    }

    public function validate(Request $request, Response $response, array $args): Response
    {
        /** @var \App\Entities\User $user */
        $user = $request->getAttribute('user');

        if (!hash_equals((string) $user->getId(), $args['uuid']) || !hash_equals(sha1($user->getEmail()), $args['hash'])) {
            throw new \RuntimeException('Verification failed', 502);
        }

        if (!$user->getVerifiedAt()) {
            $this->userProviderService->verifyUser($user);
        }

        return $response->withHeader('Location', '/')->withStatus(302);
    }
}