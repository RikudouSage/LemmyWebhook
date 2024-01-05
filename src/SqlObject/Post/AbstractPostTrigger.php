<?php

namespace App\SqlObject\Post;

use App\Enum\DatabaseOperation;
use App\SqlObject\AbstractTableTrigger;
use Doctrine\DBAL\Connection;

abstract readonly class AbstractPostTrigger extends AbstractTableTrigger
{
    public function getTable(): string
    {
        return 'post';
    }

    public function getFields(): array
    {
        return [
            'id',
            'name',
            'url',
            'body',
            'creator_id',
            'community_id',
            'removed',
            'locked',
            'deleted',
            'nsfw',
            'ap_id',
            'local',
            'language_id',
        ];
    }
}
