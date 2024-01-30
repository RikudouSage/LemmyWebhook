<?php

namespace App\ApiFilter;

use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use LogicException;
use Rikudou\JsonApiBundle\Service\Filter\AbstractFilteredQueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\HttpFoundation\ParameterBag;

final class ApiFilterCoordinator extends AbstractFilteredQueryBuilder
{
    /**
     * @param iterable<ApiFilter> $filters
     */
    public function __construct(
        #[TaggedIterator('app.api.filter')]
        private readonly iterable $filters,
        private readonly Security $security,
    ) {
    }

    public function get(string $class, ParameterBag $queryParams, bool $useFilter = true, bool $useSort = true,): QueryBuilder
    {
        $currentUser = $this->getCurrentUser();
        $builder = parent::get($class, $queryParams, $useFilter, $useSort);
        foreach ($this->filters as $filter) {
            if (is_a($class, $filter->getEntityClass(), true)) {
                $builder = $filter->getQueryBuilder($builder, $currentUser);
            }
        }

        return $builder;
    }

    private function getCurrentUser(): User
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new LogicException('Trying to get current user when not logged in');
        }

        return $user;
    }
}
