<?php

namespace App\SqlObject\Community;

use App\Enum\DatabaseOperation;

final readonly class CommunityDeletedTrigger extends AbstractCommunityTrigger
{
    protected function getTriggerOperation(): DatabaseOperation
    {
        return DatabaseOperation::Delete;
    }

    public function getName(): string
    {
        return 'rikudou_community_deleted';
    }
}
