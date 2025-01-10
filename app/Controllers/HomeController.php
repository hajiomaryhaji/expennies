<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Entities\User;
use App\Helpers\ResponseFormatter;
use App\Services\TransactionService;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class HomeController
{
    public function __construct(private readonly Twig $twig, private readonly TransactionService $transactionService)
    {

    }

    public function index(Response $response): Response
    {
        $startDate = \DateTime::createFromFormat('Y-m-d', date('Y-m-01'));
        $endDate = new \DateTime('now', new \DateTimeZone('Africa/Dar_es_salaam'));
        $totals = $this->transactionService->getTotals($startDate, $endDate);
        $recentTransactions = $this->transactionService->getRecentTransactions(8);
        $topSpendingCategories = $this->transactionService->getTopSpendingCategories(4);

        // echo '<pre>';
        // var_dump($this->transactionService->getTopSpendingCategories(5));
        // echo '</pre>';

        return $this->twig->render(
            $response,
            'dashboard.html.twig',
            [
                'totals' => $totals,
                'transactions' => $recentTransactions,
                'topSpendingCategories' => $topSpendingCategories,
                'year' => date('Y')
            ]
        );
    }

    public function getYearToDateStatistics(Response $response, User $user): Response
    {
        $data = $this->transactionService->getMonthlySummary((int) date('Y'), $user->getId());

        return ResponseFormatter::json($response, $data);
    }
}