<?php

namespace App\Entity;

use App\Repository\WebhookResponseRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Rikudou\JsonApiBundle\Attribute\ApiProperty;
use Rikudou\JsonApiBundle\Attribute\ApiResource;

#[ApiResource]
#[ORM\Entity(repositoryClass: WebhookResponseRepository::class)]
#[ORM\Table(name: 'webhook_responses')]
#[ORM\HasLifecycleCallbacks]
class WebhookResponse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ApiProperty(relation: true)]
    #[ORM\ManyToOne(inversedBy: 'webhookResponses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Webhook $webhook = null;

    #[ApiProperty]
    #[ORM\Column]
    private ?int $statusCode = null;

    #[ApiProperty]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $body = null;

    #[ApiProperty]
    #[ORM\Column(type: Types::JSON)]
    private array $headers = [];

    #[ApiProperty]
    #[ORM\Column]
    private ?DateTimeImmutable $validUntil = null;

    #[ApiProperty]
    #[ORM\Column]
    private ?DateTimeImmutable $created = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWebhook(): ?Webhook
    {
        return $this->webhook;
    }

    public function setWebhook(?Webhook $webhook): static
    {
        $this->webhook = $webhook;

        return $this;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function setStatusCode(int $statusCode): static
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): static
    {
        $this->body = $body;

        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setHeaders(array $headers): static
    {
        $this->headers = $headers;

        return $this;
    }

    public function getValidUntil(): ?DateTimeImmutable
    {
        return $this->validUntil;
    }

    public function setValidUntil(DateTimeImmutable $validUntil): static
    {
        $this->validUntil = $validUntil;

        return $this;
    }

    public function getCreated(): ?DateTimeImmutable
    {
        return $this->created;
    }

    public function setCreated(DateTimeImmutable $created): static
    {
        $this->created = $created;

        return $this;
    }

    #[ORM\PrePersist]
    public function setCreatedDateOnPersist(): void
    {
        if ($this->getCreated() === null) {
            $this->setCreated(new DateTimeImmutable());
        }
    }
}
