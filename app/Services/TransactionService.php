<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\DataTablesQueryParams;
use App\DTOs\TransactionData;
use App\Entities\Transaction;
use App\Entities\User;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Tools\Pagination\Paginator;

class TransactionService
{
    public function __construct(private readonly EntityManagerService $entityManagerService)
    {

    }

    public function create(TransactionData $data, User $user): void
    {
        $transaction = new Transaction();

        $transaction->setUser($user);

        $this->update($transaction, $data);
    }

    public function update(Transaction $transaction, TransactionData $data): Transaction
    {
        $transaction->setAmount($data->amount);
        $transaction->setCategory($data->category);
        $transaction->setDate($data->date);
        $transaction->setDescription($data->description);

        $this->entityManagerService->sync($transaction);

        return $transaction;
    }

    public function find(int $id): Transaction
    {
        return $this->entityManagerService->getRepository(Transaction::class)->find($id);
    }

    public function dataTable(DataTablesQueryParams $params): Paginator
    {
        $orderBy = in_array($params->orderBy, ['description', 'amount', 'date', 'category']) ? $params->orderBy : 'date';
        $orderDir = strtolower($params->orderDir) === 'asc' ? 'asc' : 'desc';

        $query = $this->entityManagerService
            ->getRepository(Transaction::class)
            ->createQueryBuilder('t')
            ->select('t', 'c', 'r')
            ->leftJoin('t.category', 'c')
            ->leftJoin('t.receipts', 'r')
            ->setFirstResult($params->start)
            ->setMaxResults($params->length);

        if (!empty($params->searchValue)) {
            $query->where('t.description LIKE :description')->setParameter(':description', (string) '%' . addcslashes($params->searchValue, '%_') . '%');
        }

        if ($orderBy === 'category') {
            $query->orderBy('c.name', $orderDir);
        } else {
            $query->orderBy((string) 't.' . $orderBy, $orderDir);
        }

        return new Paginator($query);
    }

    public function review(Transaction $transaction): void
    {
        $transaction->setWasReviewed(!$transaction->getWasReviewed());

        $this->entityManagerService->sync($transaction);
    }

    public function delete(Transaction $transaction, bool $sync): void
    {
        $this->entityManagerService->delete($transaction, $sync);
    }

    public function getTotals(\DateTime $startDate, \DateTime $endDate): array
    {
        $amounts = $this->entityManagerService->getRepository(Transaction::class)
            ->createQueryBuilder('t')
            ->select('t.amount')
            ->where('t.updatedAt BETWEEN :startDate AND :endDate')
            ->setParameter(':startDate', $startDate)
            ->setParameter(':endDate', $endDate)
            ->getQuery()
            ->getResult();

        $totals = [
            'expenses' => 0,
            'income' => 0,
            'netProfit' => 0
        ];

        foreach ($amounts as $key => $amount) {
            $amount = $amount['amount'];

            if ($amount > 0) {
                $totals['income'] += $amount;
            } else {
                $totals['expenses'] += $amount;
            }
        }

        $totals['netProfit'] = $totals['expenses'] + $totals['income'];

        return $totals;
    }

    public function getRecentTransactions(int $limit)
    {
        return $this->entityManagerService->getRepository(Transaction::class)
            ->createQueryBuilder('t')
            ->orderBy('t.updatedAt', 'desc')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getTopSpendingCategories(int $limit): array
    {
        $dql = '
            SELECT c.name, SUM(t.amount) AS total_amount
            FROM App\Entities\Transaction t
            JOIN t.category c
            GROUP BY c.id
            ORDER BY total_amount DESC
        ';

        return $this->entityManagerService->createQuery($dql)->setMaxResults($limit)->getArrayResult();
    }

    public function getMonthlySummary(int $year, int $userId): array
    {
        $sql = "
    SELECT 
        MONTH(created_at) AS m,
        SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END) AS income,
        SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END) AS expense
    FROM transactions
    WHERE YEAR(created_at) = :year AND user_id = :userId
    GROUP BY m
    ORDER BY m ASC;
";

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('m', 'm');
        $rsm->addScalarResult('income', 'income');
        $rsm->addScalarResult('expense', 'expense');

        $query = $this->entityManagerService->createNativeQuery($sql, $rsm);
        $query->setParameter(':year', $year);
        $query->setParameter(':userId', $userId);

        $results = $query->getResult();

        $formattedResults = [];
        foreach ($results as $result) {
            $formattedResults[] = [
                'income' => (float) $result['income'],
                'expense' => (float) $result['expense'],
                'm' => (int) $result['m'],
            ];
        }

        return $formattedResults;

        // return [
        //     ['income' => 1500, 'expense' => 1100, 'm' => '3'],
        //     ['income' => 2000, 'expense' => 1800, 'm' => '4'],
        //     ['income' => 2500, 'expense' => 1900, 'm' => '5'],
        //     ['income' => 2600, 'expense' => 1950, 'm' => '6'],
        //     ['income' => 3000, 'expense' => 2200, 'm' => '7'],
        // ];
    }
}