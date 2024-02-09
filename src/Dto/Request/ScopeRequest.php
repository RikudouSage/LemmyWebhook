<?php

namespace App\Dto\Request;

final readonly class ScopeRequest
{
    public function __construct(
        public string $scope,
    ) {
    }
}
