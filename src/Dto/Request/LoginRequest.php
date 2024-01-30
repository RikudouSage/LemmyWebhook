<?php

namespace App\Dto\Request;

use SensitiveParameter;

final readonly class LoginRequest
{
    public function __construct(
        public string $username,
        #[SensitiveParameter]
        public string $password,
    ) {
    }
}
