<?php

namespace App\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class RawDataType
{
    public function __construct(
        public string $table,
    ) {
    }
}
