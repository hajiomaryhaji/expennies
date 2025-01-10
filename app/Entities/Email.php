<?php

declare(strict_types=1);

namespace App\Entities;

use App\Enums\EmailStatus;
use App\Traits\HasTimestamps;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Entity, HasLifecycleCallbacks]
#[Table(name: 'emails')]
class Email
{
    use HasTimestamps;

    #[Id, GeneratedValue]
    #[Column(options: ['unsigned' => true])]
    private int $id;

    #[Column(name: 'user_id', options: ['unsigned' => true])]
    private int $userId;

    #[Column]
    private string $subject;

    #[Column]
    private EmailStatus $status;

    #[Column]
    private string $metadata;

    #[Column(name: 'text_body', nullable: true)]
    private ?string $textBody;

    #[Column(name: 'html_body')]
    private string $htmlBody;

    #[ManyToOne(inversedBy: 'emails')]
    private User $user;


    public function getId(): int
    {
        return $this->id;
    }

    public function setSubject(string $subject): Email
    {
        $this->subject = $subject;

        return $this;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setStatus(EmailStatus $status): Email
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus(): EmailStatus
    {
        return $this->status;
    }

    public function setMetadata(string $metadata): Email
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function getMetadata(): string
    {
        return $this->metadata;
    }

    public function setTextBody(string $textBody): Email
    {
        $this->textBody = $textBody;

        return $this;
    }

    public function getTextBody(): ?string
    {
        return $this->textBody;
    }

    public function setHtmlBody(string $htmlBody): Email
    {
        $this->htmlBody = $htmlBody;

        return $this;
    }

    public function getHtmlBody(): string
    {
        return $this->htmlBody;
    }

    public function setUser(User $user): Email
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}