<?php

namespace App\Dto\RawData;

use App\Attribute\RawDataType;
use DateTimeInterface;

#[RawDataType(table: 'post_saved')]
final readonly class PostSavedData
{
    public int $postId;
    public int $personId;
    public DateTimeInterface $published;
}
