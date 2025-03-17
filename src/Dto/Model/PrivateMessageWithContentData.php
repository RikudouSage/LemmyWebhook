<?php

namespace App\Dto\Model;

final readonly class PrivateMessageWithContentData
{
    public int $id;
    public int $creatorId;
    public int $recipientId;
    public bool $local;
    public string $content;
}
