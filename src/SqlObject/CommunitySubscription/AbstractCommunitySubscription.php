<?php

namespace App\SqlObject\CommunitySubscription;

use App\SqlObject\AbstractTableTrigger;

abstract readonly class AbstractCommunitySubscription extends AbstractTableTrigger
{
    public function getTable(): string
    {
        return 'community_follower';
    }

    protected function getFields(): array
    {
        return [
            'community_id',
            'person_id',
            'pending',
        ];
    }
}
