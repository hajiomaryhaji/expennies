<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\TransactionData;
use App\Entities\Transaction;
use App\Entities\User;

class ImportService
{
    public function __construct(
        private readonly CategoryService $categoryService,
        private readonly TransactionService $transactionService,
        private readonly EntityManagerService $entityManagerService
    ) {

    }

    public function importCSV(string $file, User $user): void
    {
        $resource = fopen($file, 'r');
        $categories = $this->categoryService->getArrayOfCategories();

        fgetcsv($resource);

        $count = 1;
        $batchSize = 250;

        while (($data = fgetcsv($resource)) !== false) {
            [$date, $description, $category, $amount] = $data;

            $date = \DateTime::createFromFormat('d/m/Y H:i', $date);
            $category = $categories[strtolower($category)] ?? null;
            $amount = (float) str_replace(['$', ','], '', $amount);

            $this->transactionService->create(
                new TransactionData(
                    $description,
                    $amount,
                    $date,
                    $category
                ),
                $user
            );

            if ($count % $batchSize === 0) {
                $this->entityManagerService->flush();
                $this->entityManagerService->clear(Transaction::class);

                $count = 1;
            } else {
                $count++;
            }
        }

        if ($count > 1) {
            $this->entityManagerService->flush();
            $this->entityManagerService->clear(Transaction::class);
        }
    }
}