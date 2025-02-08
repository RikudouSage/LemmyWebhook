<?php

namespace App\SqlObject\Community;

use App\SqlObject\AbstractTableTrigger;

abstract readonly class AbstractCommunityTrigger extends AbstractTableTrigger
{
    public function getTable(): string
    {
        return 'community';
    }

    protected function getFields(): array
    {
        return [
            'id',
            'name',
            'title',
            'description',
            'removed',
            'deleted',
            'nsfw',
            'actor_id',
            'local',
            'hidden',
            'posting_restricted_to_mods',
            'instance_id',
        ];
    }
}
