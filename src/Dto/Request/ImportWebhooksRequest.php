<?php

namespace App\Dto\Request;

final readonly class ImportWebhooksRequest
{
    public function __construct(
        public string $configuration,
    ) {
    }
}
