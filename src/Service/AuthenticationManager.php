<?php

namespace App\Service;

use App\Entity\AuthenticationToken;
use App\Entity\RefreshToken;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

final readonly class AuthenticationManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SecureTokenGenerator $tokenGenerator,
    ) {
    }

    public function createAuthenticationToken(User $user): AuthenticationToken
    {
        $token = (new AuthenticationToken())
            ->setToken($this->tokenGenerator->generate())
            ->setUser($user)
            ->setValidUntil(new DateTimeImmutable('+1 hour'))
        ;
        $this->entityManager->persist($token);
        $this->entityManager->flush();

        return $token;
    }

    public function createRefreshToken(User $user): RefreshToken
    {
        $token = (new RefreshToken())
            ->setToken($this->tokenGenerator->generate())
            ->setUser($user)
        ;
        $this->entityManager->persist($token);
        $this->entityManager->flush();

        return $token;
    }

    public function invalidateRefreshToken(RefreshToken $token): void
    {
        $this->entityManager->remove($token);
        $this->entityManager->flush();
    }
}
