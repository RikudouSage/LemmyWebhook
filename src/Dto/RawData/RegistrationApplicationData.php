<?php

namespace App\Dto\RawData;

use App\Attribute\RawDataType;

#[RawDataType(table: 'registration_application')]
final readonly class RegistrationApplicationData
{
    public int $id;
    public int $localUserId;
    public string $answer;
    public ?int $adminId;
    public ?string $denyReason;
}
