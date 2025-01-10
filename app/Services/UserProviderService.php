<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\UserInterface;
use App\Contracts\UserProviderServiceInterface;
use App\DTOs\RegisterUserData;
use App\Entities\User;

class UserProviderService implements UserProviderServiceInterface
{
    public function __construct(
        private readonly EntityManagerService $entityManagerService,
        private readonly EmailService $emailService
    ) {

    }

    public function getUserEntityById(int $userId): ?UserInterface
    {
        return $this->entityManagerService->find(User::class, $userId);
    }

    public function getUserEntityByCriteria(array $criteria): ?UserInterface
    {
        return $this->entityManagerService->getRepository(User::class)->findOneBy($criteria);
    }

    public function createUser(RegisterUserData $data): UserInterface
    {
        $user = (new User())
            ->setPassword(password_hash($data->password, PASSWORD_BCRYPT, ['cost' => 12]));

        $this->updateUserProfile($user, $data);

        return $user;
    }

    public function updateUserProfile(User $user, RegisterUserData $data): UserInterface
    {
        $user->setName($data->name);
        $user->setEmail($data->email);

        $this->entityManagerService->sync($user);

        return $user;
    }

    public function updateUserPassword(User $user, string $password): UserInterface
    {
        $user->setPassword(password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]));

        $this->entityManagerService->sync($user);

        return $user;
    }


    public function verifyUser(UserInterface $user): void
    {
        $user->setVerifiedAt(new \DateTime());

        $this->entityManagerService->sync($user);
    }

    public function sendEmailVerificationLink(UserInterface $user): void
    {
        $this->emailService->queue('Welcom to Expennies', 'emails/signup.html.twig', $user);
    }

    public function send2FACodeEmail(UserInterface $user): void
    {
        $this->emailService->queue('Two Factor Authentication', 'emails/two-factor.html.twig', $user);
    }

    public function enableTwoFactorAuthentication(bool $check, UserInterface $user): void
    {
        $user->setEnableTwoFactor($check);

        $this->entityManagerService->sync($user);
    }
}