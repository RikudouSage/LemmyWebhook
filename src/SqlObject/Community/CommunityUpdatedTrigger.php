<?php

namespace App\SqlObject\Community;

use App\Enum\DatabaseOperation;

final readonly class CommunityUpdatedTrigger extends AbstractCommunityTrigger
{
    protected function getTriggerOperation(): DatabaseOperation
    {
        return DatabaseOperation::Update;
    }

    public function getName(): string
    {
        return 'rikudou_community_updated';
    }
}
