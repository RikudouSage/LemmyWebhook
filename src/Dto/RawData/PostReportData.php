<?php

namespace App\Dto\RawData;

use App\Attribute\RawDataType;

#[RawDataType(table: 'post_report')]
final readonly class PostReportData
{
    public int $id;
    public int $creatorId;
    public int $commentId;
    public string $originalPostName;
    public ?string $originalPostUrl;
    public ?string $originalPostBody;
    public string $reason;
    public bool $resolved;
    public ?int $resolverId;
}
