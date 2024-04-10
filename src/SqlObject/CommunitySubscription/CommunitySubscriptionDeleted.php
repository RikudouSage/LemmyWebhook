<?php

namespace App\SqlObject\CommunitySubscription;

use App\Enum\DatabaseOperation;

final readonly class CommunitySubscriptionDeleted extends AbstractCommunitySubscription
{

    protected function getTriggerOperation(): DatabaseOperation
    {
        return DatabaseOperation::Delete;
    }

    public function getName(): string
    {
        return 'rikudou_community_subscription_deleted';
    }
}
