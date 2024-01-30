<?php

namespace App\ApiFilter;

use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(name: 'app.api.filter')]
interface ApiFilter
{
    public function getEntityClass(): string;
    public function getQueryBuilder(QueryBuilder $builder, User $currentUser): QueryBuilder;
}
