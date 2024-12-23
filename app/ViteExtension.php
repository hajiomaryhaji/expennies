<?php

declare(strict_types=1);

namespace App;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ViteExtension extends AbstractExtension
{
    private string $manifestPath;

    public function __construct(string $manifestPath)
    {
        $this->manifestPath = $manifestPath;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('vite_asset', [$this, 'getAssetPath']),
        ];
    }

    public function getAssetPath(string $asset): string
    {
        $manifestContents = file_get_contents($this->manifestPath);
        if ($manifestContents === false) {
            throw new \RuntimeException(sprintf('Could not read manifest file at "%s".', $this->manifestPath));
        }

        $manifest = json_decode($manifestContents, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException(sprintf('JSON decode error: %s', json_last_error_msg()));
        }

        if (isset($manifest[$asset])) {
            return '/build/' . $manifest[$asset]['file'];
        }

        // Fallback for static assets copied by the plugin
        $staticFilePath = '/build/' . $asset;
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . $staticFilePath)) {
            return $staticFilePath;
        }

        throw new \RuntimeException(sprintf('Asset "%s" not found in Vite manifest or static folder.', $asset));
    }



}
