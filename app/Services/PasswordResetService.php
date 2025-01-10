<?php

declare(strict_types=1);

namespace App\Services;

use App\Entities\PasswordReset;
use App\Entities\User;

class PasswordResetService
{
    public function __construct(private readonly EntityManagerService $entityManagerService)
    {

    }

    public function generate(string $email): PasswordReset
    {
        $passwordReset = new PasswordReset();

        $token = (string) bin2hex(random_bytes(32));

        $passwordReset
            ->setToken($token)
            ->setExpirationDate(new \DateTime('+30 minutes', new \DateTimeZone('Africa/Dar_es_salaam')))
            ->setEmail($email);

        $this->entityManagerService->sync($passwordReset);

        return $passwordReset;
    }

    public function find(string $token): ?PasswordReset
    {
        return $this->entityManagerService->getRepository(PasswordReset::class)
            ->createQueryBuilder('pr')
            ->where('pr.token = :token')
            ->andWhere('pr.isActive = :isActive')
            ->andWhere('pr.expirationDate > :now')
            ->setParameter(':token', $token)
            ->setParameter(':isActive', true)
            ->setParameter(':now', new \DateTime())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getToken(string $email): ?PasswordReset
    {
        $passwordReset = $this->entityManagerService
            ->getRepository(PasswordReset::class)
            ->findOneBy(['email' => $email, 'isActive' => true]);

        if (!$passwordReset) {
            return null;
        }

        if ($passwordReset->getExpirationDate() <= new \DateTime()) {
            return null;
        }

        return $passwordReset;
    }

    // public function verify(User $user, string $code): bool
    // {
    //     $userLoginCode = $this->entityManagerService
    //         ->getRepository(UserLoginCode::class)
    //         ->findOneBy(['user' => $user, 'code' => $code, 'isActive' => true]);

    //     if (!$userLoginCode) {
    //         return false;
    //     }

    //     if ($userLoginCode->getExpirationDate() <= new \DateTime('now', new \DateTimeZone('Africa/Dar_es_salaam'))) {
    //         return false;
    //     }

    //     return true;
    // }

    public function deactivateTokens(string $email): void
    {
        $this->entityManagerService
            ->getRepository(PasswordReset::class)
            ->createQueryBuilder('pr')
            ->update()
            ->set('pr.isActive', 0)
            ->where('pr.email = :email')
            ->andWhere('pr.isActive = 1')
            ->setParameter(':email', $email)
            ->getQuery()
            ->execute();
    }

    public function update(User $user, string $password): void
    {
        $user->setPassword(password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]));

        $this->entityManagerService->sync($user);
    }
}