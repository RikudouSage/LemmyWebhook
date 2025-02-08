<?php

namespace App\SqlObject\Community;

use App\Enum\DatabaseOperation;

final readonly class CommunityCreatedTrigger extends AbstractCommunityTrigger
{
    protected function getTriggerOperation(): DatabaseOperation
    {
        return DatabaseOperation::Insert;
    }

    public function getName(): string
    {
        return 'rikudou_community_created';
    }
}
