<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\UserInterface;
use App\DTOs\DataTablesQueryParams;
use App\Entities\Category;
use Doctrine\ORM\Tools\Pagination\Paginator;

class CategoryService
{
    public function __construct(private readonly EntityManagerService $entityManagerService)
    {

    }

    public function create(string $name, UserInterface $user): Category
    {
        $category = new Category();

        $category->setUser($user);

        return $this->update($category, $name);
    }

    public function all(): array
    {
        return $this->entityManagerService->getRepository(Category::class)->findAll();
    }

    public function dataTable(DataTablesQueryParams $params): Paginator
    {
        $orderBy = in_array($params->orderBy, ['name', 'createdAt', 'updatedAt']) ? $params->orderBy : 'updatedAt';
        $orderDir = strtolower($params->orderDir) === 'asc' ? 'asc' : 'desc';

        $query = $this->entityManagerService
            ->getRepository(Category::class)
            ->createQueryBuilder('c')
            ->setFirstResult($params->start)
            ->setMaxResults($params->length);

        if (!empty($params->searchValue)) {
            $query->where('c.name LIKE :name')->setParameter(':name', (string) '%' . addcslashes($params->searchValue, '%_') . '%');
        }

        $query->orderBy((string) 'c.' . $orderBy, $orderDir);

        return new Paginator($query);
    }

    public function find(int $id): ?Category
    {
        return $this->entityManagerService->find(Category::class, $id);
    }

    public function update(Category $category, string $name): Category
    {
        $category->setName($name);

        $this->entityManagerService->sync($category);

        return $category;
    }

    public function delete(Category $category, bool $sync = false): void
    {
        $this->entityManagerService->delete($category, $sync);
    }

    public function getCategoryNames(): array
    {
        return $this->entityManagerService->getRepository(Category::class)
            ->createQueryBuilder('c')
            ->select('c.id', 'c.name')
            ->getQuery()
            ->getArrayResult();
    }

    public function getArrayOfCategories(): array
    {
        $categories = $this->entityManagerService->getRepository(Category::class)->findAll();

        $mapper = [];

        foreach ($categories as $category) {
            $mapper[strtolower($category->getName())] = $category;
        }

        return $mapper;
    }
}