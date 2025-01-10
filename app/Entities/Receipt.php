<?php

declare(strict_types=1);

namespace App\Entities;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Doctrine\ORM\Mapping\Table;
use Doctrine\Persistence\Event\LifecycleEventArgs;

#[Entity, HasLifecycleCallbacks]
#[Table('receipts')]
class Receipt
{
    #[Id, Column, GeneratedValue]
    private int $id;

    #[Column(name: 'transaction_id', options: ['unsigned' => true])]
    private int $transactionId;

    #[Column(name: 'unique_file_name', length: 255)]
    private string $uniqueFileName;

    #[Column(name: 'client_file_name', length: 100)]
    private string $clientFileName;

    #[Column(name: 'mime_type', length: 30)]
    private string $mimeType;

    #[Column(name: 'created_at')]
    private \DateTime $createdAt;

    #[ManyToOne(inversedBy: 'receipts')]
    private Transaction $transaction;

    public function getId(): int
    {
        return $this->id;
    }

    public function setUniqueFileName(string $uniqueFileName): Receipt
    {
        $this->uniqueFileName = $uniqueFileName;

        return $this;
    }

    public function getUniqueFileName(): string
    {
        return $this->uniqueFileName;
    }

    public function setClientFileName(string $clientFileName): Receipt
    {
        $this->clientFileName = $clientFileName;

        return $this;
    }

    public function getClientFileName(): string
    {
        return $this->clientFileName;
    }

    public function setMimeType(string $mimeType): Receipt
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    #[PrePersist, PreUpdate]
    public function setTimestamps(LifecycleEventArgs $arg): void
    {
        if (!isset($this->createdAt)) {
            $this->createdAt = new \DateTime('now', new \DateTimeZone('Africa/Dar_es_salaam'));
        }
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setTransaction(Transaction $transaction): Receipt
    {
        $this->transaction = $transaction;

        return $this;
    }

    public function getTransaction(): Transaction
    {
        return $this->transaction;
    }
}