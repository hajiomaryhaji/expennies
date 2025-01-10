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
#[Table(name: 'user_login_codes')]
class UserLoginCode
{
    use HasTimestamps;

    #[Id, GeneratedValue]
    #[Column(options: ['unsigned' => true])]
    private int $id;

    #[Column(name: 'user_id', options: ['unsigned' => true])]
    private int $userId;

    #[Column]
    private string $code;

    #[Column(name: 'is_active')]
    private bool $isActive;

    #[Column(name: 'expiration_date')]
    private \DateTime $expirationDate;

    #[ManyToOne(inversedBy: 'codes')]
    private User $user;

    public function __construct()
    {
        $this->isActive = true;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setCode(string $code): UserLoginCode
    {
        $this->code = $code;

        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setIsActive(bool $isActive): UserLoginCode
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function setExpirationDate(\DateTime $expirationDate): UserLoginCode
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }

    public function getExpirationDate(): \DateTime
    {
        return $this->expirationDate;
    }

    public function setUser(User $user): UserLoginCode
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}