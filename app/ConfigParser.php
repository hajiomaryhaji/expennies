<?php

declare(strict_types=1);

namespace App;

class ConfigParser
{
    public function __construct(private readonly array $settings)
    {

    }

    public function get(string $name, mixed $default = null): mixed
    {
        $path = explode('.', $name);
        $value = $this->settings[array_shift($path)] ?? null;

        if ($value === null) {
            return $default;
        }

        foreach ($path as $key) {
            if (!isset($value[$key])) {
                return $default;
            }

            $value = $value[$key];
        }

        return $value;
    }
}