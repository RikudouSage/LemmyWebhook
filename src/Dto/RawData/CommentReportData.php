<?php

namespace App\Dto\RawData;

use App\Attribute\RawDataType;

#[RawDataType(table: 'comment_report')]
final readonly class CommentReportData
{
    public int $id;
    public int $creatorId;
    public int $commentId;
    public string $originalCommentText;
    public string $reason;
    public bool $resolved;
    public ?int $resolverId;
}
