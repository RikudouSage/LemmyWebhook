<?php

namespace App\Dto\Request;

final readonly class RefreshTokenRequest
{
    public function __construct(
        public string $refreshToken,
    ) {
    }
}
