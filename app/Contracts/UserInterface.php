<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Entities\User;

interface UserInterface
{
    public function getId(): int;

    public function getName(): string;

    public function getEmail(): string;

    public function getPassword(): string;

    public function setVerifiedAt(?\DateTime $dateTime): UserInterface;

    public function setEnableTwoFactor(bool $check): UserInterface;

    public function hasTwoFactorAuthEnabled(): bool;
}