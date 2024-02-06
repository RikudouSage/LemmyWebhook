<?php

namespace App\Dto\RawData;

use App\Attribute\RawDataType;

#[RawDataType(table: 'private_message_report')]
final readonly class PrivateMessageReportData
{
    public int $id;
    public int $creatorId;
    public int $privateMessageId;
    public string $originalPmText;
    public string $reason;
    public bool $resolved;
    public ?int $resolverId;
}
