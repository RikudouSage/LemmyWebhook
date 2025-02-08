<?php

namespace App\Dto\RawData;

use App\Attribute\RawDataType;

#[RawDataType(table: 'community')]
final readonly class CommunityData
{
    public int $id;
    public string $name;
    public string $title;
    public ?string $description;
    public bool $removed;
    public bool $deleted;
    public bool $nsfw;
    public string $actorId;
    public bool $local;
    public bool $hidden;
    public bool $postingRestrictedToMods;
    public int $instanceId;
}
