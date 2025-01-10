<?php

declare(strict_types=1);

namespace App\Services;

use Doctrine\ORM\EntityManagerInterface;

/**
 * @mixin EntityManagerInterface
 */
class EntityManagerService
{
    public function __construct(protected readonly EntityManagerInterface $entityManager)
    {

    }

    public function __call(string $method, array $args): mixed
    {
        if (method_exists($this->entityManager, $method)) {
            return call_user_func_array([$this->entityManager, $method], $args);
        }

        throw new \BadMethodCallException((string) 'Call to undefined method "' . $method . '"', 422);
    }

    public function sync(?object $entity = null): void
    {
        if ($entity) {
            $this->entityManager->persist($entity);
        }

        $this->entityManager->flush();
    }

    public function delete(object $entity, bool $sync = false): void
    {
        $this->entityManager->remove($entity);

        if ($sync === true) {
            $this->entityManager->flush();
        }
    }

    public function clear(string $entityName = null): void
    {
        if ($entityName === null) {
            $this->entityManager->clear();

            return;
        }

        $unitOfWork = $this->entityManager->getUnitOfWork();
        $entities = $unitOfWork->getIdentityMap()[$entityName] ?? [];

        foreach ($entities as $entity) {
            $this->entityManager->detach($entity);
        }
    }

    public function authorizeUser(int $userId): void
    {
        $this->getFilters()->enable('user')->setParameter('user_id', $userId);
    }
}