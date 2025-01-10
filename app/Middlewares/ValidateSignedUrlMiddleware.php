<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\ConfigParser;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ValidateSignedUrlMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly ConfigParser $configParser)
    {

    }
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri();
        $queryParams = $request->getQueryParams();
        $upcomingSignature = $queryParams['signature'] ?? '';
        $expirationTime = (int) $queryParams['expirationTime'] ?? 0;

        unset($queryParams['signature']);

        $url = $uri->withQuery(http_build_query($queryParams));

        $signatureValidator = hash_hmac('sha256', (string) $url, $this->configParser->get('app_key'));

        if (!hash_equals($signatureValidator, $upcomingSignature) || !time() >= $expirationTime) {
            throw new \RuntimeException('Failed to verify signature', 502);
        }

        return $handler->handle($request);
    }
}