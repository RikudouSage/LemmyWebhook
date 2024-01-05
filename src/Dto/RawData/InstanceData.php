<?php

namespace App\Dto\RawData;

use App\Attribute\RawDataType;

#[RawDataType(table: 'instance')]
final readonly class InstanceData
{
    public int $id;
    public string $domain;
}
