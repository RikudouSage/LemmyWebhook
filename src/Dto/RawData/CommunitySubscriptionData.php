<?php

namespace App\Dto\RawData;

use App\Attribute\RawDataType;

#[RawDataType(table: 'community_follower')]
final readonly class CommunitySubscriptionData
{
    public int $communityId;
    public int $personId;
    public bool $pending;
}
