<?php

namespace App\Dto\RawData;

use App\Attribute\RawDataType;

#[RawDataType(table: 'comment')]
final readonly class CommentData
{
    public int $id;
    public int $creatorId;
    public int $postId;
    public string $content;
    public bool $removed;
    public bool $deleted;
    public string $apId;
    public bool $local;
    public bool $distinguished;
}
