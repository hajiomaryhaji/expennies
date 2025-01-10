<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\SessionInterface;
use App\DTOs\DataTablesQueryParams;
use Psr\Http\Message\ServerRequestInterface;

class RequestService
{
    public function __construct(private readonly SessionInterface $session)
    {

    }

    public function getReferer(ServerRequestInterface $request): string
    {
        $referer = $request->getHeader('referer')[0] ?? '';

        if (!$referer) {
            return $this->session->get('previousUri');
        }

        $refererHost = parse_url($referer, PHP_URL_HOST);

        if ($refererHost !== $request->getUri()->getHost()) {
            $referer = $this->session->get('previousUri');
        }

        return $referer;
    }

    public function isXHR(ServerRequestInterface $request): bool
    {
        return $request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';
    }

    public function getDataTablesQueryParams(ServerRequestInterface $request): DataTablesQueryParams
    {
        $params = $request->getQueryParams();

        $orderBy = isset($params['order']) ? $params['columns'][$params['order'][0]['column']]['data'] : 'updatedAt';
        $orderDir = isset($params['order']) ? $params['order'][0]['dir'] : 'desc';

        return new DataTablesQueryParams(
            (int) $params['start'],
            (int) $params['length'],
            $orderBy,
            $orderDir,
            $params['search']['value'],
            (int) $params['draw']
        );
    }

    public function getIpAddress(ServerRequestInterface $request, array $trustedProxies): ?string
    {
        $serverParams = $request->getServerParams();

        if (in_array($serverParams['REMOTE_ADDR'], $trustedProxies, true) && isset($serverParams['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $serverParams['HTTP_X_FORWARDED_FOR']);

            return trim($ips[0]);
        }

        return $serverParams['REMOTE_ADDR'] ?? null;
    }
}