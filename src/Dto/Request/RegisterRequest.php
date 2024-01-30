<?php

namespace App\Dto\Request;

use SensitiveParameter;

final readonly class RegisterRequest
{
    /**
     * @param array<string> $scopes
     */
    public function __construct(
        public string $username,
        #[SensitiveParameter]
        public string $password,
        public array $scopes = [],
    ) {
    }
}
