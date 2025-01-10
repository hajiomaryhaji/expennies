<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Contracts\RequestValidatorFactoryInterface;
use App\Entities\Receipt;
use App\Entities\Transaction;
use App\Services\ReceiptService;
use App\Services\TransactionService;
use App\Validators\RequestValidators\UploadReceiptRequestValidator;
use League\Flysystem\Filesystem;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Stream;

class ReceiptController
{
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly TransactionService $transactionService,
        private readonly ReceiptService $receiptService
    ) {

    }

    public function store(Request $request, Response $response, Transaction $transaction): Response
    {
        $file = $this->requestValidatorFactory->make(UploadReceiptRequestValidator::class)->validate($request->getUploadedFiles())['receipt'];

        $clientfilename = $file->getClientFileName();

        $uniqueFilename = bin2hex(random_bytes(25));

        $this->filesystem->write((string) 'receipts/' . $uniqueFilename, $file->getStream()->getContents());

        $this->receiptService->create($transaction, $clientfilename, $uniqueFilename, $file->getClientMediaType());

        return $response;
    }

    public function download(Response $response, Transaction $transaction, Receipt $receipt): Response
    {

        if ($receipt->getTransaction()->getId() !== $transaction->getId()) {
            return $response->withStatus(401);
        }

        $file = $this->filesystem->readStream('receipts/' . $receipt->getUniqueFileName());

        $response = $response->withHeader(
            'Content-Disposition',
            'inline; filename=' . $receipt->getClientFileName()
        )->withHeader(
                'Content-Type',
                $receipt->getMimeType()
            );

        return $response->withBody(new Stream($file));
    }

    public function destroy(Response $response, Transaction $transaction, Receipt $receipt): Response
    {

        if ($receipt->getTransaction()->getId() !== $transaction->getId()) {
            return $response->withStatus(404);
        }

        $this->filesystem->delete('receipts/' . $receipt->getUniqueFileName());

        $this->receiptService->delete($receipt, true);

        return $response;
    }
}