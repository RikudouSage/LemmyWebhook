<?php

namespace App\Dto\RawData;

use App\Attribute\RawDataType;
use Rikudou\LemmyApi\Enum\Language;

#[RawDataType(table: 'post')]
final readonly class PostData
{
    public int $id;
    public ?string $url;
    public ?string $body;
    public string $name;
    public bool $nsfw;
    public bool $local;
    public ?string $apId;
    public int $creatorId;
    public Language $languageId;
    public int $communityId;
    public bool $locked;
    public bool $deleted;
    public bool $removed;
}
