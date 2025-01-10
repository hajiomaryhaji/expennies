<?php

declare(strict_types=1);

namespace App\Contracts;

interface SessionInterface
{
    public function start(): void;

    public function save(): void;

    public function get(string $key, mixed $default = null): mixed;

    public function put(string $key, mixed $value): void;

    public function forget(string $key): void;

    public function regenerate(): void;

    public function unset(string $key): void;

    public function flash(string $key, array $flashedData): void;

    public function getFlashedData(string $key): array;

}