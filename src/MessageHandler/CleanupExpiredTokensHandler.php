<?php

namespace App\MessageHandler;

use App\Message\CleanupExpiredTokensMessage;
use App\Repository\AuthenticationTokenRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CleanupExpiredTokensHandler
{
    public function __construct(
        private AuthenticationTokenRepository $authenticationTokenRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(CleanupExpiredTokensMessage $message): void
    {
        $now = new DateTimeImmutable();
        foreach ($this->authenticationTokenRepository->findAll() as $authenticationToken) {
            if (!$authenticationToken->getValidUntil()) {
                $this->entityManager->remove($authenticationToken);
                continue;
            }
            if ($authenticationToken->getValidUntil() < $now) {
                $this->entityManager->remove($authenticationToken);
            }
        }
        $this->entityManager->flush();
    }
}
