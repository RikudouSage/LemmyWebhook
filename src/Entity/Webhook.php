<?php

namespace App\Entity;

use App\Enum\DatabaseOperation;
use App\Enum\RequestMethod;
use App\Repository\WebhookRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\ExpectedValues;
use Rikudou\JsonApiBundle\Attribute\ApiProperty;
use Rikudou\JsonApiBundle\Attribute\ApiResource;
use Symfony\Component\HttpFoundation\Request;
use \App\Enum\SigningMode;

#[ApiResource]
#[ORM\Entity(repositoryClass: WebhookRepository::class)]
#[ORM\Table(name: 'webhooks')]
#[ORM\Index(fields: ['objectType'])]
#[ORM\Index(fields: ['operation'])]
#[ORM\Index(fields: ['enabled'])]
#[ORM\UniqueConstraint(fields: ['user', 'uniqueMachineName'])]
class Webhook
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ApiProperty]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $url = null;

    #[ApiProperty(setter: 'setMethodAsString')]
    #[ORM\Column(length: 10, enumType: RequestMethod::class)]
    private RequestMethod $method = RequestMethod::Get;

    #[ApiProperty]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bodyExpression = null;

    #[ApiProperty]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $filterExpression = null;

    #[ApiProperty]
    #[ORM\Column(length: 180)]
    private ?string $objectType = null;

    #[ApiProperty(setter: 'setOperationAsString')]
    #[ORM\Column(length: 180, nullable: true, enumType: DatabaseOperation::class)]
    private ?DatabaseOperation $operation = null;

    #[ApiProperty(relation: false)]
    #[ORM\Column(nullable: true)]
    private ?array $headers = null;

    #[ApiProperty]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $enhancedFilter = null;

    #[ApiProperty]
    #[ORM\Column]
    private bool $enabled = true;

    #[ApiProperty(relation: true)]
    #[ORM\ManyToOne(inversedBy: 'webhooks')]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'webhook', targetEntity: WebhookResponse::class, orphanRemoval: true)]
    private Collection $webhookResponses;

    #[ApiProperty(getter: 'shouldLogResponses')]
    #[ORM\Column]
    private bool $logResponses = false;

    #[ApiProperty]
    #[ORM\Column(length: 180, nullable: true)]
    private ?string $uniqueMachineName = null;

    #[ApiProperty(setter: 'setSigningModeAsString')]
    #[ORM\Column(enumType: SigningMode::class)]
    private SigningMode $signingMode = SigningMode::None;

    #[ApiProperty]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $signingKey = null;

    #[ApiProperty(getter: 'shouldRetryOnFailure')]
    #[ORM\Column(options: ['default' => true])]
    private bool $retryOnFailure = true;

    public function __construct()
    {
        $this->webhookResponses = new ArrayCollection();
    }

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

    public function setMethodAsString(string $method): static
    {
        return $this->setMethod(RequestMethod::from($method));
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

    public function setOperationAsString(?string $operation): static
    {
        if ($operation !== null) {
            $operation = DatabaseOperation::from($operation);
        }

        return $this->setOperation($operation);
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, WebhookResponse>
     */
    public function getWebhookResponses(): Collection
    {
        return $this->webhookResponses;
    }

    public function addWebhookResponse(WebhookResponse $webhookResponse): static
    {
        if (!$this->webhookResponses->contains($webhookResponse)) {
            $this->webhookResponses->add($webhookResponse);
            $webhookResponse->setWebhook($this);
        }

        return $this;
    }

    public function removeWebhookResponse(WebhookResponse $webhookResponse): static
    {
        if ($this->webhookResponses->removeElement($webhookResponse)) {
            // set the owning side to null (unless already changed)
            if ($webhookResponse->getWebhook() === $this) {
                $webhookResponse->setWebhook(null);
            }
        }

        return $this;
    }

    public function shouldLogResponses(): bool
    {
        return $this->logResponses;
    }

    public function setLogResponses(bool $logResponses): static
    {
        $this->logResponses = $logResponses;

        return $this;
    }

    public function getUniqueMachineName(): ?string
    {
        return $this->uniqueMachineName;
    }

    public function setUniqueMachineName(?string $uniqueMachineName): static
    {
        $this->uniqueMachineName = $uniqueMachineName;

        return $this;
    }

    public function getSigningMode(): SigningMode
    {
        return $this->signingMode;
    }

    public function setSigningMode(SigningMode $signingMode): static
    {
        $this->signingMode = $signingMode;

        return $this;
    }

    public function setSigningModeAsString(string $signingMode): static
    {
        return $this->setSigningMode(SigningMode::from($signingMode));
    }

    public function getSigningKey(): ?string
    {
        return $this->signingKey;
    }

    public function setSigningKey(?string $signingKey): static
    {
        $this->signingKey = $signingKey;

        return $this;
    }

    public function shouldRetryOnFailure(): bool
    {
        return $this->retryOnFailure;
    }

    public function setRetryOnFailure(bool $retryOnFailure): static
    {
        $this->retryOnFailure = $retryOnFailure;

        return $this;
    }
}
