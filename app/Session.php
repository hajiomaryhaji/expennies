<?php

declare(strict_types=1);

namespace App;

use App\Contracts\SessionInterface;
use App\DTOs\SessionConfig;
use App\Exceptions\SessionException;

class Session implements SessionInterface
{
    public function __construct(private readonly SessionConfig $options)
    {

    }

    public function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            throw new SessionException('Session has already been started');
        }

        if (headers_sent($filename, $line)) {
            throw new SessionException('Headers have already sent by ' . $filename . ': ' . $line);
        }

        session_set_cookie_params([
            'httponly' => $this->options->httpOnly,
            'secure' => $this->options->secure,
            'samesite' => $this->options->sameSite->value
        ]);

        if (!empty($this->options->name)) {
            session_name($this->options->name);
        }

        if (!session_start()) {
            throw new SessionException('Unable to start session');
        }
    }

    public function save(): void
    {
        session_write_close();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : $default;
    }

    public function put(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function regenerate(): void
    {
        session_regenerate_id();
    }

    public function unset(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function flash(string $key, array $flashedData): void
    {
        $_SESSION[$this->options->flash][$key] = $flashedData;
    }

    public function getFlashedData(string $key): array
    {
        $flashedData = $_SESSION[$this->options->flash][$key] ?? [];

        unset($_SESSION[$this->options->flash][$key]);

        return $flashedData;
    }
}