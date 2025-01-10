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
        $endDate = new \DateTime();
        $totals = $this->transactionService->getTotals($startDate, $endDate);
        $recentTransactions = $this->transactionService->getRecentTransactions(8);
        $topSpendingCategories = $this->transactionService->getTopSpendingCategories(4);

        // echo '<pre>';
        // printf(date('d-m-Y h:i:s A', 1736526961));
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