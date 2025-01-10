<?php

declare(strict_types=1);

namespace App;

use App\Contracts\AuthInterface;
use App\Contracts\SessionInterface;
use App\Contracts\UserInterface;
use App\Contracts\UserProviderServiceInterface;
use App\DTOs\RegisterUserData;
use App\Entities\User;
use App\Enums\AuthAttemptStatus;
use App\Services\EmailService;
use App\Services\UserLoginCodeService;

class Auth implements AuthInterface
{
    private ?User $user = null;

    public function __construct(
        private readonly UserProviderServiceInterface $userProviderService,
        private readonly SessionInterface $session,
        private readonly EmailService $emailService,
        private readonly UserLoginCodeService $userLoginCodeService
    ) {

    }

    public function user(): ?UserInterface
    {
        if ($this->user !== null) {
            return $this->user;
        }

        $userId = $this->session->get('user');

        if (!$userId) {
            return null;
        }

        $user = $this->userProviderService->getUserEntityById($userId);

        if (!$user) {
            return null;
        }

        $this->user = $user;

        return $this->user;
    }

    public function attemptLogin(array $data): AuthAttemptStatus
    {
        $user = $this->userProviderService->getUserEntityByCriteria(['email' => $data['email']]);

        if (!$user || !password_verify($data['password'], $user->getPassword())) {
            return AuthAttemptStatus::FAILED;
        }


        if ($user->hasTwoFactorAuthEnabled()) {
            $this->start2FALogin($user);

            return AuthAttemptStatus::TWO_FACTOR_AUTH;
        }

        $this->logIn($user);

        return AuthAttemptStatus::SUCCESS;
    }

    private function start2FALogin(UserInterface $user): void
    {
        $this->session->regenerate();
        $this->session->put('2fa', $user->getId());

        $this->userLoginCodeService->deactivateCodes($user);

        $this->userProviderService->send2FACodeEmail($user);
    }

    public function attemptTwoFactorLogin(array $data): bool
    {
        $userId = $this->session->get('2fa');

        if (!$userId) {
            return false;
        }

        $user = $this->userProviderService->getUserEntityById($userId);

        if (!$user || $user->getEmail() !== $data['email']) {
            return false;
        }

        if (!$this->userLoginCodeService->verify($user, $data['code'])) {
            return false;
        }

        $this->session->forget('2fa');

        $this->logIn($user);

        $user = $this->userProviderService->getUserEntityById($userId);

        return true;
    }

    public function logIn(UserInterface $user): void
    {
        $this->session->regenerate();

        $this->session->put('user', $user->getId());

        $this->user = $user;
    }

    public function register(RegisterUserData $data): void
    {
        $user = $this->userProviderService->createUser($data);

        var_dump($data);

        $this->send($user);

        $this->logIn($user);

    }

    public function send(UserInterface $user): void
    {
        $this->userProviderService->sendEmailVerificationLink($user);
    }

    public function logOut(): void
    {
        $this->session->unset('user');

        $this->session->regenerate();

        $this->user = null;
    }
}