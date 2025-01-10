<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Contracts\RequestValidatorFactoryInterface;
use App\DTOs\TransactionData;
use App\Entities\Receipt;
use App\Entities\Transaction;
use App\Helpers\ResponseFormatter;
use App\Services\CategoryService;
use App\Services\ImportService;
use App\Services\ReceiptService;
use App\Services\RequestService;
use App\Services\TransactionService;
use App\Validators\RequestValidators\ImportTransactionsRequestValidator;
use App\Validators\RequestValidators\TransactionRequestValidator;
use League\Flysystem\Filesystem;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class TransactionController
{
    public function __construct(
        private readonly Twig $twig,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly TransactionService $transactionService,
        private readonly RequestService $requestService,
        private readonly CategoryService $categoryService,
        private readonly Filesystem $filesystem,
        private readonly ReceiptService $receiptService,
        private readonly ImportService $importService
    ) {

    }

    public function index(Response $response): Response
    {
        return $this->twig->render($response, 'transactions/index.html.twig', ['categories' => $this->categoryService->getCategoryNames()]);
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(TransactionRequestValidator::class)->validate($request->getParsedBody());

        $this->transactionService->create(
            new TransactionData(
                $data['description'],
                (float) $data['amount'],
                new \DateTime($data['date'], new \DateTimeZone('Africa/Dar_es_salaam')),
                $data['category']
            )
            ,
            $request->getAttribute('user')
        );

        return $response;
    }

    public function load(Request $request, Response $response): Response
    {
        $params = $this->requestService->getDataTablesQueryParams($request);

        $filteredTransactions = $this->transactionService->dataTable($params);

        $formatTransactions = function (Transaction $transaction): array {
            return [
                'id' => $transaction->getId(),
                'description' => $transaction->getDescription(),
                'amount' => $transaction->getAmount(),
                'date' => $transaction->getDate()->format('d-m-Y h:i A'),
                'category' => $transaction->getCategory()?->getName() ?? '',
                'receipts' => $transaction->getReceipts()->map(
                    fn(Receipt $receipt): array => [
                        'name' => $receipt->getClientFileName(),
                        'id' => $receipt->getId()
                    ]
                )->toArray(),
                'wasReviewed' => $transaction->getWasReviewed()
            ];
        };

        $totalRecords = count($filteredTransactions);

        return ResponseFormatter::json(
            $response,
            [
                'data' => array_map($formatTransactions, (array) $filteredTransactions->getIterator()),
                'draw' => $params->draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords
            ]
        );

    }

    public function show(Response $response, Transaction $transaction): Response
    {
        $data = [
            'id' => $transaction->getId(),
            'description' => $transaction->getDescription(),
            'amount' => (string) $transaction->getAmount(),
            'date' => $transaction->getDate()->format('Y-m-d\TH:i'),
            'category' => $transaction->getCategory()?->getId()
        ];

        return ResponseFormatter::json($response, $data);
    }

    public function update(Request $request, Response $response, Transaction $transaction): Response
    {
        $data = $this->requestValidatorFactory->make(TransactionRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        $this->transactionService->update($transaction, new TransactionData(
            $data['description'],
            (float) $data['amount'],
            new \DateTime($data['date'], new \DateTimeZone('Africa/Dar_es_salaam')),
            $data['category']
        ));

        return $response;
    }


    public function destroy(Response $response, Transaction $transaction): Response
    {
        $receipts = $this->receiptService->getTransactionReceipts($transaction);

        $this->transactionService->delete($transaction, true);

        /** @var Receipt $receipt */
        foreach ($receipts as $receipt) {
            $this->filesystem->delete((string) 'receipts/' . $receipt->getUniqueFileName());
            $this->receiptService->delete($receipt, true);
        }

        return $response;
    }

    public function import(Request $request, Response $response): Response
    {
        $file = $this->requestValidatorFactory->make(ImportTransactionsRequestValidator::class)->validate(
            $request->getUploadedFiles()
        )['importedCSVFile'];

        $user = $request->getAttribute('user');

        $this->importService->importCSV($file->getStream()->getMetadata('uri'), $user);

        return $response;
    }

    public function review(Response $response, Transaction $transaction): Response
    {
        $this->transactionService->review($transaction);

        return $response;
    }
}