<?php

namespace App\Dto\RawData;

use DateTimeInterface;

final readonly class ModBanData
{
    public int $id;
    public int $modPersonId;
    public int $otherPersonId;
    public ?string $reason;
    public bool $banned;
    public DateTimeInterface $expires;
    public DateTimeInterface $when;
}
