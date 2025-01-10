<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Contracts\SessionInterface;
use App\Exceptions\FormValidationException;
use App\Helpers\ResponseFormatter;
use App\Services\RequestService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FormValidationExceptionMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly SessionInterface $session,
        private readonly RequestService $requestService
    ) {

    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (FormValidationException $e) {
            $response = $this->responseFactory->createResponse(302);

            if ($this->requestService->isXHR($request)) {
                return ResponseFormatter::json($response->withStatus(422), $e->errors);
            }

            $referer = $this->requestService->getReferer($request);

            $this->session->flash('errors', $e->errors);

            $oldData = $request->getParsedBody();
            $sensitiveFields = ['password', 'passwordConfirmation'];

            $this->session->flash('old', array_diff_key($oldData, array_flip($sensitiveFields)));

            return $response->withHeader('Location', $referer);
        }
    }
}