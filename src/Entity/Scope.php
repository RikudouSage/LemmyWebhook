<?php

namespace App\Entity;

use App\Repository\ScopeRepository;
use Doctrine\ORM\Mapping as ORM;
use Rikudou\JsonApiBundle\Attribute\ApiProperty;
use Rikudou\JsonApiBundle\Attribute\ApiResource;

#[ApiResource]
#[ORM\Entity(repositoryClass: ScopeRepository::class)]
#[ORM\Table(name: 'scopes')]
#[ORM\UniqueConstraint(fields: ['user', 'scope'])]
class Scope
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ApiProperty(relation: true)]
    #[ORM\ManyToOne(inversedBy: 'scopes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ApiProperty]
    #[ORM\Column(length: 180)]
    private ?string $scope = null;

    #[ApiProperty]
    #[ORM\Column]
    private bool $granted = false;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getScope(): ?string
    {
        return $this->scope;
    }

    public function setScope(string $scope): static
    {
        $this->scope = $scope;

        return $this;
    }

    public function isGranted(): bool
    {
        return $this->granted;
    }

    public function setGranted(bool $granted): static
    {
        $this->granted = $granted;

        return $this;
    }
}
