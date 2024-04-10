<?php

namespace App\SqlObject\CommunitySubscription;

use App\Enum\DatabaseOperation;

final readonly class CommunitySubscriptionUpdated extends AbstractCommunitySubscription
{

    protected function getTriggerOperation(): DatabaseOperation
    {
        return DatabaseOperation::Update;
    }

    public function getName(): string
    {
        return 'rikudou_community_subscription_updated';
    }
}
