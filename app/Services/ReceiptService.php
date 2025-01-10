<?php

declare(strict_types=1);

namespace App\Services;

use App\Entities\Receipt;
use App\Entities\Transaction;

class ReceiptService
{
    public function __construct(private readonly EntityManagerService $entityManagerService)
    {

    }

    public function create(Transaction $transaction, string $clientName, string $uniqueName, string $mimeType): Receipt
    {
        $receipt = new Receipt();

        $receipt
            ->setClientFileName($clientName)
            ->setUniqueFileName($uniqueName)
            ->setTransaction($transaction)
            ->setMimeType($mimeType);

        $this->entityManagerService->sync($receipt);

        return $receipt;
    }

    public function find(int $id): Receipt
    {
        return $this->entityManagerService->find(Receipt::class, $id);
    }

    public function delete(Receipt $receipt, bool $sync = false): void
    {
        $this->entityManagerService->delete($receipt, $sync);
    }

    public function getTransactionReceipts(Transaction $transaction): array
    {
        return $this->entityManagerService
            ->getRepository(Receipt::class)
            ->createQueryBuilder('r')
            ->where('r.transactionId = :transactionId')
            ->setParameter(':transactionId', $transaction->getId())
            ->getQuery()
            ->getResult();
    }
}