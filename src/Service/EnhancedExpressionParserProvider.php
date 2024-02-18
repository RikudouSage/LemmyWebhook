<?php

namespace App\Service;

use App\Dto\RawData\CommentData;
use App\Dto\RawData\CommunityData;
use App\Dto\RawData\InstanceData;
use App\Dto\RawData\LocalUserData;
use App\Dto\RawData\PersonData;
use App\Dto\RawData\PostData;
use App\Dto\RawData\PrivateMessageData;
use App\Repository\UserRepository;
use App\SqlObject\Comment\CommentCreatedTrigger;
use App\SqlObject\Instance\InstanceCreatedTrigger;
use App\SqlObject\LocalUser\LocalUserCreatedTrigger;
use App\SqlObject\Person\PersonCreatedTrigger;
use App\SqlObject\Post\PostCreatedTrigger;
use App\SqlObject\PrivateMessage\PrivateMessageCreatedTrigger;
use Doctrine\DBAL\Connection;
use LogicException;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

final readonly class EnhancedExpressionParserProvider implements ExpressionFunctionProviderInterface
{
    public function __construct(
        private Connection $connection,
        private RawWebhookParser $webhookParser,
        private AccessDecisionManagerInterface $accessDecisionManager,
        private UserRepository $userRepository,
        private PostCreatedTrigger $postTrigger,
        private InstanceCreatedTrigger $instanceCreatedTrigger,
        private PersonCreatedTrigger $personTrigger,
        private CommentCreatedTrigger $commentTrigger,
        private LocalUserCreatedTrigger $localUserTrigger,
        private PrivateMessageCreatedTrigger $privateMessageTrigger,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new ExpressionFunction(
                'community',
                fn () => throw new LogicException('This function cannot be compiled.'),
                function (array $context, int $communityId): ?CommunityData {
                    if (!$this->doesHaveAccess('community', $context['triggering_user'])) {
                        return null;
                    }
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
                    if (!$this->doesHaveAccess($this->instanceCreatedTrigger->getTable(), $context['triggering_user'])) {
                        return null;
                    }
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
                    if (!$this->doesHaveAccess($this->postTrigger->getTable(), $context['triggering_user'])) {
                        return null;
                    }
                    $fields = implode(',', $this->postTrigger->getFields());
                    $data = $this->connection->executeQuery("select {$fields} from post where id = :id", ['id' => $postId])->fetchAssociative();
                    if ($data === false) {
                        return null;
                    }

                    return $this->webhookParser->deserialize($data, PostData::class);
                }
            ),
            new ExpressionFunction(
                'person',
                fn () => throw new LogicException('This function cannot be compiled.'),
                function (array $context, int $personId): ?PersonData {
                    if (!$this->doesHaveAccess($this->personTrigger->getTable(), $context['triggering_user'])) {
                        return null;
                    }
                    $fields = implode(',', $this->personTrigger->getFields());
                    $data = $this->connection->executeQuery("select {$fields} from person where id = :id", ['id' => $personId])->fetchAssociative();
                    if ($data === false) {
                        return null;
                    }

                    return $this->webhookParser->deserialize($data, PersonData::class);
                },
            ),
            new ExpressionFunction(
                'comment',
                fn () => throw new LogicException('This function cannot be compiled.'),
                function (array $context, int $commentId): ?CommentData {
                    if (!$this->doesHaveAccess($this->commentTrigger->getTable(), $context['triggering_user'])) {
                        return null;
                    }
                    $fields = implode(',', $this->commentTrigger->getFields());
                    $data = $this->connection->executeQuery("select {$fields} from comment where id = :id", ['id' => $commentId])->fetchAssociative();
                    if ($data === false) {
                        return null;
                    }

                    return $this->webhookParser->deserialize($data, CommentData::class);
                },
            ),
            new ExpressionFunction(
                'local_user',
                fn () => throw new LogicException('This function cannot be compiled.'),
                function (array $context, int $userId): ?LocalUserData {
                    if (!$this->doesHaveAccess($this->localUserTrigger->getTable(), $context['triggering_user'])) {
                        return null;
                    }
                    $fields = implode(',', $this->localUserTrigger->getFields());
                    $data = $this->connection->executeQuery("select {$fields} from local_user where id = :id", ['id' => $userId])->fetchAssociative();
                    if ($data === false) {
                        return null;
                    }

                    return $this->webhookParser->deserialize($data, LocalUserData::class);
                },
            ),
            new ExpressionFunction(
                'private_message',
                fn () => throw new LogicException('This function cannot be compiled.'),
                function (array $context, int $userId): ?PrivateMessageData {
                    if (!$this->doesHaveAccess($this->privateMessageTrigger->getTable(), $context['triggering_user'])) {
                        return null;
                    }
                    $fields = implode(',', $this->privateMessageTrigger->getFields());
                    $data = $this->connection->executeQuery("select {$fields} from private_message where id = :id", ['id' => $userId])->fetchAssociative();
                    if ($data === false) {
                        return null;
                    }

                    return $this->webhookParser->deserialize($data, PrivateMessageData::class);
                },
            )
        ];
    }

    private function doesHaveAccess(string $table, ?int $userId): bool
    {
        if ($userId === null) {
            return true;
        }
        $targetUser = $this->userRepository->find($userId);
        if ($targetUser === null) {
            return false;
        }

        $token = new UsernamePasswordToken($targetUser, 'main', $targetUser->getRoles());
        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        return $targetUser->findScopeByType($table)?->isGranted() ?? false;
    }
}
