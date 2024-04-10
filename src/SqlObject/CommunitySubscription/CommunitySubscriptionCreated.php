<?php

namespace App\SqlObject\CommunitySubscription;

use App\Enum\DatabaseOperation;

final readonly class CommunitySubscriptionCreated extends AbstractCommunitySubscription
{

    protected function getTriggerOperation(): DatabaseOperation
    {
        return DatabaseOperation::Insert;
    }

    public function getName(): string
    {
        return 'rikudou_community_subscription_created';
    }
}
