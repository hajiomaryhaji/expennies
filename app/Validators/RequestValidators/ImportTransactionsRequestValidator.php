<?php

declare(strict_types=1);

namespace App\Validators\RequestValidators;

use App\Contracts\RequestValidatorInterface;
use App\Exceptions\FormValidationException;
use League\MimeTypeDetection\FinfoMimeTypeDetector;
use \Psr\Http\Message\UploadedFileInterface;

class ImportTransactionsRequestValidator implements RequestValidatorInterface
{
    public function validate(array $data): array
    {
        /**
         * @var UploadedFileInterface $uploadedFile
         */
        $uploadedFile = $data['importedCSVFile'] ?? null;

        // 1. Validate uploaded file
        if (!$uploadedFile) {
            throw new FormValidationException(['importedCSVFile' => ['Please select a CSV file to import']]);
        }

        if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
            throw new FormValidationException(['importedCSVFile' => ['Failed to upload a CSV file']]);
        }

        // 2. Validate file name
        $filename = $uploadedFile->getClientFilename();

        if (!preg_match('/^[a-zA-Z0-9\s._-]+$/', $filename)) {
            throw new FormValidationException(['importedCSVFile' => ['Invalid filename (Allowed characters are a to z, A to Z, 0 to 9, space, ., -, or _)']]);
        }

        if (!(strlen($filename) < 100)) {
            throw new FormValidationException(['importedCSVFile' => ['Filename exceeds max characters length (MAX. 100 characters)']]);
        }

        // 3. Validate file size
        $maxFileSize = 5 * 1024 * 1024;

        if (!($uploadedFile->getSize() < $maxFileSize)) {
            throw new FormValidationException(['importedCSVFile' => ['File exceeds max file size of 5MB']]);
        }

        // 4. Validate file type
        $allowedMimeTypes = ['application/vnd.ms-excel', 'text/csv'];

        if (!in_array($uploadedFile->getClientMediaType(), $allowedMimeTypes)) {
            print_r($uploadedFile->getClientMediaType());
            throw new FormValidationException(['importedCSVFile' => ['Please import a file of CSV type']]);
        }


        $tmpFilePath = $uploadedFile->getStream()->getMetadata('uri');
        $detector = new FinfoMimeTypeDetector();
        $mimeType = $detector->detectMimeTypeFromFile($tmpFilePath);

        if (!in_array($mimeType, $allowedMimeTypes)) {
            throw new FormValidationException(['importedCSVFile' => ['Please import a CSV file not else (Invalid Media Type)']]);
        }


        return $data;
    }
}