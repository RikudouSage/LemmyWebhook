<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Rikudou\JsonApiBundle\Attribute\ApiProperty;
use Rikudou\JsonApiBundle\Attribute\ApiResource;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ApiResource]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[ApiProperty]
    private ?string $username = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ApiProperty]
    #[ORM\Column]
    private bool $enabled = false;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Webhook::class, cascade: ['persist', 'remove'])]
    #[ApiProperty(relation: true)]
    private Collection $webhooks;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: AuthenticationToken::class, orphanRemoval: true)]
    private Collection $authenticationTokens;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: RefreshToken::class, orphanRemoval: true)]
    private Collection $refreshTokens;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Scope::class, orphanRemoval: true)]
    #[ApiProperty(relation: true)]
    private Collection $scopes;

    public function __construct()
    {
        $this->webhooks = new ArrayCollection();
        $this->authenticationTokens = new ArrayCollection();
        $this->refreshTokens = new ArrayCollection();
        $this->scopes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return Collection<int, Webhook>
     */
    public function getWebhooks(): Collection
    {
        return $this->webhooks;
    }

    public function addWebhook(Webhook $webhook): static
    {
        if (!$this->webhooks->contains($webhook)) {
            $this->webhooks->add($webhook);
            $webhook->setUser($this);
        }

        return $this;
    }

    public function removeWebhook(Webhook $webhook): static
    {
        if ($this->webhooks->removeElement($webhook)) {
            // set the owning side to null (unless already changed)
            if ($webhook->getUser() === $this) {
                $webhook->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AuthenticationToken>
     */
    public function getAuthenticationTokens(): Collection
    {
        return $this->authenticationTokens;
    }

    public function addAuthenticationToken(AuthenticationToken $authenticationToken): static
    {
        if (!$this->authenticationTokens->contains($authenticationToken)) {
            $this->authenticationTokens->add($authenticationToken);
            $authenticationToken->setUser($this);
        }

        return $this;
    }

    public function removeAuthenticationToken(AuthenticationToken $authenticationToken): static
    {
        if ($this->authenticationTokens->removeElement($authenticationToken)) {
            // set the owning side to null (unless already changed)
            if ($authenticationToken->getUser() === $this) {
                $authenticationToken->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RefreshToken>
     */
    public function getRefreshTokens(): Collection
    {
        return $this->refreshTokens;
    }

    public function addRefreshToken(RefreshToken $refreshToken): static
    {
        if (!$this->refreshTokens->contains($refreshToken)) {
            $this->refreshTokens->add($refreshToken);
            $refreshToken->setUser($this);
        }

        return $this;
    }

    public function removeRefreshToken(RefreshToken $refreshToken): static
    {
        if ($this->refreshTokens->removeElement($refreshToken)) {
            // set the owning side to null (unless already changed)
            if ($refreshToken->getUser() === $this) {
                $refreshToken->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Scope>
     */
    public function getScopes(): Collection
    {
        return $this->scopes;
    }

    public function addScope(Scope $scope): static
    {
        if (!$this->scopes->contains($scope)) {
            $this->scopes->add($scope);
            $scope->setUser($this);
        }

        return $this;
    }

    public function removeScope(Scope $scope): static
    {
        if ($this->scopes->removeElement($scope)) {
            // set the owning side to null (unless already changed)
            if ($scope->getUser() === $this) {
                $scope->setUser(null);
            }
        }

        return $this;
    }

    public function findScopeByType(string $type): ?Scope
    {
        foreach ($this->getScopes() as $scope) {
            if ($scope->getScope() === $type) {
                return $scope;
            }
        }

        return null;
    }
}
