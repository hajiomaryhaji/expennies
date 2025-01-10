<?php

declare(strict_types=1);

namespace App\Entities;

use App\Traits\HasTimestamps;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Entity, HasLifecycleCallbacks]
#[Table(name: 'password_resets')]
class PasswordReset
{
    use HasTimestamps;

    #[Id, GeneratedValue]
    #[Column(options: ['unsigned' => true])]
    private int $id;

    #[Column]
    private string $email;

    #[Column(unique: true)]
    private string $token;

    #[Column(name: 'is_active')]
    private bool $isActive;

    #[Column(name: 'expiration_date')]
    private \DateTime $expirationDate;

    public function __construct()
    {
        $this->isActive = true;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setEmail(string $email): PasswordReset
    {
        $this->email = $email;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setToken(string $token): PasswordReset
    {
        $this->token = $token;

        return $this;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setIsActive(bool $isActive): PasswordReset
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function setExpirationDate(\DateTime $expirationDate): PasswordReset
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }

    public function getExpirationDate(): \DateTime
    {
        return $this->expirationDate;
    }
}