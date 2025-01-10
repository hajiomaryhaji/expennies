<?php

declare(strict_types=1);

namespace App\Validators\RequestValidators;

use App\Contracts\RequestValidatorInterface;
use App\Exceptions\FormValidationException;
use League\MimeTypeDetection\FinfoMimeTypeDetector;

class UploadReceiptRequestValidator implements RequestValidatorInterface
{
    public function validate(array $data): array
    {
        /**
         * @var \Psr\Http\Message\UploadedFileInterface $uploadedFile
         */
        $uploadedFile = $data['receipt'] ?? null;

        // 1. Validate the uploaded file
        if (!$uploadedFile) {
            throw new FormValidationException(['receipt' => ['Please select a receipt file']]);
        }

        if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
            throw new FormValidationException(['receipt' => ['Failed to upload a receipt file']]);
        }

        // 2. Validate file size
        $maxFileSize = 5 * 1024 * 1024;
        $fileSize = $uploadedFile->getSize();

        if ($fileSize > $maxFileSize) {
            throw new FormValidationException(['receipt' => [(string) 'Uploaded Receipt file of size "' . $fileSize . '" exceeds maximum allowed size "' . $maxFileSize . '"']]);
        }

        // 3. Validate file name
        $filename = $uploadedFile->getClientFilename();

        if (!preg_match('/^[a-zA-Z0-9\s._-]+$/', $filename)) {
            throw new FormValidationException(['receipt' => ['Invalid filename (Allowed characters (a to z, A to Z, . , _ , or -))']]);
        }


        $length = strlen($filename);
        if (!($length < 100)) {
            throw new FormValidationException(['receipt' => [(string) 'Filename length "' . $length . '" characters exceeds max length of "100" characters']]);
        }

        // 4. Validate file type
        $allowedMIMETypes = ['image/jpg', 'image/jpeg', 'image/png', 'application/pdf'];
        $allowedFileExtensions = ['pdf', 'jpg', 'png', 'jpeg'];


        if (!in_array($uploadedFile->getClientMediaType(), $allowedMIMETypes)) {
            throw new FormValidationException(['receipt' => ['Receipt should be an image or pdf file']]);
        }

        $tmpFilePath = $uploadedFile->getStream()->getMetadata('uri');
        $detector = new FinfoMimeTypeDetector();
        $mimeType = $detector->detectMimeTypeFromFile($tmpFilePath);

        if (!in_array($mimeType, $allowedMIMETypes)) {
            throw new FormValidationException(['receipt' => ['Invalid MIME Type (Allowed MIME types are (image/jpg, image/jpeg, application/pdf. or image/png))']]);
        }

        return $data;
    }
}