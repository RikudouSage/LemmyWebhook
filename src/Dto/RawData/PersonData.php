<?php

namespace App\Dto\RawData;

use App\Attribute\RawDataType;
use DateTimeInterface;

#[RawDataType(table: 'person')]
final readonly class PersonData
{
    public int $id;
    public string $name;
    public ?string $displayName;
    public ?string $avatar;
    public bool $banned;
    public string $actorId;
    public ?string $bio;
    public bool $local;
    public ?string $banner;
    public bool $deleted;
    public ?string $matrixUserId;
    public bool $botAccount;
    public ?DateTimeInterface $banExpires;
    public int $instanceId;
}
