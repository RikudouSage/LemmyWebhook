<?php

namespace App\Service;

use App\Dto\Model\WebhookImportData;
use App\Entity\User;
use App\Entity\Webhook;
use App\Enum\DatabaseOperation;
use App\Enum\RequestMethod;
use App\Enum\SigningMode;
use App\Exception\InvalidImportException;
use App\Repository\WebhookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Yaml\Yaml;

final readonly class WebhookImporter
{
    public function __construct(
        private RawWebhookParser $webhookParser,
        private WebhookRepository $webhookRepository,
        private EntityManagerInterface $entityManager,
        private Security $security,
    ) {
    }

    public function import(string $configuration): void
    {
        $parsed = Yaml::parse($configuration);
        if (!isset($parsed['webhooks'])) {
            throw new InvalidImportException("The 'webhooks' root property is missing.");
        }

        $currentUser = $this->security->getUser();
        assert($currentUser instanceof User);

        foreach ($parsed['webhooks'] as $webhook) {
            if (!isset($webhook['uniqueMachineName'])) {
                throw new InvalidImportException("Each imported webhook must contain the 'uniqueMachineName' field.");
            }
            $uniqueName = $webhook['uniqueMachineName'];
            if (!isset($webhook['url'])) {
                throw new InvalidImportException("The webhook '{$uniqueName}' is missing the 'url' field.");
            }
            if (!isset($webhook['method'])) {
                throw new InvalidImportException("The webhook '{$uniqueName}' is missing the 'method' field.");
            }
            $method = RequestMethod::tryFrom($webhook['method']);
            if ($method === null) {
                throw new InvalidImportException("The webhook '{$uniqueName}' contains an invalid 'method' value: '{$webhook['method']}'");
            }
            if (!isset($webhook['objectType'])) {
                throw new InvalidImportException("The webhook '{$uniqueName}' is missing the 'objectType' field.");
            }
            if (!$this->webhookParser->isValidTable($webhook['objectType'])) {
                throw new InvalidImportException("The webhook '{$uniqueName}' contains an invalid 'objectType' value: '{$webhook['objectType']}'");
            }
            if (isset($webhook['operation']) && DatabaseOperation::tryFrom($webhook['operation']) === null) {
                throw new InvalidImportException("The webhook '{$uniqueName}' contains an invalid 'operation' value: '{$webhook['operation']}'");
            }

            $webhookDto = new WebhookImportData(
                uniqueMachineName: $uniqueName,
                url: $webhook['url'],
                method: $method,
                objectType: $webhook['objectType'],
                operation: isset($webhook['operation']) ? DatabaseOperation::tryFrom($webhook['operation']) : null,
                bodyExpression: $webhook['bodyExpression'] ?? null,
                filterExpression: $webhook['filterExpression'] ?? null,
                enhancedFilterExpression: $webhook['enhancedFilterExpression'] ?? null,
                headers: $webhook['headers'] ?? null,
                enabled: $webhook['enabled'] ?? true,
                signingMode: SigningMode::tryFrom($webhook['signingMode'] ?? '') ?? SigningMode::None,
                signingKey: $webhook['signingKey'] ?? null,
            );

            $webhookEntity = $this->webhookRepository->findOneBy([
                'uniqueMachineName' => $webhookDto->uniqueMachineName,
                'user' => $currentUser,
            ]);
            $webhookEntity ??= (new Webhook())
                ->setUniqueMachineName($webhookDto->uniqueMachineName)
                ->setUser($currentUser)
                ->setEnabled($webhookDto->enabled)
            ;

            $webhookEntity
                ->setUrl($webhookDto->url)
                ->setMethod($webhookDto->method)
                ->setObjectType($webhookDto->objectType)
                ->setOperation($webhookDto->operation)
                ->setBodyExpression($webhookDto->bodyExpression)
                ->setFilterExpression($webhookDto->filterExpression)
                ->setEnhancedFilter($webhookDto->enhancedFilterExpression)
                ->setHeaders($webhookDto->headers)
                ->setSigningMode($webhookDto->signingMode)
                ->setSigningKey($webhookDto->signingKey)
            ;
            $this->entityManager->persist($webhookEntity);
        }

        $this->entityManager->flush();
    }
}
