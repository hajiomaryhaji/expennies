<?php

declare(strict_types=1);

namespace App\Helpers;

use Psr\Http\Message\ResponseInterface;

class ResponseFormatter
{
    public static function json(
        ResponseInterface $response,
        mixed $data,
        int $flags = JSON_THROW_ON_ERROR | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG
    ): ResponseInterface {
        $response = $response->withHeader('Content-Type', 'application/json');

        $response->getBody()->write(json_encode($data, $flags));

        return $response;
    }
}