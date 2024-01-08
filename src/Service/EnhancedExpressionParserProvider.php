<?php

namespace App\Service;

use App\Dto\RawData\CommunityData;
use App\Dto\RawData\InstanceData;
use App\Dto\RawData\PostData;
use App\SqlObject\Instance\InstanceCreatedTrigger;
use App\SqlObject\Post\PostCreatedTrigger;
use Doctrine\DBAL\Connection;
use LogicException;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

final readonly class EnhancedExpressionParserProvider implements ExpressionFunctionProviderInterface
{
    public function __construct(
        private Connection $connection,
        private RawWebhookParser $webhookParser,
        private PostCreatedTrigger $postTrigger,
        private InstanceCreatedTrigger $instanceCreatedTrigger,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new ExpressionFunction(
                'community',
                fn () => throw new LogicException('This function cannot be compiled.'),
                function (array $context, int $communityId): ?CommunityData {
                    $data = $this->connection->executeQuery('select id, name, title, description, removed, deleted, nsfw, actor_id, local, hidden, instance_id from community where id = :id', ['id' => $communityId])->fetchAssociative();
                    if ($data === false) {
                        return null;
                    }

                    return $this->webhookParser->deserialize($data, CommunityData::class);
                }
            ),
            new ExpressionFunction(
                'instance',
                fn () => throw new LogicException('This function cannot be compiled.'),
                function (array $context, int $instanceId): ?InstanceData {
                    $fields = implode(',', $this->instanceCreatedTrigger->getFields());
                    $data = $this->connection->executeQuery("select {$fields} from instance where id = :id", ['id' => $instanceId])->fetchAssociative();
                    if ($data === false) {
                        return null;
                    }

                    return $this->webhookParser->deserialize($data, InstanceData::class);
                }
            ),
            new ExpressionFunction(
                'post',
                fn () => throw new LogicException('This function cannot be compiled.'),
                function (array $context, int $postId): ?PostData {
                    $fields = implode(',', $this->postTrigger->getFields());
                    $data = $this->connection->executeQuery("select {$fields} from post where id = :id", ['id' => $postId])->fetchAssociative();
                    if ($data === false) {
                        return null;
                    }

                    return $this->webhookParser->deserialize($data, PostData::class);
                }
            )
        ];
    }
}
