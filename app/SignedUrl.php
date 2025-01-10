<?php

declare(strict_types=1);

namespace App;

use Slim\Interfaces\RouteParserInterface;

class SignedUrl
{
    public function __construct(
        private readonly ConfigParser $configParser,
        private readonly RouteParserInterface $routeParser
    ) {

    }

    public function generate(string $routeName, array $routeParams, array $queryParams): string
    {
        $baseUri = trim($this->configParser->get('app_url'), '/');
        $url = $baseUri . $this->routeParser->urlFor($routeName, $routeParams, $queryParams);

        $signature = hash_hmac('sha256', $url, $this->configParser->get('app_key'));

        return $baseUri . $this->routeParser->urlFor(
            $routeName,
            $routeParams,
            $queryParams + ['signature' => $signature]
        );
    }
}