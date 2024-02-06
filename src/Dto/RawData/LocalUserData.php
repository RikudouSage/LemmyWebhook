<?php

namespace App\Dto\RawData;

use App\Attribute\RawDataType;

#[RawDataType(table: 'local_user')]
final readonly class LocalUserData
{
    public int $id;
    public int $personId;
    public ?string $email;
    public bool $emailVerified;
    public bool $acceptedApplication;
    public bool $admin;
    public bool $totp2faEnabled;
}
