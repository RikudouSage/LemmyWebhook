<?php

namespace App\Service;

use App\Dto\RawData\CommentData;
use App\Dto\RawData\CommunityData;
use App\Dto\RawData\InstanceData;
use App\Dto\RawData\LocalUserData;
use App\Dto\RawData\ModBanData;
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
use Rikudou\MemoizeBundle\Cache\InMemoryCachePool;
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
        private InMemoryCachePool $inMemoryCache,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new ExpressionFunction(
                'community',
                fn () => throw new LogicException('This function cannot be compiled.'),
                fn (array $context, int $communityId): ?CommunityData => $this->getDto(
                    table: 'community',
                    triggeringUser: $context['triggering_user'],
                    id: $communityId,
                    fields: ['id', 'name', 'title', 'description', 'removed', 'deleted', 'nsfw', 'actor_id', 'local', 'hidden', 'instance_id'],
                    class: CommunityData::class,
                ),
            ),
            new ExpressionFunction(
                'instance',
                fn () => throw new LogicException('This function cannot be compiled.'),
                fn (array $context, int $instanceId): ?InstanceData => $this->getDto(
                    table: $this->instanceCreatedTrigger->getTable(),
                    triggeringUser: $context['triggering_user'],
                    id: $instanceId,
                    fields: $this->instanceCreatedTrigger->getFields(),
                    class: InstanceData::class,
                ),
            ),
            new ExpressionFunction(
                'post',
                fn () => throw new LogicException('This function cannot be compiled.'),
                fn (array $context, int $postId): ?PostData => $this->getDto(
                    table: $this->postTrigger->getTable(),
                    triggeringUser: $context['triggering_user'],
                    id: $postId,
                    fields: $this->postTrigger->getFields(),
                    class: PostData::class,
                ),
            ),
            new ExpressionFunction(
                'person',
                fn () => throw new LogicException('This function cannot be compiled.'),
                fn (array $context, int $personId): ?PersonData => $this->getDto(
                    table: $this->personTrigger->getTable(),
                    triggeringUser: $context['triggering_user'],
                    id: $personId,
                    fields: $this->personTrigger->getFields(),
                    class: PersonData::class,
                ),
            ),
            new ExpressionFunction(
                'comment',
                fn () => throw new LogicException('This function cannot be compiled.'),
                fn (array $context, int $commentId): ?CommentData => $this->getDto(
                    table: $this->commentTrigger->getTable(),
                    triggeringUser: $context['triggering_user'],
                    id: $commentId,
                    fields: $this->commentTrigger->getFields(),
                    class: CommentData::class,
                ),
            ),
            new ExpressionFunction(
                'local_user',
                fn () => throw new LogicException('This function cannot be compiled.'),
                fn (array $context, int $userId): ?LocalUserData => $this->getDto(
                    table: $this->localUserTrigger->getTable(),
                    triggeringUser: $context['triggering_user'],
                    id: $userId,
                    fields: $this->localUserTrigger->getFields(),
                    class: LocalUserData::class,
                ),
            ),
            new ExpressionFunction(
                'private_message',
                fn () => throw new LogicException('This function cannot be compiled.'),
                fn (array $context, int $privateMessageId): ?PrivateMessageData => $this->getDto(
                    table: $this->privateMessageTrigger->getTable(),
                    triggeringUser: $context['triggering_user'],
                    id: $privateMessageId,
                    fields: $this->privateMessageTrigger->getFields(),
                    class: PrivateMessageData::class,
                ),
            ),
            new ExpressionFunction(
                'global_ban',
                fn () => throw new LogicException('This function cannot be compiled.'),
                fn (array $context, int $personId): ?ModBanData => $this->getDto(
                    table: 'mod_ban',
                    triggeringUser: $context['triggering_user'],
                    id: $personId,
                    fields: ['id', 'mod_person_id', 'other_person_id', 'reason', 'banned', 'expires', 'when_'],
                    class: ModBanData::class,
                    idField: 'other_person_id',
                ),
            ),
        ];
    }

    /**
     * @template TDto of object
     * @param array<string> $fields
     * @param class-string<TDto> $class
     * @return TDto|null
     */
    private function getDto(string $table, ?int $triggeringUser, int $id, array $fields, string $class, ?string $idField = 'id'): ?object
    {
        $fields = implode(',', $fields);
        $cacheItem = $this->inMemoryCache->getItem("dto.{$table}.{$triggeringUser}.{$id}.{$fields}.{$class}");
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        if (!$this->doesHaveAccess($table, $triggeringUser)) {
            return null;
        }
        $data = $this->connection->executeQuery("select {$fields} from {$table} where {$idField} = :id", ['id' => $id])->fetchAssociative();
        if ($data === false) {
            return null;
        }

        $cacheItem->set($this->webhookParser->deserialize($data, $class));
        $this->inMemoryCache->save($cacheItem);

        return $cacheItem->get();
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
