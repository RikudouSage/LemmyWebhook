<?php

namespace App\Entity;

use App\Enum\DatabaseOperation;
use App\Enum\RequestMethod;
use App\Repository\WebhookRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\ExpectedValues;
use Symfony\Component\HttpFoundation\Request;

#[ORM\Entity(repositoryClass: WebhookRepository::class)]
#[ORM\Table(name: 'webhooks')]
#[ORM\Index(fields: ['objectType'])]
#[ORM\Index(fields: ['operation'])]
#[ORM\Index(fields: ['enabled'])]
class Webhook
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $url = null;

    #[ORM\Column(length: 10, enumType: RequestMethod::class)]
    private RequestMethod $method = RequestMethod::Get;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bodyExpression = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $filterExpression = null;

    #[ORM\Column(length: 180)]
    private ?string $objectType = null;

    #[ORM\Column(length: 180, nullable: true, enumType: DatabaseOperation::class)]
    private ?DatabaseOperation $operation = null;

    #[ORM\Column(nullable: true)]
    private ?array $headers = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $enhancedFilter = null;

    #[ORM\Column]
    private bool $enabled = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getMethod(): RequestMethod
    {
        return $this->method;
    }

    public function setMethod(RequestMethod $method): static
    {
        $this->method = $method;

        return $this;
    }

    public function getBodyExpression(): ?string
    {
        return $this->bodyExpression;
    }

    public function setBodyExpression(?string $bodyExpression): static
    {
        $this->bodyExpression = $bodyExpression;

        return $this;
    }

    public function getFilterExpression(): ?string
    {
        return $this->filterExpression;
    }

    public function setFilterExpression(?string $filterExpression): static
    {
        $this->filterExpression = $filterExpression;

        return $this;
    }

    public function getObjectType(): ?string
    {
        return $this->objectType;
    }

    public function setObjectType(string $objectType): static
    {
        $this->objectType = $objectType;

        return $this;
    }

    public function getOperation(): ?DatabaseOperation
    {
        return $this->operation;
    }

    public function setOperation(?DatabaseOperation $operation): static
    {
        $this->operation = $operation;

        return $this;
    }

    public function getHeaders(): ?array
    {
        return $this->headers;
    }

    public function setHeaders(?array $headers): static
    {
        $this->headers = $headers;

        return $this;
    }

    public function getEnhancedFilter(): ?string
    {
        return $this->enhancedFilter;
    }

    public function setEnhancedFilter(?string $enhancedFilter): static
    {
        $this->enhancedFilter = $enhancedFilter;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }
}
