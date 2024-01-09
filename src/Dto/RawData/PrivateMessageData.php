<?php

namespace App\Dto\RawData;

use App\Attribute\RawDataType;

#[RawDataType('private_message')]
final readonly class PrivateMessageData
{
    public int $id;
    public int $creatorId;
    public int $recipientId;
    public bool $local;
}
