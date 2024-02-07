<?php

namespace App\MessageHandler;

use App\Message\CleanupExpiredRowsMessage;
use App\Repository\AuthenticationTokenRepository;
use App\Repository\RefreshTokenRepository;
use App\Repository\WebhookResponseRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CleanupExpiredRowsHandler
{
    public function __construct(
        private AuthenticationTokenRepository $authenticationTokenRepository,
        private RefreshTokenRepository $refreshTokenRepository,
        private WebhookResponseRepository $webhookResponseRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(CleanupExpiredRowsMessage $message): void
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
        foreach ($this->refreshTokenRepository->findAll() as $refreshToken) {
            if (!$refreshToken->getValidUntil()) {
                continue;
            }
            if ($refreshToken->getValidUntil() < $now) {
                $this->entityManager->remove($refreshToken);
            }
        }
        foreach ($this->webhookResponseRepository->findAll() as $webhookResponse) {
            if (!$webhookResponse->getValidUntil()) {
                $this->entityManager->remove($webhookResponse);
                continue;
            }
            if ($webhookResponse->getValidUntil() < $now) {
                $this->entityManager->remove($webhookResponse);
            }
        }
        $this->entityManager->flush();
    }
}
