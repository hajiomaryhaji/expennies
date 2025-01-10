<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\RegisterUserData;
use App\Enums\AuthAttemptStatus;

interface AuthInterface
{
    public function user(): ?UserInterface;

    public function attemptLogin(array $data): AuthAttemptStatus;

    public function logOut(): void;

    public function register(RegisterUserData $data): void;

    public function logIn(UserInterface $user): void;

    public function send(UserInterface $user): void;

    public function attemptTwoFactorLogin(array $data): bool;
}