<?php

namespace App\ApiFilter\Filter;

use App\ApiFilter\ApiFilter;
use App\Entity\User;
use App\Entity\WebhookResponse;
use Doctrine\ORM\QueryBuilder;
use Override;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final readonly class WebhookResponseApiFilter implements ApiFilter
{
    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    #[Override]
    public function getEntityClass(): string
    {
        return WebhookResponse::class;
    }

    #[Override]
    public function getQueryBuilder(QueryBuilder $builder, User $currentUser): QueryBuilder
    {
        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            return $builder;
        }

        return $builder
            ->innerJoin('entity.webhook', 'w')
            ->andWhere('w.user = :currentUser')
            ->setParameter('currentUser', $currentUser)
        ;
    }
}
