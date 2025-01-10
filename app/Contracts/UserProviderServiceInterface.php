<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\RegisterUserData;
use App\Entities\User;

interface UserProviderServiceInterface
{
    public function getUserEntityById(int $userId): ?UserInterface;

    public function getUserEntityByCriteria(array $criteria): ?UserInterface;

    public function createUser(RegisterUserData $data): UserInterface;

    public function updateUserProfile(User $user, RegisterUserData $data): UserInterface;

    public function updateUserPassword(User $user, string $password): UserInterface;

    public function verifyUser(UserInterface $user): void;

    public function sendEmailVerificationLink(UserInterface $user): void;

    public function send2FACodeEmail(UserInterface $user): void;

    public function enableTwoFactorAuthentication(bool $check, UserInterface $user): void;
}