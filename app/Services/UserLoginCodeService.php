<?php

declare(strict_types=1);

namespace App\Services;

use App\Entities\User;
use App\Entities\UserLoginCode;

class UserLoginCodeService
{
    public function __construct(private readonly EntityManagerService $entityManagerService)
    {

    }

    public function generate(User $user): UserLoginCode
    {
        $userLoginCode = new UserLoginCode();

        $code = (string) random_int(100_000, 999_999);

        $userLoginCode
            ->setCode($code)
            ->setExpirationDate(new \DateTime('+30 minutes', new \DateTimeZone('Africa/Dar_es_salaam')))
            ->setUser($user);

        $this->entityManagerService->sync($userLoginCode);

        return $userLoginCode;
    }

    public function verify(User $user, string $code): bool
    {
        $userLoginCode = $this->entityManagerService
            ->getRepository(UserLoginCode::class)
            ->findOneBy(['user' => $user, 'code' => $code, 'isActive' => true]);

        if (!$userLoginCode) {
            return false;
        }

        if ($userLoginCode->getExpirationDate() <= new \DateTime('now', new \DateTimeZone('Africa/Dar_es_salaam'))) {
            return false;
        }

        return true;
    }

    public function deactivateCodes(User $user): void
    {
        $this->entityManagerService
            ->getRepository(UserLoginCode::class)
            ->createQueryBuilder('c')
            ->update()
            ->set('c.isActive', 0)
            ->where('c.user = :user')
            ->andWhere('c.isActive = 1')
            ->setParameter(':user', $user)
            ->getQuery()
            ->execute();
    }
}