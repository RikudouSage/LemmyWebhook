<?php

namespace App\Dto\RawData;

use App\Enum\DatabaseOperation;
use DateTimeInterface;

/**
 * @template TData of object
 */
final readonly class RawData
{
    public DateTimeInterface $timestamp;
    public DatabaseOperation $operation;
    public string $schema;
    public string $table;

    /**
     * @var TData
     */
    public object $data;
}
