<?php

declare(strict_types=1);

namespace App\Entities;

use App\Contracts\OwnableInterface;
use App\Contracts\UserInterface;
use App\Traits\HasTimestamps;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;

#[Entity, HasLifecycleCallbacks]
#[Table('users')]
class User implements UserInterface
{
    use HasTimestamps;

    #[Column(options: ['unsigned' => true]), Id, GeneratedValue]
    private int $id;

    #[Column(length: 80)]
    private string $name;

    #[Column(unique: true)]
    private string $email;

    #[Column]
    private string $password;

    #[Column(name: 'verified_at', nullable: true)]
    private ?\DateTime $verifiedAt;

    #[Column(name: 'enable_two_factor')]
    private bool $enableTwoFactor;

    #[OneToMany(targetEntity: Category::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    private Collection $categories;

    #[OneToMany(targetEntity: Transaction::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    private Collection $transactions;

    #[OneToMany(targetEntity: Email::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    private Collection $emails;

    #[OneToMany(targetEntity: UserLoginCode::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    private Collection $codes;

    public function __construct()
    {
        $this->enableTwoFactor = true;
        $this->categories = new ArrayCollection();
        $this->transactions = new ArrayCollection();
        $this->emails = new ArrayCollection();
        $this->codes = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setName(string $name): User
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setEmail(string $email): User
    {
        $this->email = $email;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setPassword(string $password): User
    {
        $this->password = $password;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function addCategory(Category $category): User
    {
        $category->setUser($this);

        $this->categories->add($category);

        return $this;
    }

    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addEmail(Email $email): User
    {
        $email->setUser($this);

        $this->transactions->add($email);

        return $this;
    }

    public function getEmails(): Collection
    {
        return $this->emails;
    }

    public function addCodes(UserLoginCode $code): User
    {
        $code->setUser($this);

        $this->transactions->add($code);

        return $this;
    }

    public function getCodes(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): User
    {
        $transaction->setUser($this);

        $this->transactions->add($transaction);

        return $this;
    }

    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function setVerifiedAt(?\DateTime $verifiedAt): User
    {
        $this->verifiedAt = $verifiedAt;

        return $this;
    }

    public function getVerifiedAt(): ?\DateTime
    {
        return $this->verifiedAt;
    }

    public function can(OwnableInterface $entity): bool
    {
        return $this->getId() === $entity->getUser()->getId();
    }

    public function setEnableTwoFactor(bool $enableTwoFactor): User
    {
        $this->enableTwoFactor = $enableTwoFactor;

        return $this;
    }

    public function hasTwoFactorAuthEnabled(): bool
    {
        return $this->enableTwoFactor;
    }
}