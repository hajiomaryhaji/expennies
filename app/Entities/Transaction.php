<?php

declare(strict_types=1);

namespace App\Entities;

use App\Contracts\OwnableInterface;
use App\Traits\HasTimestamps;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;

#[Entity, HasLifecycleCallbacks]
#[Table('transactions')]
class Transaction implements OwnableInterface
{
    use HasTimestamps;

    #[Id, Column(options: ['unsigned' => true]), GeneratedValue]
    private int $id;

    #[Column(name: 'was_reveiwed', options: ['default' => 0])]
    private bool $wasReviewed;

    #[Column(name: 'category_id', options: ['unsigned' => true], nullable: true)]
    private int $categoryId;

    #[Column(name: 'user_id', options: ['unsigned' => true])]
    private int $userId;

    #[Column(length: 40)]
    private string $description;

    #[Column]
    private \DateTime $date;

    #[Column(type: 'decimal', precision: 15, scale: 2)]
    private float $amount;

    #[ManyToOne(inversedBy: 'transactions')]
    private User $user;

    #[ManyToOne(inversedBy: 'transactions')]
    private ?Category $category;

    #[OneToMany(targetEntity: Receipt::class, mappedBy: 'transaction', cascade: ['persist', 'remove'])]
    private Collection $receipts;

    public function __construct()
    {
        $this->receipts = new ArrayCollection();
        $this->wasReviewed = false;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setWasReviewed(bool $wasReviewed): Transaction
    {
        $this->wasReviewed = $wasReviewed;

        return $this;
    }

    public function getWasReviewed(): bool
    {
        return $this->wasReviewed;
    }

    public function setDescription(string $description): Transaction
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDate(\DateTime $date): Transaction
    {
        $this->date = $date;

        return $this;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function setAmount(float $amount): Transaction
    {
        $this->amount = $amount;

        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }


    public function setUser(User $user): Transaction
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setCategory(?Category $category): Transaction
    {
        $this->category = $category;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category ?? null;
    }

    public function addReceipt(Receipt $receipt): Transaction
    {
        $this->receipts->add($receipt);

        $receipt->setTransaction($this);

        return $this;
    }

    public function getReceipts(): Collection
    {
        return $this->receipts;
    }
}